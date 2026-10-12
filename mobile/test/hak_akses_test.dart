// Menyeluruh: module yang terlihat per role.
//
// Test render di `home_screen_test.dart` hanya memastikan tidak ada crash.
// Test ini menutup celah lain: kalau menu untuk role tertentu muncul
// tanpa sengaja, atau tombol tulis (Tambah / Ubah / Hapus) bocor ke user
// yang tidak berhak, kasir akan menekan sesuatu yang pasti dibalas 403.
import 'dart:convert';

import 'package:apotikdear_mobile/core/api_client.dart';
import 'package:apotikdear_mobile/core/theme.dart';
import 'package:apotikdear_mobile/models/auth_user.dart';
import 'package:apotikdear_mobile/providers/auth_provider.dart';
import 'package:apotikdear_mobile/providers/cart_provider.dart';
import 'package:apotikdear_mobile/providers/sync_provider.dart';
import 'package:apotikdear_mobile/screens/home_screen.dart';
import 'package:apotikdear_mobile/services/auth_service.dart';
import 'package:apotikdear_mobile/services/printer_service.dart';
import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:provider/provider.dart';

ApiClient _api() => ApiClient(
  client: MockClient((req) async {
    final p = req.url.path;

    if (p.endsWith('/site')) {
      return http.Response(
        jsonEncode({
          'data': {
            'nama_sistem': 'Apotikdear POS',
            'nama_toko': 'Az Zaitun',
            'ppn': 0,
            'min_stok': 90,
          },
        }),
        200,
        headers: {'content-type': 'application/json'},
      );
    }

    if (p.endsWith('/kategori') || p.endsWith('/rak')) {
      return http.Response(
        jsonEncode([
          {'id': 9, 'nama': 'Obat Keras'},
        ]),
        200,
        headers: {'content-type': 'application/json'},
      );
    }

    return http.Response(
      jsonEncode({
        'data': [
          {
            'id': 'OB00001',
            'barcode': '899123',
            'obat': 'FOLAMIL GENIO',
            'harga_pokok': 4500,
            'harga_jual': 6300,
            'stock': 50,
            'min_stok': 90,
            'stok_status': 'menipis',
          },
        ],
        'links': {'first': null, 'last': null, 'prev': null, 'next': null},
        'meta': {'current_page': 1, 'last_page': 1, 'per_page': 25, 'total': 1},
      }),
      200,
      headers: {'content-type': 'application/json'},
    );
  }),
  storage: const FlutterSecureStorage(
    aOptions: AndroidOptions(encryptedSharedPreferences: true),
  ),
);

AuthUser _user({required int akses, required bool transaksi, required bool master}) =>
    AuthUser(
      id: '1',
      nama: 'Uji',
      username: 'uji',
      hakAkses: akses,
      role: {1: 'admin', 2: 'kasir', 3: 'gudang'}[akses]!,
      isAdmin: akses == 1,
      bisaTransaksi: transaksi,
      bisaMaster: master,
    );

final kasir = _user(akses: 2, transaksi: true, master: false);
final admin = _user(akses: 1, transaksi: true, master: true);
final gudang = _user(akses: 3, transaksi: false, master: true);

Future<void> _sembunyikan(WidgetTester tester) async {
  for (var i = 0; i < 8; i++) {
    await tester.pump(const Duration(milliseconds: 120));
  }
}

Future<void> _bukaHome(WidgetTester tester, AuthUser u) async {
  tester.view.physicalSize = const Size(1400, 900);
  tester.view.devicePixelRatio = 1.0;
  addTearDown(tester.view.resetPhysicalSize);
  addTearDown(tester.view.resetDevicePixelRatio);

  final api = _api();
  final auth = AuthProvider(api: api, service: AuthService(api))
    ..debugSetUser(u);

  await tester.pumpWidget(
    MultiProvider(
      providers: [
        Provider<ApiClient>.value(value: api),
        Provider<PrinterService>.value(value: PrinterService()),
        ChangeNotifierProvider<AuthProvider>.value(value: auth),
        ChangeNotifierProvider<SyncProvider>(create: (_) => SyncProvider(api)),
        ChangeNotifierProvider<CartProvider>(create: (_) => CartProvider()),
      ],
      child: MaterialApp(theme: AppTheme.light(), home: const HomeScreen()),
    ),
  );

  await _sembunyikan(tester);
}

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('kasir tidak melihat menu Kategori & Rak', (tester) async {
    await _bukaHome(tester, kasir);

    expect(find.text('Kategori & Rak'), findsNothing);
  });

  testWidgets('kasir melihat Dashboard, Kasir, Obat, Riwayat', (tester) async {
    await _bukaHome(tester, kasir);

    expect(find.text('Dashboard'), findsWidgets);
    expect(find.text('Kasir'), findsWidgets);
    expect(find.text('Obat'), findsWidgets);
    expect(find.text('Riwayat'), findsWidgets);
  });

  testWidgets('gudang melihat master data tapi tidak melihat Kasir', (
    tester,
  ) async {
    await _bukaHome(tester, gudang);

    expect(find.text('Kategori & Rak'), findsWidgets);
    expect(find.text('Kasir'), findsNothing);
    expect(find.text('Riwayat'), findsNothing);
  });

  testWidgets('admin melihat semua menu', (tester) async {
    await _bukaHome(tester, admin);

    expect(find.text('Dashboard'), findsWidgets);
    expect(find.text('Kasir'), findsWidgets);
    expect(find.text('Obat'), findsWidgets);
    expect(find.text('Kategori & Rak'), findsWidgets);
    expect(find.text('Riwayat'), findsWidgets);
  });

  testWidgets('kasir tidak melihat tombol Tambah di daftar obat', (
    tester,
  ) async {
    await _bukaHome(tester, kasir);

    await tester.tap(find.text('Obat').first);
    await _sembunyikan(tester);

    expect(find.text('Tambah'), findsNothing);
  });

  testWidgets('admin melihat tombol Tambah di daftar obat', (tester) async {
    await _bukaHome(tester, admin);

    await tester.tap(find.text('Obat').first);
    await _sembunyikan(tester);

    expect(find.text('Tambah'), findsWidgets);
  });
}