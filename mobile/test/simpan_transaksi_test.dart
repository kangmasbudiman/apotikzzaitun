// Uji alur simpan transaksi tanpa server sungguhan.
//
// Tujuannya: memastikan payload yang DIKIRIM aplikasi sama dengan yang
// diharapkan API. Kalau ada bug di sisi mobile, test ini gagal di sini
// tanpa perlu emulator.
import 'dart:convert';

import 'package:apotikdear_mobile/core/api_client.dart';
import 'package:apotikdear_mobile/models/medicine.dart';
import 'package:apotikdear_mobile/providers/cart_provider.dart';
import 'package:apotikdear_mobile/services/master_service.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/testing.dart';
import 'package:http/http.dart' as http;

typedef _Responder = Object Function(Uri url, String body);

/// Client tiruan: mencatat request, membalas response yang diminta.
class _RekamRequest {
  final List<Map<String, dynamic>> bodies = [];
  final List<Uri> urls = [];

  _RekamRequest(this._respon) {
    client = MockClient((req) async {
      urls.add(req.url);
      if (req.body.isNotEmpty) {
        bodies.add(jsonDecode(req.body) as Map<String, dynamic>);
      }
      return http.Response(
        jsonEncode(_respon(req.url, req.body)),
        200,
        headers: {'content-type': 'application/json'},
      );
    });
  }

  final _Responder _respon;

  late final http.Client client;
}

Medicine _obat(String id, int harga, {int stock = 10}) => Medicine(
  id: id,
  barcode: '899$id',
  nama: 'Obat $id',
  noisbn: null,
  suplier: 'SUP',
  tahun: '2026',
  hargaPokok: harga - 1000,
  hargaJual: harga,
  disc: 0,
  stock: stock,
  minStok: 5,
  stokStatus: 'aman',
  idKategori: 1,
  idRak: 1,
  namaKategori: 'OBT',
  namaRak: 'Rak 1',
);

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  group('Simpan transaksi dari mobile', () {
    test('kirim 1 item dengan benar', () async {
      final rekam = _RekamRequest((url, body) {
        expect(url.toString(), contains('/transaksi'));
        return {
          'message': 'Transaksi berhasil disimpan.',
          'data': {
            'id': 'A12345-678901-11102026',
            'id_user': '1',
            'nama_user': 'Uji',
            'tanggal': '2026-10-11 10:00:00',
            'status': true,
            'total': 12000,
            'total_bayar': 15000,
            'diskon': 0,
            'grandtotal': 12000,
            'kembalian': 3000,
            'jumlah_item': 2,
            'items': [
              {
                'id': 'T1',
                'id_buku': 'OB00001',
                'obat': 'Obat OB00001',
                'harga_pokok': 5000,
                'harga_jual': 6000,
                'disc': 0,
                'jumlah': 2,
                'subtotal': 12000,
              },
            ],
          },
        };
      });

      final api = ApiClient(
        client: rekam.client,
        storage: const FlutterSecureStorage(
          aOptions: AndroidOptions(encryptedSharedPreferences: true),
        ),
      );

      final cart = CartProvider();
      cart.tambah(_obat('OB00001', 6000));
      cart.tambah(_obat('OB00001', 6000));

      final hasil = await cart.simpan(
        service: TransactionService(api),
        totalBayar: 15000,
      );

      expect(hasil, isNotNull, reason: 'transaksi harus berhasil disimpan');
      expect(hasil!.kembalian, 3000);

      // Ini yang dikirim ke server.
      final body = rekam.bodies.single;
      expect(body['items'], isA<List>());
      expect((body['items'] as List).length, 1, reason: '1 id obat unik');
      expect((body['items'] as List).first['id_buku'], 'OB00001');
      expect((body['items'] as List).first['jumlah'], 2);
      expect(body['total_bayar'], 15000);
      expect(body['diskon'], 0);

      // Harga TIDAK boleh ikut terkirim.
      expect(body.containsKey('harga_jual'), isFalse);
      expect(body.containsKey('harga_pokok'), isFalse);
    });

    test('kirim banyak item + diskon', () async {
      final rekam = _RekamRequest(
        (url, body) => {
          'message': 'ok',
          'data': {
            'id': 'A1',
            'nama_user': 'Uji',
            'tanggal': '2026-10-11 10:00:00',
            'status': true,
            'total': 14500,
            'total_bayar': 20000,
            'diskon': 5000,
            'grandtotal': 19500,
            'kembalian': 5500,
            'jumlah_item': 3,
            'items': const [],
          },
        },
      );

      final api = ApiClient(
        client: rekam.client,
        storage: const FlutterSecureStorage(
          aOptions: AndroidOptions(encryptedSharedPreferences: true),
        ),
      );

      final cart = CartProvider();
      cart.tambah(_obat('OB00001', 6000));
      cart.tambah(_obat('OB00002', 4500, stock: 2));
      cart.tambah(_obat('OB00002', 4500, stock: 2));
      cart.setDiskon(5000);

      final hasil = await cart.simpan(
        service: TransactionService(api),
        totalBayar: 20000,
      );

      expect(hasil, isNotNull);

      final body = rekam.bodies.single;
      expect((body['items'] as List).length, 2);
      expect(body['diskon'], 5000);
      expect(body['total_bayar'], 20000);
    });

    test('menampilkan pesan server saat stok kurang', () async {
      final rekam = _RekamRequest(
        (url, body) => ({
          'message': 'Stok FOLAMIL tidak cukup. Tersedia 3, diminta 9.',
          'kode': 'STOK_KURANG',
          'errors': {'items': 'Stok FOLAMIL tidak cukup.'},
        }),
      );

      // Ubah status jadi 422.
      final api422 = ApiClient(
        client: MockClient((req) async {
          rekam.bodies.add(jsonDecode(req.body) as Map<String, dynamic>);
          return http.Response(
            jsonEncode({
              'message': 'Stok FOLAMIL tidak cukup. Tersedia 3, diminta 9.',
              'kode': 'STOK_KURANG',
              'errors': {'items': 'Stok FOLAMIL tidak cukup.'},
            }),
            422,
            headers: {'content-type': 'application/json'},
          );
        }),
        storage: const FlutterSecureStorage(
          aOptions: AndroidOptions(encryptedSharedPreferences: true),
        ),
      );

      final cart = CartProvider();
      cart.tambah(_obat('OB00001', 6000));

      final hasil = await cart.simpan(
        service: TransactionService(api422),
        totalBayar: 6000,
      );

      expect(hasil, isNull, reason: 'harus gagal');
      expect(cart.pesanGagal, contains('Stok FOLAMIL tidak cukup'));

      // Keranjang harus tetap utuh supaya kasir bisa correcting.
      expect(cart.kosong, isFalse);
      expect(cart.jumlahItem, 1);
    });
  });
}
