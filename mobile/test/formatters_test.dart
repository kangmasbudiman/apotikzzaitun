// Penjaga untuk LocaleDataException.
//
// Bug ini dulu lolos dari test karena `Fmt.sejak()` untuk tanggal "hari ini"
// mengembalikan teks relatif ("Baru saja") tanpa pernah menyentuh DateFormat.
// Test ini sengaja memakai tanggal yang pasti butuh DateFormat.
import 'package:apotikdear_mobile/core/formatters.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  // Sengaja TIDAK memanggil Fmt.init() di sini — test ini membuktikan
  // bahwa format tanggal tidak melempar exception meski locale data
  // belum diinisialisasi.
  //
  // Kesalahan yang sama sebelumnya: initializeDateFormatting tidak dipanggil
  // di main(), sehingga dialog struk langsung crash setelah transaksi disimpan.

  group('Format tanggal tidak boleh melempar exception', () {
    final d = DateTime(2026, 10, 11, 14, 30);
    final lama = DateTime(2025, 1, 5, 8, 5);

    test('tanggal()', () {
      expect(() => Fmt.tanggal(d), returnsNormally);
      expect(Fmt.tanggal(d), isNotEmpty);
    });

    test('tanggalJam() — dipakai dialog struk setelah simpan', () {
      expect(() => Fmt.tanggalJam(d), returnsNormally);
      expect(Fmt.tanggalJam(d), isNotEmpty);
    });

    test('jam()', () {
      expect(() => Fmt.jam(d), returnsNormally);
    });

    test('sejak() untuk tanggal LAMA — wajib pakai DateFormat', () {
      // Inilah kasus yang dulu terlewat: tanggal hari ini menghasilkan
      // "Baru saja" sehingga DateFormat tidak pernah tersentuh.
      expect(() => Fmt.sejak(lama), returnsNormally);
      expect(Fmt.sejak(lama), isNotEmpty);
    });

    test('rupiah & angka', () {
      expect(Fmt.rp(15000), contains('15.000'));
      expect(Fmt.angka(15000), '15.000');
      expect(Fmt.tanggalApi(d), '2026-10-11');
    });

    test('nilai null tidak boleh crash', () {
      expect(Fmt.tanggal(null), '-');
      expect(Fmt.tanggalJam(null), '-');
      expect(Fmt.jam(null), '-');
      expect(Fmt.sejak(null), '-');
      expect(Fmt.rp(null), contains('0'));
    });
  });

  group('Setelah Fmt.init() dipanggil', () {
    test('locale id_ID termuat', () {
      Fmt.init();
      // Format Indonesia memakai singkatan bulan Bahasa Indonesia.
      expect(Fmt.tanggal(DateTime(2026, 1, 5)), '5 Jan 2026');
      expect(Fmt.tanggalJam(DateTime(2026, 1, 5, 9, 7)), '5 Jan 2026, 09:07');
    });
  });
}
