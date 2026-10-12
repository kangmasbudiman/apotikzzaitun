import 'package:esc_pos_utils/esc_pos_utils.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_bluetooth_serial_plus/flutter_bluetooth_serial_plus.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import '../models/cart_item.dart';

/// Printer Bluetooth yang tersimpan.
class PrinterDevice {
  const PrinterDevice({required this.mac, required this.nama});

  /// Alamat MAC, mis. `00:11:22:33:44:55`.
  final String mac;
  final String nama;
}

/// Pengaturan printer.
class PrinterSetting {
  const PrinterSetting({
    required this.mac,
    required this.nama,
    required this.karakterPerBaris,
    required this.potongOtomatis,
    required this.footer,
  });

  final String mac;
  final String nama;

  /// 32 untuk kertas 58mm, 48 untuk 80mm.
  final int karakterPerBaris;

  /// Kirim perintah potong kertas setelah mencetak.
  final bool potongOtomatis;

  /// Teks penutup di kaki struk.
  final String footer;

  bool get sudahDipilih => mac.isNotEmpty;

  PrinterSetting copyWith({
    String? mac,
    String? nama,
    int? karakterPerBaris,
    bool? potongOtomatis,
    String? footer,
  }) => PrinterSetting(
    mac: mac ?? this.mac,
    nama: nama ?? this.nama,
    karakterPerBaris: karakterPerBaris ?? this.karakterPerBaris,
    potongOtomatis: potongOtomatis ?? this.potongOtomatis,
    footer: footer ?? this.footer,
  );

  static const kosong = PrinterSetting(
    mac: '',
    nama: '',
    karakterPerBaris: 32,
    potongOtomatis: true,
    footer: 'Terima kasih telah berbelanja',
  );
}

/// Error saat mencetak — pesannya aman ditampilkan ke kasir.
class PrintException implements Exception {
  PrintException(this.pesan);
  final String pesan;

  @override
  String toString() => pesan;
}

/// Layanan cetak struk ke printer thermal Bluetooth (ESC/POS).
///
/// Printer kasir umumnya merk Xprinter, Iware, Zjiang, Rongta, atau printer
/// 58mm generik yang terdaftar dengan nama "Bluetooth Printer" / "MTP-II".
class PrinterService {
  PrinterService({FlutterSecureStorage? storage})
    : _storage = storage ?? const FlutterSecureStorage();

  final FlutterSecureStorage _storage;
  final FlutterBluetoothSerial _bt = FlutterBluetoothSerial.instance;

  BluetoothConnection? _koneksi;

  bool tersambung = false;
  String? macAktif;

  PrinterSetting? _cache;

  static const _kMac = 'printer_mac';
  static const _kNama = 'printer_nama';
  static const _kKarakter = 'printer_karakter';
  static const _kPotong = 'printer_potong';
  static const _kFooter = 'printer_footer';

  // ------------------------------------------------------------ pengaturan

  Future<PrinterSetting> pengaturan() async {
    if (_cache != null) return _cache!;

    final v = await _storage.readAll();

    _cache = PrinterSetting(
      mac: v[_kMac] ?? '',
      nama: v[_kNama] ?? '',
      karakterPerBaris: int.tryParse(v[_kKarakter] ?? '') ?? 32,
      potongOtomatis: v[_kPotong] != '0',
      footer: (v[_kFooter]?.isNotEmpty ?? false)
          ? v[_kFooter]!
          : PrinterSetting.kosong.footer,
    );

    return _cache!;
  }

  Future<void> simpanPengaturan(PrinterSetting s) async {
    await _storage.write(key: _kMac, value: s.mac);
    await _storage.write(key: _kNama, value: s.nama);
    await _storage.write(key: _kKarakter, value: '${s.karakterPerBaris}');
    await _storage.write(key: _kPotong, value: s.potongOtomatis ? '1' : '0');
    await _storage.write(key: _kFooter, value: s.footer);
    _cache = s;
  }

  // ------------------------------------------------------------- bluetooth

  /// Pastikan Bluetooth aktif & berizin.
  ///
  /// Android 12+ meminta izin runtime; Android 11 ke bawah hanya perlu
  /// bluetooth menyala.
  Future<bool> siap() async {
    try {
      await _bt.requestEnable();
      return true;
    } catch (e) {
      debugPrint('Bluetooth belum bisa diaktifkan: $e');
      return false;
    }
  }

  /// Daftar perangkat yang sudah dipasangkan dengan HP.
  ///
  /// Ini cara paling andal untuk printer kasir: printer hampir selalu sudah
  /// dipasangkan dari pengaturan Bluetooth Android, dan tidak butuh
  /// discovery yang mahal dan lambat.
  Future<List<PrinterDevice>> perangkatTerpasang() async {
    try {
      final daftar = await _bt.getBondedDevices();

      final hasil =
          daftar
              .map(
                (d) => PrinterDevice(
                  mac: d.address,
                  nama: (d.name?.isNotEmpty ?? false) ? d.name! : 'Tanpa nama',
                ),
              )
              .where((d) => d.mac.isNotEmpty)
              .toList()
            ..sort((a, b) => a.nama.compareTo(b.nama));

      return hasil;
    } catch (e) {
      debugPrint('Gagal membaca perangkat BT: $e');
      return const [];
    }
  }

  /// Sambung ke printer berdasarkan alamat MAC.
  Future<bool> sambung(String mac) async {
    await putuskan();

    if (mac.isEmpty) return false;

    try {
      _koneksi = await BluetoothConnection.toAddress(mac);

      // Beri waktu singkat untuk handshake selesai.
      await Future<void>.delayed(const Duration(milliseconds: 600));

      if (!(_koneksi?.isConnected ?? false)) {
        debugPrint('Printer $mac terhubung tapi belum siap.');
      }

      tersambung = true;
      macAktif = mac;
      return true;
    } catch (e) {
      debugPrint('Gagal sambung printer $mac: $e');
      await putuskan();
      return false;
    }
  }

  /// Sambung otomatis ke printer yang tersimpan.
  Future<bool> sambungTersimpan() async {
    final s = await pengaturan();
    if (!s.sudahDipilih) return false;
    return sambung(s.mac);
  }

  Future<void> putuskan() async {
    try {
      _koneksi?.finish();
    } catch (_) {
      // Belum pernah tersambung — abaikan.
    }
    _koneksi = null;
    tersambung = false;
    macAktif = null;
  }

  // --------------------------------------------------------------- cetak

  /// Cetak struk transaksi.
  ///
  /// [autoSambung]=true akan mencoba menyambung ke printer tersimpan dulu
  /// supaya kasir tidak perlu menekan tombol apa pun.
  Future<void> cetakStruk({
    required TransactionResult trx,
    required String namaToko,
    required String alamatToko,
    String teleponToko = '',
    bool autoSambung = true,
  }) async {
    if (!tersambung && autoSambung) {
      await sambungTersimpan();
    }

    if (!tersambung) {
      throw PrintException(
        'Printer belum tersambung. Buka Pengaturan Printer lalu pilih printer.',
      );
    }

    final setelan = await pengaturan();

    final profil = await _profil();
    final ukuran = setelan.karakterPerBaris >= 40
        ? PaperSize.mm80
        : PaperSize.mm58;
    final generator = Generator(ukuran, profil);

    final bytes = susunStruk(
      generator: generator,
      trx: trx,
      namaToko: namaToko,
      alamatToko: alamatToko,
      teleponToko: teleponToko,
      setelan: setelan,
    );

    try {
      _koneksi?.output.add(Uint8List.fromList(bytes));

      // Beri waktu printer memproses sebelum koneksi ditutup.
      await Future<void>.delayed(const Duration(milliseconds: 400));
    } catch (e) {
      debugPrint('Gagal tulis ke printer: $e');
      throw PrintException(
        'Gagal mengirim ke printer. Periksa koneksi printer.',
      );
    }
  }

  /// Cetak struk percobaan (untuk tes printer sebelum dipakai).
  Future<void> cetakUji({bool autoSambung = true}) async {
    if (!tersambung && autoSambung) {
      await sambungTersimpan();
    }
    if (!tersambung) {
      throw PrintException('Printer belum tersambung.');
    }

    await cetakStruk(
      trx: _trxUji(),
      namaToko: 'APOTIKDEAR POS',
      alamatToko: 'Jl. Contoh No. 1',
      teleponToko: '0812-0000-0000',
      autoSambung: false,
    );
  }

  static TransactionResult _trxUji() => TransactionResult(
    id: 'A00000-000000-00000000',
    namaUser: 'Uji Printer',
    tanggal: DateTime(2026, 1, 1, 10, 30),
    total: 25000,
    totalBayar: 50000,
    diskon: 0,
    kembalian: 25000,
    jumlahItem: 2,
    items: [
      const TransactionItem(
        idBuku: 'OB00001',
        obat: 'PARACETAMOL 500 TAB',
        hargaJual: 15000,
        jumlah: 1,
        subtotal: 15000,
      ),
      const TransactionItem(
        idBuku: 'OB00002',
        obat: 'VITAMIN C 500 MG',
        hargaJual: 10000,
        jumlah: 1,
        subtotal: 10000,
      ),
    ],
  );

  static Future<CapabilityProfile> _profil() async {
    try {
      return await CapabilityProfile.load();
    } catch (e) {
      // Printer thermal generik tidak punya profile spesifik.
      // 'default' selalu tersedia karena datanya dibundel di intl package.
      debugPrint('CapabilityProfile tidak tersedia, pakai default: $e');
      return await CapabilityProfile.load(name: 'default');
    }
  }

  // ------------------------------------------------------- penyusun struk

  /// Susun byte ESC/POS untuk satu struk.
  ///
  /// Dipisah dari [cetakStruk] supaya bisa diuji tanpa printer sungguhan.
  @visibleForTesting
  static List<int> susunStruk({
    required Generator generator,
    required TransactionResult trx,
    required String namaToko,
    required String alamatToko,
    required String teleponToko,
    required PrinterSetting setelan,
  }) {
    const kecil = PosStyles(fontType: PosFontType.fontB);
    final lebar = setelan.karakterPerBaris;
    final bytes = <int>[];

    void tambah(List<int> b) => bytes.addAll(b);

    // ------------------------------- kop -------------------------------
    tambah(
      generator.text(
        namaToko.toUpperCase(),
        styles: const PosStyles(
          align: PosAlign.center,
          height: PosTextSize.size2,
          width: PosTextSize.size2,
          bold: true,
        ),
        maxCharsPerLine: lebar,
      ),
    );
    tambah(generator.emptyLines(1));

    if (alamatToko.trim().isNotEmpty) {
      tambah(
        generator.text(
          alamatToko,
          styles: const PosStyles(align: PosAlign.center),
          maxCharsPerLine: lebar,
        ),
      );
    }

    if (teleponToko.trim().isNotEmpty) {
      tambah(
        generator.text(
          teleponToko,
          styles: const PosStyles(align: PosAlign.center),
          maxCharsPerLine: lebar,
        ),
      );
    }

    tambah(generator.emptyLines(1));
    tambah(generator.hr(ch: '=', len: lebar));

    // -------------------------- info transaksi -------------------------
    //
    // CATATAN: lebar kolom pada Generator.row() berada di skala 1..12 dan
    // WAJIB berjumlah tepat 12 — bukan jumlah karakter. Teks yang melebihi
    // ruangnya otomatis dipindah ke baris berikutnya oleh library, jadi
    // nomor transaksi yang panjang tetap aman.
    void baris(String label, String nilai) {
      tambah(
        generator.row([
          PosColumn(text: label, width: 4, styles: kecil),
          PosColumn(
            text: nilai,
            width: 8,
            styles: const PosStyles(align: PosAlign.right),
          ),
        ]),
      );
    }

    baris('No.', trx.id);
    baris('Waktu', _jamSingkat(trx.tanggal));
    baris('Kasir', trx.namaUser);
    tambah(generator.hr(ch: '-', len: lebar));

    // ------------------------------ item ------------------------------
    var totalItem = 0;

    for (final item in trx.items) {
      totalItem += item.jumlah;

      tambah(
        generator.text(
          item.obat,
          styles: const PosStyles(bold: true),
          maxCharsPerLine: lebar,
        ),
      );

      // "2 x 6.000" di kiri, subtotal di kanan.
      tambah(
        generator.row([
          PosColumn(
            text: '${item.jumlah} x ${rpSingkat(item.hargaJual)}',
            width: 8,
            styles: kecil,
          ),
          PosColumn(
            text: rpSingkat(item.subtotal),
            width: 4,
            styles: const PosStyles(align: PosAlign.right),
          ),
        ]),
      );

      tambah(generator.emptyLines(1));
    }

    tambah(generator.hr(ch: '-', len: lebar));

    // ------------------------------ total -----------------------------
    void total(String label, String nilai, {bool tebal = false}) {
      // Total diberi ruang lebih lega supaya angka rupiah yang panjang
      // tetap muat (4 dari 12 kolom).
      tambah(
        generator.row([
          PosColumn(
            text: label,
            width: 8,
            styles: tebal
                ? const PosStyles(bold: true, height: PosTextSize.size2)
                : kecil,
          ),
          PosColumn(
            text: nilai,
            width: 4,
            styles: tebal
                ? const PosStyles(
                    align: PosAlign.right,
                    bold: true,
                    height: PosTextSize.size2,
                  )
                : const PosStyles(align: PosAlign.right),
          ),
        ]),
      );
    }

    total('Jumlah item', '$totalItem');
    if (trx.diskon > 0) total('Diskon', '-${rpSingkat(trx.diskon)}');
    total('TOTAL', rpSingkat(trx.total), tebal: true);

    if (trx.totalBayar > 0) total('Bayar', rpSingkat(trx.totalBayar));
    if (trx.kembalian > 0) {
      total('Kembali', rpSingkat(trx.kembalian), tebal: true);
    }

    // ------------------------------- kaki ------------------------------
    tambah(generator.emptyLines(1));
    tambah(generator.hr(ch: '=', len: lebar));
    tambah(
      generator.text(
        setelan.footer,
        styles: const PosStyles(align: PosAlign.center),
        maxCharsPerLine: lebar,
      ),
    );
    tambah(generator.emptyLines(1));
    tambah(
      generator.text(
        trx.id,
        styles: const PosStyles(
          align: PosAlign.center,
          fontType: PosFontType.fontB,
        ),
        maxCharsPerLine: lebar,
      ),
    );

    // Maju kertas supaya bagian yang sudah tercetak tidak ikut terpotong.
    tambah(generator.feed(3));

    if (setelan.potongOtomatis) {
      tambah(generator.cut(mode: PosCutMode.partial));
    }

    return bytes;
  }

  /// Format angka ala Indonesia tanpa simbol & spasi, mis. `15000` -> `15.000`.
  @visibleForTesting
  static String rpSingkat(int nilai) {
    final s = nilai.abs().toString();
    final signum = nilai < 0 ? '-' : '';
    final buf = StringBuffer(signum);

    for (var i = 0; i < s.length; i++) {
      if (i > 0 && (s.length - i) % 3 == 0) buf.write('.');
      buf.write(s[i]);
    }

    return buf.toString();
  }

  static String _jamSingkat(DateTime? d) {
    if (d == null) return '-';
    final dd = d.day.toString().padLeft(2, '0');
    final mm = d.month.toString().padLeft(2, '0');
    final hh = d.hour.toString().padLeft(2, '0');
    final mn = d.minute.toString().padLeft(2, '0');
    return '$dd/$mm/${d.year} $hh:$mn';
  }
}
