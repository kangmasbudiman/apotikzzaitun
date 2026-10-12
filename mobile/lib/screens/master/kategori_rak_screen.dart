import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/api_client.dart';
import '../../core/theme.dart';
import '../../models/medicine.dart';
import '../../providers/auth_provider.dart';
import '../../services/master_service.dart';
import '../../widgets/responsive_data_view.dart';

/// Kelola Kategori & Rak dalam satu layar dengan dua tab.
///
/// Keduanya结构性 sama: daftar pendek + tambah/ubah/hapus, jadi cukup satu
/// widget generik dengan callback.
class KategoriRakScreen extends StatefulWidget {
  const KategoriRakScreen({super.key});

  @override
  State<KategoriRakScreen> createState() => _KategoriRakScreenState();
}

class _KategoriRakScreenState extends State<KategoriRakScreen>
    with SingleTickerProviderStateMixin {
  late final TabController _tab = TabController(length: 2, vsync: this);

  List<Category> _kategori = const [];
  List<Rack> _rak = const [];

  bool _memuat = true;

  @override
  void initState() {
    super.initState();
    _muat();
  }

  @override
  void dispose() {
    _tab.dispose();
    super.dispose();
  }

  MasterService get _svc => MasterService(context.read<ApiClient>());

  Future<void> _muat() async {
    setState(() => _memuat = true);

    try {
      final k = await _svc.kategori();
      final r = await _svc.rak();
      if (!mounted) return;
      setState(() {
        _kategori = k;
        _rak = r;
        _memuat = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() => _memuat = false);

      // Error yang bukan ApiException (mis. TypeError dari parsing) tetap
      // harus terlihat. Kalau disembunyikan, layar tampil "Belum ada
      // kategori" padahal server sebenarnya mengirim data — persis seperti
      // yang terjadi saat array polos salah dibaca.
      final pesan = e is ApiException
          ? e.message
          : 'Gagal memuat kategori & rak. Silakan coba lagi.';

      ScaffoldMessenger.of(context)
        ..hideCurrentSnackBar()
        ..showSnackBar(SnackBar(content: Text(pesan)));
    }
  }

  Future<void> _tambahKategori() async {
    final nama = await _inputNama('Tambah Kategori');
    if (nama == null) return;

    try {
      await _svc.simpanKategori(nama);
      await _muat();
    } catch (e) {
      _tampilGagal(e);
    }
  }

  Future<void> _ubahKategori(Category c) async {
    final nama = await _inputNama('Ubah Kategori', awal: c.nama);
    if (nama == null) return;

    try {
      await _svc.ubahKategori(c.id, nama);
      await _muat();
    } catch (e) {
      _tampilGagal(e);
    }
  }

  Future<void> _hapusKategori(Category c) async {
    if (!await _konfirmasi('Hapus kategori "${c.nama}"?')) return;

    try {
      await _svc.hapusKategori(c.id);
      await _muat();
    } catch (e) {
      _tampilGagal(e);
    }
  }

  Future<void> _tambahRak() async {
    final nama = await _inputNama('Tambah Rak');
    if (nama == null) return;

    try {
      await _svc.simpanRak(nama);
      await _muat();
    } catch (e) {
      _tampilGagal(e);
    }
  }

  Future<void> _ubahRak(Rack r) async {
    final nama = await _inputNama('Ubah Rak', awal: r.nama);
    if (nama == null) return;

    try {
      await _svc.ubahRak(r.id, nama);
      await _muat();
    } catch (e) {
      _tampilGagal(e);
    }
  }

  Future<void> _hapusRak(Rack r) async {
    if (!await _konfirmasi('Hapus rak "${r.nama}"?')) return;

    try {
      await _svc.hapusRak(r.id);
      await _muat();
    } catch (e) {
      _tampilGagal(e);
    }
  }

  void _tampilGagal(Object e) {
    ScaffoldMessenger.of(context)
      ..hideCurrentSnackBar()
      ..showSnackBar(
        SnackBar(
          content: Text(e is ApiException ? e.pesanPerField : 'Gagal.'),
          backgroundColor: AppTheme.danger,
        ),
      );
  }

  Future<bool> _konfirmasi(String pesan) async {
    final ya = await showDialog<bool>(
      context: context,
      builder: (c) => AlertDialog(
        title: const Text('Konfirmasi'),
        content: Text(pesan),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(c, false),
            child: const Text('Batal'),
          ),
          FilledButton(
            style: FilledButton.styleFrom(backgroundColor: AppTheme.danger),
            onPressed: () => Navigator.pop(c, true),
            child: const Text('Hapus'),
          ),
        ],
      ),
    );
    return ya == true;
  }

  Future<String?> _inputNama(String judul, {String awal = ''}) async {
    final ctl = TextEditingController(text: awal);

    return showDialog<String>(
      context: context,
      builder: (c) => AlertDialog(
        title: Text(judul),
        content: TextField(
          controller: ctl,
          autofocus: true,
          textCapitalization: TextCapitalization.words,
          decoration: const InputDecoration(hintText: 'Nama'),
          onSubmitted: (v) => Navigator.pop(c, v.trim()),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(c),
            child: const Text('Batal'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(c, ctl.text.trim()),
            child: const Text('Simpan'),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final bolehUbah = context.watch<AuthProvider>().user?.bisaMaster ?? false;

    return Column(
      children: [
        if (bolehUbah)
          TabBar(
            controller: _tab,
            labelColor: AppTheme.primaryDark,
            unselectedLabelColor: AppTheme.muted,
            indicatorColor: AppTheme.primary,
            tabs: [
              Tab(text: 'Kategori (${_kategori.length})'),
              Tab(text: 'Rak (${_rak.length})'),
            ],
          ),
        Expanded(
          child: _memuat
              ? const Center(child: CircularProgressIndicator())
              : TabBarView(
                  controller: _tab,
                  children: [_daftarKategori(bolehUbah), _daftarRak(bolehUbah)],
                ),
        ),
      ],
    );
  }

  Widget _daftarKategori(bool bolehUbah) {
    return ResponsiveDataView<Category>(
      items: _kategori,
      kosongPesan: 'Belum ada kategori.',
      onTap: bolehUbah ? _ubahKategori : null,
      kolom: [
        Kolom(
          judul: 'ID',
          sel: (_, c) => Text(
            '${c.id}',
            style: const TextStyle(color: AppTheme.muted, fontSize: 12.5),
          ),
        ),
        Kolom(
          judul: 'Nama Kategori',
          sel: (_, c) =>
              Text(c.nama, style: const TextStyle(fontWeight: FontWeight.w700)),
        ),
        if (bolehUbah)
          Kolom(
            judul: '',
            sel: (_, c) => IconButton(
              icon: const Icon(
                Icons.delete_outline_rounded,
                color: AppTheme.danger,
                size: 20,
              ),
              onPressed: () => _hapusKategori(c),
            ),
          ),
      ],
      kartu: (_, c) => Row(
        children: [
          CircleAvatar(
            radius: 16,
            backgroundColor: AppTheme.primary.withValues(alpha: 0.12),
            child: Text(
              '${c.id}',
              style: const TextStyle(
                fontSize: 11,
                fontWeight: FontWeight.w800,
                color: AppTheme.primaryDark,
              ),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              c.nama,
              style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14),
            ),
          ),
          if (bolehUbah)
            IconButton(
              icon: const Icon(
                Icons.delete_outline_rounded,
                color: AppTheme.danger,
                size: 20,
              ),
              onPressed: () => _hapusKategori(c),
            ),
        ],
      ),
      footer: bolehUbah
          ? SafeArea(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(14, 0, 14, 14),
                child: SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: _tambahKategori,
                    icon: const Icon(Icons.add_rounded),
                    label: const Text('Tambah Kategori'),
                  ),
                ),
              ),
            )
          : null,
    );
  }

  Widget _daftarRak(bool bolehUbah) {
    return ResponsiveDataView<Rack>(
      items: _rak,
      kosongPesan: 'Belum ada rak.',
      onTap: bolehUbah ? _ubahRak : null,
      kolom: [
        Kolom(
          judul: 'ID',
          sel: (_, r) => Text(
            '${r.id}',
            style: const TextStyle(color: AppTheme.muted, fontSize: 12.5),
          ),
        ),
        Kolom(
          judul: 'Nama Rak',
          sel: (_, r) =>
              Text(r.nama, style: const TextStyle(fontWeight: FontWeight.w700)),
        ),
        if (bolehUbah)
          Kolom(
            judul: '',
            sel: (_, r) => IconButton(
              icon: const Icon(
                Icons.delete_outline_rounded,
                color: AppTheme.danger,
                size: 20,
              ),
              onPressed: () => _hapusRak(r),
            ),
          ),
      ],
      kartu: (_, r) => Row(
        children: [
          CircleAvatar(
            radius: 16,
            backgroundColor: AppTheme.lime.withValues(alpha: 0.25),
            child: const Icon(
              Icons.shelves,
              size: 18,
              color: AppTheme.primaryDeep,
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              r.nama,
              style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 14),
            ),
          ),
          if (bolehUbah)
            IconButton(
              icon: const Icon(
                Icons.delete_outline_rounded,
                color: AppTheme.danger,
                size: 20,
              ),
              onPressed: () => _hapusRak(r),
            ),
        ],
      ),
      footer: bolehUbah
          ? SafeArea(
              child: Padding(
                padding: const EdgeInsets.fromLTRB(14, 0, 14, 14),
                child: SizedBox(
                  width: double.infinity,
                  child: ElevatedButton.icon(
                    onPressed: _tambahRak,
                    icon: const Icon(Icons.add_rounded),
                    label: const Text('Tambah Rak'),
                  ),
                ),
              ),
            )
          : null,
    );
  }
}
