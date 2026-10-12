import 'package:flutter/material.dart';

import '../core/formatters.dart';
import '../core/theme.dart';
import '../models/medicine.dart';

/// Panel kecil yang menampilkan info batch & kedaluwarsa.
///
/// Dipakai di layar kasir: kasir bisa tahu batch mana yang akan keluar lebih
/// dulu sebelum menutup struk ke pelanggan.
class PanelBatch extends StatelessWidget {
  const PanelBatch({
    super.key,
    required this.info,
    required this.obat,
    this.ringkas = true,
  });

  final InfoBatch info;
  final String obat;

  /// true = hanya tampilkan ringkasan (1 baris).
  /// false = tampilkan daftar semua batch.
  final bool ringkas;

  static ({Color fg, Color bg}) _warna(String tingkat) {
    return switch (tingkat) {
      'lewat' => (
        fg: AppTheme.danger,
        bg: AppTheme.danger.withValues(alpha: 0.12),
      ),
      'kritis' => (
        fg: AppTheme.danger,
        bg: AppTheme.danger.withValues(alpha: 0.10),
      ),
      'mepet' => (
        fg: AppTheme.warning,
        bg: AppTheme.warning.withValues(alpha: 0.12),
      ),
      _ => (fg: AppTheme.success, bg: AppTheme.success.withValues(alpha: 0.12)),
    };
  }

  static String _label(String tingkat, int hari) {
    return switch (tingkat) {
      'lewat' => 'Lewat ${hari.abs()} hari',
      'kritis' => '$hari hari lagi',
      'mepet' => '$hari hari lagi',
      _ => '$hari hari lagi',
    };
  }

  @override
  Widget build(BuildContext context) {
    if (!info.punyaEkspirasi) {
      return _Baris(
        ikon: Icons.inventory_2_outlined,
        warna: AppTheme.muted,
        teks: 'Tidak ada data kedaluwarsa untuk obat ini',
      );
    }

    // Batch terdekat = yang paling dulu keluar (FEFO).
    final dekat = info.sisaPerBatch.isNotEmpty ? info.sisaPerBatch.first : null;

    if (dekat == null) {
      return _Baris(
        ikon: Icons.inventory_2_outlined,
        warna: AppTheme.muted,
        teks: 'Tidak ada batch tersisa',
      );
    }

    final c = _warna(dekat.tingkat);

    if (ringkas) {
      return Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 7),
        decoration: BoxDecoration(
          color: c.bg,
          borderRadius: BorderRadius.circular(10),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(Icons.schedule_rounded, size: 14, color: c.fg),
            const SizedBox(width: 6),
            Flexible(
              child: Text(
                'FEFO · ${dekat.noBatch ?? '-'} · exp '
                '${Fmt.tanggal(dekat.expired)} (${_label(dekat.tingkat, dekat.hariLagi)})',
                maxLines: 1,
                overflow: TextOverflow.ellipsis,
                style: TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w700,
                  color: c.fg,
                ),
              ),
            ),
          ],
        ),
      );
    }

    return _DaftarBatch(info: info);
  }
}

class _DaftarBatch extends StatelessWidget {
  const _DaftarBatch({required this.info});

  final InfoBatch info;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            const Icon(
              Icons.schedule_rounded,
              size: 16,
              color: AppTheme.primaryDark,
            ),
            const SizedBox(width: 8),
            const Text(
              'Urutan keluar (FEFO)',
              style: TextStyle(
                fontSize: 12,
                fontWeight: FontWeight.w800,
                color: AppTheme.primaryDeep,
              ),
            ),
            const Spacer(),
            Text(
              '${info.jumlahBatch} batch',
              style: const TextStyle(fontSize: 11, color: AppTheme.muted),
            ),
          ],
        ),
        const SizedBox(height: 8),
        ...info.sisaPerBatch.asMap().entries.map((e) {
          final b = e.value;
          final c = PanelBatch._warna(b.tingkat);
          final pertama = e.key == 0;

          return Container(
            margin: const EdgeInsets.only(bottom: 6),
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: pertama ? c.bg : Colors.white,
              borderRadius: BorderRadius.circular(10),
              border: Border.all(
                color: pertama ? c.fg.withValues(alpha: 0.35) : AppTheme.line,
              ),
            ),
            child: Row(
              children: [
                if (pertama) ...[
                  const Icon(
                    Icons.arrow_downward_rounded,
                    size: 14,
                    color: AppTheme.primaryDark,
                  ),
                  const SizedBox(width: 6),
                ],
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        b.noBatch ?? 'Tanpa nomor batch',
                        style: const TextStyle(
                          fontSize: 12.5,
                          fontWeight: FontWeight.w700,
                          fontFamily: 'monospace',
                        ),
                      ),
                      const SizedBox(height: 2),
                      Text(
                        'Exp ${Fmt.tanggal(b.expired)} · ${b.stok} pcs · modal ${Fmt.rp(b.hargaPokok)}',
                        style: const TextStyle(
                          fontSize: 11,
                          color: AppTheme.muted,
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 8,
                    vertical: 3,
                  ),
                  decoration: BoxDecoration(
                    color: c.bg,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    PanelBatch._label(b.tingkat, b.hariLagi),
                    style: TextStyle(
                      fontSize: 10,
                      fontWeight: FontWeight.w800,
                      color: c.fg,
                    ),
                  ),
                ),
              ],
            ),
          );
        }),
      ],
    );
  }
}

class _Baris extends StatelessWidget {
  const _Baris({required this.ikon, required this.warna, required this.teks});

  final IconData ikon;
  final Color warna;
  final String teks;

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(ikon, size: 14, color: warna),
        const SizedBox(width: 6),
        Flexible(
          child: Text(
            teks,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            style: TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w600,
              color: warna,
            ),
          ),
        ),
      ],
    );
  }
}
