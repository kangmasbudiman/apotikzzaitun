import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/api_client.dart';
import '../../core/config.dart';
import '../../core/formatters.dart';
import '../../core/theme.dart';
import '../../models/cart_item.dart';
import '../../services/master_service.dart';
import '../../widgets/responsive_data_view.dart';

/// Riwayat penjualan. Kasir hanya melihat transaksinya sendiri
/// (sudah dibatasi server lewat `idUser`).
class RiwayatScreen extends StatefulWidget {
  const RiwayatScreen({super.key});

  @override
  State<RiwayatScreen> createState() => _RiwayatScreenState();
}

class _RiwayatScreenState extends State<RiwayatScreen> {
  final _cariCtl = TextEditingController();
  final List<TransactionRow> _items = [];

  int _page = 1;
  int _lastPage = 1;
  int _total = 0;
  bool _memuatAwal = true;
  bool _memuatLagi = false;

  @override
  void initState() {
    super.initState();
    _muatAwal();
  }

  @override
  void dispose() {
    _cariCtl.dispose();
    super.dispose();
  }

  Future<void> _muatAwal() async {
    setState(() {
      _memuatAwal = true;
      _page = 1;
    });
    await _ambil();
  }

  Future<void> _muatLagi() async {
    if (_memuatLagi || _memuatAwal || _page >= _lastPage) return;

    setState(() => _memuatLagi = true);
    await _ambil(halaman: _page + 1);
  }

  Future<void> _ambil({int? halaman}) async {
    try {
      final res = await TransactionService(context.read<ApiClient>()).riwayat(
        q: _cariCtl.text.trim().isEmpty ? null : _cariCtl.text.trim(),
        page: halaman ?? 1,
        perPage: AppConfig.perPage,
      );

      if (!mounted) return;

      setState(() {
        if (halaman == null || halaman == 1) {
          _items
            ..clear()
            ..addAll(res.items);
        } else {
          _items.addAll(res.items);
        }
        _page = res.page;
        _lastPage = res.lastPage;
        _total = res.total;
        _memuatAwal = false;
        _memuatLagi = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _memuatAwal = false;
        _memuatLagi = false;
      });
      if (e is ApiException) {
        ScaffoldMessenger.of(context)
          ..hideCurrentSnackBar()
          ..showSnackBar(SnackBar(content: Text(e.message)));
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(14, 12, 14, 6),
          child: Row(
            children: [
              Expanded(
                child: TextField(
                  controller: _cariCtl,
                  onSubmitted: (_) => _muatAwal(),
                  decoration: const InputDecoration(
                    hintText: 'Cari nomor transaksi atau nama kasir…',
                    prefixIcon: Icon(Icons.search_rounded, size: 20),
                    isDense: true,
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Text(
                '$_total',
                style: const TextStyle(
                  color: AppTheme.muted,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ],
          ),
        ),
        Expanded(
          child: _memuatAwal
              ? const Center(child: CircularProgressIndicator())
              : RefreshIndicator(
                  onRefresh: _muatAwal,
                  child: ResponsiveDataView<TransactionRow>(
                    items: _items,
                    onLoadMore: _muatLagi,
                    kosongPesan: 'Belum ada transaksi.',
                    footer: _memuatLagi
                        ? const Padding(
                            padding: EdgeInsets.all(14),
                            child: Center(
                              child: SizedBox(
                                width: 22,
                                height: 22,
                                child: CircularProgressIndicator(
                                  strokeWidth: 2.4,
                                ),
                              ),
                            ),
                          )
                        : null,
                    kolom: [
                      Kolom(
                        judul: 'No. Transaksi',
                        sel: (_, t) => Text(
                          t.id,
                          style: const TextStyle(
                            fontSize: 12,
                            fontFamily: 'monospace',
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ),
                      Kolom(
                        judul: 'Kasir',
                        hanyaDiLebar: true,
                        sel: (_, t) => Text(
                          t.namaUser,
                          style: const TextStyle(fontSize: 12.5),
                        ),
                      ),
                      Kolom(
                        judul: 'Waktu',
                        sel: (_, t) => Text(
                          Fmt.tanggalJam(t.tanggal),
                          style: const TextStyle(
                            fontSize: 12.5,
                            color: AppTheme.muted,
                          ),
                        ),
                      ),
                      Kolom(
                        judul: 'Item',
                        rataKanan: true,
                        sel: (_, t) => Text(
                          '${t.jumlahItem}',
                          style: const TextStyle(fontSize: 12.5),
                        ),
                      ),
                      Kolom(
                        judul: 'Total',
                        rataKanan: true,
                        sel: (_, t) => Text(
                          Fmt.rp(t.total),
                          style: const TextStyle(
                            fontWeight: FontWeight.w800,
                            color: AppTheme.primaryDark,
                          ),
                        ),
                      ),
                      Kolom(
                        judul: 'Status',
                        sel: (_, t) => _chipStatus(t.status),
                      ),
                    ],
                    kartu: (_, t) => Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(
                          children: [
                            Expanded(
                              child: Text(
                                t.id,
                                style: const TextStyle(
                                  fontSize: 12.5,
                                  fontFamily: 'monospace',
                                  fontWeight: FontWeight.w700,
                                ),
                              ),
                            ),
                            _chipStatus(t.status),
                          ],
                        ),
                        const SizedBox(height: 4),
                        Text(
                          '${Fmt.sejak(t.tanggal)} · ${t.namaUser} · ${t.jumlahItem} item',
                          style: const TextStyle(
                            fontSize: 12,
                            color: AppTheme.muted,
                          ),
                        ),
                        const SizedBox(height: 6),
                        Text(
                          Fmt.rp(t.total),
                          style: TextStyle(
                            fontSize: 17,
                            fontWeight: FontWeight.w900,
                            color: t.status
                                ? AppTheme.primaryDark
                                : AppTheme.muted,
                            decoration: t.status
                                ? null
                                : TextDecoration.lineThrough,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
        ),
      ],
    );
  }

  Widget _chipStatus(bool ok) {
    final c = ok
        ? (fg: AppTheme.success, bg: AppTheme.success.withValues(alpha: 0.13))
        : (fg: AppTheme.danger, bg: AppTheme.danger.withValues(alpha: 0.12));

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3),
      decoration: BoxDecoration(
        color: c.bg,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        ok ? 'Berhasil' : 'Batal',
        style: TextStyle(
          fontSize: 11,
          fontWeight: FontWeight.w800,
          color: c.fg,
        ),
      ),
    );
  }
}
