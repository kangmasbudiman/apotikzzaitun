import 'dart:async';

import 'package:connectivity_plus/connectivity_plus.dart';
import 'package:flutter/foundation.dart';

/// Memantau apakah ponsel sedang online.
///
/// Dipakai untuk:
/// - memicu sinkronisasi antrean transaksi saat jaringan kembali,
/// - memberi tahu kasir bahwa data yang dilihat mungkin basi.
///
/// Catatan: `connectivity_plus` hanya melaporkan **ada koneksi jaringan**,
/// bukan menjamin server bisa dijangkau. Koneksi Wi-Fi yang tidak punya
/// internet tetap dianggap "online" di sini — kegagalan tetap ditangani
/// lewat timeout HTTP.
class ConnectivityService {
  ConnectivityService._();

  static final ConnectivityService instance = ConnectivityService._();

  final Connectivity _connectivity = Connectivity();

  StreamSubscription<List<ConnectivityResult>>? _langganan;
  bool _online = true;

  /// Status terakhir yang diketahui. Default `true` supaya aplikasi tidak
  /// langsung menganggap offline sebelum cek pertama selesai.
  bool get online => _online;

  /// Baca status tanpa harus berlangganan — dipakai widget yang hanya perlu
  /// menampilkan indikator.
  static final ValueNotifier<bool> value = ValueNotifier<bool>(true);

  final _pengamat = StreamController<bool>.broadcast();

  /// Berubah nilainya setiap kali status online/offline berganti.
  Stream<bool> get perubahan => _pengamat.stream;

  Future<void> mulai() async {
    try {
      final hasil = await _connectivity.checkConnectivity();
      _online = _apakahOnline(hasil);
    } catch (_) {
      // Plugin gagal (mis. di emulator) — anggap online, aplikasi tetap jalan.
      _online = true;
    }

    _langganan = _connectivity.onConnectivityChanged.listen((hasil) {
      final baru = _apakahOnline(hasil);

      if (baru != _online) {
        _online = baru;
        value.value = baru;
        _pengamat.add(baru);
      }
    });
  }

  bool _apakahOnline(List<ConnectivityResult> hasil) {
    // `none` = tidak ada jaringan sama sekali. Sisanya (wifi, mobile,
    // ethernet, vpn) dianggap online.
    return hasil.isNotEmpty &&
        !hasil.every((r) => r == ConnectivityResult.none);
  }

  Future<void> berhenti() async {
    await _langganan?.cancel();
    await _pengamat.close();
  }
}
