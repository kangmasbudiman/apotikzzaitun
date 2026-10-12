// Uji parsing endpoint yang balas array polos.
//
// `GET /kategori` dan `GET /rak` membalas array JSON, bukan objek.
// `ApiClient._parse` membungkus array jadi `{'data': [...]}` supaya
// semua respons punya bentuk seragam. Kalau lapisan service lupa
// membaca `data`, hasilnya `TypeError` yang tidak tertangkap sebagai
// ApiException — layar diam-diam menampilkan "Belum ada kategori"
// padahal server sebenarnya mengirim data.
import 'dart:convert';

import 'package:apotikdear_mobile/core/api_client.dart';
import 'package:apotikdear_mobile/services/master_service.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

/// Body persis seperti yang dikirim CategoryController / RackController:
/// array polos, tanpa pembungkus `data`.
ApiClient _apiDari(Map<String, List<Map<String, dynamic>>> jalan) =>
    ApiClient(
      client: MockClient((req) async {
        final nama = req.url.path.split('/').last;

        return http.Response(
          jsonEncode(jalan[nama] ?? const []),
          200,
          headers: {'content-type': 'application/json'},
        );
      }),
      storage: const FlutterSecureStorage(
        aOptions: AndroidOptions(encryptedSharedPreferences: true),
      ),
    );

void main() {
  test('kategori() membaca array polos jadi daftar kategori', () async {
    final svc = MasterService(
      _apiDari({
        'kategori': [
          {'id': 8, 'nama': 'Obat Keras'},
          {'id': 12, 'nama': 'Alkes, Makanan, Minuman'},
        ],
      }),
    );

    final hasil = await svc.kategori();

    expect(hasil.map((k) => k.nama), ['Obat Keras', 'Alkes, Makanan, Minuman']);
  });

  test('rak() membaca array polos jadi daftar rak', () async {
    final svc = MasterService(
      _apiDari({
        'rak': [
          {'id': 5, 'nama': 'Rak 1'},
          {'id': 7, 'nama': 'Rak 3'},
        ],
      }),
    );

    final hasil = await svc.rak();

    expect(hasil.map((r) => r.nama), ['Rak 1', 'Rak 3']);
  });

  test('kategori() mengembalikan daftar kosong, bukan melempar', () async {
    final hasil = await MasterService(_apiDari({})).kategori();

    expect(hasil, isEmpty);
  });
}