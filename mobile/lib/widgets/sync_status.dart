import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../core/theme.dart';
import '../providers/sync_provider.dart';
import '../services/connectivity_service.dart';

/// Indikator status koneksi & antrean transaksi.
///
/// Menjawab pertanyaan yang paling sering ditanya kasir saat internet mati:
/// * "Transaksi tadi masuk tidak?" → lihat badge antrean.
/// * "Kenapa tidak bisa simpan?" → lihat chip "Offline".
///
/// Sengaja dibuat kecil: informatif, tapi tidak menutupi kasir.
class SyncStatusChip extends StatelessWidget {
  const SyncStatusChip({super.key, this.compact = false});

  /// Mode compact hanya untuk ruang sempit (mis. di dalam sheet).
  final bool compact;

  @override
  Widget build(BuildContext context) {
    final sync = context.watch<SyncProvider>();
    final online = ConnectivityService.value.value;

    // Di HP 400px, header sudah penuh dengan badge keranjang + titik status +
    // avatar. Chip offline ber-ikon saja supaya tidak overflow.
    final kompak = MediaQuery.sizeOf(context).width < 500;

    // Prioritas 1: ada transaksi menunggu — ini yang paling perlu dilihat kasir.
    if (sync.jumlahAntrean > 0) {
      return _Chip(
        ikon: Icons.cloud_off_rounded,
        warna: AppTheme.warning,
        label: '${sync.jumlahAntrean} transaksi menunggu',
        kompak: kompak,
        onTap: () => bukaLembarAntrean(context),
      );
    }

    // Prioritas 2: sedang mengirim ulang.
    if (sync.status == StatusSync.mengirim) {
      return _Chip(
        ikon: Icons.sync_rounded,
        warna: AppTheme.primary,
        label: 'Mengirim ulang…',
        kompak: kompak,
      );
    }

    // Prioritas 3: offline tanpa antrean.
    if (!online) {
      return _Chip(
        ikon: Icons.cloud_off_rounded,
        warna: AppTheme.danger,
        label: 'Offline',
        kompak: kompak,
      );
    }

    if (compact) return const SizedBox.shrink();

    return _Chip(
      ikon: Icons.cloud_done_rounded,
      warna: AppTheme.success,
      label: 'Online',
      kompak: kompak,
    );
  }
}

/// Buka bottom sheet antrean transaksi.
///
/// Dipanggil dari [SyncStatusChip] saat kasir mengetuk chip "transaksi
/// menunggu". Sengaja dibuat top-level supaya bisa dipakai dari mana saja.
void bukaLembarAntrean(BuildContext context) {
  showModalBottomSheet<void>(
    context: context,
    isScrollControlled: true,
    backgroundColor: Colors.transparent,
    builder: (_) => const LembarAntrean(),
  );
}

class _Chip extends StatelessWidget {
  const _Chip({
    required this.ikon,
    required this.warna,
    required this.label,
    this.onTap,
    this.kompak = false,
  });

  final IconData ikon;
  final Color warna;
  final String label;
  final VoidCallback? onTap;

  /// Mode ikon saja — dipakai di layar sempit (HP) di mana teks akan
  /// membuat header overflow.
  final bool kompak;

  @override
  Widget build(BuildContext context) {
    final warnaLatar = warna.withValues(alpha: 0.12);

    Widget isi = Material(
      color: warnaLatar,
      borderRadius: BorderRadius.circular(20),
      child: InkWell(
        borderRadius: BorderRadius.circular(20),
        onTap: onTap,
        child: Padding(
          padding: EdgeInsets.symmetric(
            horizontal: kompak ? 9 : 10,
            vertical: 6,
          ),
          child: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              Icon(ikon, size: kompak ? 16 : 14, color: warna),
              if (!kompak) ...[
                const SizedBox(width: 5),
                Text(
                  label,
                  style: TextStyle(
                    fontSize: 10.5,
                    fontWeight: FontWeight.w800,
                    color: warna,
                  ),
                ),
              ],
            ],
          ),
        ),
      ),
    );

    // Tooltip hanya ada kalau memang ada pesan. Tooltip dengan message null
    // melempar assertion "Either message or richMessage must be specified".
    if (onTap == null) return isi;

    return Tooltip(
      message: 'Ketuk untuk melihat transaksi yang menunggu dikirim',
      child: isi,
    );
  }
}

/// Bottom sheet berisi transaksi yang belum sempat terkirim.
///
/// Sengaja menampilkan `percobaan` & `pesanError` — kalau transaksi yang
/// sama gagal berulang-ulang, owner tahu bahwa masalahnya kemungkinan besar
/// di server (bukan sekadar internet putus-putus).
class LembarAntrean extends StatelessWidget {
  const LembarAntrean({super.key});

  @override
  Widget build(BuildContext context) {
    final sync = context.watch<SyncProvider>();

    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      child: SafeArea(
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
            Padding(
              padding: const EdgeInsets.fromLTRB(18, 16, 18, 6),
              child: Row(
                children: [
                  const Icon(Icons.cloud_off_rounded, color: AppTheme.warning),
                  const SizedBox(width: 10),
                  const Expanded(
                    child: Text(
                      'Transaksi Menunggu',
                      style: TextStyle(
                        fontSize: 17,
                        fontWeight: FontWeight.w800,
                      ),
                    ),
                  ),
                  SyncStatusChip(),
                ],
              ),
            ),
            if (sync.antrean.isEmpty)
              const Padding(
                padding: EdgeInsets.all(24),
                child: Text(
                  'Tidak ada transaksi menunggu.',
                  textAlign: TextAlign.center,
                  style: TextStyle(color: AppTheme.muted),
                ),
              )
            else
              Flexible(
                child: ListView.separated(
                  shrinkWrap: true,
                  padding: const EdgeInsets.fromLTRB(14, 6, 14, 8),
                  itemCount: sync.antrean.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 8),
                  itemBuilder: (_, i) {
                    final t = sync.antrean[i];

                    return Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        border: Border.all(color: AppTheme.line),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Expanded(
                                child: Text(
                                  'Dibuat ${_waktu(t.dibuatPada)}',
                                  style: const TextStyle(
                                    fontSize: 12.5,
                                    fontWeight: FontWeight.w700,
                                  ),
                                ),
                              ),
                              Text(
                                '${t.percobaan}× gagal',
                                style: const TextStyle(
                                  fontSize: 10.5,
                                  color: AppTheme.muted,
                                ),
                              ),
                            ],
                          ),
                          if (t.pesanError != null &&
                              t.pesanError!.isNotEmpty) ...[
                            const SizedBox(height: 4),
                            Text(
                              t.pesanError!,
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                fontSize: 11,
                                color: AppTheme.danger,
                              ),
                            ),
                          ],
                        ],
                      ),
                    );
                  },
                ),
              ),
            if (sync.antrean.isNotEmpty)
              Padding(
                padding: const EdgeInsets.fromLTRB(16, 8, 16, 14),
                child: Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () =>
                            sync.buang(sync.antrean.first.idAntrean),
                        icon: const Icon(
                          Icons.delete_outline_rounded,
                          size: 18,
                        ),
                        label: const Text('Buang yang paling lama'),
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: FilledButton.icon(
                        onPressed: () => sync.sinkronkan(),
                        icon: const Icon(Icons.sync_rounded, size: 18),
                        label: const Text('Kirim Ulang'),
                      ),
                    ),
                  ],
                ),
              ),
          ],
        ),
      ),
    );
  }

  static String _waktu(DateTime t) {
    String dua(int v) => v.toString().padLeft(2, '0');

    return '${dua(t.hour)}:${dua(t.minute)}';
  }
}
