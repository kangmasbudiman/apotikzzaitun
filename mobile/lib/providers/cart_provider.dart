import 'package:flutter/foundation.dart';

import '../core/api_client.dart';
import '../models/cart_item.dart';
import '../models/medicine.dart';
import '../services/master_service.dart';
import 'sync_provider.dart';

/// Kesalahan saat menambah barang ke keranjang.
class CartError {
  const CartError(this.pesan, this.idObat);
  final String pesan;
  final String idObat;
}

class CartProvider extends ChangeNotifier {
  // ignore: prefer_initializing_formals
  // ignore: prefer_initializing_formals
  CartProvider({SyncProvider? sync}) : _sync = sync;

  /// Bisa null di unit test yang tidak peduli offline mode.
  final SyncProvider? _sync;

  final List<CartItem> _items = [];

  List<CartItem> get items => List.unmodifiable(_items);
  bool get kosong => _items.isEmpty;
  int get jumlahItem => _items.fold(0, (a, e) => a + e.jumlah);

  int get subtotal => _items.fold(0, (a, e) => a + e.subtotal);

  int diskon = 0;

  /// Nominal yang harus dibayar (setelah diskon, tidak pernah negatif).
  int get grandTotal {
    final v = subtotal - diskon;
    return v < 0 ? 0 : v;
  }

  int _indexOf(String id) => _items.indexWhere((e) => e.obat.id == id);

  /// Tambah satu obat ke keranjang.
  ///
  /// Mengembalikan [CartError] kalau stoknya tidak cukup — pemanggil bisa
  /// menampilkan snackbar dengan pesan server apa adanya.
  CartError? tambah(Medicine obat, {int jumlah = 1}) {
    final i = _indexOf(obat.id);

    if (obat.stock <= 0) {
      return CartError('Stok ${obat.nama} habis.', obat.id);
    }

    if (i >= 0) {
      final baru = _items[i].jumlah + jumlah;

      if (baru > obat.stock) {
        return CartError('Stok ${obat.nama} hanya ${obat.stock}.', obat.id);
      }

      _items[i].jumlah = baru;
    } else {
      if (jumlah > obat.stock) {
        return CartError('Stok ${obat.nama} hanya ${obat.stock}.', obat.id);
      }
      _items.add(CartItem(obat: obat, jumlah: jumlah));
    }

    _kurangiDiskonAgarValid();
    notifyListeners();
    return null;
  }

  void setJumlah(String idObat, int jumlah) {
    final i = _indexOf(idObat);
    if (i < 0) return;

    if (jumlah <= 0) {
      _items.removeAt(i);
    } else {
      // Jangan biarkan melebihi stok yang diketahui perangkat.
      final batas = _items[i].obat.stock;
      _items[i].jumlah = jumlah > batas ? batas : jumlah;
    }

    _kurangiDiskonAgarValid();
    notifyListeners();
  }

  void tambahSatu(String idObat) {
    final i = _indexOf(idObat);
    if (i < 0) return;
    setJumlah(idObat, _items[i].jumlah + 1);
  }

  void kurangiSatu(String idObat) {
    final i = _indexOf(idObat);
    if (i < 0) return;
    setJumlah(idObat, _items[i].jumlah - 1);
  }

  void hapus(String idObat) {
    _items.removeWhere((e) => e.obat.id == idObat);
    _kurangiDiskonAgarValid();
    notifyListeners();
  }

  void setDiskon(int nilai) {
    diskon = nilai < 0 ? 0 : nilai;
    if (diskon > subtotal) diskon = subtotal;
    notifyListeners();
  }

  void bersihkan() {
    _items.clear();
    diskon = 0;
    notifyListeners();
  }

  /// Diskon tidak boleh lebih besar dari subtotal.
  void _kurangiDiskonAgarValid() {
    if (diskon > subtotal) diskon = subtotal;
  }

  // --------------------------------------------------------------- simpan

  bool sedangSimpan = false;
  String? pesanGagal;

  Future<TransactionResult?> simpan({
    required TransactionService service,
    required int totalBayar,
  }) async {
    sedangSimpan = true;
    pesanGagal = null;
    notifyListeners();

    final payload = {
      'items': _items
          .map((e) => {'id_buku': e.obat.id, 'jumlah': e.jumlah})
          .toList(),
      'diskon': diskon,
      'total_bayar': totalBayar,
    };

    try {
      final hasil = await service.simpan(
        items: _items,
        diskon: diskon,
        totalBayar: totalBayar,
      );

      bersihkan();
      sedangSimpan = false;
      notifyListeners();
      return hasil;
    } on ApiException catch (e) {
      // Transaksi tidak sampai ke server karena jaringan, bukan karena
      // penolakan server. Selama kita bisa membuktikan itu, lebih baik
      // menyimpannya di antrean daripada membuat kasir mengetik ulang.
      final sync = _sync;
      if (sync != null && (e.code == 'OFFLINE' || e.code == 'TIMEOUT')) {
        final terkirim = await sync.kirimTransaksi(payload);

        if (!terkirim) {
          // Sudah masuk antrean. Kasir tetap mendapat "struk" markas supaya
          // alurnya tidak berubah, dengan penanda jelas bahwa transaksi
          // ini BELUM tercatat di server.
          final hasil = TransactionResult.diantrekan(
            id: 'ANTREAN-${DateTime.now().millisecondsSinceEpoch}',
            namaKasir: '',
            tanggal: DateTime.now(),
            total: grandTotal,
            totalBayar: totalBayar,
            diskon: diskon,
            kembalian: totalBayar - grandTotal,
            items: _items
                .map(
                  (e) => TransactionItem(
                    idBuku: e.obat.id,
                    obat: e.obat.nama,
                    hargaJual: e.hargaSatuan,
                    jumlah: e.jumlah,
                    subtotal: e.subtotal,
                  ),
                )
                .toList(),
          );

          bersihkan();
          sedangSimpan = false;
          notifyListeners();
          return hasil;
        }
      }

      sedangSimpan = false;
      pesanGagal = e.pesanPerField;
      notifyListeners();
      return null;
    } catch (e) {
      // Jaring pengaman: error tak terduga (mis. FormatException dari JSON
      // yang aneh) harus tetap menghasilkan pesan yang bisa dibaca kasir,
      // bukan membuat aplikasi diam atau crash tanpa keterangan.
      sedangSimpan = false;
      pesanGagal =
          'Terjadi kesalahan tak terduga saat menyimpan. '
          'Transaksi belum tersimpan, silakan coba lagi.';
      debugPrint('GAGAL SIMPAN TRANSAKSI: $e');
      notifyListeners();
      return null;
    }
  }
}
