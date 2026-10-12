import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import 'core/api_client.dart';
import 'core/formatters.dart';
import 'core/theme.dart';
import 'providers/auth_provider.dart';
import 'providers/cart_provider.dart';
import 'providers/sync_provider.dart';
import 'screens/home_screen.dart';
import 'screens/login_screen.dart';
import 'services/auth_service.dart';
import 'services/connectivity_service.dart';
import 'services/printer_service.dart';
import 'widgets/layar_error.dart';

void main() {
  WidgetsFlutterBinding.ensureInitialized();

  // Muat data tanggal untuk locale Indonesia.
  //
  // Tanpa baris ini, setiap pemanggilan DateFormat(..., 'id_ID') melempar
  // LocaleDataException. Gejalanya baru muncul saat transaksi disimpan,
  // karena saat itu dialog struk memformat tanggal transaksi.
  Fmt.init();

  // Tangkap error yang tidak tertangani.
  //
  // Tanpa ini, error di tengah render hanya muncul sebagai layar
  // merah/abu-abu tanpa keterangan — persis kondisi yang membuat bug sulit
  // dilacak. Dengan ini errornya masuk ke log dan bisa ditampilkan.
  FlutterError.onError = (details) {
    FlutterError.presentError(details);
    LayarError.catat(details.exceptionAsString(), details.stack);
  };

  PlatformDispatcher.instance.onError = (error, stack) {
    LayarError.catat('$error', stack);
    return true;
  };

  runApp(const ApotikdearApp());
}

class ApotikdearApp extends StatefulWidget {
  const ApotikdearApp({super.key});

  @override
  State<ApotikdearApp> createState() => _ApotikdearAppState();
}

class _ApotikdearAppState extends State<ApotikdearApp> {
  late final ApiClient _api;
  late final AuthProvider _auth;
  late final PrinterService _printer;
  late final SyncProvider _sync;

  @override
  void initState() {
    super.initState();

    _api = ApiClient();
    _auth = AuthProvider(api: _api, service: AuthService(_api));
    _printer = PrinterService();
    _sync = SyncProvider(_api);

    // Kalau server membalas 401 di tengah pemakaian, paksa user login ulang.
    _api.onUnauthorized = () {
      if (mounted) _auth.logout();
    };

    // Pantau jaringan: begitu kembali online, antrean transaksi dikirim
    // otomatis tanpa perlu campur tangan kasir.
    ConnectivityService.instance.mulai();
    _sync.mulai();

    _auth.bootstrap();
  }

  @override
  void dispose() {
    _api.close();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        Provider<ApiClient>.value(value: _api),
        Provider<PrinterService>.value(value: _printer),
        ChangeNotifierProvider<AuthProvider>.value(value: _auth),
        ChangeNotifierProvider<SyncProvider>.value(value: _sync),
        ChangeNotifierProvider<CartProvider>(
          create: (_) => CartProvider(sync: _sync),
        ),
      ],
      child: MaterialApp(
        title: 'Apotikdear POS',
        debugShowCheckedModeBanner: false,
        theme: AppTheme.light(),
        // Kalau ada error render, tampilkan layar yang bisa dibaca
        // alih-alih layar merah tanpa keterangan.
        builder: (context, child) =>
            LayarError(child: child ?? const SizedBox.shrink()),
        home: const _Gerbang(),
      ),
    );
  }
}

/// Memilih antara splash, login, atau aplikasi utama.
class _Gerbang extends StatelessWidget {
  const _Gerbang();

  @override
  Widget build(BuildContext context) {
    final status = context.watch<AuthProvider>().status;

    return AnimatedSwitcher(
      duration: const Duration(milliseconds: 260),
      child: switch (status) {
        AuthStatus.belumCek || AuthStatus.memuat => const _Splash(),
        AuthStatus.masuk => const HomeScreen(),
        AuthStatus.keluar => const LoginScreen(),
      },
    );
  }
}

class _Splash extends StatelessWidget {
  const _Splash();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppTheme.primaryDeep,
      body: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(24),
              ),
              child: const Icon(
                Icons.local_pharmacy_rounded,
                size: 46,
                color: AppTheme.primary,
              ),
            ),
            const SizedBox(height: 26),
            const SizedBox(
              width: 26,
              height: 26,
              child: CircularProgressIndicator(
                strokeWidth: 2.6,
                color: Colors.white70,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
