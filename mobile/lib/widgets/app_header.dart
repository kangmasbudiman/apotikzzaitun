import 'package:flutter/material.dart';

import '../core/theme.dart';

/// Header aplikasi — menggantikan `AppBar` polos.
///
/// Yang membedakannya dari AppBar biasa:
///  * Ikon halaman dalam kotak berwarna emerald, bukan teks telanjang.
///  * Subtitle jadi *chip* kecil, mudah dibaca tanpa mengganggu.
///  * Slot aksi yang fleksibel (status printer, jumlah keranjang).
///  * Garis bawah bergradasi yang lembut, bukan garis hitam/tebal.
class AppHeader extends StatelessWidget implements PreferredSizeWidget {
  const AppHeader({
    super.key,
    required this.judul,
    required this.subtitle,
    required this.ikon,
    this.aksi = const [],
    this.elevasi = true,
  });

  final String judul;

  /// Baris kedua — misalnya nama toko atau jumlah data.
  final String? subtitle;

  final IconData ikon;

  /// Tombol-tombol di kanan (status printer, keranjang, dsb).
  final List<Widget> aksi;

  final bool elevasi;

  @override
  Size get preferredSize => const Size.fromHeight(68);

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: elevasi
            ? [
                BoxShadow(
                  color: AppTheme.primaryDeep.withValues(alpha: 0.06),
                  blurRadius: 12,
                  offset: const Offset(0, 3),
                ),
              ]
            : null,
        border: const Border(
          bottom: BorderSide(color: AppTheme.line, width: 1),
        ),
      ),
      child: SafeArea(
        bottom: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(16, 10, 8, 12),
          child: Row(
            children: [
              // Ikon dalam kotak bergradien emerald.
              Container(
                width: 42,
                height: 42,
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(13),
                  gradient: const LinearGradient(
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                    colors: [AppTheme.primary, AppTheme.primaryDark],
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: AppTheme.primary.withValues(alpha: 0.35),
                      blurRadius: 10,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Icon(ikon, color: Colors.white, size: 22),
              ),
              const SizedBox(width: 12),

              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(
                      judul,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: const TextStyle(
                        fontSize: 19,
                        fontWeight: FontWeight.w900,
                        color: AppTheme.text,
                        height: 1.15,
                        letterSpacing: -0.2,
                      ),
                    ),
                    if (subtitle != null && subtitle!.isNotEmpty) ...[
                      const SizedBox(height: 3),
                      Row(
                        children: [
                          Container(
                            width: 5,
                            height: 5,
                            decoration: const BoxDecoration(
                              color: AppTheme.primary,
                              shape: BoxShape.circle,
                            ),
                          ),
                          const SizedBox(width: 6),
                          Flexible(
                            child: Text(
                              subtitle!,
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: const TextStyle(
                                fontSize: 11.5,
                                fontWeight: FontWeight.w600,
                                color: AppTheme.muted,
                              ),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ],
                ),
              ),

              ...aksi,
            ],
          ),
        ),
      ),
    );
  }
}

/// Badge bulat untuk jumlah item keranjang.
class BadgeAngka extends StatelessWidget {
  const BadgeAngka({
    super.key,
    required this.jumlah,
    required this.ikon,
    this.onTap,
    this.aktif = false,
  });

  final int jumlah;
  final IconData ikon;
  final VoidCallback? onTap;

  /// Tampilan "menonjol" ketika ada isi.
  final bool aktif;

  @override
  Widget build(BuildContext context) {
    final warna = aktif ? AppTheme.primary : AppTheme.muted;
    final latar = aktif
        ? AppTheme.primary.withValues(alpha: 0.12)
        : AppTheme.bg;

    return Padding(
      padding: const EdgeInsets.only(left: 4),
      child: Material(
        color: latar,
        borderRadius: BorderRadius.circular(14),
        child: InkWell(
          borderRadius: BorderRadius.circular(14),
          onTap: onTap,
          child: Padding(
            padding: EdgeInsets.fromLTRB(9, 8, jumlah > 0 ? 6 : 9, 8),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(ikon, size: 19, color: warna),
                if (jumlah > 0) ...[
                  const SizedBox(width: 5),
                  Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 7,
                      vertical: 2,
                    ),
                    decoration: BoxDecoration(
                      color: warna,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      '$jumlah',
                      style: const TextStyle(
                        color: Colors.white,
                        fontSize: 10.5,
                        fontWeight: FontWeight.w900,
                      ),
                    ),
                  ),
                ],
              ],
            ),
          ),
        ),
      ),
    );
  }
}

/// Titik status kecil — dipakai untuk menunjukkan printer tersambung.
class TitikStatus extends StatelessWidget {
  const TitikStatus({
    super.key,
    required this.aktif,
    required this.onTap,
    required this.aktifLabel,
    required this.nonaktifLabel,
  });

  final bool aktif;
  final VoidCallback onTap;
  final String aktifLabel;
  final String nonaktifLabel;

  @override
  Widget build(BuildContext context) {
    final warna = aktif ? AppTheme.success : AppTheme.muted;

    return Padding(
      padding: const EdgeInsets.only(left: 4),
      child: Tooltip(
        message: aktif ? aktifLabel : nonaktifLabel,
        child: Material(
          color: aktif ? AppTheme.success.withValues(alpha: 0.12) : AppTheme.bg,
          borderRadius: BorderRadius.circular(14),
          child: InkWell(
            borderRadius: BorderRadius.circular(14),
            onTap: onTap,
            child: Padding(
              padding: const EdgeInsets.all(9),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(
                    aktif ? Icons.print_rounded : Icons.print_outlined,
                    size: 19,
                    color: warna,
                  ),
                  const SizedBox(width: 6),
                  Container(
                    width: 7,
                    height: 7,
                    decoration: BoxDecoration(
                      color: warna,
                      shape: BoxShape.circle,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}

/// Avatar user dengan cincin emerald — lebih rapi dari CircleAvatar polos.
class AvatarUser extends StatelessWidget {
  const AvatarUser({
    super.key,
    required this.inisial,
    required this.nama,
    this.onTap,
  });

  final String inisial;
  final String nama;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(left: 4, right: 4),
      child: Tooltip(
        message: nama,
        child: InkWell(
          borderRadius: BorderRadius.circular(24),
          onTap: onTap,
          child: Container(
            width: 38,
            height: 38,
            decoration: BoxDecoration(
              shape: BoxShape.circle,
              gradient: const LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [AppTheme.primary, AppTheme.primaryDeep],
              ),
              border: Border.all(color: Colors.white, width: 2),
              boxShadow: [
                BoxShadow(
                  color: AppTheme.primary.withValues(alpha: 0.30),
                  blurRadius: 8,
                  offset: const Offset(0, 3),
                ),
              ],
            ),
            child: Center(
              child: Text(
                inisial,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 13.5,
                  fontWeight: FontWeight.w900,
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}
