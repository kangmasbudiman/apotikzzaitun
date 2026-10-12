import 'package:intl/date_symbol_data_local.dart';
import 'package:intl/intl.dart';

/// Format angka & tanggal gaya Indonesia, dipakai di seluruh aplikasi.
class Fmt {
  const Fmt._();

  static const locale = 'id_ID';

  /// Dipanggil sekali saat aplikasi start.
  ///
  /// Tanpa ini, `DateFormat(..., 'id_ID')` melempar LocaleDataException
  /// karena data tanggal untuk locale Indonesia belum dimuat. Gejalanya
  /// muncul saat transaksi disimpan — dialog struk memformat tanggal.
  static void init() {
    initializeDateFormatting(locale, null);
  }

  // id_ID memakai titik sebagai pemisah ribuan dan koma sebagai desimal.
  static final _rp = NumberFormat.currency(
    locale: locale,
    symbol: 'Rp ',
    decimalDigits: 0,
  );

  static final NumberFormat _angka = NumberFormat.decimalPattern(locale);

  static final DateFormat _tanggal = _tanggalFormat('d MMM yyyy');
  static final DateFormat _tanggalJam = _tanggalFormat('d MMM yyyy, HH:mm');
  static final DateFormat _jam = _tanggalFormat('HH:mm');
  static final DateFormat _api = _tanggalFormat('yyyy-MM-dd');

  /// Jaring pengaman: kalau data locale belum siap (mis. `init()` lupa
  /// dipanggil, atau dipanggil dari isolate lain), pakai locale bawaan
  /// alih-alih membuat seluruh aplikasi crash.
  static DateFormat _tanggalFormat(String pattern) {
    try {
      return DateFormat(pattern, locale);
    } catch (_) {
      return DateFormat(pattern);
    }
  }

  /// 15000 -> "Rp 15.000"
  static String rp(num? nilai) => _rp.format(nilai ?? 0);

  /// 1500 -> "1.500"
  static String angka(num? nilai) => _angka.format(nilai ?? 0);

  static String tanggal(DateTime? d) => d == null ? '-' : _tanggal.format(d);

  static String tanggalJam(DateTime? d) =>
      d == null ? '-' : _tanggalJam.format(d);

  static String jam(DateTime? d) => d == null ? '-' : _jam.format(d);

  static String tanggalApi(DateTime d) => _api.format(d);

  /// "3 menit lalu" — dipakai di layar riwayat.
  static String sejak(DateTime? d) {
    if (d == null) return '-';

    final selisih = DateTime.now().difference(d);
    if (selisih.inSeconds < 60) return 'Baru saja';
    if (selisih.inMinutes < 60) return '${selisih.inMinutes} menit lalu';
    if (selisih.inHours < 24) return '${selisih.inHours} jam lalu';
    if (selisih.inDays < 30) return '${selisih.inDays} hari lalu';

    return _tanggal.format(d);
  }
}
