// Smoke test dasar: aplikasi harus bisa dibangun tanpa error.
//
// Ganti dengan uji widget yang sebenarnya bila sudah ada flows untuk diuji.
import 'package:flutter_test/flutter_test.dart';

import 'package:apotikdear_mobile/main.dart';

import 'helpers/db_test_setup.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  // ApotikdearApp membuka SQLite saat startup (antrean transaksi),
  // jadi database factory harus sudah siap.
  setUpAll(() async {
    await siapkanDatabase();
  });

  testWidgets('aplikasi bisa dijalankan', (WidgetTester tester) async {
    await tester.pumpWidget(const ApotikdearApp());
    await tester.pump();

    // Hanya memastikan tidak ada exception saat build pertama.
    expect(tester.takeException(), isNull);
  });
}
