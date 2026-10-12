import 'dart:convert';
import 'dart:io';

import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http/http.dart' as http;

import 'config.dart';

/// Error dari server yang sudah "dibaca" dan siap ditampilkan ke kasir.
class ApiException implements Exception {
  ApiException(this.message, {this.statusCode, this.code, this.errors});

  final String message;
  final int? statusCode;

  /// Kode domain dari server, mis. STOK_KURANG / UANG_KURANG.
  final String? code;

  /// Pesan validasi per field.
  final Map<String, dynamic>? errors;

  /// Pesan pertama dari errors, kalau ada — lebih spesifik dari message umum.
  String get pesanPerField {
    if (errors == null || errors!.isEmpty) return message;

    final first = errors!.values.first;
    if (first is List && first.isNotEmpty) return first.first.toString();

    return message;
  }

  @override
  String toString() => message;
}

/// Dipanggil setiap kali server membalas 401, supaya aplikasi bisa
/// memaksa user login ulang dari satu titik.
typedef UnauthorizedHandler = void Function();

class ApiClient {
  ApiClient({http.Client? client, FlutterSecureStorage? storage})
    : _client = client ?? http.Client(),
      _storage = storage ?? const FlutterSecureStorage();

  final http.Client _client;
  final FlutterSecureStorage _storage;

  String? _token;

  /// Alamat server yang sedang dipakai. Bisa diubah dari layar login bila
  /// alamat bawaan tidak sesuai jaringan tempat app dijalankan.
  String _baseUrl = AppConfig.apiBaseUrlAwal;

  UnauthorizedHandler? onUnauthorized;

  String? get token => _token;
  String get baseUrl => _baseUrl;

  void setBaseUrl(String url) {
    _baseUrl = _normalisasi(url);
  }

  /// Buang garis miring di akhir supaya tidak jadi `//auth/login`.
  static String _normalisasi(String url) {
    var u = url.trim();
    while (u.endsWith('/')) {
      u = u.substring(0, u.length - 1);
    }
    return u;
  }

  // ---------------------------------------------------------------- token

  Future<void> loadToken() async {
    _token = await _storage.read(key: AppConfig.tokenKey);

    // Alamat server tersimpan (diisi layar login) menang atas nilai bawaan,
    // supaya tidak perlu build ulang kalau server pindah alamat.
    final tersimpan = await _storage.read(key: AppConfig.baseUrlKey);
    if (tersimpan != null && tersimpan.isNotEmpty) {
      _baseUrl = _normalisasi(tersimpan);
    }
  }

  Future<void> simpanBaseUrl(String url) async {
    setBaseUrl(url);
    await _storage.write(key: AppConfig.baseUrlKey, value: _baseUrl);
  }

  Future<void> saveToken(String token) async {
    _token = token;
    await _storage.write(key: AppConfig.tokenKey, value: token);
  }

  Future<void> clearToken() async {
    _token = null;
    await _storage.delete(key: AppConfig.tokenKey);
  }

  // -------------------------------------------------------------- request

  Future<Map<String, dynamic>> get(
    String path, {
    Map<String, dynamic>? query,
  }) => _send('GET', path, query: query);

  Future<Map<String, dynamic>> post(String path, {Object? body}) =>
      _send('POST', path, body: body);

  Future<Map<String, dynamic>> put(String path, {Object? body}) =>
      _send('PUT', path, body: body);

  Future<Map<String, dynamic>> patch(String path, {Object? body}) =>
      _send('PATCH', path, body: body);

  Future<Map<String, dynamic>> delete(String path) => _send('DELETE', path);

  Future<Map<String, dynamic>> _send(
    String method,
    String path, {
    Object? body,
    Map<String, dynamic>? query,
  }) async {
    final uri = _buildUri(path, query);

    final headers = <String, String>{
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };
    if (_token != null) {
      headers['Authorization'] = 'Bearer $_token';
    }

    late http.Response res;
    try {
      res = await switch (method) {
        'POST' => _client.post(
          uri,
          headers: headers,
          body: jsonEncode(body ?? {}),
        ),
        'PUT' => _client.put(
          uri,
          headers: headers,
          body: jsonEncode(body ?? {}),
        ),
        'PATCH' => _client.patch(
          uri,
          headers: headers,
          body: jsonEncode(body ?? {}),
        ),
        'DELETE' => _client.delete(uri, headers: headers),
        _ => _client.get(uri, headers: headers),
      }.timeout(AppConfig.requestTimeout);
    } on SocketException {
      throw ApiException(
        'Tidak bisa menghubungi server.\n\n'
        'Alamat yang dicoba:\n$_baseUrl\n\n'
        'Periksa:\n'
        '• Server sudah jalan? (php artisan serve --host=0.0.0.0)\n'
        '• Alamat di bawah benar untuk perangkat ini?\n'
        '• HP dan komputer di Wi-Fi yang sama?\n'
        'Di Android emulator pakai 10.0.2.2, bukan 127.0.0.1.',
        code: 'OFFLINE',
      );
    } catch (_) {
      throw ApiException(
        'Server terlalu lama merespons. Silakan coba lagi.\n\n'
        'Alamat: $_baseUrl',
        code: 'TIMEOUT',
      );
    }

    return _parse(res);
  }

  Uri _buildUri(String path, Map<String, dynamic>? query) {
    final base = Uri.parse('$_baseUrl/$path');

    if (query == null || query.isEmpty) return base;

    final params = <String, String>{};
    query.forEach((k, v) {
      if (v != null && '$v'.isNotEmpty) params[k] = '$v';
    });

    return base.replace(queryParameters: params);
  }

  Map<String, dynamic> _parse(http.Response res) {
    final status = res.statusCode;

    dynamic decoded;
    try {
      decoded = res.body.isEmpty ? null : jsonDecode(res.body);
    } catch (_) {
      // Server balas HTML (mis. halaman error bawaan) — jangan tampilkan
      // mentah-mentah ke kasir.
      if (status >= 200 && status < 300) return const {};
      throw ApiException(
        'Server membalas dengan format yang tidak dikenali.',
        statusCode: status,
      );
    }

    final map = decoded is Map<String, dynamic> ? decoded : {'data': decoded};

    if (status >= 200 && status < 300) return map;

    // --- Token tidak valid -> lempar ke login, hentikan semua request lain
    if (status == 401) {
      onUnauthorized?.call();
      throw ApiException(
        map['message']?.toString() ?? 'Sesi berakhir, silakan login ulang.',
        statusCode: 401,
      );
    }

    // --- Error server (500). Pesan bawaan Laravel sengaja dibuat generik
    // supaya detail/stack trace tidak bocor ke perangkat. Karena itu di sini
    // kita tambahkan petunjuk penyebab yang paling sering terjadi di lapangan.
    if (status >= 500) {
      throw ApiException(
        '${map['message']?.toString() ?? 'Terjadi kesalahan di server.'}\n\n'
        'Kemungkinan:\n'
        '• Folder storage/ tidak bisa ditulis oleh user web server.\n'
        '  Perbaiki: sudo chown -R <user-web>:<grup> storage bootstrap/cache\n'
        '• Server sedang error — cek storage/logs/laravel.log',
        statusCode: status,
      );
    }

    throw ApiException(
      map['message']?.toString() ?? 'Terjadi kesalahan (HTTP $status).',
      statusCode: status,
      code: map['kode']?.toString(),
      errors: map['errors'] is Map
          ? Map<String, dynamic>.from(map['errors'] as Map)
          : null,
    );
  }

  void close() => _client.close();
}
