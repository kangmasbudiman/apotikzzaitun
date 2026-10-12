import 'medicine.dart';

/// Satu baris di keranjang kasir.
///
/// Perhatikan: harga TIDAK dikirim ke server. Server menghitung ulang dari
/// master obat, jadi nilai di sini hanya untuk tampilan.
class CartItem {
  CartItem({required this.obat, required this.jumlah});

  final Medicine obat;
  int jumlah;

  int get hargaSatuan => obat.hargaJual;

  /// Nilai diskon item dalam rupiah (bukan persen).
  int get nilaiDisc => (jumlah * (hargaSatuan * obat.disc / 100)).round();

  int get subtotal => (hargaSatuan * jumlah) - nilaiDisc;

  bool get stokHabis => jumlah >= obat.stock;

  CartItem copyWith({Medicine? obat, int? jumlah}) =>
      CartItem(obat: obat ?? this.obat, jumlah: jumlah ?? this.jumlah);
}

/// Hasil simpan transaksi, dari `POST /transaksi`.
///
/// [diantrekan] true kalau transaksi **tidak** sampai ke server karena
/// jaringan mati — payload sudah disimpan di antrean lokal dan akan dikirim
/// otomatis saat internet kembali. Kasir tetap melihat struk-nya, dengan
/// catatan bahwa struk ini belum pasti tercatat di server.
class TransactionResult {
  const TransactionResult({
    required this.id,
    required this.namaUser,
    required this.tanggal,
    required this.total,
    required this.totalBayar,
    required this.diskon,
    required this.kembalian,
    required this.jumlahItem,
    required this.items,
    this.diantrekan = false,
  });

  final String id;
  final String namaUser;
  final DateTime? tanggal;
  final int total;
  final int totalBayar;
  final int diskon;
  final int kembalian;
  final int jumlahItem;
  final List<TransactionItem> items;
  final bool diantrekan;

  factory TransactionResult.fromJson(Map<String, dynamic> json) {
    final raw = json['tanggal']?.toString();

    return TransactionResult(
      id: json['id']?.toString() ?? '',
      namaUser: json['nama_user']?.toString() ?? '',
      tanggal: raw == null ? null : DateTime.tryParse(raw),
      total: int.tryParse('${json['total']}') ?? 0,
      totalBayar: int.tryParse('${json['total_bayar']}') ?? 0,
      diskon: int.tryParse('${json['diskon']}') ?? 0,
      kembalian: int.tryParse('${json['kembalian']}') ?? 0,
      jumlahItem: int.tryParse('${json['jumlah_item']}') ?? 0,
      items: (json['items'] as List? ?? [])
          .whereType<Map<String, dynamic>>()
          .map(TransactionItem.fromJson)
          .toList(),
    );
  }

  /// Hasil "markas" untuk transaksi yang gagal terkirim.
  ///
  /// Dipakai supaya alur UI tetap sama: kasir melihat struk, lalu struk itu
  /// bisa dicetak ulang kalau perlu.
  factory TransactionResult.diantrekan({
    required String id,
    required String namaKasir,
    required DateTime tanggal,
    required int total,
    required int totalBayar,
    required int diskon,
    required int kembalian,
    required List<TransactionItem> items,
  }) => TransactionResult(
    id: id,
    namaUser: namaKasir,
    tanggal: tanggal,
    total: total,
    totalBayar: totalBayar,
    diskon: diskon,
    kembalian: totalBayar - total,
    jumlahItem: items.fold(0, (a, e) => a + e.jumlah),
    items: items,
    diantrekan: true,
  );
}

class TransactionItem {
  const TransactionItem({
    required this.idBuku,
    required this.obat,
    required this.hargaJual,
    required this.jumlah,
    required this.subtotal,
  });

  final String idBuku;
  final String obat;
  final int hargaJual;
  final int jumlah;
  final int subtotal;

  factory TransactionItem.fromJson(Map<String, dynamic> json) =>
      TransactionItem(
        idBuku: json['id_buku']?.toString() ?? '',
        obat: json['obat']?.toString() ?? '',
        hargaJual: int.tryParse('${json['harga_jual']}') ?? 0,
        jumlah: int.tryParse('${json['jumlah']}') ?? 0,
        subtotal: int.tryParse('${json['subtotal']}') ?? 0,
      );
}

/// Satu baris riwayat transaksi, dari `GET /transaksi`.
class TransactionRow {
  const TransactionRow({
    required this.id,
    required this.namaUser,
    required this.tanggal,
    required this.status,
    required this.total,
    required this.diskon,
    required this.kembalian,
    required this.jumlahItem,
  });

  final String id;
  final String namaUser;
  final DateTime? tanggal;
  final bool status;
  final int total;
  final int diskon;
  final int kembalian;
  final int jumlahItem;

  factory TransactionRow.fromJson(Map<String, dynamic> json) {
    final raw = json['tanggal']?.toString();
    return TransactionRow(
      id: json['id']?.toString() ?? '',
      namaUser: json['nama_user']?.toString() ?? '',
      tanggal: raw == null ? null : DateTime.tryParse(raw),
      status: json['status'] == true,
      total: int.tryParse('${json['total']}') ?? 0,
      diskon: int.tryParse('${json['diskon']}') ?? 0,
      kembalian: int.tryParse('${json['kembalian']}') ?? 0,
      jumlahItem: int.tryParse('${json['jumlah_item']}') ?? 0,
    );
  }
}
