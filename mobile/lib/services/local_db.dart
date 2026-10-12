import 'dart:convert';

import 'package:path/path.dart' as p;
import 'package:sqflite/sqflite.dart';

import '../models/medicine.dart';

/// Database lokal di ponsel.
///
/// Tiga peran:
/// 1. **Cache obat** — kasir tetap bisa mencari & menjual saat internet mati.
/// 2. **Antrean transaksi** — transaksi yang gagal terkirim disimpan dulu,
///    lalu dikirim ulang saat jaringan kembali.
/// 3. **Penanda sinkron** — kapan cache terakhir diperbarui.
class LocalDb {
  LocalDb._();

  static final LocalDb instance = LocalDb._();

  static const _namaDb = 'apotikdear.db';
  static const _versi = 1;

  Database? _db;

  Future<Database> get database async => _db ??= await _buka();

  Future<Database> _buka() async {
    final lokasi = await getDatabasesPath();

    return openDatabase(
      p.join(lokasi, _namaDb),
      version: _versi,
      onCreate: _buatTabel,
    );
  }

  Future<void> _buatTabel(Database db, int version) async {
    // ---------------------------------------------------------- cache obat
    await db.execute('''
      CREATE TABLE obat_cache (
        idBuku       TEXT PRIMARY KEY,
        obat         TEXT,
        idKategori   TEXT,
        namaKategori TEXT,
        idRak        TEXT,
        namaRak      TEXT,
        suplier      TEXT,
        hargaJual    INTEGER,
        hargaPokok   INTEGER,
        stock        INTEGER,
        minStok      INTEGER,
        kode         TEXT,
        updatedAt    INTEGER
      )
    ''');

    // ---------------------------------------------------- antrean transaksi
    //
    // `payload` berisi JSON persis seperti yang dikirim ke
    // `POST /api/transaksi`. Kalau pengiriman gagal lagi, kita tinggal
    // mengirim ulang isi kolom itu — tidak perlu membangun ulang.
    await db.execute('''
      CREATE TABLE antrean_trx (
        idAntrean     INTEGER PRIMARY KEY AUTOINCREMENT,
        payload       TEXT NOT NULL,
        dibuatPada    INTEGER NOT NULL,
        percobaan     INTEGER NOT NULL DEFAULT 0,
        terakhirGagal INTEGER,
        pesanError    TEXT
      )
    ''');

    // ------------------------------------------------------ penanda sinkron
    await db.execute('''
      CREATE TABLE meta (
        kunci TEXT PRIMARY KEY,
        nilai TEXT
      )
    ''');
  }

  // ============================================================ cache obat

  /// Simpan (atau timpa) sekelompok obat ke cache.
  Future<void> simpanObat(List<MedicineCache> daftar) async {
    final db = await database;
    final batch = db.batch();

    for (final o in daftar) {
      batch.insert('obat_cache', {
        ...o.toJson(),
        'updatedAt': DateTime.now().millisecondsSinceEpoch,
      }, conflictAlgorithm: ConflictAlgorithm.replace);
    }

    await batch.commit(noResult: true);
  }

  /// Cari obat di cache. List kosong berarti tidak ada yang cocok.
  Future<List<MedicineCache>> cariObat(String kata, {int limit = 30}) async {
    final db = await database;
    final like = '%$kata%';

    final rows = await db.query(
      'obat_cache',
      where: 'obat LIKE ? OR idBuku LIKE ? OR kode LIKE ?',
      whereArgs: [like, like, like],
      orderBy: 'obat ASC',
      limit: limit,
    );

    return rows.map(MedicineCache.fromRow).toList();
  }

  Future<MedicineCache?> obatById(String idBuku) async {
    final db = await database;
    final hasil = await db.query(
      'obat_cache',
      where: 'idBuku = ?',
      whereArgs: [idBuku],
      limit: 1,
    );

    return hasil.isEmpty ? null : MedicineCache.fromRow(hasil.first);
  }

  Future<int> jumlahObatTercache() async {
    final db = await database;
    final hasil = await db.rawQuery('SELECT COUNT(*) AS j FROM obat_cache');

    return Sqflite.firstIntValue(hasil) ?? 0;
  }

  /// Kosongkan cache obat.
  Future<void> hapusObatCache() async {
    final db = await database;
    await db.delete('obat_cache');
  }

  // ========================================================== meta sinkron

  Future<void> setMeta(String kunci, String nilai) async {
    final db = await database;

    await db.insert('meta', {
      'kunci': kunci,
      'nilai': nilai,
    }, conflictAlgorithm: ConflictAlgorithm.replace);
  }

  Future<String?> getMeta(String kunci) async {
    final db = await database;
    final hasil = await db.query(
      'meta',
      columns: ['nilai'],
      where: 'kunci = ?',
      whereArgs: [kunci],
      limit: 1,
    );

    return hasil.isEmpty ? null : hasil.first['nilai']?.toString();
  }

  /// Kapan cache obat terakhir diperbarui (untuk ditampilkan di UI).
  Future<DateTime?> cacheTerakhir() async {
    final mentah = await getMeta('cache_obat_pada');

    return mentah == null ? null : DateTime.tryParse(mentah);
  }

  Future<void> setCacheTerakhir(DateTime waktu) =>
      setMeta('cache_obat_pada', waktu.toIso8601String());

  // ========================================================= antrean trx

  /// Simpan transaksi ke antrean; dipakai saat pengiriman gagal.
  ///
  /// Payload disimpan sebagai JSON string supaya saat retry tidak perlu
  /// logika tambahan — cukup di-`jsonDecode` dan dikirim ulang.
  Future<int> tambahAntrean(Map<String, dynamic> payload) async {
    final db = await database;

    return db.insert('antrean_trx', {
      'payload': jsonEncode(payload),
      'dibuatPada': DateTime.now().millisecondsSinceEpoch,
      'percobaan': 0,
    });
  }

  /// Semua transaksi yang menunggu, urut dari yang paling lama.
  Future<List<AntreanTrx>> daftarAntrean() async {
    final db = await database;

    final rows = await db.query('antrean_trx', orderBy: 'idAntrean ASC');

    return rows.map(AntreanTrx.fromRow).toList();
  }

  Future<int> jumlahAntrean() async {
    final db = await database;
    final hasil = await db.rawQuery('SELECT COUNT(*) AS j FROM antrean_trx');

    return Sqflite.firstIntValue(hasil) ?? 0;
  }

  Future<void> catatGagal(int id, String pesan) async {
    final db = await database;

    // Atomik — tidak perlu membaca nilai dulu.
    await db.rawUpdate(
      'UPDATE antrean_trx SET percobaan = percobaan + 1, '
      'terakhirGagal = ?, pesanError = ? WHERE idAntrean = ?',
      [DateTime.now().millisecondsSinceEpoch, pesan, id],
    );
  }

  Future<void> hapusAntrean(int id) async {
    final db = await database;

    await db.delete('antrean_trx', where: 'idAntrean = ?', whereArgs: [id]);
  }

  /// Hapus seluruh antrean. Dipakai test supaya tidak bocor antar test.
  Future<void> hapusSemuaAntrean() async {
    final db = await database;

    await db.delete('antrean_trx');
  }
}

/// Baris cache obat. Hanya field yang dipakai kasir.
class MedicineCache {
  const MedicineCache({
    required this.idBuku,
    required this.obat,
    required this.hargaJual,
    required this.stock,
    this.idKategori,
    this.namaKategori,
    this.idRak,
    this.namaRak,
    this.suplier,
    this.hargaPokok = 0,
    this.minStok = 0,
    this.kode,
  });

  final String idBuku;
  final String obat;
  final String? idKategori;
  final String? namaKategori;
  final String? idRak;
  final String? namaRak;
  final String? suplier;
  final int hargaJual;
  final int hargaPokok;
  final int stock;
  final int minStok;
  final String? kode;

  Map<String, dynamic> toJson() => {
    'idBuku': idBuku,
    'obat': obat,
    'idKategori': idKategori,
    'namaKategori': namaKategori,
    'idRak': idRak,
    'namaRak': namaRak,
    'suplier': suplier,
    'hargaJual': hargaJual,
    'hargaPokok': hargaPokok,
    'stock': stock,
    'minStok': minStok,
    'kode': kode,
  };

  factory MedicineCache.fromRow(Map<String, dynamic> r) => MedicineCache(
    idBuku: r['idBuku']?.toString() ?? '',
    obat: r['obat']?.toString() ?? '',
    idKategori: r['idKategori']?.toString(),
    namaKategori: r['namaKategori']?.toString(),
    idRak: r['idRak']?.toString(),
    namaRak: r['namaRak']?.toString(),
    suplier: r['suplier']?.toString(),
    hargaJual: int.tryParse('${r['hargaJual']}') ?? 0,
    hargaPokok: int.tryParse('${r['hargaPokok']}') ?? 0,
    stock: int.tryParse('${r['stock']}') ?? 0,
    minStok: int.tryParse('${r['minStok']}') ?? 0,
    kode: r['kode']?.toString(),
  );

  /// Kembalikan ke model Medicines supaya bisa langsung dipakai layar Kasir.
  ///
  /// `stok_status` tidak disimpan di cache karena status itu turunan dari
  /// `stock` vs `min_stok` — dihitung ulang di sini supaya warna badge
  /// tetap benar meski data cache sudah beberapa menit lama.
  Medicine toMedicine() => Medicine(
    id: idBuku,
    barcode: kode ?? '',
    nama: obat,
    noisbn: null,
    suplier: suplier,
    tahun: null,
    hargaPokok: hargaPokok,
    hargaJual: hargaJual,
    disc: 0,
    stock: stock,
    minStok: minStok,
    stokStatus: stock <= 0
        ? 'habis'
        : (minStok > 0 && stock < minStok ? 'menipis' : 'aman'),
    idKategori: idKategori == null ? null : int.tryParse(idKategori!),
    idRak: idRak == null ? null : int.tryParse(idRak!),
    namaKategori: namaKategori,
    namaRak: namaRak,
  );
}

/// Satu transaksi yang menunggu untuk dikirim ulang.
class AntreanTrx {
  const AntreanTrx({
    required this.idAntrean,
    required this.payload,
    required this.dibuatPada,
    required this.percobaan,
    this.terakhirGagal,
    this.pesanError,
  });

  final int idAntrean;
  final String payload;
  final DateTime dibuatPada;
  final int percobaan;
  final DateTime? terakhirGagal;
  final String? pesanError;

  Map<String, dynamic> get payloadMap =>
      jsonDecode(payload) as Map<String, dynamic>;

  factory AntreanTrx.fromRow(Map<String, dynamic> r) => AntreanTrx(
    idAntrean: int.tryParse('${r['idAntrean']}') ?? 0,
    payload: r['payload']?.toString() ?? '{}',
    dibuatPada: DateTime.fromMillisecondsSinceEpoch(_toInt(r['dibuatPada'])),
    percobaan: _toInt(r['percobaan']),
    terakhirGagal: r['terakhirGagal'] == null
        ? null
        : DateTime.fromMillisecondsSinceEpoch(_toInt(r['terakhirGagal'])),
    pesanError: r['pesanError']?.toString(),
  );

  static int _toInt(Object? v) {
    if (v is int) return v;

    return int.tryParse(v?.toString() ?? '') ?? 0;
  }
}
