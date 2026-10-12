// Uji parsing + render dashboard.
//
// Tujuannya memastikan:
//  1. Bentuk JSON dari server (termasuk angka yang kadang jadi string dari
//     SUM() MySQL) selalu terbaca jadi number.
//  2. Layar bisa dirender untuk data kosong maupun berisi.
import 'dart:convert';

import 'package:apotikdear_mobile/core/api_client.dart';
import 'package:apotikdear_mobile/core/theme.dart';
import 'package:apotikdear_mobile/models/auth_user.dart';
import 'package:apotikdear_mobile/models/dashboard.dart';
import 'package:apotikdear_mobile/providers/auth_provider.dart';
import 'package:apotikdear_mobile/providers/cart_provider.dart';
import 'package:apotikdear_mobile/screens/dashboard/dashboard_screen.dart';
import 'package:apotikdear_mobile/services/auth_service.dart';
import 'package:apotikdear_mobile/services/printer_service.dart';
import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:provider/provider.dart';

/// Bentuk respons seperti yang dikirim DashboardController.
Map<String, dynamic> jsonPenuh() => {
  'tanggal': '2026-10-11',
  'ringkasan': {
    'omzet': 134300,
    'transaksi': 5,
    'transaksi_batal': 1,
    'item_terjual': 16,
    'laba_kotor': 37667,
    'pengeluaran': 25000,
    'laba_bersih': 12667,
    'rata_per_transaksi': 26860,
  },
  'grafik': [
    for (var i = 0; i < 7; i++)
      {
        'tanggal': '2026-10-${(5 + i).toString().padLeft(2, '0')}',
        'label': ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'][i],
        'omzet': i == 6 ? 134300 : 0,
        'transaksi': i == 6 ? 5 : 0,
      },
  ],
  'top_obat': [
    {
      'id_buku': 'OB00944',
      'obat': 'L-VIT D3 5000 IU',
      'qty': 11,
      'omzet': 63800,
    },
    {
      'id_buku': 'OB00730',
      'obat': 'ACETYLCYSTEINE KAP 200 MG',
      'qty': 3,
      'omzet': 4500,
    },
  ],
  'stok_kritis': [
    {
      'id': 'OB00041',
      'obat': 'GENTAMYCIN CREAM',
      'stock': 0,
      'barcode': '899123',
      'stok_status': 'habis',
    },
  ],
  'mendekati_kedaluwarsa': [
    {
      'id': 'OB00120',
      'obat': 'BISOLVON EXTRA SYR',
      'kedaluwarsa': '2026-11-15',
      'hari_lagi': 35,
      'sudah_lewat': false,
      'stock': 5,
    },
    {
      'id': 'OB00121',
      'obat': 'OBAT KEDALUWARSA',
      'kedaluwarsa': '2026-01-10',
      'hari_lagi': -244,
      'sudah_lewat': true,
      'stock': 3,
    },
  ],
  'transaksi_terbaru': [
    {
      'id': 'A96996-300402-11102026',
      'nama_user': 'Meli Yani',
      'tanggal': '2026-10-11 09:47:38',
      'total': 12600,
      'status': true,
    },
  ],
};

/// Varian worst-case: angka datang sebagai string (fakta SUM() MySQL).
Map<String, dynamic> jsonStringAngka() {
  final d = jsonPenuh();
  d['ringkasan'] = {
    'omzet': '134300',
    'transaksi': '5',
    'transaksi_batal': '1',
    'item_terjual': '16',
    'laba_kotor': '37667',
    'pengeluaran': '25000',
    'laba_bersih': '12667',
    'rata_per_transaksi': '26860',
  };
  return d;
}

Map<String, dynamic> jsonKosong() => {
  'tanggal': '2026-10-11',
  'ringkasan': {
    'omzet': 0,
    'transaksi': 0,
    'transaksi_batal': 0,
    'item_terjual': 0,
    'laba_kotor': 0,
    'pengeluaran': 0,
    'laba_bersih': 0,
    'rata_per_transaksi': 0,
  },
  'grafik': const [],
  'top_obat': const [],
  'stok_kritis': const [],
  'mendekati_kedaluwarsa': const [],
  'transaksi_terbaru': const [],
};

Widget _app(ApiClient api, Map<String, dynamic> body) {
  final auth = AuthProvider(api: api, service: AuthService(api))
    ..debugSetUser(
      const AuthUser(
        id: '1',
        nama: 'Meli Yani',
        username: 'meli',
        hakAkses: 2,
        role: 'kasir',
        isAdmin: false,
        bisaTransaksi: true,
        bisaMaster: false,
      ),
    );

  return MultiProvider(
    providers: [
      Provider<ApiClient>.value(value: api),
      Provider<PrinterService>.value(value: PrinterService()),
      ChangeNotifierProvider<AuthProvider>.value(value: auth),
      ChangeNotifierProvider<CartProvider>(create: (_) => CartProvider()),
    ],
    child: MaterialApp(theme: AppTheme.light(), home: const DashboardScreen()),
  );
}

ApiClient _api(Map<String, dynamic> body) => ApiClient(
  client: MockClient((req) async {
    if (req.url.path.endsWith('/auth/me')) {
      return http.Response(
        jsonEncode({
          'user': {
            'id': '1',
            'nama': 'Meli Yani',
            'username': 'meli',
            'hak_akses': 2,
            'role': 'kasir',
            'is_admin': false,
            'bisa_transaksi': true,
            'bisa_master': false,
          },
        }),
        200,
        headers: {'content-type': 'application/json'},
      );
    }

    return http.Response(
      jsonEncode(body),
      200,
      headers: {'content-type': 'application/json'},
    );
  }),
  storage: const FlutterSecureStorage(
    aOptions: AndroidOptions(encryptedSharedPreferences: true),
  ),
);

Future<void> _tenang(WidgetTester tester) async {
  for (var i = 0; i < 10; i++) {
    await tester.pump(const Duration(milliseconds: 120));
  }
}

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  group('Parsing DashboardData', () {
    test('angka dari server dibaca sebagai number', () {
      final d = DashboardData.fromJson(jsonPenuh());

      expect(d.ringkasan.omzet, 134300);
      expect(d.ringkasan.transaksi, 5);
      expect(d.ringkasan.labaKotor, 37667);
      expect(d.grafik.length, 7);
      expect(d.grafik.last.omzet, 134300);
      expect(d.topObat.first.qty, 11);
      expect(d.stokKritis.first.stock, 0);
      expect(d.mendekatiKedaluwarsa.first.hariLagi, 35);
      expect(d.mendekatiKedaluwarsa.last.sudahLewat, isTrue);
      expect(d.transaksiTerbaru.first.status, isTrue);
    });

    test('angka berupa string (SUM() MySQL) tetap jadi number', () {
      final d = DashboardData.fromJson(jsonStringAngka());

      expect(d.ringkasan.omzet, 134300);
      expect(d.ringkasan.transaksi, 5);
      expect(d.ringkasan.rataPerTransaksi, 26860);
    });

    test('data kosong tidak membuat exception', () {
      final d = DashboardData.fromJson(jsonKosong());

      expect(d.ringkasan.omzet, 0);
      expect(d.grafik, isEmpty);
      expect(d.topObat, isEmpty);
    });
  });

  group('Render Dashboard', () {
    testWidgets('data penuh bisa dirender', (tester) async {
      tester.view.physicalSize = const Size(420, 900);
      tester.view.devicePixelRatio = 1.0;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);

      await tester.pumpWidget(_app(_api(jsonPenuh()), jsonPenuh()));
      await _tenang(tester);

      expect(tester.takeException(), isNull);

      // Angka utama harus tampil.
      expect(find.text('OMZET HARI INI'), findsOneWidget);
      expect(find.textContaining('134.300'), findsWidgets);
      expect(find.text('OBAT TERLARIS'), findsOneWidget);
      expect(find.text('MENDEKATI KEDALUWARSA'), findsOneWidget);

      // Bagian di bawah layar harus bisa di-scroll sampai terlihat.
      await tester.scrollUntilVisible(
        find.text('STOK KRITIS (1)'),
        300,
        scrollable: find.byType(Scrollable).first,
      );
      expect(find.text('STOK KRITIS (1)'), findsOneWidget);

      await tester.scrollUntilVisible(
        find.text('TRANSAKSI TERBARU'),
        300,
        scrollable: find.byType(Scrollable).first,
      );
      expect(find.text('TRANSAKSI TERBARU'), findsOneWidget);

      // Tidak boleh ada overflow setelah di-scroll.
      expect(tester.takeException(), isNull);
    });

    testWidgets('data kosong tetap bisa dirender', (tester) async {
      tester.view.physicalSize = const Size(420, 900);
      tester.view.devicePixelRatio = 1.0;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);

      await tester.pumpWidget(_app(_api(jsonKosong()), jsonKosong()));
      await _tenang(tester);

      expect(tester.takeException(), isNull);
      expect(find.text('OMZET HARI INI'), findsOneWidget);
    });

    testWidgets('server error menampilkan pesan, tidak blank', (tester) async {
      tester.view.physicalSize = const Size(420, 900);
      tester.view.devicePixelRatio = 1.0;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);

      final api = ApiClient(
        client: MockClient((req) async {
          if (req.url.path.endsWith('/auth/me')) {
            return http.Response(
              jsonEncode({
                'user': {
                  'id': '1',
                  'nama': 'Meli Yani',
                  'username': 'meli',
                  'hak_akses': 2,
                  'role': 'kasir',
                  'is_admin': false,
                  'bisa_transaksi': true,
                  'bisa_master': false,
                },
              }),
              200,
              headers: {'content-type': 'application/json'},
            );
          }

          return http.Response(
            jsonEncode({'message': 'Server sedang sibuk.'}),
            500,
            headers: {'content-type': 'application/json'},
          );
        }),
        storage: const FlutterSecureStorage(
          aOptions: AndroidOptions(encryptedSharedPreferences: true),
        ),
      );

      await tester.pumpWidget(_app(api, jsonKosong()));
      await _tenang(tester);

      expect(tester.takeException(), isNull);
      expect(find.text('Coba Lagi'), findsOneWidget);
    });
  });
}
