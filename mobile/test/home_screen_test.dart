// Menyeluruh: render HomeScreen untuk semua kombinasi role & ukuran layar.
//
// Tujuannya menangkap crash yang hanya muncul untuk kombinasi tertentu
// (mis. menu master data untuk admin, atau NavigationRail di tablet).
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

    if (p.endsWith('/kategori') || p.endsWith('/rak')) {
      return http.Response(
        jsonEncode([
          {'id': 9, 'nama': 'Obat Bebas'},
          {'id': 8, 'nama': 'Obat Keras'},
        ]),
        200,
        headers: {'content-type': 'application/json'},
      );
    }

    if (p.endsWith('/site')) {
      return http.Response(
        jsonEncode({
          'data': {
            'nama_sistem': 'Apotikdear POS',
            'nama_toko': 'Az Zaitun',
            'alamat_toko': 'Jl. Test',
            'telepon_toko': '0822',
            'ppn': 0,
            'min_stok': 90,
          },
        }),
        200,
        headers: {'content-type': 'application/json'},
      );
    }

    if (p.contains('/transaksi')) {
      return http.Response(
        jsonEncode({
          'data': const [],
          'meta': {
            'total': 0,
            'per_page': 25,
            'halaman_sekarang': 1,
            'halaman_terakhir': 1,
            'ada_halaman_berikutnya': false,
          },
        }),
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
            'disc': 0,
            'stock': 50,
            'min_stok': 90,
            'stok_status': 'menipis',
            'id_kategori': 9,
            'id_rak': 7,
            'kategori': {'id': 9, 'nama': 'OBT'},
            'rak': {'id': 7, 'nama': 'Rak 3'},
          },
        ],
        'links': {
          'first': null,
          'last': 'x?page=1',
          'prev': null,
          'next': null,
        },
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

AuthUser _user({
  required int akses,
  required String role,
  required bool transaksi,
  required bool master,
}) => AuthUser(
  id: '1',
  nama: 'Uji $role',
  username: 'uji',
  hakAkses: akses,
  role: role,
  isAdmin: akses == 1,
  bisaTransaksi: transaksi,
  bisaMaster: master,
);

Future<void> _sembunykan(WidgetTester tester) async {
  for (var i = 0; i < 8; i++) {
    await tester.pump(const Duration(milliseconds: 120));
  }
}

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  final role = {
    'admin': _user(akses: 1, role: 'admin', transaksi: true, master: true),
    'kasir': _user(akses: 2, role: 'kasir', transaksi: true, master: false),
    'gudang': _user(akses: 3, role: 'gudang', transaksi: false, master: true),
  };

  final ukuran = {
    'HP (400x850)': Size(400, 850),
    'Tablet (800x1200)': Size(800, 1200),
    'PC (1400x900)': Size(1400, 900),
  };

  for (final r in role.entries) {
    for (final u in ukuran.entries) {
      testWidgets('${r.key} di ${u.key} bisa dirender', (tester) async {
        tester.view.physicalSize = u.value;
        tester.view.devicePixelRatio = 1.0;
        addTearDown(tester.view.resetPhysicalSize);
        addTearDown(tester.view.resetDevicePixelRatio);

        final api = _api();
        final auth = AuthProvider(api: api, service: AuthService(api))
          ..debugSetUser(r.value);

        await tester.pumpWidget(
          MultiProvider(
            providers: [
              Provider<ApiClient>.value(value: api),
              Provider<PrinterService>.value(value: PrinterService()),
              ChangeNotifierProvider<AuthProvider>.value(value: auth),
              ChangeNotifierProvider<SyncProvider>(
                create: (_) => SyncProvider(api),
              ),
              ChangeNotifierProvider<CartProvider>(
                create: (_) => CartProvider(),
              ),
            ],
            child: MaterialApp(
              theme: AppTheme.light(),
              home: const HomeScreen(),
            ),
          ),
        );

        await _sembunykan(tester);

        var e = tester.takeException();
        expect(e, isNull, reason: 'render ${r.key} @ ${u.key} gagal: $e');

        // PENTING: hanya mengecek tab pertama tidak cukup. Semua menu yang
        // tersedia untuk role ini harus bisa dibuka — bug "infinite height"
        // di ResponsiveDataView hanya muncul di tab daftar data, dan tab itu
        // bukan tab pertama untuk kebanyakan role.
        for (final menu in menuUntuk(r.value)) {
          final finder = find.text(menu);
          if (finder.evaluate().isEmpty) continue;

          await tester.tap(finder.first);
          await _sembunykan(tester);

          e = tester.takeException();
          expect(
            e,
            isNull,
            reason: 'tab "$menu" (${r.key} @ ${u.key}) gagal: $e',
          );
        }
      });
    }
  }
}

/// Daftar menu yang harusnya terlihat untuk sebuah user.
List<String> menuUntuk(AuthUser u) => [
  'Dashboard',
  if (u.bisaTransaksi) 'Kasir',
  'Obat',
  if (u.bisaMaster) 'Kategori & Rak',
  if (u.bisaTransaksi) 'Riwayat',
];
