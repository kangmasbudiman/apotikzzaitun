import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/api_client.dart';
import '../../core/formatters.dart';
import '../../core/theme.dart';
import '../../models/dashboard.dart';
import '../../services/master_service.dart';

/// Layar ringkasan untuk pemilik/admin & kasir.
///
/// Untuk kasir, angka otomatis dibatasi ke transaksinya sendiri oleh server.
class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});

  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  DashboardData? _data;
  bool _memuat = true;
  String? _galat;

  @override
  void initState() {
    super.initState();
    _muat();
  }

  Future<void> _muat() async {
    setState(() {
      _memuat = true;
      _galat = null;
    });

    try {
      final data = await TransactionService(context.read<ApiClient>())
          .dashboard();

      if (!mounted) return;
      setState(() {
        _data = data;
        _memuat = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _galat = e is ApiException ? e.message : 'Gagal memuat dashboard.';
        _memuat = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_memuat) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_galat != null) {
      return Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(32),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              const Icon(
                Icons.cloud_off_rounded,
                size: 48,
                color: AppTheme.muted,
              ),
              const SizedBox(height: 12),
              // Pesan error dari server bisa panjang (mengandung petunjuk),
              // jadi dibungkus Flexible supaya tidak meluber.
              Flexible(
                child: Text(
                  _galat!,
                  textAlign: TextAlign.center,
                  style: const TextStyle(fontSize: 13, height: 1.5),
                ),
              ),
              const SizedBox(height: 16),
              OutlinedButton.icon(
                onPressed: _muat,
                icon: const Icon(Icons.refresh_rounded),
                label: const Text('Coba Lagi'),
              ),
            ],
          ),
        ),
      );
    }

    final d = _data!;
    final r = d.ringkasan;

    return RefreshIndicator(
      onRefresh: _muat,
      child: ListView(
        padding: const EdgeInsets.fromLTRB(14, 14, 14, 90),
        children: [
          // ---------- kartu utama ----------
          _KartuOmzet(omzet: r.omzet, transaksi: r.transaksi),
          const SizedBox(height: 12),

          Row(
            children: [
              Expanded(
                child: _KartuStatistik(
                  label: 'Laba Kotor',
                  nilai: Fmt.rp(r.labaKotor),
                  ikon: Icons.trending_up_rounded,
                  warna: AppTheme.success,
                  catatan: 'sebelum pengeluaran',
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: _KartuStatistik(
                  label: 'Laba Bersih',
                  nilai: Fmt.rp(r.labaBersih),
                  ikon: Icons.account_balance_wallet_rounded,
                  warna: r.labaBersih >= 0
                      ? AppTheme.primaryDark
                      : AppTheme.danger,
                  catatan: 'setelah pengeluaran',
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),

          Row(
            children: [
              Expanded(
                child: _KartuStatistik(
                  label: 'Item Terjual',
                  nilai: '${r.itemTerjual}',
                  ikon: Icons.inventory_2_rounded,
                  warna: AppTheme.muted,
                  catatan: 'total kuantitas',
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: _KartuStatistik(
                  label: 'Rata-rata',
                  nilai: Fmt.rp(r.rataPerTransaksi),
                  ikon: Icons.receipt_long_rounded,
                  warna: AppTheme.muted,
                  catatan: 'per transaksi',
                ),
              ),
            ],
          ),

          if (r.transaksiBatal > 0) ...[
            const SizedBox(height: 10),
            _Peringatan(
              ikon: Icons.cancel_rounded,
              warna: AppTheme.danger,
              pesan:
                  '${r.transaksiBatal} transaksi dibatalkan hari ini. Jangan lupa cek cepat.',
            ),
          ],

          const SizedBox(height: 18),

          // ---------- grafik ----------
          _JudulBagian('Omzet 7 Hari Terakhir'),
          const SizedBox(height: 10),
          _GrafikOmzet(data: d.grafik),

          const SizedBox(height: 18),

          // ---------- top obat ----------
          if (d.topObat.isNotEmpty) ...[
            _JudulBagian('Obat Terlaris'),
            const SizedBox(height: 10),
            _Kartu(
              isi: Column(
                children: [
                  for (var i = 0; i < d.topObat.length; i++) ...[
                    if (i > 0) const Divider(height: 1),
                    _BarisTop(
                      urutan: i + 1,
                      item: d.topObat[i],
                      // Skala relatif terhadap penjualan terbesar.
                      rasio: d.topObat.first.qty == 0
                          ? 0
                          : d.topObat[i].qty / d.topObat.first.qty,
                    ),
                  ],
                ],
              ),
            ),
            const SizedBox(height: 18),
          ],

          // ---------- kedaluwarsa ----------
          if (d.mendekatiKedaluwarsa.isNotEmpty) ...[
            _JudulBagian('Mendekati Kedaluwarsa'),
            const SizedBox(height: 4),
            Text(
              'Hanya batch yang masih ada stoknya. Batch paling depan keluar dulu '
              '(FEFO) saat kasir menjual obat ini.',
              style: TextStyle(
                fontSize: 11.5,
                color: AppTheme.muted,
                fontStyle: FontStyle.italic,
              ),
            ),
            const SizedBox(height: 10),
            _Kartu(
              isi: Column(
                children: [
                  for (var i = 0; i < d.mendekatiKedaluwarsa.length; i++) ...[
                    if (i > 0) const Divider(height: 1),
                    _BarisKedaluwarsa(item: d.mendekatiKedaluwarsa[i]),
                  ],
                ],
              ),
            ),
            const SizedBox(height: 18),
          ],

          // ---------- kesehatan batch (admin) ----------
          if (d.kesehatanBatch != null) ...[
            _JudulBagian('Kesehatan Batch'),
            const SizedBox(height: 10),
            _Kartu(
              isi: Row(
                children: [
                  Icon(
                    d.kesehatanBatch!.sehat
                        ? Icons.verified_outlined
                        : Icons.warning_amber_rounded,
                    size: 20,
                    color: d.kesehatanBatch!.sehat
                        ? AppTheme.success
                        : AppTheme.warning,
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      d.kesehatanBatch!.pesan,
                      style: TextStyle(
                        fontSize: 12.5,
                        fontWeight: FontWeight.w600,
                        color: d.kesehatanBatch!.sehat
                            ? AppTheme.success
                            : AppTheme.warning,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 18),
          ],

          // ---------- stok kritis ----------
          if (d.stokKritis.isNotEmpty) ...[
            _JudulBagian('Stok Kritis (${d.stokKritis.length})'),
            const SizedBox(height: 10),
            _Kartu(
              isi: Column(
                children: [
                  for (var i = 0; i < d.stokKritis.length; i++) ...[
                    if (i > 0) const Divider(height: 1),
                    _BarisStokKritis(item: d.stokKritis[i]),
                  ],
                ],
              ),
            ),
            const SizedBox(height: 18),
          ],

          // ---------- transaksi terbaru ----------
          if (d.transaksiTerbaru.isNotEmpty) ...[
            _JudulBagian('Transaksi Terbaru'),
            const SizedBox(height: 10),
            _Kartu(
              isi: Column(
                children: [
                  for (var i = 0; i < d.transaksiTerbaru.length; i++) ...[
                    if (i > 0) const Divider(height: 1),
                    _BarisTransaksi(item: d.transaksiTerbaru[i]),
                  ],
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }
}

// -------------------------------------------------------------- bagian

class _JudulBagian extends StatelessWidget {
  const _JudulBagian(this.teks);

  final String teks;

  @override
  Widget build(BuildContext context) {
    return Text(
      teks.toUpperCase(),
      style: const TextStyle(
        fontSize: 11.5,
        fontWeight: FontWeight.w800,
        color: AppTheme.primaryDeep,
        letterSpacing: 0.5,
      ),
    );
  }
}

class _Kartu extends StatelessWidget {
  const _Kartu({required this.isi});

  final Widget isi;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(padding: const EdgeInsets.all(4), child: isi),
    );
  }
}

/// Kartu omzet besar — angka terpenting, jadi dibuat menonjol.
class _KartuOmzet extends StatelessWidget {
  const _KartuOmzet({required this.omzet, required this.transaksi});

  final int omzet;
  final int transaksi;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(18),
        gradient: const LinearGradient(
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
          colors: [AppTheme.primaryDeep, AppTheme.primaryDark],
        ),
        boxShadow: [
          BoxShadow(
            color: AppTheme.primaryDark.withValues(alpha: 0.30),
            blurRadius: 16,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'OMZET HARI INI',
                  style: TextStyle(
                    color: Colors.white70,
                    fontSize: 10.5,
                    fontWeight: FontWeight.w800,
                    letterSpacing: 0.8,
                  ),
                ),
                const SizedBox(height: 6),
                FittedBox(
                  fit: BoxFit.scaleDown,
                  alignment: Alignment.centerLeft,
                  child: Text(
                    Fmt.rp(omzet),
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 30,
                      fontWeight: FontWeight.w900,
                      letterSpacing: -0.5,
                    ),
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  '$transaksi transaksi berhasil',
                  style: const TextStyle(color: Colors.white70, fontSize: 12),
                ),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.14),
              borderRadius: BorderRadius.circular(14),
            ),
            child: const Icon(
              Icons.point_of_sale_rounded,
              color: Colors.white,
              size: 26,
            ),
          ),
        ],
      ),
    );
  }
}

class _KartuStatistik extends StatelessWidget {
  const _KartuStatistik({
    required this.label,
    required this.nilai,
    required this.ikon,
    required this.warna,
    required this.catatan,
  });

  final String label;
  final String nilai;
  final IconData ikon;
  final Color warna;
  final String catatan;

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(14),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(6),
                  decoration: BoxDecoration(
                    color: warna.withValues(alpha: 0.12),
                    borderRadius: BorderRadius.circular(9),
                  ),
                  child: Icon(ikon, size: 16, color: warna),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    label,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w700,
                      color: AppTheme.muted,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 10),
            FittedBox(
              fit: BoxFit.scaleDown,
              alignment: Alignment.centerLeft,
              child: Text(
                nilai,
                style: TextStyle(
                  fontSize: 19,
                  fontWeight: FontWeight.w900,
                  color: warna,
                  letterSpacing: -0.3,
                ),
              ),
            ),
            const SizedBox(height: 2),
            Text(
              catatan,
              style: const TextStyle(fontSize: 10.5, color: AppTheme.muted),
            ),
          ],
        ),
      ),
    );
  }
}

/// Grafik batang sederhana — digambar sendiri supaya tidak menambah
/// dependency chart yang berat.
class _GrafikOmzet extends StatelessWidget {
  const _GrafikOmzet({required this.data});

  final List<GrafikHari> data;

  @override
  Widget build(BuildContext context) {
    if (data.isEmpty) {
      return const Card(
        child: Padding(
          padding: EdgeInsets.all(24),
          child: Text(
            'Belum ada data penjualan.',
            style: TextStyle(color: AppTheme.muted),
          ),
        ),
      );
    }

    final maks = data.fold<int>(0, (a, e) => e.omzet > a ? e.omzet : a);

    return Card(
      child: Padding(
        padding: const EdgeInsets.fromLTRB(14, 18, 14, 12),
        child: Column(
          children: [
            SizedBox(
              // Cukup tinggi untuk bar (maks 104) + label omzet + nama hari.
              // Kalau terlalu pendek, Column di dalam batang akan meluber.
              height: 152,
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  for (final h in data)
                    Expanded(
                      child: _Batang(
                        data: h,
                        maksimal: maks,
                        // Hari ini diberi warna penuh, hari lain lebih pucat.
                        menonjol: h.tanggal == data.last.tanggal,
                      ),
                    ),
                ],
              ),
            ),
            const SizedBox(height: 8),
            const Divider(height: 1),
            const SizedBox(height: 10),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Container(
                  width: 9,
                  height: 9,
                  decoration: BoxDecoration(
                    color: AppTheme.primary,
                    borderRadius: BorderRadius.circular(3),
                  ),
                ),
                const SizedBox(width: 6),
                const Text(
                  'Omzet 7 hari terakhir',
                  style: TextStyle(fontSize: 11.5, color: AppTheme.muted),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class _Batang extends StatelessWidget {
  const _Batang({
    required this.data,
    required this.maksimal,
    required this.menonjol,
  });

  final GrafikHari data;
  final int maksimal;
  final bool menonjol;

  @override
  Widget build(BuildContext context) {
    // Batang minimum 4px supaya hari tanpa penjualan tetap terlihat.
    // Maksimum 96 supaya bar + label masih muat di SizedBox grafik.
    final rasio = maksimal == 0 ? 0.0 : data.omzet / maksimal;
    final tinggi = data.omzet == 0 ? 4.0 : (4 + rasio * 96);

    return Column(
      mainAxisAlignment: MainAxisAlignment.end,
      children: [
        if (data.omzet > 0)
          Padding(
            padding: const EdgeInsets.only(bottom: 4),
            child: FittedBox(
              fit: BoxFit.scaleDown,
              child: Text(
                // Ringkas supaya tidak memenuhi kolom sempit.
                _ringkas(data.omzet),
                style: TextStyle(
                  fontSize: 9.5,
                  fontWeight: FontWeight.w800,
                  color: menonjol ? AppTheme.primaryDark : AppTheme.muted,
                ),
              ),
            ),
          ),
        Container(
          width: 18,
          height: tinggi,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(6),
            gradient: LinearGradient(
              begin: Alignment.bottomCenter,
              end: Alignment.topCenter,
              colors: menonjol
                  ? [AppTheme.primaryDark, AppTheme.primary]
                  : [AppTheme.mint, AppTheme.mint.withValues(alpha: 0.55)],
            ),
          ),
        ),
        const SizedBox(height: 6),
        Text(
          data.label,
          style: TextStyle(
            fontSize: 10.5,
            fontWeight: menonjol ? FontWeight.w800 : FontWeight.w600,
            color: menonjol ? AppTheme.primaryDeep : AppTheme.muted,
          ),
        ),
      ],
    );
  }

  /// 1.234.567 -> "1,2jt"
  static String _ringkas(int nilai) {
    if (nilai >= 1000000) {
      final jt = nilai / 1000000;
      return '${jt.toStringAsFixed(jt >= 10 ? 0 : 1)}jt';
    }
    if (nilai >= 1000) return '${(nilai / 1000).round()}rb';
    return '$nilai';
  }
}

class _BarisTop extends StatelessWidget {
  const _BarisTop({
    required this.urutan,
    required this.item,
    required this.rasio,
  });

  final int urutan;
  final TopObat item;
  final double rasio;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      child: Row(
        children: [
          SizedBox(
            width: 24,
            child: Text(
              '$urutan',
              style: const TextStyle(
                fontWeight: FontWeight.w900,
                color: AppTheme.muted,
                fontSize: 12,
              ),
            ),
          ),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  item.obat,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 5),
                // Batang proporsional qty.
                ClipRRect(
                  borderRadius: BorderRadius.circular(4),
                  child: LinearProgressIndicator(
                    value: rasio.clamp(0.0, 1.0),
                    minHeight: 5,
                    backgroundColor: AppTheme.bg,
                    valueColor: const AlwaysStoppedAnimation(AppTheme.primary),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(width: 10),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                '${item.qty} pcs',
                style: const TextStyle(
                  fontWeight: FontWeight.w800,
                  fontSize: 12.5,
                ),
              ),
              Text(
                Fmt.rp(item.omzet),
                style: const TextStyle(fontSize: 11, color: AppTheme.muted),
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _BarisKedaluwarsa extends StatelessWidget {
  const _BarisKedaluwarsa({required this.item});

  final ObatKedaluwarsa item;

  @override
  Widget build(BuildContext context) {
    // Warna makin merah kalau makin mepet.
    final warna = item.sudahLewat
        ? AppTheme.danger
        : item.hariLagi <= 30
        ? AppTheme.danger
        : item.hariLagi <= 60
        ? AppTheme.warning
        : AppTheme.primaryDark;

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      child: Row(
        children: [
          Container(
            width: 4,
            height: 36,
            decoration: BoxDecoration(
              color: warna,
              borderRadius: BorderRadius.circular(3),
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  item.obat,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  // Tampilkan stok pada batch yang dimaksud, bukan stok total
                  // obat — kalau obat punya 500 pcs tapi hanya 12 pcs-nya yang
                  // mau kedaluwarsa, yang perlu act adalah 12 itu.
                  'Exp ${Fmt.tanggal(item.kedaluwarsa)}'
                  '${item.stokBatch != null ? ' · batch ini ${item.stokBatch} pcs' : ' · stok ${item.stock}'}',
                  style: const TextStyle(fontSize: 11, color: AppTheme.muted),
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
            decoration: BoxDecoration(
              color: warna.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Text(
              item.sudahLewat
                  ? 'Lewat ${item.hariLagi.abs()}h'
                  : '${item.hariLagi} hari',
              style: TextStyle(
                fontSize: 10.5,
                fontWeight: FontWeight.w800,
                color: warna,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _BarisStokKritis extends StatelessWidget {
  const _BarisStokKritis({required this.item});

  final ObatKritis item;

  @override
  Widget build(BuildContext context) {
    final warna = item.stock <= 0 ? AppTheme.danger : AppTheme.warning;

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 9),
      child: Row(
        children: [
          Expanded(
            child: Text(
              item.obat,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: const TextStyle(fontSize: 13),
            ),
          ),
          const SizedBox(width: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
            decoration: BoxDecoration(
              color: warna.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(20),
            ),
            child: Text(
              item.stock <= 0 ? 'Habis' : '${item.stock} pcs',
              style: TextStyle(
                fontSize: 10.5,
                fontWeight: FontWeight.w800,
                color: warna,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _BarisTransaksi extends StatelessWidget {
  const _BarisTransaksi({required this.item});

  final TransaksiSingkat item;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      child: Row(
        children: [
          Container(
            width: 32,
            height: 32,
            decoration: BoxDecoration(
              color: (item.status ? AppTheme.success : AppTheme.danger)
                  .withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(9),
            ),
            child: Icon(
              item.status
                  ? Icons.check_circle_outline_rounded
                  : Icons.cancel_outlined,
              size: 17,
              color: item.status ? AppTheme.success : AppTheme.danger,
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  item.id,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontSize: 11.5,
                    fontFamily: 'monospace',
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  '${item.namaUser} · ${Fmt.jam(item.tanggal)}',
                  style: const TextStyle(fontSize: 11, color: AppTheme.muted),
                ),
              ],
            ),
          ),
          const SizedBox(width: 8),
          Text(
            Fmt.rp(item.total),
            style: TextStyle(
              fontWeight: FontWeight.w800,
              fontSize: 13,
              color: item.status ? AppTheme.primaryDark : AppTheme.muted,
            ),
          ),
        ],
      ),
    );
  }
}

class _Peringatan extends StatelessWidget {
  const _Peringatan({
    required this.ikon,
    required this.warna,
    required this.pesan,
  });

  final IconData ikon;
  final Color warna;
  final String pesan;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: warna.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: warna.withValues(alpha: 0.25)),
      ),
      child: Row(
        children: [
          Icon(ikon, color: warna, size: 20),
          const SizedBox(width: 10),
          Expanded(
            child: Text(
              pesan,
              style: TextStyle(
                fontSize: 12.5,
                color: warna,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ],
      ),
    );
  }
}
