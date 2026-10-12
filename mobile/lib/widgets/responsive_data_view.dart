import 'package:flutter/material.dart';

import '../core/config.dart';
import '../core/theme.dart';

/// Definisi satu kolom tabel.
///
/// [hanyaDiLebar] menandai kolom yang hanya muncul di tablet/PC — misalnya
/// barcode atau nama kategori. Di HP kolom ini disembunyikan supaya tabel
/// tidak perlu di-scroll ke samping.
class Kolom<T> {
  const Kolom({
    required this.judul,
    required this.sel,
    this.hanyaDiLebar = false,
    this.rataKanan = false,
  });

  final String judul;
  final Widget Function(BuildContext context, T item) sel;
  final bool hanyaDiLebar;
  final bool rataKanan;
}

/// Tabel yang menyesuaikan sendiri dengan lebar layar.
///
/// Menggantikan `DataTable` polos yang punya tiga masalah di HP:
///  1. **Sempit** — 6 kolom dalam 360dp membuat tiap sel cuma 60dp.
///     Di bawah [AppConfig.breakpointTable] tabel diganti daftar kartu.
///  2. **Kolom menumpuk** — kolom yang tidak penting disembunyikan di layar
///     sedang lewat flag [Kolom.hanyaDiLebar].
///  3. **Susah diklik** — di HP tiap baris diklik lewat kartu, bukan sel.
///
/// Perilaku:
///  * < 720dp   : daftar kartu
///  * 720–1000dp: tabel ringkas (kolom `hanyaDiLebar` disembunyikan)
///  * \>= 1000dp : tabel lengkap + semua kolom
class ResponsiveDataView<T> extends StatelessWidget {
  const ResponsiveDataView({
    super.key,
    required this.items,
    required this.kolom,
    required this.kartu,
    this.kosongPesan = 'Belum ada data.',
    this.onTap,
    this.accent,
    this.footer,
    this.onLoadMore,
  });

  final List<T> items;

  /// Definisi kolom — dipakai di mode tabel saja.
  final List<Kolom<T>> kolom;

  /// Tampilan satu baris di mode HP.
  final Widget Function(BuildContext context, T item) kartu;

  final String kosongPesan;
  final void Function(T item)? onTap;

  /// Warna garis aksen di sisi kiri kartu / kolom pertama tabel.
  final Color? Function(T item)? accent;

  /// Widget di bawah daftar (mis. tombol tambah / paginasi).
  final Widget? footer;

  /// Dipanggil saat daftar sudah ~320px dari bawah, untuk infinite scroll.
  final VoidCallback? onLoadMore;

  /// True kalau daftar ini masih punya halaman berikutnya.
  @override
  Widget build(BuildContext context) {
    if (items.isEmpty && footer == null) {
      return _Kosong(pesan: kosongPesan);
    }

    final isi = _isi(context);

    if (footer == null) return isi;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Expanded(child: isi),
        footer!,
      ],
    );
  }

  Widget _isi(BuildContext context) {
    if (items.isEmpty) return _Kosong(pesan: kosongPesan);

    final lebar = MediaQuery.sizeOf(context).width;

    return lebar < AppConfig.breakpointTable
        ? _modeKartu(context)
        : _modeTabel(
            context,
            layarLebar: lebar >= AppConfig.breakpointFullTable,
          );
  }

  /// Infinite scroll dipasang lewat NotificationListener supaya tidak perlu
  /// memegang ScrollController milik ListView/DataTable di bawahnya.
  bool _deteksiAkhir(ScrollNotification n) {
    if (onLoadMore == null) return false;

    if (n.metrics.axis == Axis.vertical &&
        n.metrics.pixels > n.metrics.maxScrollExtent - 320) {
      onLoadMore!();
    }
    return false;
  }

  // ------------------------------------------------------------------- HP

  Widget _modeKartu(BuildContext context) {
    return NotificationListener<ScrollNotification>(
      onNotification: _deteksiAkhir,
      child: ListView.separated(
        padding: const EdgeInsets.fromLTRB(14, 12, 14, 16),
        itemCount: items.length,
        separatorBuilder: (_, __) => const SizedBox(height: 10),
        itemBuilder: (context, i) {
          final item = items[i];

          return Material(
            color: Colors.white,
            borderRadius: BorderRadius.circular(14),
            child: InkWell(
              borderRadius: BorderRadius.circular(14),
              onTap: onTap == null ? null : () => onTap!(item),
              child: Container(
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(14),
                  border: Border.all(color: AppTheme.line),
                ),
                clipBehavior: Clip.antiAlias,
                child: Row(
                  // JANGAN pakai CrossAxisAlignment.stretch di sini.
                  //
                  // Baris ini berada di dalam ListView, yang memberi tinggi
                  // tak terbatas kepada anaknya. `stretch` lalu memaksa
                  // anaknya setinggi Infinity dan Flutter melempar:
                  //   "BoxConstraints forces an infinite height."
                  //
                  // Karena itu garis aksen memakai tinggi tetap.
                  crossAxisAlignment: CrossAxisAlignment.center,
                  children: [
                    if (accent != null)
                      Container(width: 4, height: 56, color: accent!(item)),
                    Expanded(
                      child: Padding(
                        padding: const EdgeInsets.all(12),
                        child: kartu(context, item),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          );
        },
      ),
    );
  }

  // ------------------------------------------------------- Tablet / PC

  Widget _modeTabel(BuildContext context, {required bool layarLebar}) {
    final tampil = layarLebar
        ? kolom
        : kolom.where((k) => !k.hanyaDiLebar).toList(growable: false);

    return NotificationListener<ScrollNotification>(
      onNotification: _deteksiAkhir,
      child: LayoutBuilder(
        builder: (context, constraints) {
          return SingleChildScrollView(
            scrollDirection: Axis.horizontal,
            child: ConstrainedBox(
              constraints: BoxConstraints(minWidth: constraints.maxWidth),
              child: SingleChildScrollView(
                padding: const EdgeInsets.only(bottom: 16),
                child: DataTable(
                  headingRowColor: WidgetStatePropertyAll(
                    AppTheme.primary.withValues(alpha: 0.06),
                  ),
                  headingRowHeight: 46,
                  dataRowMinHeight: 52,
                  dataRowMaxHeight: 64,
                  columnSpacing: 22,
                  horizontalMargin: 18,
                  dividerThickness: 0.8,
                  showCheckboxColumn: false,
                  headingTextStyle: const TextStyle(
                    fontWeight: FontWeight.w800,
                    fontSize: 12.5,
                    color: AppTheme.primaryDeep,
                    letterSpacing: 0.3,
                  ),
                  columns: [
                    // Kolom pertama khusus garis aksen status.
                    const DataColumn(label: SizedBox.shrink()),
                    for (final k in tampil)
                      DataColumn(
                        label: Text(k.judul.toUpperCase()),
                        numeric: k.rataKanan,
                      ),
                  ],
                  rows: [
                    for (final item in items)
                      DataRow(
                        onSelectChanged: onTap == null
                            ? null
                            : (_) => onTap!(item),
                        cells: [
                          DataCell(
                            Container(
                              width: 4,
                              height: 34,
                              color: accent?.call(item),
                            ),
                          ),
                          for (final k in tampil)
                            DataCell(
                              Align(
                                alignment: k.rataKanan
                                    ? Alignment.centerRight
                                    : Alignment.centerLeft,
                                child: k.sel(context, item),
                              ),
                            ),
                        ],
                      ),
                  ],
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}

class _Kosong extends StatelessWidget {
  const _Kosong({required this.pesan});

  final String pesan;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(40),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.inbox_rounded, size: 52, color: AppTheme.muted),
            const SizedBox(height: 12),
            Text(
              pesan,
              textAlign: TextAlign.center,
              style: const TextStyle(color: AppTheme.muted, fontSize: 14),
            ),
          ],
        ),
      ),
    );
  }
}

/// Baris "label: nilai" — dipakai di dalam kartu mode HP.
class InfoBaris extends StatelessWidget {
  const InfoBaris({
    super.key,
    required this.label,
    required this.nilai,
    this.rataKanan = false,
    this.tebal,
  });

  final String label;
  final Widget nilai;
  final bool rataKanan;
  final bool? tebal;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(top: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 96,
            child: Text(
              label,
              style: const TextStyle(
                fontSize: 12.5,
                color: AppTheme.muted,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
          Expanded(
            child: Align(
              alignment: rataKanan
                  ? Alignment.centerRight
                  : Alignment.centerLeft,
              child: DefaultTextStyle.merge(
                style: TextStyle(
                  fontSize: 13.5,
                  fontWeight: (tebal ?? false)
                      ? FontWeight.w800
                      : FontWeight.w600,
                  color: AppTheme.text,
                ),
                child: nilai,
              ),
            ),
          ),
        ],
      ),
    );
  }
}

/// Chip status stok (aman/menipis/habis) untuk kartu & tabel.
class ChipStok extends StatelessWidget {
  const ChipStok({super.key, required this.status, required this.jumlah});

  final String status;
  final int jumlah;

  @override
  Widget build(BuildContext context) {
    final c = AppTheme.stokBadge(status);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
      decoration: BoxDecoration(
        color: c.bg,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        '${AppTheme.labelStok(status)} · $jumlah',
        style: TextStyle(
          color: c.fg,
          fontSize: 11.5,
          fontWeight: FontWeight.w800,
        ),
      ),
    );
  }
}
