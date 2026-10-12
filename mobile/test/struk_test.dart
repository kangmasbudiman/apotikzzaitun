// Uji penyusun struk ESC/POS tanpa printer sungguhan.
//
// Yang diuji: format angka, isi struk, dan elite bukan byte acak.
import 'package:apotikdear_mobile/models/cart_item.dart';
import 'package:apotikdear_mobile/services/printer_service.dart';
import 'package:esc_pos_utils/esc_pos_utils.dart';
import 'package:flutter_test/flutter_test.dart';

TransactionResult _trx({int diskon = 0, int kembalian = 3000}) {
  return TransactionResult(
    id: 'A87583-573511-11102026',
    namaUser: 'Meli Yani',
    tanggal: DateTime(2026, 10, 11, 9, 47),
    total: 11500,
    totalBayar: 20000,
    diskon: diskon,
    kembalian: kembalian,
    jumlahItem: 3,
    items: const [
      TransactionItem(
        idBuku: 'OB00943',
        obat: 'PARACETAMOL 500 TAB @STRIP',
        hargaJual: 6000,
        jumlah: 2,
        subtotal: 12000,
      ),
      TransactionItem(
        idBuku: 'OB00042',
        obat: 'VITAMIN C 500 MG',
        hargaJual: 2500,
        jumlah: 1,
        subtotal: 2500,
      ),
    ],
  );
}

void main() {
  // CapabilityProfile.load() membaca berkas JSON lewat rootBundle, jadi
  // binding Flutter harus sudah siap.
  TestWidgetsFlutterBinding.ensureInitialized();

  group('Format angka struk', () {
    test('pemisah ribuan memakai titik', () {
      expect(PrinterService.rpSingkat(15000), '15.000');
      expect(PrinterService.rpSingkat(1000), '1.000');
      expect(PrinterService.rpSingkat(999), '999');
      expect(PrinterService.rpSingkat(1234567), '1.234.567');
      expect(PrinterService.rpSingkat(0), '0');
    });

    test('nilai negatif dapat tanda minus', () {
      expect(PrinterService.rpSingkat(-5000), '-5.000');
    });
  });

  group('Penyusunan struk', () {
    late Generator generator;

    setUpAll(() async {
      final profil = await CapabilityProfile.load();
      generator = Generator(PaperSize.mm58, profil);
    });

    test('menghasilkan byte yang tidak kosong', () {
      final bytes = PrinterService.susunStruk(
        generator: generator,
        trx: _trx(),
        namaToko: 'Az Zaitun',
        alamatToko: 'Jl. Hayam wuruk no. 58',
        teleponToko: '082269854883',
        setelan: PrinterSetting.kosong,
      );

      expect(bytes, isNotEmpty);
      expect(bytes.length, greaterThan(100));
    });

    test('memuat perintah potong kertas kalau diaktifkan', () {
      final denganPotong = PrinterService.susunStruk(
        generator: generator,
        trx: _trx(),
        namaToko: 'Toko',
        alamatToko: '',
        teleponToko: '',
        setelan: PrinterSetting.kosong, // potongOtomatis = true
      );

      final tanpaPotong = PrinterService.susunStruk(
        generator: generator,
        trx: _trx(),
        namaToko: 'Toko',
        alamatToko: '',
        teleponToko: '',
        setelan: PrinterSetting.kosong.copyWith(potongOtomatis: false),
      );

      // Perintah potong menambah beberapa byte di akhir.
      expect(denganPotong.length, greaterThan(tanpaPotong.length));
    });

    test('kedua ukuran kertas menghasilkan struk valid', () async {
      // Dicerminkan seperti cara PrinterService.cetakStruk membuat
      // generator: ukuran kertas ikut menentukan generator.
      //
      // Catatan: struk 80mm tidak selalu lebih pendek ukurannya dalam byte,
      // karena garis pemisah (hr) ikut lebih panjang. Yang di sini dicek
      // adalah keduanya tetap valid dan tidak kosong.
      final profil = await CapabilityProfile.load();

      for (final (ukuran, karakter) in [
        (PaperSize.mm58, 32),
        (PaperSize.mm80, 48),
      ]) {
        final bytes = PrinterService.susunStruk(
          generator: Generator(ukuran, profil),
          trx: _trx(),
          namaToko: 'Toko Apotek',
          alamatToko: 'Jl. Contoh Nomor Satu',
          teleponToko: '',
          setelan: PrinterSetting.kosong.copyWith(karakterPerBaris: karakter),
        );

        expect(bytes, isNotEmpty, reason: 'struk $ukuran harus ada isinya');
        expect(bytes.length, greaterThan(300));
      }
    });

    test('tidak melempar exception walau data tidak lengkap', () {
      // Namapasien/tgl_lahir sudah dihapus dari schema, tanggal pun bisa null.
      final tanpaTanggal = TransactionResult(
        id: 'X-1-2',
        namaUser: 'Kasir',
        tanggal: null,
        total: 0,
        totalBayar: 0,
        diskon: 0,
        kembalian: 0,
        jumlahItem: 0,
        items: const [],
      );

      expect(
        () => PrinterService.susunStruk(
          generator: generator,
          trx: tanpaTanggal,
          namaToko: '',
          alamatToko: '',
          teleponToko: '',
          setelan: PrinterSetting.kosong,
        ),
        returnsNormally,
      );
    });
  });

  group('Pengaturan printer', () {
    test('default memakai kertas 58mm dan potong otomatis', () {
      const s = PrinterSetting.kosong;
      expect(s.karakterPerBaris, 32);
      expect(s.potongOtomatis, isTrue);
      expect(s.sudahDipilih, isFalse);
    });

    test('copyWith menimpa hanya field yang diberikan', () {
      const asli = PrinterSetting.kosong;
      final baru = asli.copyWith(mac: '00:11:22:33:44:55');

      expect(baru.mac, '00:11:22:33:44:55');
      expect(baru.sudahDipilih, isTrue);
      expect(baru.karakterPerBaris, asli.karakterPerBaris);
      expect(baru.footer, asli.footer);
    });
  });
}
