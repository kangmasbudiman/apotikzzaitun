import 'package:flutter/material.dart';

import '../core/theme.dart';

/// Penangkap error global.
///
/// Flutter biasanya menampilkan error render sebagai kotak merah/abu-abu yang
/// tidak bisa dibaca pengguna kasir. Widget ini membungkus seluruh aplikasi:
/// kalau ada error saat render, pesan aslinya ditampilkan dalam bentuk yang
/// bisa dibaca — dan bisa disalin untuk dilaporkan.
class LayarError extends StatefulWidget {
  const LayarError({super.key, required this.child});

  final Widget child;

  /// Error terakhir yang tertangkap, newest first.
  static final List<String> _tercatat = [];

  static void catat(String pesan, StackTrace? stack) {
    debugPrint('=== ERROR APLIKASI ===');
    debugPrint(pesan);
    if (stack != null) debugPrint(stack.toString());

    _tercatat.insert(0, pesan);
    if (_tercatat.length > 5) _tercatat.removeLast();
  }

  static List<String> get tercatat => List.unmodifiable(_tercatat);

  @override
  State<LayarError> createState() => _LayarErrorState();
}

class _LayarErrorState extends State<LayarError> {
  @override
  Widget build(BuildContext context) {
    // Kalau ada error yang belum tertangkap, tampilkan supaya terlihat.
    final adaError = LayarError._tercatat.isNotEmpty;

    if (!adaError) return widget.child;

    return _TampilanError(child: widget.child);
  }
}

class _TampilanError extends StatelessWidget {
  const _TampilanError({required this.child});

  final Widget child;

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        // Aplikasi tetap jalan di belakang — kasir masih bisa melihat data.
        child,

        // Pita peringatan di bawah supaya tidak menutupi seluruh layar.
        Positioned(
          left: 8,
          right: 8,
          bottom: 8,
          child: Material(
            color: AppTheme.danger,
            borderRadius: BorderRadius.circular(12),
            elevation: 8,
            child: InkWell(
              borderRadius: BorderRadius.circular(12),
              onTap: () => _tampilkanDetail(context),
              child: Padding(
                padding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 11,
                ),
                child: Row(
                  children: [
                    const Icon(
                      Icons.bug_report_outlined,
                      color: Colors.white,
                      size: 20,
                    ),
                    const SizedBox(width: 10),
                    const Expanded(
                      child: Text(
                        'Ada kesalahan di aplikasi. Ketuk untuk melihat.',
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: 13,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                    Icon(Icons.chevron_right, color: Colors.white70, size: 20),
                  ],
                ),
              ),
            ),
          ),
        ),
      ],
    );
  }

  void _tampilkanDetail(BuildContext context) {
    showDialog(
      context: context,
      builder: (c) => AlertDialog(
        title: const Text('Detail Kesalahan'),
        content: SizedBox(
          width: double.maxFinite,
          child: SingleChildScrollView(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisSize: MainAxisSize.min,
              children: [
                for (final e in LayarError.tercatat)
                  Padding(
                    padding: const EdgeInsets.only(bottom: 10),
                    child: Text(
                      e,
                      style: const TextStyle(fontSize: 11, height: 1.4),
                    ),
                  ),
              ],
            ),
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(c),
            child: const Text('Tutup'),
          ),
        ],
      ),
    );
  }
}
