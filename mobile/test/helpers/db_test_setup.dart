// Setup bersama untuk test yang memakai sqflite.
//
// Di `flutter test` tidak ada plugin platform asli, sehingga
// `sqflite` akan melempar "databaseFactory not initialized"
// begitu `LocalDb` dibuka. `sqflite_common_ffi` menyediakan
// implementasi murni-Dart yang berjalan di mana saja — cukup
// daftarkan sebelum test dimulai.
import 'package:flutter_test/flutter_test.dart';
import 'package:sqflite_common_ffi/sqflite_ffi.dart';

Future<void> siapkanDatabase() async {
  sqfliteFfiInit();
  databaseFactory = databaseFactoryFfi;
}
