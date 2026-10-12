import 'package:flutter/foundation.dart';

import '../core/api_client.dart';
import '../models/auth_user.dart';
import '../models/site_setting.dart';
import '../services/auth_service.dart';

enum AuthStatus { belumCek, memuat, masuk, keluar }

class AuthProvider extends ChangeNotifier {
  AuthProvider({required this.api, required this.service});

  final ApiClient api;
  final AuthService service;

  AuthStatus status = AuthStatus.belumCek;
  AuthUser? user;
  SiteSetting? site;
  String? pesanError;

  bool get sudahMasuk => status == AuthStatus.masuk && user != null;

  /// Hanya untuk keperluan test — memasang user tanpa lewat login sungguhan.
  @visibleForTesting
  void debugSetUser(AuthUser u) {
    user = u;
    status = AuthStatus.masuk;
    notifyListeners();
  }

  /// Dipanggil sekali saat aplikasi dibuka: coba pakai token tersimpan.
  Future<void> bootstrap() async {
    status = AuthStatus.memuat;
    notifyListeners();

    await api.loadToken();

    if (api.token == null) {
      status = AuthStatus.keluar;
      notifyListeners();
      return;
    }

    // Validasi token masih hidup. Kalau tidak, api_client sudah memicu
    // onUnauthorized yang memanggil _forceLogout().
    try {
      user = await service.me();
      await _muatSite();
      status = AuthStatus.masuk;
    } on ApiException catch (e) {
      if (e.statusCode == 401) return; // _forceLogout() sudah jalan
      pesanError = e.message;
      status = AuthStatus.keluar;
    }

    notifyListeners();
  }

  Future<bool> login(String username, String password) async {
    status = AuthStatus.memuat;
    pesanError = null;
    notifyListeners();

    try {
      final res = await service.login(username: username, password: password);

      user = res.user;
      await _muatSite();
      status = AuthStatus.masuk;
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      pesanError = e.pesanPerField;
      status = AuthStatus.keluar;
      notifyListeners();
      return false;
    }
  }

  Future<void> _muatSite() async {
    try {
      site = await service.site();
    } catch (_) {
      // Pengaturan toko hanya kosmetik — app tetap jalan kalau gagal diambil.
      site = null;
    }
  }

  Future<void> logout() async {
    await service.logout();
    _forceLogout();
  }

  /// Dipanggil dari ApiClient saat server membalas 401.
  void _forceLogout() {
    api.clearToken();
    user = null;
    site = null;
    status = AuthStatus.keluar;
    notifyListeners();
  }
}
