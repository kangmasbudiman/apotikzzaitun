# Apotikdear POS — Mobile (Flutter)

Aplikasi kasir mobile untuk **Apotikdear POS**. Berjalan di atas REST API Laravel
yang ada di repo `apotikdear` — bukan sistem terpisah, datanya satu sumber.

---

## Menjalankan

```bash
flutter pub get

# Backend WAJIB jalan lebih dulu
cd ../apotikdear && php artisan serve --host=0.0.0.0 --port=8000
```

### Memilih alamat server

`127.0.0.1` **tidak selalu berarti "komputer ini"**. Yang benar bergantung di
mana aplikasinya dijalankan:

| App jalan di | Alamat API |
|---|---|
| Android Emulator | `http://10.0.2.2:8000/api` |
| iOS Simulator | `http://127.0.0.1:8000/api` |
| Flutter Web (Chrome) | `http://127.0.0.1:8000/api` |
| **HP Android / iOS asli** | `http://192.168.1.8:8000/api` (IP komputer) |

> Ganti `192.168.1.8` dengan IP komputer Anda. Cek dengan
> `ipconfig getifaddr en0` (macOS) atau `ipconfig` (Windows).

Dua cara mengaturnya:

**1. Saat build/run** (override, tidak disimpan permanen):

```bash
flutter run --dart-define=API_BASE_URL=http://192.168.1.8:8000/api
```

**2. Dari dalam aplikasi** (disimpan permanen, Recommended)

Di layar login ada teks alamat server di bawah tombol **Masuk**. Ketuk untuk
mengubahnya — tersimpan di secure storage dan tetap ada setelah restart, jadi
tidak perlu build ulang kalau server pindah alamat atau ganti Wi-Fi.

> `--host=0.0.0.0` saat `artisan serve` itu wajib, kalau tidak HP tidak akan
> bisa menjangkau server.

---

## Troubleshooting

### "Terjadi kesalahan di server. Silakan coba lagi."

Hampir selalu berarti **izin tulis folder `storage/`**. Cek:

```bash
tail -50 ../apotikdear/storage/logs/laravel.log
```

Kalau muncul `Unable to create lockable file`, perbaiki:

```bash
sudo chown -R <user-web-server>:<grup> ../apotikdear/storage ../apotikdear/bootstrap/cache
```

`<user-web-server>` adalah user yang menjalankan PHP. Untuk `php artisan serve`
biasanya user yang sedang login di shell; untuk Apache/php-fpm biasanya
`www-data`, `_www`, atau `daemon` — cek dengan `ps aux | grep -E 'php-fpm|httpd'`.

> **Jangan pernah menjalankan `php artisan` sebagai root** kalau web server-nya
> tidak jalan sebagai root. Perintah itu akan membuat file cache/session milik
> root, sedangkan web server tidak bisa menulisnya — hasilanya seluruh aplikasi
> (termasuk login) membalas 500.

### "Tidak bisa menghubungi server"

Pesan error sudah menyebut **alamat yang dicoba** — cocokkan dengan tabel di
bagian [Memilih alamat server](#memilih-alamat-server).

- Android emulator: `10.0.2.2`, **bukan** `127.0.0.1`.
- HP asli: pakai IP komputer (`ipconfig getifaddr en0`).
- Pastikan HP dan komputer di Wi-Fi yang sama.
- Pastikan server dijalankan dengan `--host=0.0.0.0`.
- Kalau IP komputer berubah karena DHCP, buka lagi aplikasi lalu ketuk alamat
  server di layar login untuk memperbarui — tidak perlu build ulang.

### Build release tidak bisa mengakses jaringan

Android hanya menambah permission `INTERNET` secara otomatis pada build
**debug**. Manifest di `android/app/src/main/AndroidManifest.xml` sudah
menyertakannya secara eksplisit — jangan dihapus. Cleartext HTTP ke IP LAN juga
sudah diizinkan lewat `res/xml/network_security_config.xml` (dan
`NSAllowsArbitraryLoads` untuk iOS). Kalau server nanti dipindah ke HTTPS,
blok-blok itu bisa dibuang.

---

## Struktur

```
lib/
├── main.dart                     # entry point + Provider + routing auth
├── core/
│   ├── config.dart               # base URL & breakpoint responsif
│   ├── theme.dart                # tema "Emerald Cerah" (sama dgn web)
│   ├── api_client.dart           # HTTP + token + penanganan error
│   └── formatters.dart           # rupiah & tanggal Indonesia
├── models/                       # AuthUser, SiteSetting, Medicine, CartItem…
├── services/                     # AuthService, MasterService, TransactionService
├── providers/                    # AuthProvider, CartProvider
├── screens/
│   ├── login_screen.dart
│   ├── home_screen.dart          # navigasi adaptif (rail / bottom bar)
│   ├── kasir/                    # kasir_screen + pembayaran_sheet
│   ├── master/                   # obat_list, obat_form, kategori_rak
│   └── transaksi/riwayat_screen.dart
└── widgets/
    ├── responsive_data_view.dart # ★ tabel/kartu responsif
    ├── panel_batch.dart          # ★ badge FEFO (batch & kedaluwarsa)
    └── app_header.dart           # navbar + menu akun
```

---

## Tabel responsif

Ini bagian yang paling sering jadi masalah: `DataTable` polos di HP itu sempit,
kolomnya menumpuk, dan selnya susah diklik.

`ResponsiveDataView` cosyatakannya sendiri:

| Lebar layar | Tampilan |
|---|---|
| < 720 dp | **Daftar kartu** — tiap baris jadi kartu, ada garis aksen warna status |
| 720–1000 dp | **Tabel ringkas** — kolom `hanyaDiLebar` disembunyikan |
| ≥ 1000 dp | **Tabel lengkap** — semua kolom |

Cara pakai:

```dart
ResponsiveDataView<Medicine>(
  items: daftarObat,
  onLoadMore: _muatHalaman,
  accent: (o) => AppTheme.stokBadge(o.stokStatus).fg,
  onTap: (o) => _bukaForm(obat: o),

  // Kolom — hanya untuk mode tabel
  kolom: [
    Kolom(judul: 'Obat',  sel: (_, o) => Text(o.nama)),
    Kolom(judul: 'Rak',   hanyaDiLebar: true, sel: (_, o) => Text(o.namaRak ?? '-')),
    Kolom(judul: 'Jual',  rataKanan: true,  sel: (_, o) => Text(Fmt.rp(o.hargaJual))),
    Kolom(judul: 'Stok',  sel: (_, o) => ChipStok(status: o.stokStatus, jumlah: o.stock)),
  ],

  // Kartu — hanya untuk mode HP
  kartu: (_, o) => Column(children: [ /* ... */ ]),
)
```

Infinite scroll dipasang lewat `NotificationListener`, jadi tidak perlupegang
`ScrollController` milik `ListView`/`DataTable`.

Navigasi di `home_screen.dart` memakai `NavigationRail` di tablet dan
`NavigationBar` di HP, dengan breakpoint yang sama.

Header di `widgets/app_header.dart` — judul besar dengan ikon bergradasen,
subtitle sebagai titik+nama toko, badge keranjang, titik status printer, dan
avatar user ber-ring emerald. Menu akun dipanggil lewat avatar (bukan
`PopupMenuButton` agar lebih rapi di layar kecil).

---

## Aturan penting

- **Harga tidak pernah dikirim ke server.** Waktu kasir menekan simpan, yang
  terkirim hanya `id_buku` + `jumlah`. Server menghitung ulang harga dari master
  obat, jadi app tidak bisa mengirim harga palsu.
- **Stok juga tidak dikirim.** Stok berkurang karena trigger MySQL
  (`detail_penjualan`) di sisi server.
- **Kolom stok tidak ada di form obat.** Stok hanya bertambah lewat modul
  Pembelian, supaya tidak bisa disalahgunakan dari HP.
- **Token disimpan di `flutter_secure_storage`**, bukan `shared_preferences`.
- Semua layar kirim `Accept: application/json`; `401` ditangani global di
  `ApiClient` → user dipaksa login ulang.

---

## Yang belum dikerjakan

- Modul Pembelian (stok masuk, batch, jatuh tempo)
- Laporan
- Offline mode + sync queue (antrean transaksi saat tidak ada internet)

---

## Test

```bash
flutter test
```

Ada 7 berkas test:

| Berkas | Yang diuji |
|---|---|
| `test/widget_test.dart` | aplikasi bisa dijalankan tanpa exception |
| `test/simpan_transaksi_test.dart` | payload ke API benar, harga tidak pernah ikut terkirim, pesan error server ditampilkan, keranjang tetap utuh saat gagal |
| `test/pembayaran_widget_test.dart` | tekan "Simpan Transaksi" → sheet tertutup, struk dikembalikan, tidak ada error navigasi |
| `test/home_screen_test.dart` | semua tab bisa dirender untuk 3 role × 3 ukuran layar |
| `test/formatters_test.dart` | format tanggal & rupiah tidak melempar `LocaleDataException` |
| `test/struk_test.dart` | builder struk ESC/POS menghasilkan byte yang valid |
| `test/dashboard_test.dart` | data dashboard (termasuk `kesehatan_batch`) ter-parse dengan benar |
| `test/batch_test.dart` | model `BatchObat`/`InfoBatch` + widget `PanelBatch` (FEFO) |
| `test/master_service_test.dart` | endpoint yang membalas array polos (`/kategori`, `/rak`) ter-parse |
| `test/hak_akses_test.dart` | menu & tombol tulis per role (kasir/gudang/admin) |
| `test/cache_obat_test.dart` | `Medicine` ↔ cache SQLite + pencarian cache |
| `test/kasir_offline_test.dart` | pencarian obat tetap jalan dari cache saat server mati |

> Catatan: jangan pakai `pumpAndSettle` di test yang memuat
> `CircularProgressIndicator` — animasinya tidak pernah idle sehingga test akan
> hang. Pakai helper `_tenang()` di `pembayaran_widget_test.dart`.

### Dua jebakan yang sudah pernah menggigit

1. **`initializeDateFormatting('id_ID')` wajib dipanggil di `main()`**
   (sudah dilakukan lewat `Fmt.init()`). Tanpa itu, dialog struk langsung
   crash setelah transaksi tersimpan — karena saat itu tanggal transaksi
   diformat. Penjaganya ada di `test/formatters_test.dart`.

2. **Jangan pakai `CrossAxisAlignment.stretch` pada `Row` di dalam `ListView`**
   — `ListView` memberi tinggi tak terbatas kepada anaknya, `stretch` memaksa
   tinggi `Infinity`, dan seluruh daftar gagal dirender. Pakai tinggi tetap
   atau `CrossAxisAlignment.center`.

3. **`Generator.row()` memakai lebar kolom skala 1..12 yang jumlahnya harus
   tepat 12** — bukan jumlah karakter. Lebar 32/20 akan melempar
   `Column width must be between 1..12`. Teks yang melebihi ruangnya otomatis
   dipindah ke baris berikutnya oleh library.

---

## Cetak struk (Bluetooth)

Menu: avatar (kanan atas) → **Pengaturan Printer**.

Langkah sekali saja:

1. **Pasangkan printer** di Pengaturan Android → Bluetooth → Pasangkan
   perangkat baru. Printer thermal kasir biasanya bernama `Bluetooth Printer`,
   `MTP-II`, atau `BT-310`.
2. Buka **Pengaturan Printer** di aplikasi, lalu pilih printernya.
3. Pilih ukuran kertas: **58 mm** (32 karakter/baris — paling umum) atau
   **80 mm** (48 karakter/baris).
4. Tekan **Cetak Uji** untuk memastikan. Kalau kertas tercetak tapi berantakan
   (kotak-kotak), setel mode kertas di printer ke "Plain"/"Text".
5. Selesai — struk bisa dicetak dari dialog "Transaksi Berhasil".

Kalau printer tidak punya pemotong kertas, matikan **Potong kertas otomatis**.

> Percetakan memakai ESC/POS lewat `esc_pos_utils`. Hampir semua printer
> thermal Bluetooth mendukungnya, termasuk Xprinter, Iware, Zjiang, dan
> Rongta.

---

## Pindai barcode

Di layar **Kasir**, tekan tombol hijau berikon QR di sebelah kolom pencarian.

Setelah kode terbaca:

- **Ketemu persis** → langsung masuk keranjang, tidak perlu ketuk lagi.
- **Tidak ketemu** → kode diisi ke kolom pencarian supaya kasir bisa memilih
  dari saran atau mengoreksi sendiri.

Mendukung EAN-13, EAN-8, UPC-A/E, Code 128, Code 39, dan QR.

> Ada jeda 1,8 detik antar-scan supaya kamera tidak mengirim kode yang sama
> berulang kali. Kamera juga otomatis dimatikan saat aplikasi masuk
> background — kalau tidak, Android akan membunuh aplikasi.

---

## Dashboard

Menu pertama di navigasi bawah. Isinya:

- **Kartu omzet besar** — omzet hari ini + jumlah transaksi.
- **Laba kotor & laba bersih** — dihitung dari baris `detail_penjualan`,
  bukan dari kolom `total`, jadi akurat.
- **Grafik omzet 7 hari** — digambar sendiri, tanpa dependency chart.
- **Obat terlaris** — 10 teratas dengan batang proporsional qty.
- **Mendekati kedaluwarsa** — dari tabel `batch_obat` (FEFO), dengan
  warna makin merah kalau makin mepet atau sudah lewat. Hanya menampilkan
  batch yang **masih ada stoknya** — obat yang sudah habis terjual
  tidak lagi diperingatkan.
- **Stok kritis** — obat di bawah `min_stok`.
- **Transaksi terbaru**.
- **Kesehatan batch** (admin saja) — menandakan apakah sisa seluruh
  batch masih cocok dengan stok buku.

Untuk **kasir**, semua angka otomatis dibatasi ke transaksinya sendiri —
pembatasan ditegakkan di **server**, bukan sekadar disembunyikan di app.

---

## Offline mode & antrean transaksi

Kalau internet mati di tengah kasir:

1. **Kasir tetap bisa menjual** — daftar obat diambil dari cache di
   perangkat, jadi pencarian & pemindaian barcode tetap jalan. Ada
   banner kuning "Offline — stok & harga dari cache perangkat" supaya
   kasir tahu angka yang tampil belum tentu sama dengan server.
2. **Chip di header** berubah jadi kuning bertuliskan
   "N transaksi menunggu" — kasir tahu ada yang belum terkirim.
3. Begitu jaringan kembali, **antrean dikirim otomatis** (cek tiap
   8 detik), urut dari yang paling lama. Tidak perlu campur tangan.
4. Ketuk chip itu untuk melihat antrean, status percobaan, dan
   pesan error tiap transaksi — atau kirim ulang manual.

**Penting:** penolakan server (mis. stok kurang, uang kurang)
**tidak** pernah masuk antrean — mengirim ulang transaksi yang
sudah ditolak tidak ada gunanya. Hanya kegagalan jaringan
(`OFFLINE`/`TIMEOUT`) yang diantrekan.

> Harga obat tidak pernah dikirim dari app (lihat "Struktur" di
> atas), jadi transaksi yang tertunda diantrekan tetap dihitung
> ulang oleh server saat akhirnya terkirim — harga tidak bisa
> "membeku" mengikuti cache lokal.

> **Cache obat hanya terisi 20 obat pertama** yang dimuat setiap kali
> layar Kasir dibuka. Kasir yang rutin menjual obat di luar daftar itu
> harus dicari minimal sekali saat online supaya ikut tersimpan.

---

## Dokumentasi API

Lihat `../apotikdear/docs/api-mobile.md` — berisi semua endpoint, contoh
payload, format error, dan aturan hak akses.