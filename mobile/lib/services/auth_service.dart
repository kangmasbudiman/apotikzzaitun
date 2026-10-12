import 'dart:io' show Platform;

import '../core/api_client.dart';
import '../models/auth_user.dart';
import '../models/site_setting.dart';

class AuthResult {
  const AuthResult({required this.user, required this.token});

  final AuthUser user;
  final String token;
}

class AuthService {
  AuthService(this.api);

  final ApiClient api;

  Future<AuthResult> login({
    required String username,
    required String password,
    String? deviceName,
    String? devicePlatform,
  }) async {
    final res = await api.post(
      'auth/login',
      body: {
        'username': username,
        'password': password,
        'device_name': deviceName ?? 'flutter-app',
        'device_platform': devicePlatform ?? Platform.operatingSystem,
      },
    );

    final token = res['access_token']?.toString() ?? '';
    final user = AuthUser.fromJson(
      Map<String, dynamic>.from(res['user'] ?? {}),
    );

    await api.saveToken(token);

    return AuthResult(user: user, token: token);
  }

  Future<AuthUser> me() async {
    final res = await api.get('auth/me');
    return AuthUser.fromJson(Map<String, dynamic>.from(res['user'] ?? {}));
  }

  Future<void> logout() async {
    try {
      await api.post('auth/logout');
    } catch (_) {
      // Token mungkin sudah kedaluwarsa — tetap bersihkan di sisi app.
    }
    await api.clearToken();
  }

  Future<SiteSetting> site() async {
    final res = await api.get('site');
    return SiteSetting.fromJson(Map<String, dynamic>.from(res['data'] ?? {}));
  }
}
