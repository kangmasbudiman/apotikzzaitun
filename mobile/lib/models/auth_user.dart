/// User yang sedang login, sesuai balasan `/auth/login` & `/auth/me`.
class AuthUser {
  const AuthUser({
    required this.id,
    required this.nama,
    required this.username,
    required this.hakAkses,
    required this.role,
    required this.isAdmin,
    required this.bisaTransaksi,
    required this.bisaMaster,
  });

  final String id;
  final String nama;
  final String username;
  final int hakAkses;
  final String role;
  final bool isAdmin;
  final bool bisaTransaksi;
  final bool bisaMaster;

  factory AuthUser.fromJson(Map<String, dynamic> json) => AuthUser(
    id: json['id']?.toString() ?? '',
    nama: json['nama']?.toString() ?? '',
    username: json['username']?.toString() ?? '',
    hakAkses: int.tryParse('${json['hak_kses']}') ?? 2,
    role: json['role']?.toString() ?? 'kasir',
    isAdmin: json['is_admin'] == true,
    bisaTransaksi: json['bisa_transaksi'] == true,
    bisaMaster: json['bisa_master'] == true,
  );

  /// Inisial untuk avatar, mis. "Meli Yani" -> "MY".
  String get inisial {
    final parts = nama
        .trim()
        .split(RegExp(r'\s+'))
        .where((e) => e.isNotEmpty)
        .toList();

    if (parts.isEmpty) return '?';

    if (parts.length == 1) {
      final satu = parts.first;
      return (satu.length >= 2 ? satu.substring(0, 2) : satu).toUpperCase();
    }

    return (parts[0][0] + parts[1][0]).toUpperCase();
  }
}
