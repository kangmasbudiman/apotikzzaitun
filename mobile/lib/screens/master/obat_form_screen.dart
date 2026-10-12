import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import '../../core/api_client.dart';
import '../../core/theme.dart';
import '../../models/medicine.dart';
import '../../services/master_service.dart';

/// Tambah / ubah obat.
///
/// Catatan penting: kolom stok SENGAJA tidak ada di form ini. Stok hanya
/// bertambah lewat modul Pembelian, jadi kasir/gudang tidak bisa menambah
/// stok lewat aplikasi.
class ObatFormScreen extends StatefulWidget {
  const ObatFormScreen({
    super.key,
    required this.api,
    required this.kategori,
    this.obat,
  });

  final ApiClient api;
  final List<Category> kategori;
  final Medicine? obat;

  @override
  State<ObatFormScreen> createState() => _ObatFormScreenState();
}

class _ObatFormScreenState extends State<ObatFormScreen> {
  final _formKey = GlobalKey<FormState>();

  late final _nama = TextEditingController(text: widget.obat?.nama ?? '');
  late final _barcode = TextEditingController(text: widget.obat?.barcode ?? '');
  late final _noisbn = TextEditingController(text: widget.obat?.noisbn ?? '');
  late final _suplier = TextEditingController(text: widget.obat?.suplier ?? '');
  late final _tahun = TextEditingController(text: widget.obat?.tahun ?? '');
  late final _pokok = TextEditingController(
    text: widget.obat == null ? '' : '${widget.obat!.hargaPokok}',
  );
  late final _jual = TextEditingController(
    text: widget.obat == null ? '' : '${widget.obat!.hargaJual}',
  );
  late final _disc = TextEditingController(
    text: widget.obat == null ? '0' : '${widget.obat!.disc.round()}',
  );

  late int? _kategoriId =
      widget.obat?.idKategori ??
      (widget.kategori.isNotEmpty ? widget.kategori.first.id : null);
  late int? _rakId = widget.obat?.idRak;

  List<Rack> _rak = const [];

  bool _menyimpan = false;

  @override
  void initState() {
    super.initState();
    _muatRak();
  }

  Future<void> _muatRak() async {
    try {
      final r = await MasterService(widget.api).rak();
      if (!mounted) return;
      setState(() {
        _rak = r;
        _rakId ??= r.isNotEmpty ? r.first.id : null;
      });
    } catch (_) {
      // Daftar rak gagal diambil — biarkan user coba lagi / ubah di web.
    }
  }

  @override
  void dispose() {
    _nama.dispose();
    _barcode.dispose();
    _noisbn.dispose();
    _suplier.dispose();
    _tahun.dispose();
    _pokok.dispose();
    _jual.dispose();
    _disc.dispose();
    super.dispose();
  }

  Future<void> _simpan() async {
    if (!_formKey.currentState!.validate()) return;
    setState(() => _menyimpan = true);

    final body = {
      'idKategori': _kategoriId,
      'idRak': _rakId,
      'barcode': _barcode.text.trim(),
      'obat': _nama.text.trim(),
      'noisbn': _noisbn.text.trim().isEmpty ? null : _noisbn.text.trim(),
      'suplier': _suplier.text.trim(),
      'tahun': _tahun.text.trim().isEmpty ? null : _tahun.text.trim(),
      'disc': double.tryParse(_disc.text) ?? 0,
      'hargaPokok': int.tryParse(_pokok.text) ?? 0,
      'hargaJual': int.tryParse(_jual.text) ?? 0,
    };

    try {
      final svc = MasterService(widget.api);
      if (widget.obat == null) {
        await svc.simpanObat(body);
      } else {
        await svc.ubahObat(widget.obat!.id, body);
      }

      if (!mounted) return;
      Navigator.pop(context, true);
    } catch (e) {
      if (!mounted) return;
      setState(() => _menyimpan = false);

      ScaffoldMessenger.of(context)
        ..hideCurrentSnackBar()
        ..showSnackBar(
          SnackBar(
            content: Text(
              e is ApiException ? e.pesanPerField : 'Gagal menyimpan.',
            ),
            backgroundColor: AppTheme.danger,
          ),
        );
    }
  }

  Future<void> _hapusObat() async {
    final o = widget.obat!;
    try {
      await MasterService(widget.api).hapusObat(o.id);
      if (!mounted) return;
      Navigator.pop(context, true);
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context)
        ..hideCurrentSnackBar()
        ..showSnackBar(
          SnackBar(
            content: Text(
              e is ApiException ? e.pesanPerField : 'Gagal menghapus.',
            ),
            backgroundColor: AppTheme.danger,
          ),
        );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.obat == null ? 'Tambah Obat' : 'Ubah Obat'),
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            _label('Nama obat'),
            TextFormField(
              controller: _nama,
              textCapitalization: TextCapitalization.characters,
              decoration: const InputDecoration(
                hintText: 'mis. PARACETAMOL 500 TAB',
              ),
              validator: (v) => (v == null || v.trim().isEmpty)
                  ? 'Nama obat wajib diisi'
                  : null,
            ),
            const SizedBox(height: 14),

            _label('Barcode'),
            TextFormField(
              controller: _barcode,
              keyboardType: TextInputType.number,
              inputFormatters: [FilteringTextInputFormatter.digitsOnly],
              decoration: const InputDecoration(
                hintText: 'EAN / barcode produk',
              ),
              validator: (v) => (v == null || v.trim().isEmpty)
                  ? 'Barcode wajib diisi'
                  : null,
            ),
            const SizedBox(height: 14),

            _label('Kategori'),
            DropdownButtonFormField<int>(
              initialValue: _kategoriId,
              items: [
                for (final k in widget.kategori)
                  DropdownMenuItem(value: k.id, child: Text(k.nama)),
              ],
              onChanged: (v) => setState(() => _kategoriId = v),
              validator: (v) => v == null ? 'Kategori wajib dipilih' : null,
            ),
            const SizedBox(height: 14),

            _label('Rak'),
            DropdownButtonFormField<int>(
              initialValue: _rakId,
              hint: const Text('Pilih rak'),
              items: [
                for (final r in _rak)
                  DropdownMenuItem(value: r.id, child: Text(r.nama)),
              ],
              onChanged: (v) => setState(() => _rakId = v),
              validator: (v) => v == null ? 'Rak wajib dipilih' : null,
            ),
            const SizedBox(height: 6),

            if (widget.obat?.stock != null)
              Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: Row(
                  children: [
                    const Icon(
                      Icons.info_outline,
                      size: 16,
                      color: AppTheme.muted,
                    ),
                    const SizedBox(width: 6),
                    Text(
                      'Stok saat ini ${widget.obat!.stock}. Stok hanya bisa '
                      'diubah lewat menu Pembelian.',
                      style: const TextStyle(
                        fontSize: 12,
                        color: AppTheme.muted,
                      ),
                    ),
                  ],
                ),
              ),

            _label('Suplier'),
            TextFormField(
              controller: _suplier,
              decoration: const InputDecoration(hintText: 'mis. KIMIA FARMA'),
              validator: (v) => (v == null || v.trim().isEmpty)
                  ? 'Suplier wajib diisi'
                  : null,
            ),
            const SizedBox(height: 14),

            _label('Harga Pokok (modal)'),
            TextFormField(
              controller: _pokok,
              keyboardType: TextInputType.number,
              inputFormatters: [FilteringTextInputFormatter.digitsOnly],
              decoration: const InputDecoration(prefixText: 'Rp '),
              validator: (v) {
                final n = int.tryParse(v ?? '');
                if (n == null || n <= 0) return 'Harga pokok harus di atas 0';
                return null;
              },
            ),
            const SizedBox(height: 14),

            _label('Harga Jual'),
            TextFormField(
              controller: _jual,
              keyboardType: TextInputType.number,
              inputFormatters: [FilteringTextInputFormatter.digitsOnly],
              decoration: const InputDecoration(prefixText: 'Rp '),
              validator: (v) {
                final n = int.tryParse(v ?? '');
                if (n == null || n <= 0) return 'Harga jual harus di atas 0';

                final pokok = int.tryParse(_pokok.text) ?? 0;
                if (pokok > 0 && n < pokok) {
                  return 'Harga jual tidak boleh di bawah harga pokok';
                }
                return null;
              },
            ),
            const SizedBox(height: 14),

            _label('Diskon per item (%)'),
            TextFormField(
              controller: _disc,
              keyboardType: TextInputType.number,
              inputFormatters: [FilteringTextInputFormatter.digitsOnly],
              decoration: const InputDecoration(suffixText: '%'),
            ),

            const SizedBox(height: 26),
            ElevatedButton(
              onPressed: _menyimpan ? null : _simpan,
              child: _menyimpan
                  ? const SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(
                        strokeWidth: 2.4,
                        color: Colors.white,
                      ),
                    )
                  : Text(
                      widget.obat == null ? 'Simpan Obat' : 'Simpan Perubahan',
                    ),
            ),

            if (widget.obat != null) ...[
              const SizedBox(height: 10),
              OutlinedButton.icon(
                onPressed: _menyimpan ? null : _hapusObat,
                style: OutlinedButton.styleFrom(
                  foregroundColor: AppTheme.danger,
                  side: BorderSide(
                    color: AppTheme.danger.withValues(alpha: 0.4),
                  ),
                ),
                icon: const Icon(Icons.delete_outline_rounded),
                label: const Text('Hapus Obat'),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _label(String t) => Padding(
    padding: const EdgeInsets.only(bottom: 6),
    child: Text(
      t,
      style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700),
    ),
  );
}
