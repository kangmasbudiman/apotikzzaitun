// Cache obat untuk mode offline.
//
// README menjanjikan "kasir tetap bisa mencari & menjual saat internet
// mati". Tanpa cache yang benar-benar dipakai layar Kasir, janji itu
// tidak terpenuhi: begitu jaringan putus, kasir tidak bisa memasukkan
// obat apa pun ke keranjang, sehingga fitur antrean transaksi ikut
// jadi tidak berguna.
//
// Test ini mengunci dua hal:
//  1. `Medicine` bisa diubah menjadi baris cache dan kembali lagi
//     tanpa kehilangan field yang dipakai kasir.
//  2. Pencarian cache mencocokkan nama, id, dan barcode.
import 'package:apotikdear_mobile/models/medicine.dart';
import 'package:apotikdear_mobile/services/local_db.dart';
import 'package:flutter_test/flutter_test.dart';

import 'helpers/db_test_setup.dart';

const _paracetamol = Medicine(
  id: 'OB00730',
  barcode: '9786230039355',
  nama: 'ACETYLCYSTEINE KAP 200 MG',
  noisbn: null,
  suplier: 'PT Kimia Farma',
  tahun: '2024',
  hargaPokok: 1000,
  hargaJual: 1500,
  disc: 0,
  stock: 17,
  minStok: 20,
  stokStatus: 'menipis',
  idKategori: 8,
  idRak: 5,
  namaKategori: 'Obat Keras',
  namaRak: 'Rak 1',
);

void main() {
  setUp(siapkanDatabase);
  tearDown(() => LocalDb.instance.hapusObatCache());

  group('Medicine <-> MedicineCache', () {
    test(' Medicine.toCache lalu kembali mempertahankan field kasir', () {
      final kembali = _paracetamol.toCache().toMedicine();

      expect(kembali.id, 'OB00730');
      expect(kembali.barcode, '9786230039355');
      expect(kembali.nama, 'ACETYLCYSTEINE KAP 200 MG');
      expect(kembali.hargaJual, 1500);
      expect(kembali.stock, 17);
      expect(kembali.namaKategori, 'Obat Keras');
      expect(kembali.namaRak, 'Rak 1');
    });
  });

  group('pencarian cache', () {
    setUp(() => LocalDb.instance.simpanObat([_paracetamol.toCache()]));

    test('cari berdasarkan nama obat', () async {
      final hasil = await LocalDb.instance.cariObat('ACETYL');

      expect(hasil.map((o) => o.obat), ['ACETYLCYSTEINE KAP 200 MG']);
    });

    test('cari berdasarkan id buku', () async {
      final hasil = await LocalDb.instance.cariObat('OB00730');

      expect(hasil.single.idBuku, 'OB00730');
    });

    test('cari berdasarkan barcode', () async {
      final hasil = await LocalDb.instance.cariObat('9786230039355');

      expect(hasil.single.kode, '9786230039355');
    });

    test('obat tidak ada mengembalikan daftar kosong', () async {
      final hasil = await LocalDb.instance.cariObat('ZZZZ');

      expect(hasil, isEmpty);
    });
  });
}