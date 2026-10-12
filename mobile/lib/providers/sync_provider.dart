import 'dart:async';

import 'package:flutter/foundation.dart';

import '../core/api_client.dart';
import '../services/connectivity_service.dart';
import '../services/local_db.dart';

/// Status sinkronisasi antrean transaksi.
enum StatusSync {
  /// Tidak ada transaksi menunggu.
  kosong,

  /// Ada transaksi menunggu tapi sedang tidak ada jaringan.
  menunggu,

  /// Sedang mengirim ulang transaksi.
  mengirim,

  /// Semua transaksi berhasil dikirim.
  selesai,

  /// Ada transaksi yang gagal dikirim setelah beberapa percobaan.
  gagal,
}

/// Mengatur **offline mode** aplikasi.
///
/// Tiga tanggung jawab:
/// 1. **Cache obat** — diisi saat aplikasi dibuka dan kasir mencari obat,
///    supaya penjualan tetap bisa berjalan tanpa internet.
/// 2. **Antrean transaksi** — saat [kirimTransaksi] gagal karena jaringan,
///    payload-nya disimpan di SQLite, bukan dibuang.
/// 3. **Sinkronisasi otomatis** — begitu jaringan kembali (atau kasir menekan
///    tombol "Kirim Ulang"), antrean dikirim satu per satu sesuai urutan.
class SyncProvider extends ChangeNotifier {
  SyncProvider(this._api) {
    _langganan = ConnectivityService.instance.perubahan.listen((online) {
      if (online) {
        // Jaringan kembali — coba kirim antrean secara otomatis.
        sinkronkan();
      } else {
        _status = _adaAntrean ? StatusSync.menunggu : StatusSync.kosong;
        notifyListeners();
      }
    });
  }

  final ApiClient _api;

  /// Jeda antar percobaan kirim ulang, supaya tidak membanjiri server.
  static const _jeda = Duration(seconds: 8);

  final List<AntreanTrx> _antrean = [];
  StreamSubscription<bool>? _langganan;
  Timer? _cuci;

  StatusSync _status = StatusSync.kosong;
  DateTime? _sinkronPada;
  String? _pesanTerakhir;

  StatusSync get status => _status;
  List<AntreanTrx> get antrean => List.unmodifiable(_antrean);
  int get jumlahAntrean => _antrean.length;
  DateTime? get sinkronPada => _sinkronPada;
  String? get pesanTerakhir => _pesanTerakhir;

  /// Transaksi yang masih menunggu untuk dikirim (untuk badge di navbar).
  int get jumlahMenunggu => _antrean.length;

  bool get _adaAntrean => _antrean.isNotEmpty;

  /// Muat antrean dari SQLite dan putuskan status awal.
  Future<void> mulai() async {
    await _muatAntrean();

    if (_adaAntrean) {
      _status = ConnectivityService.instance.online
          ? StatusSync.menunggu
          : StatusSync.menunggu;
    }

    notifyListeners();

    // Cek berkala apakah sudah waktunya mencoba lagi. Interval singkat
    // supaya kasir tidak perlu menekan tombol terus-menerus.
    _cuci = Timer.periodic(_jeda, (_) {
      if (ConnectivityService.instance.online && _adaAntrean) {
        sinkronkan();
      }
    });
  }

  Future<void> _muatAntrean() async {
    _antrean
      ..clear()
      ..addAll(await LocalDb.instance.daftarAntrean());
  }

  // Cache obat tidak diatur di sini — `MasterService` yang menulis dan
  // membacanya, supaya pilihan "server dulu, cache cadangan" ada di satu
  // tempat dan tidak terpecah antara dua kelas.

  // =========================================================== transaksi

  /// Kirim transaksi ke server.
  ///
  /// Kalau gagal karena **masalah jaringan** (bukan penolakan server),
  /// payload disimpan di antrean supaya tidak hilang. Penolakan server
  /// (mis. stok kurang, uang tidak cukup) **tidak** diantrekan — mengirim
  /// ulang transaksi yang ditolak tidak ada gunanya.
  ///
  /// Mengembalikan `true` kalau transaksi benar-benar tersimpan di server.
  Future<bool> kirimTransaksi(Map<String, dynamic> payload) async {
    try {
      await _api.post('transaksi', body: payload);
      return true;
    } on ApiException catch (e) {
      if (e.code == 'OFFLINE' || e.code == 'TIMEOUT') {
        await LocalDb.instance.tambahAntrean(payload);
        await _muatAntrean();
        _status = StatusSync.menunggu;
        _pesanTerakhir = 'Transaksi disimpan di antrean — menunggu jaringan.';
        notifyListeners();
        return false;
      }
      // Penolakan server — biarkanlempar, jangan diantrekan.
      rethrow;
    } catch (_) {
      await LocalDb.instance.tambahAntrean(payload);
      await _muatAntrean();
      _status = StatusSync.menunggu;
      _pesanTerakhir = 'Transaksi disimpan di antrean — menunggu jaringan.';
      notifyListeners();
      return false;
    }
  }

  /// Coba kirim semua transaksi yang menunggu, satu per satu.
  ///
  /// Urutan penting: transaksi lebih dulu dibuat harus lebih dulu dikirim,
  /// supaya FEFO dan nomor struk tetap masuk akal.
  Future<void> sinkronkan() async {
    if (_status == StatusSync.mengirim || _antrean.isEmpty) return;

    if (!ConnectivityService.instance.online) {
      _status = StatusSync.menunggu;
      notifyListeners();
      return;
    }

    _status = StatusSync.mengirim;
    notifyListeners();

    var adaGagal = false;

    // Salin daftar supaya aman selama pengiriman berlangsung.
    for (final item in List<AntreanTrx>.from(_antrean)) {
      try {
        await _api.post('transaksi', body: item.payloadMap);
        await LocalDb.instance.hapusAntrean(item.idAntrean);
        _antrean.removeWhere((e) => e.idAntrean == item.idAntrean);
        notifyListeners();
      } on ApiException catch (e) {
        await LocalDb.instance.catatGagal(item.idAntrean, e.message);
        adaGagal = true;
        // Penolakan server yang tidak mungkin berubah (mis. stok kurang)
        // — hentikan putaran ini supaya tidak menghabiskan waktu.
        if (e.code == 'OFFLINE' || e.code == 'TIMEOUT') break;
        continue;
      } catch (_) {
        adaGagal = true;
        break;
      }
    }

    await _muatAntrean();
    _sinkronPada = DateTime.now();
    _status = _adaAntrean
        ? (adaGagal ? StatusSync.gagal : StatusSync.menunggu)
        : StatusSync.selesai;
    _pesanTerakhir = _adaAntrean
        ? '${_antrean.length} transaksi masih menunggu.'
        : 'Semua transaksi berhasil dikirim.';
    notifyListeners();
  }

  /// Buang satu transaksi dari antrean (mis. kasir membatalkannya manual).
  Future<void> buang(int idAntrean) async {
    await LocalDb.instance.hapusAntrean(idAntrean);
    await _muatAntrean();
    _status = _adaAntrean ? StatusSync.menunggu : StatusSync.kosong;
    notifyListeners();
  }

  @override
  void dispose() {
    _langganan?.cancel();
    _cuci?.cancel();
    super.dispose();
  }
}
