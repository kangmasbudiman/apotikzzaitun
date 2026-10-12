import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:mobile_scanner/mobile_scanner.dart';

import '../../core/theme.dart';

/// Layar pemindai barcode.
///
/// Dirancang untuk kasir: satu scan langsung menutup layar dan mengembalikan
/// kode. Ada jeda anti-scan-ganda supaya kamera tidak mengirim kode yang sama
/// puluhan kali per detik.
class BarcodeScannerScreen extends StatefulWidget {
  const BarcodeScannerScreen({super.key, this.judul = 'Pindai Barcode'});

  final String judul;

  /// Hasil pemindaian. Null kalau pengguna menekan tombol kembali.
  static Future<String?> pindai(BuildContext context, {String? judul}) {
    return Navigator.of(context).push<String>(
      MaterialPageRoute(
        builder: (_) => BarcodeScannerScreen(judul: judul ?? 'Pindai Barcode'),
      ),
    );
  }

  @override
  State<BarcodeScannerScreen> createState() => _BarcodeScannerScreenState();
}

class _BarcodeScannerScreenState extends State<BarcodeScannerScreen>
    with WidgetsBindingObserver {
  final MobileScannerController _controller = MobileScannerController(
    detectionSpeed: DetectionSpeed.normal,
    formats: const [
      BarcodeFormat.ean13,
      BarcodeFormat.ean8,
      BarcodeFormat.upcA,
      BarcodeFormat.upcE,
      BarcodeFormat.code128,
      BarcodeFormat.code39,
      BarcodeFormat.qrCode,
    ],
  );

  String? _kodeTerakhir;
  DateTime? _waktuTerakhir;
  bool _menyala = false;

  /// Jeda minimal antar scan supaya kode yang sama tidak diproses berulang.
  static const _jedaScan = Duration(milliseconds: 1800);

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    _controller.dispose();
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    final controller = _controller;

    // Kamera harus dimatikan saat aplikasi ke background, kalau tidak
    // Android akan murdering aplikasi.
    if (!controller.value.isInitialized) return;

    switch (state) {
      case AppLifecycleState.resumed:
        controller.start();
      case AppLifecycleState.inactive:
      case AppLifecycleState.paused:
      case AppLifecycleState.detached:
      case AppLifecycleState.hidden:
        controller.stop();
    }
  }

  void _deteksi(BarcodeCapture capture) {
    final kode = capture.barcodes
        .map((b) => b.rawValue)
        .firstWhere(
          (v) => v != null && v.trim().isNotEmpty,
          orElse: () => null,
        );

    if (kode == null) return;

    final sekarang = DateTime.now();
    final terakhir = _waktuTerakhir;

    // Kode sama dalam jeda singkat -> abaikan (kamera membaca terus-menerus).
    if (kode == _kodeTerakhir &&
        terakhir != null &&
        sekarang.difference(terakhir) < _jedaScan) {
      return;
    }

    _kodeTerakhir = kode;
    _waktuTerakhir = sekarang;

    // Beri umpan balik visual + getaran sebelum menutup layar.
    HapticFeedback.lightImpact();
    setState(() {});
    Navigator.of(context).pop(kode);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.black,
      extendBodyBehindAppBar: true,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        foregroundColor: Colors.white,
        elevation: 0,
        title: Text(widget.judul, style: const TextStyle(color: Colors.white)),
        actions: [
          IconButton(
            tooltip: 'Nyalakan senter',
            onPressed: () async {
              try {
                await _controller.toggleTorch();
                if (mounted) setState(() => _menyala = !_menyala);
              } catch (_) {
                // Beberapa emulator tidak punya senter.
              }
            },
            icon: Icon(
              _menyala
                  ? Icons.flashlight_on_rounded
                  : Icons.flashlight_off_rounded,
              color: _menyala ? AppTheme.lime : Colors.white,
            ),
          ),
          IconButton(
            tooltip: 'Ganti kamera',
            onPressed: () => _controller.switchCamera(),
            icon: const Icon(
              Icons.flip_camera_ios_outlined,
              color: Colors.white,
            ),
          ),
        ],
      ),
      body: Stack(
        fit: StackFit.expand,
        children: [
          MobileScanner(
            controller: _controller,
            onDetect: _deteksi,
            errorBuilder: (context, error, child) => _KamarTidakAktif(error),
          ),

          // Bingkai area pindai.
          CustomPaint(painter: _BingkaiPainter()),

          // Petunjuk di bawah.
          Positioned(
            left: 24,
            right: 24,
            bottom: MediaQuery.of(context).padding.bottom + 40,
            child: Column(
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 18,
                    vertical: 12,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.black.withValues(alpha: 0.55),
                    borderRadius: BorderRadius.circular(30),
                  ),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(
                        Icons.qr_code_scanner_rounded,
                        color: AppTheme.lime,
                        size: 20,
                      ),
                      SizedBox(width: 10),
                      Flexible(
                        child: Text(
                          'Arahkan kamera ke barcode obat',
                          style: TextStyle(color: Colors.white, fontSize: 13.5),
                        ),
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 16),
                TextButton.icon(
                  onPressed: () => Navigator.of(context).pop(),
                  style: TextButton.styleFrom(foregroundColor: Colors.white70),
                  icon: const Icon(Icons.keyboard_rounded, size: 18),
                  label: const Text('Ketik kode manual'),
                ),
              ],
            ),
          ),

          if (_kodeTerakhir != null)
            Positioned(
              left: 24,
              right: 24,
              top: MediaQuery.of(context).padding.top + 80,
              child: Container(
                padding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 12,
                ),
                decoration: BoxDecoration(
                  color: AppTheme.success.withValues(alpha: 0.92),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Row(
                  children: [
                    const Icon(
                      Icons.check_circle_rounded,
                      color: Colors.white,
                      size: 20,
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Text(
                        _kodeTerakhir!,
                        style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w700,
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
        ],
      ),
    );
  }
}

class _KamarTidakAktif extends StatelessWidget {
  const _KamarTidakAktif(this.error);

  final MobileScannerException error;

  @override
  Widget build(BuildContext context) {
    return Container(
      color: Colors.black,
      padding: const EdgeInsets.all(28),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(
            Icons.no_photography_rounded,
            color: Colors.white54,
            size: 54,
          ),
          const SizedBox(height: 16),
          Text(
            error.errorDetails?.message ??
                'Kamera tidak bisa dibuka.\n\n'
                    'Pastikan aplikasi punya izin kamera, lalu ketik kode barcode secara manual.',
            textAlign: TextAlign.center,
            style: const TextStyle(color: Colors.white70, height: 1.5),
          ),
          const SizedBox(height: 20),
          OutlinedButton.icon(
            onPressed: () => Navigator.of(context).pop(),
            style: OutlinedButton.styleFrom(
              foregroundColor: Colors.white,
              side: const BorderSide(color: Colors.white38),
            ),
            icon: const Icon(Icons.arrow_back_rounded),
            label: const Text('Kembali'),
          ),
        ],
      ),
    );
  }
}

/// Menggambar bingkai area pindai dengan sudut yangETE rounded.
class _BingkaiPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final lebarKotak = size.width * 0.78;
    final tinggiKotak = lebarKotak * 0.62;
    final kiri = (size.width - lebarKotak) / 2;
    final atas = (size.height - tinggiKotak) / 2 - size.height * 0.04;

    final kotak = RRect.fromRectAndRadius(
      Rect.fromLTWH(kiri, atas, lebarKotak, tinggiKotak),
      const Radius.circular(20),
    );

    // Gelapkan area luar bingkai.
    final jalur = Path()
      ..addRect(Offset.zero & size)
      ..addRRect(kotak)
      ..fillType = PathFillType.evenOdd;

    canvas.drawPath(
      jalur,
      Paint()..color = Colors.black.withValues(alpha: 0.5),
    );

    // Sudut emerald.
    const panjang = 26.0;
    final sapuan = Paint()
      ..color = AppTheme.primary
      ..strokeWidth = 4
      ..strokeCap = StrokeCap.round
      ..style = PaintingStyle.stroke;

    void sudut(Offset o, double dx, double dy) {
      canvas.drawLine(o, o.translate(dx * panjang, 0), sapuan);
      canvas.drawLine(o, o.translate(0, dy * panjang), sapuan);
    }

    sudut(Offset(kiri, atas), 1, 1);
    sudut(Offset(kiri + lebarKotak, atas), -1, 1);
    sudut(Offset(kiri, atas + tinggiKotak), 1, -1);
    sudut(Offset(kiri + lebarKotak, atas + tinggiKotak), -1, -1);
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}
