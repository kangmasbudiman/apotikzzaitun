import '../core/api_client.dart';
import '../models/cart_item.dart';
import '../models/dashboard.dart';
import '../models/medicine.dart';
import 'local_db.dart';

/// Hasil satu halaman + info untuk infinite scroll.
class Page<T> {
  const Page({
    required this.items,
    required this.total,
    required this.page,
    required this.lastPage,
  });

  final List<T> items;
  final int total;
  final int page;
  final int lastPage;

  bool get adaBerikutnya => page < lastPage;
}

class MasterService {
  MasterService(this.api);

  final ApiClient api;

  // ------------------------------------------------------------------ obat

  Future<Page<Medicine>> obat({
    String? search,
    int? kategoriId,
    int? rakId,
    String? stok,
    String? sort,
    String? direction,
    int page = 1,
    int perPage = 25,
  }) async {
    final res = await api.get(
      'obat',
      query: {
        'search': search,
        'kategori_id': kategoriId,
        'rak_id': rakId,
        'stok': stok,
        'sort': sort,
        'direction': direction,
        'page': page,
        'per_page': perPage,
      },
    );

    final data = (res['data'] as List? ?? [])
        .whereType<Map<String, dynamic>>()
        .map(Medicine.fromJson)
        .toList();

    final meta = Map<String, dynamic>.from(res['meta'] ?? {});

    return Page(
      items: data,
      total: int.tryParse('${meta['total']}') ?? data.length,
      page: int.tryParse('${meta['current_page']}') ?? page,
      lastPage: int.tryParse('${meta['last_page']}') ?? page,
    );
  }

  Future<Medicine?> detailObat(String id) async {
    final res = await api.get('obat/$id');
    final data = res['data'];
    if (data is! Map<String, dynamic>) return null;
    return Medicine.fromJson(data);
  }

  /// Daftar obat untuk layar kasir, tetap jalan walau server mati.
  ///
  /// Hasil dari server langsung disimpan ke cache; kalau server tidak bisa
  /// dijangkau, isi cache yang dipakai. Tanpa ini, begitu internet putus
  /// kasir tidak bisa memasukkan obat apa pun ke keranjang — sehingga fitur
  /// antrean transaksi juga tidak ada gunanya.
  Future<({List<Medicine> daftar, bool dariCache})> obatUntukKasir({
    String? cari,
    int perPage = 20,
  }) async {
    try {
      final res = await api.get(
        'obat',
        query: {
          if (cari != null && cari.isNotEmpty) 'cari': cari,
          'sort': 'obat',
          'per_page': perPage,
        },
      );

      final daftar = (res['data'] as List? ?? const [])
          .whereType<Map<String, dynamic>>()
          .map(Medicine.fromJson)
          .toList();

      if (daftar.isNotEmpty) {
        await _simpanCache(daftar);
      }

      return (daftar: daftar, dariCache: false);
    } catch (e) {
      final cached = await LocalDb.instance
          .cariObat(cari ?? '', limit: perPage)
          .then((b) => b.map((o) => o.toMedicine()).toList())
          .catchError((_) => <Medicine>[]);

      return (daftar: cached, dariCache: true);
    }
  }

  /// Pencarian obat + fallback cache, untuk layar kasir.
  ///
  /// Bedanya dengan [cariObat] biasa: hasilnya selalu berupa daftar, dan
  /// [dariCache] memberi tahu layar bahwa data itu belum tentu mutakhir.
  Future<({List<Medicine> daftar, bool dariCache})> cariUntukKasir(
    String q,
  ) async {
    try {
      final r = await cariObat(q);
      final daftar = [if (r.exact != null) r.exact!, ...r.suggestions];

      if (daftar.isNotEmpty) {
        await _simpanCache(daftar);
      }

      return (daftar: daftar, dariCache: false);
    } catch (e) {
      final cached = await LocalDb.instance
          .cariObat(q)
          .then((b) => b.map((o) => o.toMedicine()).toList())
          .catchError((_) => <Medicine>[]);

      return (daftar: cached, dariCache: true);
    }
  }

  /// Satu obat berdasarkan id, untuk hasil pindai barcode saat offline.
  Future<Medicine?> obatDariCache(String idBuku) async => LocalDb.instance
      .obatById(idBuku)
      .then((o) => o?.toMedicine())
      .catchError((_) => null);

  /// Tulis hasil server ke cache & catat kapan terakhir diperbarui.
  Future<void> _simpanCache(List<Medicine> daftar) async {
    await LocalDb.instance.simpanObat(daftar.map((o) => o.toCache()).toList());
    await LocalDb.instance.setCacheTerakhir(DateTime.now());
  }

  /// Pencarian cepat untuk scan barcode.
  ///
  /// Kalau [q] cocok persis, [exact] terisi — inilah yang dipakai kasir untuk
  /// langsung masukkan keranjang tanpa memilih.
  Future<({Medicine? exact, List<Medicine> suggestions})> cariObat(
    String q,
  ) async {
    final res = await api.get('obat/cari', query: {'q': q});

    final exact = res['exact'] is Map<String, dynamic>
        ? Medicine.fromJson(Map<String, dynamic>.from(res['exact']))
        : null;

    final suggestions = (res['suggestions'] as List? ?? [])
        .whereType<Map<String, dynamic>>()
        .map(Medicine.fromJson)
        .toList();

    return (exact: exact, suggestions: suggestions);
  }

  Future<Medicine> simpanObat(Map<String, dynamic> body) async {
    final res = await api.post('obat', body: body);
    return Medicine.fromJson(Map<String, dynamic>.from(res['data'] ?? {}));
  }

  Future<Medicine> ubahObat(String id, Map<String, dynamic> body) async {
    final res = await api.put('obat/$id', body: body);
    return Medicine.fromJson(Map<String, dynamic>.from(res['data'] ?? {}));
  }

  Future<void> hapusObat(String id) => api.delete('obat/$id');

  // -------------------------------------------------------------- kategori

  Future<List<Category>> kategori() async {
    final res = await api.get('kategori');
    return (res['data'] as List? ?? const [])
        .whereType<Map<String, dynamic>>()
        .map(Category.fromJson)
        .toList();
  }

  Future<Category> simpanKategori(String nama) async {
    final res = await api.post('kategori', body: {'nama_kategori': nama});
    return Category.fromJson(Map<String, dynamic>.from(res['data'] ?? {}));
  }

  Future<Category> ubahKategori(int id, String nama) async {
    final res = await api.put('kategori/$id', body: {'nama_kategori': nama});
    return Category.fromJson(Map<String, dynamic>.from(res['data'] ?? {}));
  }

  Future<void> hapusKategori(int id) => api.delete('kategori/$id');

  // -------------------------------------------------------------------- rak

  /// Batch obat untuk satu obat (urutan FEFO).
  Future<InfoBatch> batch(String idBuku) async {
    final res = await api.get('batch/$idBuku');

    return InfoBatch.fromJson(Map<String, dynamic>.from(res['data'] ?? {}));
  }

  /// Cek apakah sisa batch seluruh obat masih cocok dengan stok buku.
  Future<({bool sehat, String pesan, List<Map<String, dynamic>> data})>
  cekBatch() async {
    final res = await api.get('batch');

    return (
      sehat: res['sehat'] == true,
      pesan: res['pesan']?.toString() ?? '',
      data: (res['data'] as List? ?? [])
          .whereType<Map<String, dynamic>>()
          .toList(),
    );
  }

  Future<List<Rack>> rak() async {
    final res = await api.get('rak');
    return (res['data'] as List? ?? const [])
        .whereType<Map<String, dynamic>>()
        .map(Rack.fromJson)
        .toList();
  }

  Future<Rack> simpanRak(String nama) async {
    final res = await api.post('rak', body: {'nama_rak': nama});
    return Rack.fromJson(Map<String, dynamic>.from(res['data'] ?? {}));
  }

  Future<Rack> ubahRak(int id, String nama) async {
    final res = await api.put('rak/$id', body: {'nama_rak': nama});
    return Rack.fromJson(Map<String, dynamic>.from(res['data'] ?? {}));
  }

  Future<void> hapusRak(int id) => api.delete('rak/$id');
}

class TransactionService {
  TransactionService(this.api);

  final ApiClient api;

  /// Ringkasan dashboard. [tanggal] format YYYY-MM-DD; null = hari ini.
  Future<DashboardData> dashboard({String? tanggal}) async {
    final res = await api.get('dashboard', query: {'tanggal': tanggal});

    return DashboardData.fromJson(Map<String, dynamic>.from(res));
  }

  /// Simpan penjualan.
  ///
  /// [items] hanya berisi id obat + jumlah. Harga sengaja tidak dikirim:
  /// server menghitung ulang dan menolak kalau pembayaran kurang.
  Future<TransactionResult> simpan({
    required List<CartItem> items,
    required int diskon,
    required int totalBayar,
  }) async {
    final res = await api.post(
      'transaksi',
      body: {
        'items': items
            .map((e) => {'id_buku': e.obat.id, 'jumlah': e.jumlah})
            .toList(),
        'diskon': diskon,
        'total_bayar': totalBayar,
      },
    );

    return TransactionResult.fromJson(
      Map<String, dynamic>.from(res['data'] ?? {}),
    );
  }

  Future<Page<TransactionRow>> riwayat({
    String? dari,
    String? sampai,
    int? status,
    String? q,
    int page = 1,
    int perPage = 25,
  }) async {
    final res = await api.get(
      'transaksi',
      query: {
        'dari': dari,
        'sampai': sampai,
        'status': status,
        'q': q,
        'page': page,
        'per_page': perPage,
      },
    );

    final meta = Map<String, dynamic>.from(res['meta'] ?? {});

    return Page(
      items: (res['data'] as List? ?? [])
          .whereType<Map<String, dynamic>>()
          .map(TransactionRow.fromJson)
          .toList(),
      total: int.tryParse('${meta['total']}') ?? 0,
      page: int.tryParse('${meta['halaman_sekarang']}') ?? page,
      lastPage: int.tryParse('${meta['halaman_terakhir']}') ?? page,
    );
  }

  Future<TransactionResult> detail(String id) async {
    final res = await api.get('transaksi/$id');
    return TransactionResult.fromJson(
      Map<String, dynamic>.from(res['data'] ?? {}),
    );
  }

  Future<void> batalkan(String id) => api.patch('transaksi/$id/batal');
}
