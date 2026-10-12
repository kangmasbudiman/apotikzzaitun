// Pencarian obat di layar Kasir harus tetap jalan saat server mati.
//
// Ini yang membuat fitur antrean transaksi berguna: kalau kasir tidak
// bisa memasukkan obat ke keranjang saat offline, tidak ada transaksi
// yang bisa diantrekan.
//
// Diuji di level service (bukan widget) supaya tidak rapuh: yang penting
// adalah keputusan "server dulu, cache cadangan" dan penanda `dariCache`.
import 'dart:convert';

import 'package:apotikdear_mobile/core/api_client.dart';
import 'package:apotikdear_mobile/models/medicine.dart';
import 'package:apotikdear_mobile/services/local_db.dart';
import 'package:apotikdear_mobile/services/master_service.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:sqflite_common_ffi/sqflite_ffi.dart';

const _obatJson = {
  'id': 'OB00730',
  'barcode': '9786230039355',
  'obat': 'ACETYLCYSTEINE KAP 200 MG',
  'harga_pokok': 1000,
  'harga_jual': 1500,
  'disc': 0,
  'stock': 17,
  'min_stok': 20,
  'stok_status': 'menipis',
};

/// Server tidak terjangkau — meniru kondisi internet mati.
ApiClient _apiMati() => ApiClient(
  client: MockClient(
    (_) async => throw http.ClientException('Tak ada jaringan'),
  ),
  storage: _storage,
);

/// Server hidup dan mengembalikan satu obat.
ApiClient _apiHidup() => ApiClient(
  client: MockClient((req) async {
    if (req.url.path.endsWith('/obat/cari')) {
      return http.Response(
        jsonEncode({'exact': _obatJson, 'suggestions': const []}),
        200,
        headers: {'content-type': 'application/json'},
      );
    }

    return http.Response(
      jsonEncode({
        'data': [_obatJson],
        'meta': {'total': 1, 'current_page': 1, 'last_page': 1},
      }),
      200,
      headers: {'content-type': 'application/json'},
    );
  }),
  storage: _storage,
);

const _storage = FlutterSecureStorage(
  aOptions: AndroidOptions(encryptedSharedPreferences: true),
);

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  setUp(() async {
    sqfliteFfiInit();
    databaseFactory = databaseFactoryFfi;
    await LocalDb.instance.hapusObatCache();
  });

  group('server mati', () {
    setUp(() => LocalDb.instance.simpanObat([Medicine.fromJson(_obatJson).toCache()]));

    test('cariUntukKasir jatuh ke cache dan menandai dariCache', () async {
      final hasil = await MasterService(_apiMati()).cariUntukKasir('ACETYL');

      expect(hasil.dariCache, isTrue);
      expect(hasil.daftar.map((o) => o.nama), ['ACETYLCYSTEINE KAP 200 MG']);
    });

    test('obatUntukKasir jatuh ke cache', () async {
      final hasil = await MasterService(_apiMati()).obatUntukKasir();

      expect(hasil.dariCache, isTrue);
      expect(hasil.daftar, isNotEmpty);
    });

    test('barcode yang ada di cache tetap bisa diambil', () async {
      final obat = await MasterService(_apiMati()).obatDariCache('OB00730');

      expect(obat?.nama, 'ACETYLCYSTEINE KAP 200 MG');
    });

    test('barcode yang tidak ada di cache mengembalikan null', () async {
      final obat = await MasterService(_apiMati()).obatDariCache('OB99999');

      expect(obat, isNull);
    });
  });

  test('cache kosong tidak membuat layar error, hanya kosong', () async {
    await LocalDb.instance.hapusObatCache();

    final hasil = await MasterService(_apiMati()).cariUntukKasir('ACETYL');

    expect(hasil.daftar, isEmpty);
    expect(hasil.dariCache, isTrue);
  });

  group('server hidup', () {
    test('cariUntukKasir memakai hasil server dan menyimpannya ke cache', () async {
      final hasil = await MasterService(_apiHidup()).cariUntukKasir('ACETYL');

      expect(hasil.dariCache, isFalse);
      expect(hasil.daftar.single.nama, 'ACETYLCYSTEINE KAP 200 MG');
      expect(await LocalDb.instance.jumlahObatTercache(), 1);
    });

    test('obatUntukKasir memakai hasil server dan menyimpannya ke cache', () async {
      final hasil = await MasterService(_apiHidup()).obatUntukKasir();

      expect(hasil.dariCache, isFalse);
      expect(await LocalDb.instance.jumlahObatTercache(), 1);
    });
  });
}