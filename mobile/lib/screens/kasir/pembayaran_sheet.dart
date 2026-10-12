import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';

import '../../core/api_client.dart';
import '../../core/formatters.dart';
import '../../core/theme.dart';
import '../../providers/cart_provider.dart';
import '../../services/master_service.dart';

/// Sheet pembayaran: pilih nominal uang (cepat) atau ketik manual,
/// lalu simpan transaksi ke server.
class PembayaranSheet extends StatefulWidget {
  const PembayaranSheet({
    super.key,
    required this.api,
    required this.namaKasir,
  });

  final ApiClient api;
  final String namaKasir;

  @override
  State<PembayaranSheet> createState() => _PembayaranSheetState();
}

class _PembayaranSheetState extends State<PembayaranSheet> {
  final _inputCtl = TextEditingController();

  bool _sembunyi = true;

  @override
  void initState() {
    super.initState();
    _isiUlang();
  }

  @override
  void dispose() {
    _inputCtl.dispose();
    super.dispose();
  }

  int get _totalBayar =>
      int.tryParse(_inputCtl.text.replaceAll(RegExp(r'[^0-9]'), '')) ?? 0;

  void _isiUlang() {
    final cart = context.read<CartProvider>();
    _inputCtl.text = '${cart.grandTotal}';
  }

  void _pilihUang(int nominal) {
    setState(() => _inputCtl.text = '$nominal');
  }

  void _ketik(String angka) {
    final v = _totalBayar.toString();
    final baru = (v == '0') ? angka : v + angka;
    setState(() => _inputCtl.text = baru);
  }

  void _hapus() {
    final v = _totalBayar.toString();
    if (v.length <= 1) {
      setState(() => _inputCtl.text = '0');
      return;
    }
    setState(() => _inputCtl.text = v.substring(0, v.length - 1));
  }

  Future<void> _simpan() async {
    final cart = context.read<CartProvider>();
    final navigator = Navigator.of(context);
    final messenger = ScaffoldMessenger.of(context);

    final hasil = await cart.simpan(
      service: TransactionService(widget.api),
      totalBayar: _totalBayar,
    );

    if (!mounted) return;

    if (hasil == null) {
      messenger.showSnackBar(
        SnackBar(
          content: Text(cart.pesanGagal ?? 'Gagal menyimpan transaksi.'),
          backgroundColor: AppTheme.danger,
        ),
      );
      return;
    }

    // Tutup sheet dan SERAHKAN hasilnya ke layar kasir.
    //
    // Dulu struk ditampilkan dari sini, tapi `context` milik sheet sudah
    // tidak aktif begitu sheet ditutup — itu memicu error
    // "Looking up a deactivated widget's ancestor is unsafe".
    // Sekarang yang popping cukup mengembalikan nilainya; pemanggil
    // (yang masih hidup) yang menampilkan struk.
    navigator.pop(hasil);
  }

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartProvider>();
    final total = cart.grandTotal;
    final bayar = _totalBayar;
    final kurang = bayar < total;
    final kembalian = bayar - total;

    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      child: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.fromLTRB(18, 14, 18, 18),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Center(
                child: Container(
                  width: 42,
                  height: 4,
                  decoration: BoxDecoration(
                    color: AppTheme.line,
                    borderRadius: BorderRadius.circular(4),
                  ),
                ),
              ),
              const SizedBox(height: 16),

              const Text(
                'Pembayaran',
                style: TextStyle(fontSize: 19, fontWeight: FontWeight.w800),
              ),
              const SizedBox(height: 4),
              Text(
                '${cart.jumlahItem} item · Subtotal ${Fmt.rp(cart.subtotal)}',
                style: const TextStyle(color: AppTheme.muted, fontSize: 13),
              ),

              const SizedBox(height: 18),

              // Diskon
              Row(
                children: [
                  const Text(
                    'Diskon',
                    style: TextStyle(fontWeight: FontWeight.w600),
                  ),
                  const Spacer(),
                  SizedBox(
                    width: 130,
                    child: TextFormField(
                      initialValue: cart.diskon == 0 ? '' : '${cart.diskon}',
                      keyboardType: TextInputType.number,
                      textAlign: TextAlign.right,
                      inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                      decoration: const InputDecoration(
                        prefixText: 'Rp ',
                        isDense: true,
                      ),
                      onChanged: (v) => cart.setDiskon(int.tryParse(v) ?? 0),
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 14),

              // Total tagihan
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 14,
                ),
                decoration: BoxDecoration(
                  color: AppTheme.primary.withValues(alpha: 0.08),
                  borderRadius: BorderRadius.circular(14),
                ),
                child: Row(
                  children: [
                    const Text(
                      'Total Tagihan',
                      style: TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                    const Spacer(),
                    Text(
                      Fmt.rp(total),
                      style: const TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.w900,
                        color: AppTheme.primaryDark,
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 18),

              const Text(
                'Uang Diterima',
                style: TextStyle(fontWeight: FontWeight.w600),
              ),
              const SizedBox(height: 8),

              TextField(
                controller: _inputCtl,
                keyboardType: TextInputType.number,
                inputFormatters: [FilteringTextInputFormatter.digitsOnly],
                style: const TextStyle(
                  fontSize: 26,
                  fontWeight: FontWeight.w900,
                ),
                decoration: InputDecoration(
                  prefixText: 'Rp ',
                  prefixStyle: const TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.w700,
                  ),
                  suffixIcon: IconButton(
                    icon: Icon(
                      _sembunyi
                          ? Icons.visibility_off_outlined
                          : Icons.visibility_outlined,
                    ),
                    onPressed: () => setState(() => _sembunyi = !_sembunyi),
                  ),
                ),
                onChanged: (_) => setState(() {}),
              ),

              // Kembalian / kurang bayar
              const SizedBox(height: 10),
              if (!kurang)
                Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    const Text(
                      'Kembalian ',
                      style: TextStyle(color: AppTheme.muted),
                    ),
                    Text(
                      Fmt.rp(kembalian),
                      style: TextStyle(
                        fontSize: 19,
                        fontWeight: FontWeight.w900,
                        color: kembalian > 0
                            ? AppTheme.primaryDark
                            : AppTheme.muted,
                      ),
                    ),
                  ],
                )
              else
                Row(
                  mainAxisAlignment: MainAxisAlignment.end,
                  children: [
                    Icon(
                      Icons.error_outline,
                      size: 17,
                      color: AppTheme.danger.withValues(alpha: 0.9),
                    ),
                    const SizedBox(width: 6),
                    Text(
                      'Kurang ${Fmt.rp(total - bayar)}',
                      style: const TextStyle(
                        color: AppTheme.danger,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ],
                ),

              const SizedBox(height: 14),

              // Nominal cepat — sisa kembalian yang sering dipakai.
              _NominalCepat(total: total, bayar: bayar, onPilih: _pilihUang),

              const SizedBox(height: 14),

              // Keypad
              _Keypad(
                onAngka: _ketik,
                onHapus: _hapus,
                onSimpan: _simpan,
                simpanAktif: !kurang && !cart.sedangSimpan,
              ),
            ],
          ),
        ),
      ),
    );
  }
}

/// Tombol nominal cepat: pas, 50 rb, 100 rb, dst di atas total.
class _NominalCepat extends StatelessWidget {
  const _NominalCepat({
    required this.total,
    required this.bayar,
    required this.onPilih,
  });

  final int total;
  final int bayar;
  final void Function(int) onPilih;

  static const _steps = [10000, 20000, 50000, 100000];

  @override
  Widget build(BuildContext context) {
    // Bulatkan ke atas kelipatan terdekat supaya mucah dihitung kasir.
    final ceil = total <= 0 ? 0 : ((total + 9999) ~/ 10000) * 10000;

    final opsi = <int>{
      total,
      ceil,
      ..._steps.map((s) => ((total / s).ceil()) * s),
    }.where((v) => v > 0).toList()..sort();

    return SizedBox(
      height: 38,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        itemCount: opsi.length,
        separatorBuilder: (_, __) => const SizedBox(width: 8),
        itemBuilder: (_, i) {
          final v = opsi[i];
          return ChoiceChip(
            label: Text(Fmt.rp(v)),
            selected: bayar == v,
            onSelected: (_) => onPilih(v),
          );
        },
      ),
    );
  }
}

class _Keypad extends StatelessWidget {
  const _Keypad({
    required this.onAngka,
    required this.onHapus,
    required this.onSimpan,
    required this.simpanAktif,
  });

  final void Function(String) onAngka;
  final VoidCallback onHapus;
  final VoidCallback onSimpan;
  final bool simpanAktif;

  @override
  Widget build(BuildContext context) {
    Widget angka(String label) =>
        _Tombol(label: label, onTap: () => onAngka(label));

    // Grid 4 kolom: 1-9, lalu [backspace][0][00]
    const jarak = SizedBox(width: 8);

    return Column(
      children: [
        Row(
          children: [
            angka('1'),
            jarak,
            angka('2'),
            jarak,
            angka('3'),
            jarak,
            _Tombol(label: '←', onTap: onHapus, warna: AppTheme.danger),
          ],
        ),
        const SizedBox(height: 8),
        Row(
          children: [
            angka('4'),
            jarak,
            angka('5'),
            jarak,
            angka('6'),
            jarak,
            _Tombol(label: 'C', onTap: onHapus, warna: AppTheme.muted),
          ],
        ),
        const SizedBox(height: 8),
        Row(
          children: [
            angka('7'),
            jarak,
            angka('8'),
            jarak,
            angka('9'),
            jarak,
            _Tombol(
              label: '00',
              onTap: () => onAngka('00'),
              warna: AppTheme.muted,
            ),
          ],
        ),
        const SizedBox(height: 8),
        Row(
          children: [
            angka('0'),
            jarak,
            angka('00'),
            jarak,
            angka('000'),
            const SizedBox.shrink(),
          ],
        ),
        const SizedBox(height: 12),
        SizedBox(
          height: 50,
          child: ElevatedButton.icon(
            onPressed: simpanAktif ? onSimpan : null,
            icon: const Icon(Icons.check_rounded),
            label: const Text('Simpan Transaksi'),
          ),
        ),
      ],
    );
  }
}

class _Tombol extends StatelessWidget {
  const _Tombol({required this.label, required this.onTap, this.warna});

  final String label;
  final VoidCallback onTap;
  final Color? warna;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Material(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        child: InkWell(
          borderRadius: BorderRadius.circular(12),
          onTap: onTap,
          child: Container(
            height: 48,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppTheme.line),
            ),
            child: Text(
              label,
              style: TextStyle(
                fontSize: 19,
                fontWeight: FontWeight.w800,
                color: warna ?? AppTheme.text,
              ),
            ),
          ),
        ),
      ),
    );
  }
}
