/// Pengaturan toko, dari `GET /site`.
class SiteSetting {
  const SiteSetting({
    required this.namaSistem,
    required this.namaToko,
    required this.alamatToko,
    required this.teleponToko,
    required this.ppn,
    required this.minStok,
  });

  final String? namaSistem;
  final String namaToko;
  final String alamatToko;
  final String teleponToko;
  final double ppn;
  final int minStok;

  factory SiteSetting.fromJson(Map<String, dynamic> json) => SiteSetting(
    namaSistem: json['nama_sistem']?.toString(),
    namaToko: json['nama_toko']?.toString() ?? '',
    alamatToko: json['alamat_toko']?.toString() ?? '',
    teleponToko: json['telepon_toko']?.toString() ?? '',
    ppn: double.tryParse('${json['ppn']}') ?? 0,
    minStok: int.tryParse('${json['min_stok']}') ?? 0,
  );

  /// Nama yang dipakai di header aplikasi: pakai "nama sistem" kalau ada,
  /// kalau kosong jatuh ke nama toko (sama seperti perilaku halaman login web).
  String get namaUntukTampilan =>
      (namaSistem != null && namaSistem!.trim().isNotEmpty)
      ? namaSistem!.trim()
      : namaToko;
}
