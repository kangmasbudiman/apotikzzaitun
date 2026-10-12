/// Data dashboard dari `GET /api/dashboard`.
class DashboardData {
  const DashboardData({
    required this.tanggal,
    required this.ringkasan,
    required this.grafik,
    required this.topObat,
    required this.stokKritis,
    required this.mendekatiKedaluwarsa,
    required this.transaksiTerbaru,
    this.kesehatanBatch,
  });

  final String tanggal;
  final Ringkasan ringkasan;
  final List<GrafikHari> grafik;
  final List<TopObat> topObat;
  final List<ObatKritis> stokKritis;
  final List<ObatKedaluwarsa> mendekatiKedaluwarsa;
  final List<TransaksiSingkat> transaksiTerbaru;

  /// Hanya dikirim ke admin; null untuk kasir.
  final KesehatanBatch? kesehatanBatch;

  factory DashboardData.fromJson(Map<String, dynamic> json) => DashboardData(
    tanggal: json['tanggal']?.toString() ?? '',
    ringkasan: Ringkasan.fromJson(
      Map<String, dynamic>.from(json['ringkasan'] ?? {}),
    ),
    grafik: (json['grafik'] as List? ?? [])
        .whereType<Map<String, dynamic>>()
        .map(GrafikHari.fromJson)
        .toList(),
    topObat: (json['top_obat'] as List? ?? [])
        .whereType<Map<String, dynamic>>()
        .map(TopObat.fromJson)
        .toList(),
    stokKritis: (json['stok_kritis'] as List? ?? [])
        .whereType<Map<String, dynamic>>()
        .map(ObatKritis.fromJson)
        .toList(),
    mendekatiKedaluwarsa: (json['mendekati_kedaluwarsa'] as List? ?? [])
        .whereType<Map<String, dynamic>>()
        .map(ObatKedaluwarsa.fromJson)
        .toList(),
    transaksiTerbaru: (json['transaksi_terbaru'] as List? ?? [])
        .whereType<Map<String, dynamic>>()
        .map(TransaksiSingkat.fromJson)
        .toList(),
    kesehatanBatch: json['kesehatan_batch'] == null
        ? null
        : KesehatanBatch.fromJson(
            Map<String, dynamic>.from(json['kesehatan_batch'] as Map),
          ),
  );
}

/// Kartu angka utama.
class Ringkasan {
  const Ringkasan({
    required this.omzet,
    required this.transaksi,
    required this.transaksiBatal,
    required this.itemTerjual,
    required this.labaKotor,
    required this.pengeluaran,
    required this.labaBersih,
    required this.rataPerTransaksi,
  });

  final int omzet;
  final int transaksi;
  final int transaksiBatal;
  final int itemTerjual;
  final int labaKotor;
  final int pengeluaran;
  final int labaBersih;
  final int rataPerTransaksi;

  factory Ringkasan.fromJson(Map<String, dynamic> json) => Ringkasan(
    omzet: int.tryParse('${json['omzet']}') ?? 0,
    transaksi: int.tryParse('${json['transaksi']}') ?? 0,
    transaksiBatal: int.tryParse('${json['transaksi_batal']}') ?? 0,
    itemTerjual: int.tryParse('${json['item_terjual']}') ?? 0,
    labaKotor: int.tryParse('${json['laba_kotor']}') ?? 0,
    pengeluaran: int.tryParse('${json['pengeluaran']}') ?? 0,
    labaBersih: int.tryParse('${json['laba_bersih']}') ?? 0,
    rataPerTransaksi: int.tryParse('${json['rata_per_transaksi']}') ?? 0,
  );
}

/// Satu batang di grafik 7 hari.
class GrafikHari {
  const GrafikHari({
    required this.tanggal,
    required this.label,
    required this.omzet,
    required this.transaksi,
  });

  final String tanggal;
  final String label;
  final int omzet;
  final int transaksi;

  factory GrafikHari.fromJson(Map<String, dynamic> json) => GrafikHari(
    tanggal: json['tanggal']?.toString() ?? '',
    label: json['label']?.toString() ?? '',
    omzet: int.tryParse('${json['omzet']}') ?? 0,
    transaksi: int.tryParse('${json['transaksi']}') ?? 0,
  );
}

class TopObat {
  const TopObat({
    required this.idBuku,
    required this.obat,
    required this.qty,
    required this.omzet,
  });

  final String idBuku;
  final String obat;
  final int qty;
  final int omzet;

  factory TopObat.fromJson(Map<String, dynamic> json) => TopObat(
    idBuku: json['id_buku']?.toString() ?? '',
    obat: json['obat']?.toString() ?? '',
    qty: int.tryParse('${json['qty']}') ?? 0,
    omzet: int.tryParse('${json['omzet']}') ?? 0,
  );
}

class ObatKritis {
  const ObatKritis({
    required this.id,
    required this.obat,
    required this.stock,
    required this.barcode,
    required this.stokStatus,
  });

  final String id;
  final String obat;
  final int stock;
  final String barcode;
  final String stokStatus;

  factory ObatKritis.fromJson(Map<String, dynamic> json) => ObatKritis(
    id: json['id']?.toString() ?? '',
    obat: json['obat']?.toString() ?? '',
    stock: int.tryParse('${json['stock']}') ?? 0,
    barcode: json['barcode']?.toString() ?? '',
    stokStatus: json['stok_status']?.toString() ?? 'menipis',
  );
}

class ObatKedaluwarsa {
  const ObatKedaluwarsa({
    required this.id,
    required this.obat,
    required this.kedaluwarsa,
    required this.hariLagi,
    required this.sudahLewat,
    required this.stock,
    this.stokBatch,
  });

  final String id;
  final String obat;
  final DateTime? kedaluwarsa;
  final int hariLagi;
  final bool sudahLewat;
  final int stock;

  /// Stok pada batch berumur terdekat — inilah yang keluar duluan (FEFO).
  /// Null kalau server belum mengirim field ini.
  final int? stokBatch;

  factory ObatKedaluwarsa.fromJson(Map<String, dynamic> json) =>
      ObatKedaluwarsa(
        id: json['id']?.toString() ?? '',
        obat: json['obat']?.toString() ?? '',
        kedaluwarsa: DateTime.tryParse(json['kedaluwarsa']?.toString() ?? ''),
        hariLagi: int.tryParse('${json['hari_lagi']}') ?? 0,
        sudahLewat: json['sudah_lewat'] == true,
        stock: int.tryParse('${json['stock']}') ?? 0,
        stokBatch: json['stok_batch'] == null
            ? null
            : int.tryParse('${json['stok_batch']}'),
      );
}

/// Status konsistensi batch obat vs stok buku. Hanya admin yang menerimanya.
class KesehatanBatch {
  const KesehatanBatch({
    required this.sehat,
    required this.jumlah,
    required this.pesan,
  });

  final bool sehat;
  final int jumlah;
  final String pesan;

  factory KesehatanBatch.fromJson(Map<String, dynamic> json) => KesehatanBatch(
    sehat: json['sehat'] == true,
    jumlah: int.tryParse('${json['jumlah']}') ?? 0,
    pesan: json['pesan']?.toString() ?? '',
  );
}

class TransaksiSingkat {
  const TransaksiSingkat({
    required this.id,
    required this.namaUser,
    required this.tanggal,
    required this.total,
    required this.status,
  });

  final String id;
  final String namaUser;
  final DateTime? tanggal;
  final int total;
  final bool status;

  factory TransaksiSingkat.fromJson(Map<String, dynamic> json) =>
      TransaksiSingkat(
        id: json['id']?.toString() ?? '',
        namaUser: json['nama_user']?.toString() ?? '',
        tanggal: DateTime.tryParse(json['tanggal']?.toString() ?? ''),
        total: int.tryParse('${json['total']}') ?? 0,
        status: json['status'] == true,
      );
}
