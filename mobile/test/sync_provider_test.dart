import 'package:apotikdear_mobile/core/api_client.dart';
import 'package:apotikdear_mobile/providers/sync_provider.dart';
import 'package:apotikdear_mobile/services/local_db.dart';
import 'package:flutter_test/flutter_test.dart';

import 'helpers/db_test_setup.dart';

/// ApiClient palsu yang selalu gagal dengan kode OFFLINE.
class _OfflineApi extends ApiClient {
  @override
  Future<Map<String, dynamic>> post(String path, {Object? body}) async {
    throw ApiException('Tidak bisa menghubungi server.', code: 'OFFLINE');
  }
}

/// ApiClient palsu yang selalu berhasil.
class _OnlineApi extends ApiClient {
  @override
  Future<Map<String, dynamic>> post(String path, {Object? body}) async {
    return {
      'data': {
        'id': 'TRX-001',
        'nama_user': 'Kasir',
        'tanggal': '2026-10-11T10:00:00.000000Z',
        'total': 10000,
        'total_bayar': 10000,
        'diskon': 0,
        'kembalian': 0,
        'jumlah_item': 1,
        'items': [],
      },
    };
  }
}

/// ApiClient palsu yang gagal dengan penolakan server (bukan jaringan).
class _RejectApi extends ApiClient {
  @override
  Future<Map<String, dynamic>> post(String path, {Object? body}) async {
    throw ApiException('Stok tidak cukup.', code: 'STOK_KURANG');
  }
}

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  setUpAll(() async {
    await siapkanDatabase();
  });

  tearDown(() async {
    // Antrean tidak boleh bocor ke test berikutnya.
    await LocalDb.instance.hapusSemuaAntrean();
  });

  group('SyncProvider', () {
    test('transaksi online berhasil', () async {
      final sync = SyncProvider(_OnlineApi());

      final hasil = await sync.kirimTransaksi({
        'items': [
          {'id_buku': 'OB00001', 'jumlah': 2},
        ],
        'diskon': 0,
        'total_bayar': 10000,
      });

      expect(hasil, isTrue);
      expect(sync.jumlahAntrean, 0);
    });

    test('transaksi offline masuk antrean', () async {
      final sync = SyncProvider(_OfflineApi());

      final hasil = await sync.kirimTransaksi({
        'items': [
          {'id_buku': 'OB00002', 'jumlah': 1},
        ],
        'diskon': 0,
        'total_bayar': 5000,
      });

      expect(hasil, isFalse);
      expect(sync.jumlahAntrean, 1);
      expect(sync.status, StatusSync.menunggu);
    });

    test('penolakan server TIDAK masuk antrean', () async {
      final sync = SyncProvider(_RejectApi());

      // Penolakan server (mis. stok kurang) TIDAK boleh masuk antrean —
      // mengirim ulang transaksi yang ditolak tidak ada gunanya.
      expect(
        () => sync.kirimTransaksi({
          'items': [
            {'id_buku': 'OB00001', 'jumlah': 9999},
          ],
          'diskon': 0,
          'total_bayar': 10000,
        }),
        throwsA(isA<ApiException>()),
      );

      expect(sync.jumlahAntrean, 0);
    });

    test('sinkronkan tidak crash saat antrean kosong', () async {
      final sync = SyncProvider(_OnlineApi());

      // Antrean kosong: sinkronkan berhenti lebih awal
      // dan status tetap "kosong" (bukan "selesai",
      // karena tidak ada yang dikirim).
      await sync.sinkronkan();

      expect(sync.status, StatusSync.kosong);
      expect(sync.jumlahAntrean, 0);
    });

    test('sinkronkan tidak crash saat offline', () async {
      final sync = SyncProvider(_OfflineApi());

      // Tidak ada jaringan — sinkronkan harus berhenti dengan aman.
      await sync.sinkronkan();
    });

    test('sinkronkan mengosongkan antrean saat online kembali', () async {
      // Transaksi gagal saat offline → masuk antrean.
      final syncOffline = SyncProvider(_OfflineApi());
      await syncOffline.kirimTransaksi({
        'items': [
          {'id_buku': 'OB00003', 'jumlah': 1},
        ],
        'diskon': 0,
        'total_bayar': 7000,
      });

      expect(syncOffline.jumlahAntrean, 1);

      // Antrean tersimpan di SQLite — provider baru harus bisa
      // membacanya kembali.
      final sync = SyncProvider(_OnlineApi());
      await sync.mulai();

      expect(sync.jumlahAntrean, 1);

      await sync.sinkronkan();

      expect(sync.jumlahAntrean, 0);
      expect(sync.status, StatusSync.selesai);
    });
  });
}
