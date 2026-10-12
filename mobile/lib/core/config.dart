/// Konfigurasi lingkungan aplikasi.
///
/// ## Menentukan alamat server
///
/// [apiBaseUrl] bisa diubah tanpa menyentuh kode:
/// ```
/// flutter run --dart-define=API_BASE_URL=http://192.168.1.8:8000/api
/// ```
///
/// Kalau `API_BASE_URL` tidak diisi, dipakai [apiBaseUrlAwal] yang di bawah.
/// Alamat yang benar bergantung di mana aplikasinya dijalankan — `127.0.0.1`
/// TIDAK selalu berarti "komputer ini":
///
///  * Android Emulator  -> `10.0.2.2`  (alias localhost mesin pengembang)
///  * iOS Simulator     -> `127.0.0.1`
///  * HP Android / iOS  -> IP komputer di Wi-Fi, mis. `192.168.1.8`
///  * Flutter web / macOS desktop -> `127.0.0.1`
///
/// Layar login menampilkan alamat yang sedang dipakai — kalau tidak cocok,
/// itu alasan paling umum gagal connect.
class AppConfig {
  const AppConfig._();

  /// Alamat bawaan untuk **Android Emulator**.
  ///
  /// Emulator tidak punya loopback ke mesin pengembang, jadi `127.0.0.1`
  /// di dalam emulator = emulator itu sendiri. `10.0.2.2` adalah alias
  /// resmi yang diarahkan ke `localhost` mesin pengembang.
  ///
  /// Kalau menjalankan di HP asli, ganti lewat layar login (ketuk alamat
  /// server di bawah tombol Masuk) — tidak perlu build ulang.
  static const String apiBaseUrlAwal = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://10.0.2.2:8000/api',
  );

  /// Nama token yang disimpan di secure storage.
  static const String tokenKey = 'apotikdear_api_token';

  /// Kunci penyimpanan alamat server yang bisa diubah dari layar login.
  static const String baseUrlKey = 'apotikdear_api_base_url';

  /// Timeout permintaan — kasir tidak boleh menunggu lama.
  static const Duration requestTimeout = Duration(seconds: 15);

  /// Berapa baris per halaman saat mengambil daftar obat.
  static const int perPage = 25;

  /// Batas lebar sebelum tabel berubah jadi daftar kartu.
  ///
  /// Di bawah ini layar dianggap "HP" dan tabel asli akan sempit sekali,
  /// jadi otomatis diganti kartu.
  static const double breakpointTable = 720;

  /// Di atas ini semua kolom ditampilkan tanpa scroll horizontal.
  static const double breakpointFullTable = 1000;
}
