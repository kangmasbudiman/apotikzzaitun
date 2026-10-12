import '../services/local_db.dart';

/// Obat, dari `GET /obat`.
class Medicine {
  const Medicine({
    required this.id,
    required this.barcode,
    required this.nama,
    required this.noisbn,
    required this.suplier,
    required this.tahun,
    required this.hargaPokok,
    required this.hargaJual,
    required this.disc,
    required this.stock,
    required this.minStok,
    required this.stokStatus,
    required this.idKategori,
    required this.idRak,
    required this.namaKategori,
    required this.namaRak,
  });

  final String id;
  final String barcode;
  final String nama;
  final String? noisbn;
  final String? suplier;
  final String? tahun;

  final int hargaPokok;
  final int hargaJual;

  /// Diskon per item dalam persen (mis. 10 = 10%).
  final double disc;

  final int stock;
  final int minStok;

  /// 'habis' | 'menipis' | 'aman' — sudah dihitung server.
  final String stokStatus;

  final int? idKategori;
  final int? idRak;
  final String? namaKategori;
  final String? namaRak;

  factory Medicine.fromJson(Map<String, dynamic> json) => Medicine(
    id: json['id']?.toString() ?? '',
    barcode: json['barcode']?.toString() ?? '',
    nama: json['obat']?.toString() ?? '',
    noisbn: json['noisbn']?.toString(),
    suplier: json['suplier']?.toString(),
    tahun: json['tahun']?.toString(),
    hargaPokok: int.tryParse('${json['harga_pokok']}') ?? 0,
    hargaJual: int.tryParse('${json['harga_jual']}') ?? 0,
    disc: double.tryParse('${json['disc']}') ?? 0,
    stock: int.tryParse('${json['stock']}') ?? 0,
    minStok: int.tryParse('${json['min_stok']}') ?? 0,
    stokStatus: json['stok_status']?.toString() ?? 'aman',
    idKategori: _toInt(json['id_kategori']),
    idRak: _toInt(json['id_rak']),
    namaKategori: (json['kategori'] is Map)
        ? (json['kategori']['nama']?.toString())
        : null,
    namaRak: (json['rak'] is Map) ? (json['rak']['nama']?.toString()) : null,
  );

  static int? _toInt(dynamic v) {
    if (v == null) return null;
    return int.tryParse('$v');
  }

  Medicine copyWith({int? stock}) => Medicine(
    id: id,
    barcode: barcode,
    nama: nama,
    noisbn: noisbn,
    suplier: suplier,
    tahun: tahun,
    hargaPokok: hargaPokok,
    hargaJual: hargaJual,
    disc: disc,
    stock: stock ?? this.stock,
    minStok: minStok,
    stokStatus: stokStatus,
    idKategori: idKategori,
    idRak: idRak,
    namaKategori: namaKategori,
    namaRak: namaRak,
  );

  /// Ubah ke baris cache lokal supaya kasir tetap bisa mencari & menjual
  /// saat server tidak bisa dijangkau.
  MedicineCache toCache() => MedicineCache(
    idBuku: id,
    obat: nama,
    idKategori: idKategori?.toString(),
    namaKategori: namaKategori,
    idRak: idRak?.toString(),
    namaRak: namaRak,
    suplier: suplier,
    hargaJual: hargaJual,
    hargaPokok: hargaPokok,
    stock: stock,
    minStok: minStok,
    kode: barcode.isEmpty ? null : barcode,
  );
}

/// Satu batch obat, dari `GET /api/batch/{id}`.
///
/// Aplikasi memakai ini untuk memberi tahu kasir batch mana yang akan
/// keluar lebih dulu (urutan FEFO = yang paling cepat kedaluwarsa).
class BatchObat {
  const BatchObat({
    required this.noBatch,
    required this.expired,
    required this.hariLagi,
    required this.stok,
    required this.hargaPokok,
  });

  final String? noBatch;
  final DateTime? expired;

  /// Sisa hari sampai kedaluwarsa; negatif berarti sudah lewat.
  final int hariLagi;

  final int stok;
  final int hargaPokok;

  factory BatchObat.fromJson(Map<String, dynamic> json) => BatchObat(
    noBatch: json['no_batch']?.toString(),
    expired: DateTime.tryParse(json['expired']?.toString() ?? ''),
    hariLagi: int.tryParse('${json['hari_lagi']}') ?? 0,
    stok: int.tryParse('${json['stok']}') ?? 0,
    hargaPokok: int.tryParse('${json['harga_pokok']}') ?? 0,
  );

  bool get sudahLewat => hariLagi < 0;

  /// 'lewat' | 'kritis' (<= 30 hari) | 'mepet' (<= 90) | 'aman'
  String get tingkat {
    if (hariLagi < 0) return 'lewat';
    if (hariLagi <= 30) return 'kritis';
    if (hariLagi <= 90) return 'mepet';
    return 'aman';
  }
}

/// Ringkasan batch untuk satu obat.
class InfoBatch {
  const InfoBatch({
    required this.jumlahBatch,
    required this.batchTerdekat,
    required this.kedaluwarsaTerdekat,
    required this.hariLagiTerdekat,
    required this.sisaPerBatch,
  });

  final int jumlahBatch;
  final String? batchTerdekat;
  final DateTime? kedaluwarsaTerdekat;
  final int? hariLagiTerdekat;
  final List<BatchObat> sisaPerBatch;

  /// null berarti obat ini tidak punya data kedaluwarsa sama sekali
  /// (mis. stok hasil impor awal).
  bool get punyaEkspirasi => kedaluwarsaTerdekat != null;

  /// true kalau ada batch yang sudah lewat tanggal kedaluwarsa.
  bool get adaYangLewat => sisaPerBatch.any((b) => b.sudahLewat);

  factory InfoBatch.fromJson(Map<String, dynamic> json) => InfoBatch(
    jumlahBatch: int.tryParse('${json['jumlah_batch']}') ?? 0,
    batchTerdekat: json['batch_terdekat']?.toString(),
    kedaluwarsaTerdekat: DateTime.tryParse(
      json['kedaluwarsa_terdekat']?.toString() ?? '',
    ),
    hariLagiTerdekat: json['hari_lagi_terdekat'] == null
        ? null
        : int.tryParse('${json['hari_lagi_terdekat']}'),
    sisaPerBatch: (json['sisa_per_batch'] as List? ?? [])
        .whereType<Map<String, dynamic>>()
        .map(BatchObat.fromJson)
        .toList(),
  );
}

/// Kategori obat — `GET /kategori`.
class Category {
  const Category({required this.id, required this.nama});

  final int id;
  final String nama;

  factory Category.fromJson(Map<String, dynamic> json) => Category(
    id: int.tryParse('${json['id']}') ?? 0,
    nama: json['nama']?.toString() ?? '',
  );
}

/// Rak penyimpanan — `GET /rak`.
class Rack {
  const Rack({required this.id, required this.nama});

  final int id;
  final String nama;

  factory Rack.fromJson(Map<String, dynamic> json) => Rack(
    id: int.tryParse('${json['id']}') ?? 0,
    nama: json['nama']?.toString() ?? '',
  );
}
