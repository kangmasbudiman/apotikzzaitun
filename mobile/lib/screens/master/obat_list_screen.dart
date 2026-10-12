import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/api_client.dart';
import '../../core/config.dart';
import '../../core/formatters.dart';
import '../../core/theme.dart';
import '../../models/medicine.dart';
import '../../providers/auth_provider.dart';
import '../../services/master_service.dart';
import '../../widgets/responsive_data_view.dart';
import 'obat_form_screen.dart';

/// Daftar obat — contoh pemakaian [ResponsiveDataView].
///
/// Di HP setiap obat jadi kartu; di tablet/PC jadi tabel dengan kolom
/// yang semuanya muat tanpa scroll ke samping.
class ObatListScreen extends StatefulWidget {
  const ObatListScreen({super.key});

  @override
  State<ObatListScreen> createState() => _ObatListScreenState();
}

class _ObatListScreenState extends State<ObatListScreen> {
  final _cariCtl = TextEditingController();

  final List<Medicine> _items = [];
  List<Category> _kategori = const [];

  int _page = 1;
  int _lastPage = 1;
  int _total = 0;
  int? _filterKategori;
  String? _filterStok;
  String _sort = 'obat';

  bool _memuatAwal = true;
  bool _memuatLagi = false;

  @override
  void initState() {
    super.initState();
    _muatAwal();
    _muatKategori();
  }

  @override
  void dispose() {
    _cariCtl.dispose();
    super.dispose();
  }

  // ------------------------------------------------------ pemuatan data

  Future<void> _muatAwal() async {
    setState(() {
      _memuatAwal = true;
      _page = 1;
    });

    try {
      final res = await MasterService(context.read<ApiClient>()).obat(
        page: 1,
        perPage: AppConfig.perPage,
        kategoriId: _filterKategori,
        stok: _filterStok,
        sort: _sort,
      );

      if (!mounted) return;
      setState(() {
        _items
          ..clear()
          ..addAll(res.items);
        _total = res.total;
        _lastPage = res.lastPage;
        _memuatAwal = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() => _memuatAwal = false);
      _tampilPesan(e);
    }
  }

  Future<void> _muatLagi() async {
    if (_memuatLagi || _memuatAwal || _page >= _lastPage) return;

    setState(() => _memuatLagi = true);

    try {
      final res = await MasterService(context.read<ApiClient>()).obat(
        page: _page + 1,
        perPage: AppConfig.perPage,
        kategoriId: _filterKategori,
        stok: _filterStok,
        sort: _sort,
      );

      if (!mounted) return;
      setState(() {
        _items.addAll(res.items);
        _page = res.page;
        _lastPage = res.lastPage;
        _memuatLagi = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() => _memuatLagi = false);
    }
  }

  void _tampilPesan(Object e) {
    final pesan = e is ApiException ? e.pesanPerField : 'Terjadi kesalahan.';
    ScaffoldMessenger.of(context)
      ..hideCurrentSnackBar()
      ..showSnackBar(SnackBar(content: Text(pesan)));
  }

  Future<void> _cariObat(String q) async {
    try {
      final res = await MasterService(context.read<ApiClient>()).obat(
        search: q.trim().isEmpty ? null : q.trim(),
        page: 1,
        perPage: AppConfig.perPage,
        kategoriId: _filterKategori,
        stok: _filterStok,
        sort: _sort,
      );

      if (!mounted) return;
      setState(() {
        _items
          ..clear()
          ..addAll(res.items);
        _total = res.total;
        _lastPage = res.lastPage;
        _page = 1;
      });
    } catch (e) {
      _tampilPesan(e);
    }
  }

  Future<void> _muatKategori() async {
    try {
      final res = await MasterService(context.read<ApiClient>()).kategori();
      if (mounted) setState(() => _kategori = res);
    } catch (_) {
      // Filter kategori bersifat opsional.
    }
  }

  // ------------------------------------------------------------------ ui

  Future<void> _bukaForm({Medicine? obat}) async {
    final berubah = await Navigator.of(context).push<bool>(
      MaterialPageRoute(
        builder: (_) => ObatFormScreen(
          api: context.read<ApiClient>(),
          obat: obat,
          kategori: _kategori,
        ),
      ),
    );
    if (berubah == true) _muatAwal();
  }

  Future<void> _hapus(Medicine o) async {
    // Ambil ApiClient SEBELUM ada jeda async (showDialog), karena BuildContext
    // tidak aman dipakai setelah await.
    final svc = MasterService(context.read<ApiClient>());

    final ya = await showDialog<bool>(
      context: context,
      builder: (c) => AlertDialog(
        title: const Text('Hapus obat?'),
        content: Text('"${o.nama}" akan dihapus dari daftar obat.'),
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

    if (ya != true) return;

    try {
      await svc.hapusObat(o.id);
      if (!mounted) return;
      setState(() => _items.removeWhere((e) => e.id == o.id));
      ScaffoldMessenger.of(context)
        ..hideCurrentSnackBar()
        ..showSnackBar(SnackBar(content: Text('${o.nama} dihapus')));
    } catch (e) {
      _tampilPesan(e);
    }
  }

  @override
  Widget build(BuildContext context) {
    final bolehUbah = context.watch<AuthProvider>().user?.bisaMaster ?? false;

    return Column(
      children: [
        _Toolbar(
          controller: _cariCtl,
          kategori: _kategori,
          filterKategori: _filterKategori,
          filterStok: _filterStok,
          sort: _sort,
          total: _total,
          onCari: _cariObat,
          onFilterKategori: (v) {
            setState(() => _filterKategori = v);
            _muatAwal();
          },
          onFilterStok: (v) {
            setState(() => _filterStok = v);
            _muatAwal();
          },
          onSort: (v) {
            setState(() => _sort = v);
            _muatAwal();
          },
          onTambah: bolehUbah ? () => _bukaForm() : null,
        ),
        Expanded(
          child: _memuatAwal
              ? const Center(child: CircularProgressIndicator())
              : RefreshIndicator(
                  onRefresh: _muatAwal,
                  child: ResponsiveDataView<Medicine>(
                    items: _items,
                    onLoadMore: _muatLagi,
                    accent: (o) => AppTheme.stokBadge(o.stokStatus).fg,
                    onTap: bolehUbah ? (o) => _bukaForm(obat: o) : null,
                    kosongPesan: 'Tidak ada obat yang cocok.',
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

                    // Kolom yang dipakai di mode TABEL (tablet/PC).
                    kolom: [
                      Kolom(
                        judul: 'Obat',
                        sel: (_, o) => SizedBox(
                          width: 260,
                          child: Text(
                            o.nama,
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                            style: const TextStyle(fontWeight: FontWeight.w700),
                          ),
                        ),
                      ),
                      Kolom(
                        judul: 'Kategori',
                        sel: (_, o) => Text(
                          o.namaKategori ?? '-',
                          style: const TextStyle(
                            fontSize: 12.5,
                            color: AppTheme.muted,
                          ),
                        ),
                      ),
                      Kolom(
                        judul: 'Rak',
                        hanyaDiLebar: true,
                        sel: (_, o) => Text(
                          o.namaRak ?? '-',
                          style: const TextStyle(
                            fontSize: 12.5,
                            color: AppTheme.muted,
                          ),
                        ),
                      ),
                      Kolom(
                        judul: 'Barcode',
                        hanyaDiLebar: true,
                        sel: (_, o) => Text(
                          o.barcode,
                          style: const TextStyle(
                            fontSize: 12,
                            fontFamily: 'monospace',
                            color: AppTheme.muted,
                          ),
                        ),
                      ),
                      Kolom(
                        judul: 'Modal',
                        rataKanan: true,
                        sel: (_, o) => Text(
                          Fmt.rp(o.hargaPokok),
                          style: const TextStyle(fontSize: 12.5),
                        ),
                      ),
                      Kolom(
                        judul: 'Jual',
                        rataKanan: true,
                        sel: (_, o) => Text(
                          Fmt.rp(o.hargaJual),
                          style: const TextStyle(
                            fontWeight: FontWeight.w800,
                            color: AppTheme.primaryDark,
                          ),
                        ),
                      ),
                      Kolom(
                        judul: 'Stok',
                        sel: (_, o) =>
                            ChipStok(status: o.stokStatus, jumlah: o.stock),
                      ),
                      if (bolehUbah)
                        Kolom(
                          judul: '',
                          sel: (_, o) => IconButton(
                            tooltip: 'Hapus',
                            icon: const Icon(
                              Icons.delete_outline_rounded,
                              color: AppTheme.danger,
                              size: 20,
                            ),
                            onPressed: () => _hapus(o),
                          ),
                        ),
                    ],

                    // Tampilan baris di mode HP (kartu).
                    kartu: (ctx, o) => Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          o.nama,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: const TextStyle(
                            fontWeight: FontWeight.w800,
                            fontSize: 14,
                          ),
                        ),
                        const SizedBox(height: 6),
                        Row(
                          children: [
                            ChipStok(status: o.stokStatus, jumlah: o.stock),
                            const SizedBox(width: 8),
                            Expanded(
                              child: Text(
                                o.namaKategori ?? '-',
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  fontSize: 12,
                                  color: AppTheme.muted,
                                ),
                              ),
                            ),
                          ],
                        ),
                        const SizedBox(height: 8),
                        Row(
                          children: [
                            // Harga & modal dibungkus Flexible: kalau
                            // layar sempit atau angkanya panjang, teksnya
                            // ellipsis — bukan meluber keluar kartu.
                            Flexible(
                              child: Text(
                                Fmt.rp(o.hargaJual),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.w900,
                                  color: AppTheme.primaryDark,
                                ),
                              ),
                            ),
                            const SizedBox(width: 6),
                            Flexible(
                              child: Text(
                                'modal ${Fmt.rp(o.hargaPokok)}',
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: const TextStyle(
                                  fontSize: 11.5,
                                  color: AppTheme.muted,
                                ),
                              ),
                            ),
                            const Spacer(),
                            if (bolehUbah)
                              PopupMenuButton<String>(
                                padding: EdgeInsets.zero,
                                icon: const Icon(
                                  Icons.more_vert_rounded,
                                  size: 20,
                                ),
                                onSelected: (v) => v == 'hapus'
                                    ? _hapus(o)
                                    : _bukaForm(obat: o),
                                itemBuilder: (_) => const [
                                  PopupMenuItem(
                                    value: 'edit',
                                    child: Text('Ubah'),
                                  ),
                                  PopupMenuItem(
                                    value: 'hapus',
                                    child: Text('Hapus'),
                                  ),
                                ],
                              ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
        ),
      ],
    );
  }
}

class _Toolbar extends StatelessWidget {
  const _Toolbar({
    required this.controller,
    required this.kategori,
    required this.filterKategori,
    required this.filterStok,
    required this.sort,
    required this.total,
    required this.onCari,
    required this.onFilterKategori,
    required this.onFilterStok,
    required this.onSort,
    required this.onTambah,
  });

  final TextEditingController controller;
  final List<Category> kategori;
  final int? filterKategori;
  final String? filterStok;
  final String sort;
  final int total;
  final void Function(String) onCari;
  final void Function(int?) onFilterKategori;
  final void Function(String?) onFilterStok;
  final void Function(String) onSort;
  final VoidCallback? onTambah;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(14, 10, 14, 6),
      child: Column(
        children: [
          Row(
            children: [
              // Flexible, bukan Expanded: di layar sangat sempit field
              // pencarian boleh menyusut supaya tombol "Tambah" tidak
              // terdorong keluar layar.
              Flexible(
                child: TextField(
                  controller: controller,
                  autocorrect: false,
                  onSubmitted: onCari,
                  decoration: const InputDecoration(
                    hintText: 'Cari obat, barcode, atau suplier…',
                    prefixIcon: Icon(Icons.search_rounded, size: 20),
                    isDense: true,
                  ),
                ),
              ),
              if (onTambah != null) ...[
                const SizedBox(width: 10),
                ElevatedButton.icon(
                  onPressed: onTambah,
                  style: ElevatedButton.styleFrom(
                    minimumSize: const Size(0, 46),
                    padding: const EdgeInsets.symmetric(horizontal: 14),
                  ),
                  icon: const Icon(Icons.add_rounded, size: 20),
                  label: const Text('Tambah'),
                ),
              ],
            ],
          ),
          const SizedBox(height: 8),
          SizedBox(
            height: 34,
            child: ListView(
              scrollDirection: Axis.horizontal,
              children: [
                _chipKategori(),
                const SizedBox(width: 6),
                ...[('habis', 'Stok Habis'), ('menipis', 'Stok Menipis')].map(
                  (s) => Padding(
                    padding: const EdgeInsets.only(right: 6),
                    child: ChoiceChip(
                      label: Text(s.$2),
                      selected: filterStok == s.$1,
                      onSelected: (v) => onFilterStok(v ? s.$1 : null),
                    ),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.only(left: 4),
                  child: ActionChip(
                    avatar: const Icon(Icons.swap_vert_rounded, size: 16),
                    label: Text(sortLabel(sort)),
                    onPressed: () => onSort(sort == 'obat' ? 'stok' : 'obat'),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 4),
          Align(
            alignment: Alignment.centerLeft,
            child: Text(
              '$total obat ditemukan',
              style: const TextStyle(fontSize: 12, color: AppTheme.muted),
            ),
          ),
        ],
      ),
    );
  }

  Widget _chipKategori() {
    return PopupMenuButton<int?>(
      padding: EdgeInsets.zero,
      onSelected: onFilterKategori,
      itemBuilder: (_) => [
        const PopupMenuItem(value: null, child: Text('Semua kategori')),
        for (final k in kategori)
          PopupMenuItem(value: k.id, child: Text(k.nama)),
      ],
      child: Chip(
        avatar: const Icon(Icons.filter_alt_rounded, size: 16),
        label: Text(
          filterKategori == null
              ? 'Semua kategori'
              : kategori
                    .firstWhere(
                      (k) => k.id == filterKategori,
                      orElse: () => Category(id: 0, nama: 'Kategori'),
                    )
                    .nama,
          overflow: TextOverflow.ellipsis,
        ),
      ),
    );
  }

  static String sortLabel(String s) => switch (s) {
    'stok' => 'Urut: Stok',
    'harga_jual' => 'Urut: Harga',
    _ => 'Urut: Nama',
  };
}
