import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/api_client.dart';
import '../../core/formatters.dart';
import '../../core/theme.dart';
import '../../models/cart_item.dart';
import '../../models/medicine.dart';
import '../../providers/auth_provider.dart';
import '../../providers/cart_provider.dart';
import '../../services/master_service.dart';
import '../../services/printer_service.dart';
import '../../widgets/panel_batch.dart';
import '../../widgets/responsive_data_view.dart';
import 'barcode_scanner_screen.dart';
import 'pembayaran_sheet.dart';

/// Layar kasir: cari/scan obat di kiri, keranjang di kanan.
///
/// Layout menyesuaikan:
///  * Lebar (tablet/PC) : dua kolom berdampingan, keranjang mengunci di kanan.
///  * Sempit (HP)      : keranjang menjadi sheet yang muncul dari bawah,
///                       supaya area cari tetap sebesar layar.
class KasirScreen extends StatefulWidget {
  const KasirScreen({super.key});

  @override
  State<KasirScreen> createState() => _KasirScreenState();
}

class _KasirScreenState extends State<KasirScreen> {
  final _cariCtl = TextEditingController();
  final _cariFocus = FocusNode();

  List<Medicine> _hasil = [];

  // Cache info batch per obat, supaya tidak memanggil API tiap kali
  // kasir menanyakan.
  final Map<String, InfoBatch> _batch = {};
  bool _memuat = false;
  String? _pesan;

  /// Hasil terakhir berasal dari cache lokal, bukan server.
  bool _offline = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _cariAwal());
  }

  @override
  void dispose() {
    _cariCtl.dispose();
    _cariFocus.dispose();
    super.dispose();
  }

  Future<void> _cariAwal() async {
    // Tampilkan obat yang stoknya paling aman dulu supaya kasir cepat ketemu.
    // Service sudah menangani fallback ke cache saat server mati.
    final hasil = await MasterService(
      context.read<ApiClient>(),
    ).obatUntukKasir();

    if (!mounted) return;

    setState(() {
      _hasil = hasil.daftar;
      _offline = hasil.dariCache;
      _pesan = hasil.dariCache && hasil.daftar.isEmpty
          ? 'Offline dan cache perangkat masih kosong.'
          : null;
    });
  }

  Future<void> _cariObat(String q) async {
    if (q.trim().isEmpty) return _cariAwal();

    setState(() {
      _memuat = true;
      _pesan = null;
    });

    // Service menangani online-lalu-cache, jadi kasir tetap bisa mencari
    // walau server tidak terjangkau.
    final hasil = await MasterService(
      context.read<ApiClient>(),
    ).cariUntukKasir(q.trim());

    if (!mounted) return;

    setState(() {
      _memuat = false;
      _offline = hasil.dariCache;
      _hasil = hasil.daftar;
      _pesan = hasil.daftar.isEmpty
          ? (hasil.dariCache
                ? 'Tidak ada "$q" di cache perangkat ini.'
                : 'Obat "$q" tidak ditemukan.')
          : null;
    });
  }

  Future<void> _pindaiBarcode() async {
    final kode = await BarcodeScannerScreen.pindai(
      context,
      judul: 'Pindai Barcode Obat',
    );

    if (kode == null || kode.isEmpty || !mounted) return;

    // Cek dulu ke server, langsung masukkan ke keranjang kalau ketemu.
    try {
      final r = await MasterService(context.read<ApiClient>()).cariObat(kode);

      if (!mounted) return;

      if (r.exact != null) {
        _tambah(r.exact!);
        return;
      }

      // Tidak ketemu persis — isi kolom pencarian supaya kasir bisa memilih
      // dari saran atau mengoreksi sendiri.
      _cariCtl.text = kode;
      _cariAwal();

      ScaffoldMessenger.of(context)
        ..hideCurrentSnackBar()
        ..showSnackBar(
          SnackBar(
            content: Text(
              r.suggestions.isEmpty
                  ? 'Barcode $kode tidak ditemukan'
                  : 'Barcode $kode tidak persis, pilih dari daftar',
            ),
            backgroundColor: AppTheme.warning,
          ),
        );
    } catch (e) {
      if (!mounted) return;

      // Server mati — barcode tetap bisa dicocokkan dari cache lokal.
      final cached = await MasterService(
        context.read<ApiClient>(),
      ).obatDariCache(kode);

      if (!mounted) return;

      if (cached != null) {
        _tambah(cached);
        return;
      }

      ScaffoldMessenger.of(context)
        ..hideCurrentSnackBar()
        ..showSnackBar(
          SnackBar(
            content: Text(e is ApiException ? e.message : 'Gagal memindai.'),
            backgroundColor: AppTheme.danger,
          ),
        );
    }
  }

  void _tambah(Medicine obat) {
    // Muat info batch di latar belakang — tidak mengganggu interaksi kasir.
    _muatBatch(obat.id);

    final err = context.read<CartProvider>().tambah(obat);

    final messenger = ScaffoldMessenger.of(context);
    messenger.hideCurrentSnackBar();

    if (err != null) {
      messenger.showSnackBar(
        SnackBar(content: Text(err.pesan), backgroundColor: AppTheme.danger),
      );
    } else {
      messenger.showSnackBar(
        SnackBar(
          content: Text('${obat.nama} ditambahkan'),
          duration: const Duration(milliseconds: 900),
        ),
      );
    }
  }

  Future<void> _muatBatch(String idBuku) async {
    if (_batch.containsKey(idBuku)) return;

    try {
      final info = await MasterService(context.read<ApiClient>()).batch(idBuku);
      if (mounted) setState(() => _batch[idBuku] = info);
    } catch (_) {
      // Info batch bersifat tambahan — kegagalan tidak boleh mengganggu kasir.
    }
  }

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartProvider>();
    final lebar = MediaQuery.sizeOf(context).width;
    final duaKolom = lebar >= 900;

    final panelCari = _PanelCari(
      controller: _cariCtl,
      focus: _cariFocus,
      memuat: _memuat,
      pesan: _pesan,
      offline: _offline,
      hasil: _hasil,
      onCari: _cariObat,
      onTambah: _tambah,
      onPindai: _pindaiBarcode,
      batch: _batch,
    );

    // Tablet/PC: keranjang tampil di samping, jadi tidak ada sheet untuk ditutup.
    final panelKeranjang = _PanelKeranjang(
      onBayar: () => _bukaPembayaran(dariSheet: false),
      batch: _batch,
    );

    if (duaKolom) {
      return Row(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Expanded(flex: 5, child: panelCari),
          const VerticalDivider(width: 1),
          SizedBox(width: 390, child: panelKeranjang),
        ],
      );
    }

    return Stack(
      children: [
        Positioned.fill(child: panelCari),
        if (!cart.kosong)
          Positioned(
            left: 12,
            right: 12,
            bottom: 12,
            child: _TombolRingkasan(
              total: cart.grandTotal,
              jumlah: cart.jumlahItem,
              onTap: _bukaKeranjang,
            ),
          ),
      ],
    );
  }

  void _bukaKeranjang() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      useSafeArea: true,
      backgroundColor: Colors.transparent,
      builder: (_) => DraggableScrollableSheet(
        initialChildSize: 0.85,
        minChildSize: 0.5,
        maxChildSize: 0.95,
        expand: false,
        builder: (_, ctl) => Container(
          decoration: const BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
          ),
          child: _PanelKeranjang(
            dalamSheet: true,
            scrollController: ctl,
            onBayar: () => _bukaPembayaran(dariSheet: true),
            batch: _batch,
          ),
        ),
      ),
    );
  }

  Future<void> _bukaPembayaran({required bool dariSheet}) async {
    final auth = context.read<AuthProvider>();
    final api = context.read<ApiClient>();

    // Sheet keranjang hanya ada di mode HP. Di mode tablet keranjang tampil
    // permanen di samping, jadi TIDAK boleh dipop — kalau dipop, navigator
    // akan menutup halaman kasir itu sendiri.
    if (dariSheet) {
      Navigator.of(context).pop();
      // Beri jeda supaya sheet selesai turun sebelum sheet pembayaran dibuka,
      // supaya tidak ada dua sheet bertumpuk.
      await Future<void>.delayed(const Duration(milliseconds: 220));
      if (!mounted) return;
    }

    // Sheet pembayaran mengembalikan TransactionResult kalau berhasil simpan.
    final hasil = await showModalBottomSheet<TransactionResult>(
      context: context,
      isScrollControlled: true,
      useSafeArea: true,
      backgroundColor: Colors.transparent,
      builder: (_) =>
          PembayaranSheet(api: api, namaKasir: auth.user?.nama ?? ''),
    );

    if (!mounted || hasil == null) return;

    // Struk ditampilkan dari sini — konteks layar kasir masih hidup,
    // bukan konteks sheet yang baru saja ditutup.
    _tampilkanStruk(hasil);
  }

  void _tampilkanStruk(TransactionResult hasil) {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(
          children: [
            Icon(Icons.check_circle_rounded, color: AppTheme.success),
            SizedBox(width: 10),
            Text('Transaksi Berhasil'),
          ],
        ),
        content: SingleChildScrollView(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisSize: MainAxisSize.min,
            children: [
              _BarisStruk('No. Transaksi', hasil.id),
              _BarisStruk('Kasir', hasil.namaUser),
              _BarisStruk('Waktu', Fmt.tanggalJam(hasil.tanggal)),
              const Divider(height: 20),
              _BarisStruk('Item', '${hasil.jumlahItem}'),
              if (hasil.diskon > 0)
                _BarisStruk('Diskon', '-${Fmt.rp(hasil.diskon)}'),
              _BarisStruk('Total', Fmt.rp(hasil.total), tebal: true),
              _BarisStruk('Bayar', Fmt.rp(hasil.totalBayar)),
              _BarisStruk('Kembali', Fmt.rp(hasil.kembalian), tebal: true),
            ],
          ),
        ),
        actions: [
          // Cetak struk ke printer Bluetooth kasir.
          OutlinedButton.icon(
            onPressed: () => _cetakStruk(hasil),
            icon: const Icon(Icons.print_outlined, size: 18),
            label: const Text('Cetak'),
          ),
          const SizedBox(width: 8),
          FilledButton(
            onPressed: () => Navigator.of(context).pop(),
            child: const Text('Selesai'),
          ),
        ],
      ),
    );
  }

  /// Kirim struk ke printer Bluetooth.
  Future<void> _cetakStruk(TransactionResult hasil) async {
    final messenger = ScaffoldMessenger.of(context);
    final site = context.read<AuthProvider>().site;
    final printer = context.read<PrinterService>();

    messenger
      ..hideCurrentSnackBar()
      ..showSnackBar(const SnackBar(content: Text('Menghubungkan printer…')));

    try {
      await printer.cetakStruk(
        trx: hasil,
        namaToko: site?.namaToko ?? 'APOTIKDEAR POS',
        alamatToko: site?.alamatToko ?? '',
        teleponToko: site?.teleponToko ?? '',
      );

      messenger
        ..hideCurrentSnackBar()
        ..showSnackBar(
          const SnackBar(
            content: Text('Struk berhasil dicetak.'),
            backgroundColor: AppTheme.success,
          ),
        );
    } on PrintException catch (e) {
      messenger
        ..hideCurrentSnackBar()
        ..showSnackBar(
          SnackBar(
            content: Text(e.pesan),
            backgroundColor: AppTheme.danger,
            duration: const Duration(seconds: 5),
          ),
        );
    } catch (e) {
      debugPrint('Gagal cetak struk: $e');
      messenger
        ..hideCurrentSnackBar()
        ..showSnackBar(
          SnackBar(
            content: const Text('Gagal mencetak. Periksa pengaturan printer.'),
            backgroundColor: AppTheme.danger,
          ),
        );
    }
  }
}

class _BarisStruk extends StatelessWidget {
  const _BarisStruk(this.label, this.nilai, {this.tebal = false});

  final String label;
  final String nilai;
  final bool tebal;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        children: [
          Text(
            label,
            style: TextStyle(
              color: AppTheme.muted,
              fontSize: 13,
              fontWeight: tebal ? FontWeight.w800 : FontWeight.w500,
            ),
          ),
          const Spacer(),
          Text(
            nilai,
            style: TextStyle(
              fontSize: tebal ? 16 : 13.5,
              fontWeight: tebal ? FontWeight.w900 : FontWeight.w600,
              color: tebal ? AppTheme.primaryDark : AppTheme.text,
            ),
          ),
        ],
      ),
    );
  }
}

class _PanelCari extends StatelessWidget {
  const _PanelCari({
    required this.controller,
    required this.focus,
    required this.memuat,
    required this.pesan,
    required this.offline,
    required this.hasil,
    required this.onCari,
    required this.onTambah,
    required this.onPindai,
    required this.batch,
  });

  final TextEditingController controller;
  final FocusNode focus;
  final bool memuat;
  final String? pesan;

  /// Daftar obat di bawah berasal dari cache perangkat, bukan server.
  final bool offline;
  final List<Medicine> hasil;
  final void Function(String) onCari;
  final void Function(Medicine) onTambah;
  final VoidCallback onPindai;

  /// Cache info batch per idBuku — sudah dimuat saat obat dimasukkan keranjang.
  final Map<String, InfoBatch> batch;

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(14, 12, 14, 8),
          child: Row(
            children: [
              // Tombol scan — aksi tercepat untuk kasir.
              Tooltip(
                message: 'Pindai barcode',
                child: Material(
                  color: AppTheme.primary,
                  borderRadius: BorderRadius.circular(12),
                  child: InkWell(
                    borderRadius: BorderRadius.circular(12),
                    onTap: onPindai,
                    child: const SizedBox(
                      width: 52,
                      height: 52,
                      child: Icon(
                        Icons.qr_code_scanner_rounded,
                        color: Colors.white,
                        size: 24,
                      ),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: TextField(
                  controller: controller,
                  focusNode: focus,
                  autocorrect: false,
                  textInputAction: TextInputAction.search,
                  onSubmitted: onCari,
                  onChanged: (v) {
                    // Pencarian setelah 3 huruf supaya tidak saturasi server.
                    if (v.trim().length >= 3) onCari(v);
                  },
                  decoration: InputDecoration(
                    hintText: 'Ketik nama atau barcode obat…',
                    prefixIcon: const Icon(Icons.search_rounded),
                    suffixIcon: memuat
                        ? const Padding(
                            padding: EdgeInsets.all(14),
                            child: SizedBox(
                              width: 18,
                              height: 18,
                              child: CircularProgressIndicator(strokeWidth: 2),
                            ),
                          )
                        : (controller.text.isNotEmpty
                              ? IconButton(
                                  icon: const Icon(Icons.clear_rounded),
                                  onPressed: () {
                                    controller.clear();
                                    onCari('');
                                  },
                                )
                              : null),
                  ),
                ),
              ),
            ],
          ),
        ),
        // Penanda offline. Kasir harus tahu angka stok yang tampil berasal
        // dari perangkat, bukan dari server — kalau tidak, dia bisa
        // menjual obat yang ternyata sudah habis.
        if (offline && hasil.isNotEmpty)
          Container(
            width: double.infinity,
            margin: const EdgeInsets.fromLTRB(14, 0, 14, 8),
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 9),
            decoration: BoxDecoration(
              color: AppTheme.warning.withValues(alpha: 0.12),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: AppTheme.warning.withValues(alpha: 0.4)),
            ),
            child: Row(
              children: [
                const Icon(
                  Icons.cloud_off_rounded,
                  size: 17,
                  color: AppTheme.warning,
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'Offline — stok & harga dari cache perangkat.',
                    style: TextStyle(
                      fontSize: 12,
                      color: AppTheme.warning,
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                ),
              ],
            ),
          ),
        Expanded(
          child: pesan != null
              ? Center(
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(
                        Icons.search_off_rounded,
                        size: 46,
                        color: AppTheme.muted,
                      ),
                      const SizedBox(height: 10),
                      Text(
                        pesan!,
                        style: const TextStyle(color: AppTheme.muted),
                      ),
                    ],
                  ),
                )
              : hasil.isEmpty && !memuat
              ? const Center(child: Text('Ketik nama obat untuk mulai mencari'))
              : ListView.separated(
                  padding: const EdgeInsets.fromLTRB(14, 4, 14, 100),
                  itemCount: hasil.length,
                  separatorBuilder: (_, __) => const SizedBox(height: 8),
                  itemBuilder: (_, i) {
                    final o = hasil[i];
                    final warna = AppTheme.stokBadge(o.stokStatus).bg;
                    final infoBatch = batch[o.id];

                    return Material(
                      color: Colors.white,
                      borderRadius: BorderRadius.circular(12),
                      child: InkWell(
                        borderRadius: BorderRadius.circular(12),
                        onTap: o.stock > 0 ? () => onTambah(o) : null,
                        child: Container(
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: AppTheme.line),
                          ),
                          child: Row(
                            children: [
                              Container(
                                width: 4,
                                height: 44,
                                decoration: BoxDecoration(
                                  color: warna,
                                  borderRadius: BorderRadius.circular(4),
                                ),
                              ),
                              const SizedBox(width: 12),
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      o.nama,
                                      maxLines: 2,
                                      overflow: TextOverflow.ellipsis,
                                      style: const TextStyle(
                                        fontWeight: FontWeight.w700,
                                        fontSize: 13.5,
                                      ),
                                    ),
                                    const SizedBox(height: 3),
                                    Text(
                                      '${o.namaKategori ?? '-'} · ${Fmt.rp(o.hargaJual)}',
                                      style: const TextStyle(
                                        fontSize: 12,
                                        color: AppTheme.muted,
                                      ),
                                    ),
                                    // Info batch FEFO — hanya muncul kalau
                                    // server sudah mengembalikan datanya.
                                    if (infoBatch != null) ...[
                                      const SizedBox(height: 6),
                                      PanelBatch(info: infoBatch, obat: o.nama),
                                    ],
                                  ],
                                ),
                              ),
                              const SizedBox(width: 8),
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.end,
                                children: [
                                  Text(
                                    Fmt.rp(o.hargaJual),
                                    style: const TextStyle(
                                      fontWeight: FontWeight.w800,
                                      fontSize: 13.5,
                                      color: AppTheme.primaryDark,
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  ChipStok(
                                    status: o.stokStatus,
                                    jumlah: o.stock,
                                  ),
                                ],
                              ),
                              IconButton(
                                onPressed: o.stock > 0
                                    ? () => onTambah(o)
                                    : null,
                                icon: const Icon(
                                  Icons.add_circle_rounded,
                                  color: AppTheme.primary,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    );
                  },
                ),
        ),
      ],
    );
  }
}

class _PanelKeranjang extends StatelessWidget {
  const _PanelKeranjang({
    required this.onBayar,
    this.dalamSheet = false,
    this.scrollController,
    this.batch = const {},
  });

  final VoidCallback onBayar;
  final bool dalamSheet;
  final ScrollController? scrollController;

  /// Cache info batch per idBuku untuk menampilkan badge FEFO.
  final Map<String, InfoBatch> batch;

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartProvider>();

    return Container(
      color: Colors.white,
      child: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 14, 8, 8),
            child: Row(
              children: [
                const Text(
                  'Keranjang',
                  style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800),
                ),
                const SizedBox(width: 8),
                if (cart.jumlahItem > 0)
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 2,
                    ),
                    decoration: BoxDecoration(
                      color: AppTheme.primary.withValues(alpha: 0.12),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Text(
                      '${cart.jumlahItem}',
                      style: const TextStyle(
                        fontSize: 11.5,
                        fontWeight: FontWeight.w800,
                        color: AppTheme.primaryDark,
                      ),
                    ),
                  ),
                const Spacer(),
                if (!cart.kosong)
                  TextButton.icon(
                    onPressed: cart.bersihkan,
                    icon: const Icon(Icons.clear_all_rounded, size: 18),
                    label: const Text('Kosongkan'),
                  ),
              ],
            ),
          ),
          const Divider(height: 1),
          Expanded(
            child: cart.kosong
                ? const Center(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(
                          Icons.shopping_cart_outlined,
                          size: 46,
                          color: AppTheme.muted,
                        ),
                        SizedBox(height: 10),
                        Text(
                          'Keranjang masih kosong',
                          style: TextStyle(color: AppTheme.muted),
                        ),
                      ],
                    ),
                  )
                : ListView.separated(
                    controller: scrollController,
                    padding: const EdgeInsets.symmetric(vertical: 6),
                    itemCount: cart.items.length,
                    separatorBuilder: (_, __) => const Divider(height: 1),
                    itemBuilder: (_, i) {
                      final item = cart.items[i];
                      return _BarisKeranjang(
                        item: item,
                        info: batch[item.obat.id],
                      );
                    },
                  ),
          ),
          const Divider(height: 1),
          _RingkasanBayar(onBayar: onBayar),
        ],
      ),
    );
  }
}

class _BarisKeranjang extends StatelessWidget {
  const _BarisKeranjang({required this.item, this.info});

  final CartItem item;

  /// Info batch obat ini; null kalau belum sempat dimuat dari server.
  final InfoBatch? info;

  @override
  Widget build(BuildContext context) {
    final cart = context.read<CartProvider>();

    return Padding(
      padding: const EdgeInsets.fromLTRB(14, 10, 8, 10),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  item.obat.nama,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(
                    fontSize: 13.5,
                    fontWeight: FontWeight.w700,
                  ),
                ),
                const SizedBox(height: 2),
                Text(
                  '${Fmt.rp(item.hargaSatuan)} / ${item.obat.suplier ?? '-'}',
                  style: const TextStyle(fontSize: 11.5, color: AppTheme.muted),
                ),
                if (info != null && info!.punyaEkspirasi) ...[
                  const SizedBox(height: 5),
                  PanelBatch(info: info!, obat: item.obat.nama),
                ],
              ],
            ),
          ),
          IconButton(
            visualDensity: VisualDensity.compact,
            icon: const Icon(
              Icons.remove_circle_outline_rounded,
              color: AppTheme.muted,
              size: 20,
            ),
            onPressed: () => cart.kurangiSatu(item.obat.id),
          ),
          SizedBox(
            width: 32,
            child: Text(
              '${item.jumlah}',
              textAlign: TextAlign.center,
              style: const TextStyle(fontWeight: FontWeight.w800),
            ),
          ),
          IconButton(
            visualDensity: VisualDensity.compact,
            icon: Icon(
              Icons.add_circle_rounded,
              color: item.stokHabis ? AppTheme.muted : AppTheme.primary,
              size: 20,
            ),
            onPressed: item.stokHabis
                ? null
                : () => cart.tambahSatu(item.obat.id),
          ),
          SizedBox(
            width: 92,
            child: Text(
              Fmt.rp(item.subtotal),
              textAlign: TextAlign.right,
              style: const TextStyle(fontWeight: FontWeight.w800),
            ),
          ),
          IconButton(
            visualDensity: VisualDensity.compact,
            icon: const Icon(
              Icons.delete_outline_rounded,
              color: AppTheme.danger,
              size: 19,
            ),
            onPressed: () => cart.hapus(item.obat.id),
          ),
        ],
      ),
    );
  }
}

class _RingkasanBayar extends StatelessWidget {
  const _RingkasanBayar({required this.onBayar});

  final VoidCallback onBayar;

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartProvider>();

    return Padding(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 16),
      child: Column(
        children: [
          if (cart.diskon > 0)
            Row(
              children: [
                const Text(
                  'Diskon',
                  style: TextStyle(color: AppTheme.muted, fontSize: 13),
                ),
                const Spacer(),
                Text(
                  '-${Fmt.rp(cart.diskon)}',
                  style: const TextStyle(
                    color: AppTheme.danger,
                    fontWeight: FontWeight.w700,
                  ),
                ),
              ],
            ),
          const SizedBox(height: 6),
          Row(
            children: [
              const Text(
                'Total',
                style: TextStyle(fontSize: 15, fontWeight: FontWeight.w700),
              ),
              const Spacer(),
              Text(
                Fmt.rp(cart.grandTotal),
                style: const TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.w900,
                  color: AppTheme.primaryDark,
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: cart.kosong ? null : onBayar,
              icon: const Icon(Icons.payments_rounded),
              label: const Text('Bayar Sekarang'),
            ),
          ),
        ],
      ),
    );
  }
}

class _TombolRingkasan extends StatelessWidget {
  const _TombolRingkasan({
    required this.total,
    required this.jumlah,
    required this.onTap,
  });

  final int total;
  final int jumlah;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return Material(
      color: AppTheme.primaryDeep,
      elevation: 8,
      borderRadius: BorderRadius.circular(16),
      child: InkWell(
        borderRadius: BorderRadius.circular(16),
        onTap: onTap,
        child: Padding(
          padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14),
          child: Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 10,
                  vertical: 3,
                ),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.18),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  '$jumlah',
                  style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.w800,
                    fontSize: 12,
                  ),
                ),
              ),
              const SizedBox(width: 12),
              const Text(
                'Lihat Keranjang',
                style: TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w700,
                ),
              ),
              const Spacer(),
              Text(
                Fmt.rp(total),
                style: const TextStyle(
                  color: Colors.white,
                  fontWeight: FontWeight.w900,
                  fontSize: 16,
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
