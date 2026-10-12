import 'package:apotikdear_mobile/models/medicine.dart';
import 'package:apotikdear_mobile/widgets/panel_batch.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

/// Helper: bangun [InfoBatch] dari daftar batch mentah.
InfoBatch _info(List<Map<String, dynamic>> batch) => InfoBatch.fromJson({
  'jumlah_batch': batch.length,
  'batch_terdekat': batch.isEmpty ? null : batch.first['no_batch'],
  'kedaluwarsa_terdekat': batch.isEmpty ? null : batch.first['expired'],
  'hari_lagi_terdekat': batch.isEmpty ? null : batch.first['hari_lagi'],
  'sisa_per_batch': batch,
});

Map<String, dynamic> _batch(
  String no,
  String exp,
  int hari,
  int stok, {
  int harga = 3800,
}) => {
  'no_batch': no,
  'expired': exp,
  'hari_lagi': hari,
  'stok': stok,
  'harga_pokok': harga,
};

Widget _bungkus(Widget anak) => MaterialApp(
  home: Scaffold(
    body: Padding(padding: const EdgeInsets.all(16), child: anak),
  ),
);

void main() {
  group('BatchObat', () {
    test('tingkat mengikuti sisa hari', () {
      BatchObat jadi(int hari) => BatchObat.fromJson({
        'no_batch': 'X',
        'expired': '2026-12-31',
        'hari_lagi': hari,
        'stok': 10,
        'harga_pokok': 1000,
      });

      expect(jadi(-5).tingkat, 'lewat');
      expect(jadi(5).tingkat, 'kritis');
      expect(jadi(45).tingkat, 'mepet');
      expect(jadi(200).tingkat, 'aman');
    });

    test('sudahLewat benar hanya untuk hari negatif', () {
      BatchObat jadi(int hari) => BatchObat.fromJson({
        'no_batch': 'X',
        'expired': '2026-12-31',
        'hari_lagi': hari,
        'stok': 10,
        'harga_pokok': 1000,
      });

      expect(jadi(-1).sudahLewat, isTrue);
      expect(jadi(0).sudahLewat, isFalse);
    });

    test('parse aman terhadap JSON kosong / null', () {
      final b = BatchObat.fromJson({'stok': 7});

      expect(b.noBatch, isNull);
      expect(b.expired, isNull);
      expect(b.stok, 7);
      expect(b.hariLagi, 0);
    });
  });

  group('InfoBatch', () {
    test('baca jumlah dan daftar batch', () {
      final info = _info([
        _batch('54A3046', '2026-03-01', 120, 150),
        _batch('54B3041', '2026-09-01', 283, 60),
      ]);

      expect(info.jumlahBatch, 2);
      expect(info.sisaPerBatch.length, 2);
      expect(info.batchTerdekat, '54A3046');
      expect(info.punyaEkspirasi, isTrue);
    });

    test('punyaEkspirasi false saat tidak ada tanggal', () {
      final info = _info([_batch('SISA-0001', 'null', 0, 40)]);

      // expired null -> tidak ada data kedaluwarsa sama sekali.
      expect(
        InfoBatch.fromJson({
          'jumlah_batch': 1,
          'batch_terdekat': 'SISA-0001',
          'kedaluwarsa_terdekat': null,
          'hari_lagi_terdekat': null,
          'sisa_per_batch': [
            {
              'no_batch': 'SISA-0001',
              'expired': null,
              'hari_lagi': null,
              'stok': 40,
              'harga_pokok': 0,
            },
          ],
        }).punyaEkspirasi,
        isFalse,
      );
      expect(info.jumlahBatch, 1);
    });

    test('adaYangLewat benar bila salah satu batch lewat', () {
      expect(
        _info([
          _batch('A', '2025-01-01', -100, 10),
          _batch('B', '2028-01-01', 500, 10),
        ]).adaYangLewat,
        isTrue,
      );
      expect(_info([_batch('B', '2028-01-01', 500, 10)]).adaYangLewat, isFalse);
    });

    test('JSON kosong tidak melempar', () {
      final info = InfoBatch.fromJson({});

      expect(info.jumlahBatch, 0);
      expect(info.sisaPerBatch, isEmpty);
      expect(info.punyaEkspirasi, isFalse);
      expect(info.hariLagiTerdekat, isNull);
    });
  });

  group('PanelBatch', () {
    testWidgets('ringkas menampilkan batch terdekat (FEFO)', (tester) async {
      await tester.pumpWidget(
        _bungkus(
          PanelBatch(
            info: _info([
              _batch('54A3046', '2026-03-01', 120, 150),
              _batch('54B3041', '2026-09-01', 283, 60),
            ]),
            obat: 'FOLAMIL GENIO',
          ),
        ),
      );

      expect(find.textContaining('FEFO'), findsOneWidget);
      expect(find.textContaining('54A3046'), findsOneWidget);
      // Batch kedua tidak boleh muncul di mode ringkas.
      expect(find.textContaining('54B3041'), findsNothing);
    });

    testWidgets('mode daftar menampilkan semua batch', (tester) async {
      await tester.pumpWidget(
        _bungkus(
          PanelBatch(
            info: _info([
              _batch('54A3046', '2026-03-01', 120, 150),
              _batch('54B3041', '2026-09-01', 283, 60),
            ]),
            obat: 'FOLAMIL GENIO',
            ringkas: false,
          ),
        ),
      );

      expect(find.text('Urutan keluar (FEFO)'), findsOneWidget);
      expect(find.text('54A3046'), findsOneWidget);
      expect(find.text('54B3041'), findsOneWidget);
    });

    testWidgets('batch lewat tanggal diberi label negatif', (tester) async {
      await tester.pumpWidget(
        _bungkus(
          PanelBatch(
            info: _info([_batch('LAMA', '2025-01-01', -30, 12)]),
            obat: 'OBAT LAMA',
            ringkas: false,
          ),
        ),
      );

      expect(find.text('Lewat 30 hari'), findsOneWidget);
    });

    testWidgets('tanpa data kedaluwarsa memberi pesan ramah', (tester) async {
      await tester.pumpWidget(
        _bungkus(
          PanelBatch(
            info: InfoBatch.fromJson({
              'jumlah_batch': 1,
              'kedaluwarsa_terdekat': null,
              'sisa_per_batch': [
                {
                  'no_batch': 'SISA-0001',
                  'expired': null,
                  'hari_lagi': null,
                  'stok': 40,
                  'harga_pokok': 0,
                },
              ],
            }),
            obat: 'OBAT IMPOR',
          ),
        ),
      );

      expect(find.textContaining('Tidak ada data kedaluwarsa'), findsOneWidget);
    });
  });
}
