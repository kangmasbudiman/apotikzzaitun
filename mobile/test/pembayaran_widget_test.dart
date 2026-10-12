// Uji widget untuk alur pembayaran.
//
// Tujuannya: memastikan menekan "Simpan Transaksi" tidak melempar error
// navigasi (mis. memakai context sheet yang sudah ditutup) dan struk
// tetap dikembalikan ke pemanggil dengan benar.
import 'dart:async';
import 'dart:convert';

import 'package:apotikdear_mobile/core/api_client.dart';
import 'package:apotikdear_mobile/core/theme.dart';
import 'package:apotikdear_mobile/models/auth_user.dart';
import 'package:apotikdear_mobile/models/medicine.dart';
import 'package:apotikdear_mobile/providers/auth_provider.dart';
import 'package:apotikdear_mobile/providers/cart_provider.dart';
import 'package:apotikdear_mobile/screens/kasir/pembayaran_sheet.dart';
import 'package:apotikdear_mobile/services/auth_service.dart';
import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:provider/provider.dart';

Medicine _obat() => Medicine(
  id: 'OB00001',
  barcode: '899123',
  nama: 'FOLAMIL GENIO',
  noisbn: null,
  suplier: 'SUP',
  tahun: '2026',
  hargaPokok: 4500,
  hargaJual: 6300,
  disc: 0,
  stock: 50,
  minStok: 5,
  stokStatus: 'aman',
  idKategori: 1,
  idRak: 1,
  namaKategori: 'OBT',
  namaRak: 'Rak 1',
);

/// Pump beberapa frame supaya animasi sempat jalan.
///
/// Dua hal yang perlu diperhatikan:
///  * Jangan pakai `pumpAndSettle` — ada CircularProgressIndicator yang
///    berputar tanpa henti sehingga tidak akan pernah sampai idle.
///  * `tester.pump()` hanya menggerakkan waktu BUATAN. Our HTTP (MockClient)
///    memakai async sungguhan, jadi perlu diselingi `runAsync`.
Future<void> _tenang(WidgetTester tester, {bool tungguJaringan = false}) async {
  if (tungguJaringan) {
    // Biarkan waktu nyata berjalan supaya request HTTP selesai.
    await tester.runAsync(
      () => Future<void>.delayed(const Duration(milliseconds: 400)),
    );
  }

  for (var i = 0; i < 10; i++) {
    await tester.pump(const Duration(milliseconds: 120));
  }
}

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  testWidgets('Simpan Transaksi menutup sheet dan mengembalikan struk', (
    tester,
  ) async {
    // Ukuran layar test default (800x600) terlalu pendek: sheet pembayaran
    // lebih tinggi dari itu, sehingga tombol "Simpan Transaksi" berada di luar
    // area yang bisa diketuk dan tap-nya tidak mengenai.
    tester.view.physicalSize = const Size(1080, 2200);
    tester.view.devicePixelRatio = 1.0;
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);

    final api = ApiClient(
      client: MockClient((req) async {
        if (req.url.path.endsWith('/transaksi')) {
          return http.Response(
            jsonEncode({
              'message': 'Transaksi berhasil disimpan.',
              'data': {
                'id': 'A96996-300402-11102026',
                'id_user': '1',
                'nama_user': 'Uji Kasir',
                'tanggal': '2026-10-11 09:47:38',
                'status': true,
                'total': 12600,
                'total_bayar': 15000,
                'diskon': 0,
                'grandtotal': 12600,
                'kembalian': 2400,
                'jumlah_item': 2,
                'items': [
                  {
                    'id': 'T1',
                    'id_buku': 'OB00001',
                    'obat': 'FOLAMIL GENIO',
                    'harga_pokok': 4500,
                    'harga_jual': 6300,
                    'disc': 0,
                    'jumlah': 2,
                    'subtotal': 12600,
                  },
                ],
              },
            }),
            201,
            headers: {'content-type': 'application/json'},
          );
        }

        return http.Response(
          jsonEncode({'data': <Map<String, dynamic>>[]}),
          200,
          headers: {'content-type': 'application/json'},
        );
      }),
      storage: const FlutterSecureStorage(
        aOptions: AndroidOptions(encryptedSharedPreferences: true),
      ),
    );

    final auth = AuthProvider(api: api, service: AuthService(api))
      ..debugSetUser(
        const AuthUser(
          id: '1',
          nama: 'Uji Kasir',
          username: 'uji',
          hakAkses: 1,
          role: 'kasir',
          isAdmin: false,
          bisaTransaksi: true,
          bisaMaster: false,
        ),
      );

    final cart = CartProvider();
    cart.tambah(_obat());
    cart.tambah(_obat());

    late BuildContext capturedContext;

    await tester.pumpWidget(
      MultiProvider(
        providers: [
          Provider<ApiClient>.value(value: api),
          ChangeNotifierProvider<AuthProvider>.value(value: auth),
          ChangeNotifierProvider<CartProvider>.value(value: cart),
        ],
        child: MaterialApp(
          theme: AppTheme.light(),
          home: Builder(
            builder: (c) {
              capturedContext = c;
              return const Scaffold(body: Center(child: Text('Layar Kasir')));
            },
          ),
        ),
      ),
    );
    await tester.pump();

    // Buka sheet pembayaran.
    //
    // PENTING: future showModalBottomSheet tidak boleh di-await di sini —
    // future itu baru selesai setelah sheet ditutup, padahal kita masih
    // perlu menyentuh tombolnya. Meng-await akan membuat test deadlock.
    dynamic hasil;
    unawaited(
      showModalBottomSheet<dynamic>(
        context: capturedContext,
        isScrollControlled: true,
        useSafeArea: true,
        backgroundColor: Colors.transparent,
        builder: (_) => PembayaranSheet(api: api, namaKasir: 'Uji Kasir'),
      ).then((v) => hasil = v),
    );

    await _tenang(tester);

    // Total terisi otomatis dari keranjang, jadi tombol harus ada & aktif.
    // Pastikan tombolnya benar-benar terlihat & bisa diketuk.
    await tester.ensureVisible(find.text('Simpan Transaksi'));
    await _tenang(tester);

    expect(find.text('Simpan Transaksi'), findsOneWidget);

    await tester.tap(find.text('Simpan Transaksi'));
    await _tenang(tester, tungguJaringan: true);

    // Ini inti dari test: tidak boleh ada error navigasi / deactivated widget.
    expect(
      tester.takeException(),
      isNull,
      reason: 'tidak boleh melempar error saat menyimpan',
    );

    // Sheet sudah tertutup dan nilainya dikembalikan ke pemanggil.
    expect(hasil, isNotNull, reason: 'sheet harus mengembalikan hasil simpan');
    expect(hasil.id, 'A96996-300402-11102026');
    expect(hasil.kembalian, 2400);
    expect(hasil.total, 12600);

    // Keranjang dikosongkan setelah berhasil.
    expect(cart.kosong, isTrue);
    expect(cart.jumlahItem, 0);
  });
}
