# API Apotikdear POS (v1)

REST API untuk aplikasi mobile (Flutter). Berjalan di Laravel yang sama dengan
aplikasi web, memakai **Sanctum bearer token**.

Base URL lokal: `http://127.0.0.1:8000/api`

---

## 1. Autentikasi

### `POST /auth/login` — publik (rate limit 8/menit/IP)

```json
{
  "username": "kasir01",
  "password": "rahasia",
  "device_name": "hp-kasir-1",
  "device_platform": "android"
}
```

Balas `200`:

```json
{
  "token_type": "Bearer",
  "access_token": "3|xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
  "user": {
    "id": "170312",
    "nama": "Meli Yani",
    "username": "Meli",
    "hak_akses": 2,
    "role": "kasir",
    "is_admin": false,
    "bisa_transaksi": true,
    "bisa_master": false
  }
}
```

> Login dengan `device_name` yang sama akan **mengganti** token lama, jadi tidak
> menumpuk token kalau app sering crash.

### Endpoint lain

| Method | URL | Keterangan |
|---|---|---|
| `GET` | `/auth/me` | Profil user yang login |
| `POST` | `/auth/logout` | Cabut token perangkat ini |
| `POST` | `/auth/logout-all` | Cabut semua token (perangkat hilang) |

### Cara pakai

```
Authorization: Bearer <access_token>
Accept: application/json
```

---

## 2. Pengaturan Toko

### `GET /site`

Dipanggil sekali setelah login untuk tampilkan nama toko, PPN, dan ambang stok menipis.

```json
{ "data": { "nama_sistem": "Apotikdear POS", "nama_toko": "Az Zaitun",
            "alamat_toko": "JL. ...", "telepon_toko": "0822...",
            "ppn": 0, "min_stok": 90 } }
```

---

## 3. Master Data

### Kategori — `GET /kategori`, `POST /kategori`, `PUT /kategori/{id}`, `DELETE /kategori/{id}`
### Rak — `GET /rak`, `POST /rak`, `PUT /rak/{id}`, `DELETE /rak/{id}`

Bentuk data:

```json
{ "id": 9, "nama": "Obat Bebas, OBT" }
```

`DELETE` ditolak dengan `422` kalau masih dipakai obat.

### Obat

| Method | URL | Akses |
|---|---|---|
| `GET` | `/obat` | semua user login |
| `GET` | `/obat/{id}` | semua user login |
| `GET` | `/obat/cari?q=` | semua user login |
| `POST` | `/obat` | admin + gudang |
| `PUT` | `/obat/{id}` | admin + gudang |
| `DELETE` | `/obat/{id}` | admin + gudang |

#### `GET /obat`

| Parameter | Contoh | Keterangan |
|---|---|---|
| `search` | `paracetamol` | cocok nama, barcode, noisbn, suplier, penulis |
| `kategori_id` | `9` | filter kategori |
| `rak_id` | `5` | filter rak |
| `stok` | `habis` \| `menipis` \| `aman` | filter status stok |
| `sort` | `obat` \| `stok` \| `harga_jual` | kolom urut |
| `direction` | `asc` \| `desc` | arah urut |
| `page` | `1` | halaman (default 1) |
| `per_page` | `25` | max 100 |

```json
[{ "id": "OB00943", "barcode": "9786230061325", "obat": "PARACETAMOL 500 TAB",
   "noisbn": "1A1E6A027", "suplier": "KIMIA FARMA", "tahun": "2026",
   "harga_pokok": 3500, "harga_jual": 6000, "disc": 0, "ppn": 11, "margin": 20,
   "stock": 50, "min_stok": 90, "stok_status": "menipis",
   "id_kategori": 9, "id_rak": 7,
   "kategori": { "id": 9, "nama": "Obat Bebas, OBT" },
   "rak": { "id": 7, "nama": "Rak 3" } }]
```

Bentuk paginasi (Laravel resource):

```json
{ "data": [ ... ], "links": { ... }, "meta": { "current_page": 1, "last_page": 31,
  "per_page": 25, "total": 770 } }
```

#### `GET /obat/cari?q=`

Untuk scan barcode. Kalau `q` cocok **persis** dengan `barcode` atau `idBuku`,
hasil tunggal diletakkan di `exact` supaya app bisa langsung masukkan keranjang.

```json
{ "data": { ...obat... }, "exact": { ...obat... }, "suggestions": [] }
```

Kalau tidak ketemu persis, `exact` = `null` dan `suggestions` berisi maksimal 10
saran berdasarkan nama obat.

---

## 4. Transaksi / Kasir

> ⚠️ Harga **tidak pernah** diambil dari perangkat. Server selalu menghitung ulang
> dari `buku`, jadi app tidak bisa mengirim harga palsu.

### `POST /transaksi` — admin + kasir (rate limit 120/menit)

```json
{
  "items": [
    { "id_buku": "OB00943", "jumlah": 2 },
    { "id_buku": "OB00042", "jumlah": 1 }
  ],
  "diskon": 500,
  "total_bayar": 20000
}
```

Balas `201`:

```json
{
  "message": "Transaksi berhasil disimpan.",
  "data": {
    "id": "A87583-573511-11102026",
    "id_user": "170313", "nama_user": "Meli Yani",
    "tanggal": "2026-10-11 08:19:55", "status": true,
    "total": 11500, "total_bayar": 20000, "diskon": 500,
    "grandtotal": 12000, "kembalian": 8500, "jumlah_item": 3,
    "items": [
      { "id": "T39676", "id_buku": "OB00943", "obat": "PARACETAMOL 500 TAB",
        "harga_pokok": 3500, "harga_jual": 6000, "disc": 0,
        "jumlah": 2, "subtotal": 12000 }
    ]
  }
}
```

Cara hitung server:

```
subtotal  = Σ (harga_jual × jumlah − diskon_item)
total     = subtotal − diskon_transaksi     ← kolom `total` di DB
kembalian = total_bayar − total
```

> Panjang `idPenjualan` varchar(20) di DB sebenarnya 220 karena format lama
> masih dipakai; nomor tetap*A#####-######-ddmmyy*.

#### Error yang mungkin muncul

| HTTP | `kode` | Arti |
|---|---|---|
| `422` | `STOK_KURANG` | Stok obat tidak cukup (pesan menyebut sisa & permintaan) |
| `422` | `UANG_KURANG` | `total_bayar` < total belanja |
| `422` | — | validasi field (`errors` berisi pesan per field) |

Stok otomatis berkurang karena trigger MySQL `update_stok_buku` pada tabel
`detail_penjualan`. Jangan decrement stok dari sisi app.

### `GET /transaksi`

Kasir **hanya melihat transaksi sendiri**. Admin & gudang bisa semua
(opsional `?id_user=`).

| Parameter | Keterangan |
|---|---|
| `dari`, `sampai` | rentang tanggal (`YYYY-MM-DD`) |
| `status` | `1` berhasil, `0` batal |
| `q` | cari nomor transaksi atau nama kasir |
| `id_user` | filter kasir (admin/gudang) |
| `page`, `per_page` | paginasi (max 100) |

```json
{ "data": [ ...transaksi... ],
  "meta": { "total": 18227, "per_page": 25, "halaman_sekarang": 1,
            "halaman_terakhir": 729, "ada_halaman_berikutnya": true } }
```

### `GET /transaksi/{id}` — detail satu transaksi (untuk struk)
### `PATCH /transaksi/{id}/batal` — batalkan + kembalikan stok

Kasir hanya boleh membatalkan transaksi **hari ini**. Admin boleh lebih lama.

---

## 4b. Batch & Kedaluwarsa (FEFO)

Tabel `batch_obat` memecah stok tiap obat per batch fisik, lengkap dengan
tanggal kedaluwarsa. Penjualan lewat API otomatis mengambil stok dari batch yang
**paling cepat kedaluwarsa** (First Expired, First Out) supaya obat mendekati
tanggal jatuh tempo justru yang lebih dulu terjual — bukan mengendap di rak.

`buku.stock` tetap sumber kebenaran total; `batch_obat` hanya memecahnya
(`SUM(stokMasuk - stokKeluar)` selalu sama dengan `buku.stock`).

### `GET /batch/{idBuku}` — batch satu obat, urutan FEFO

```json
{ "data": {
    "id": "OB00001",
    "obat": "FOLAMIL GENIO",
    "stock": 210,
    "jumlah_batch": 2,
    "batch_terdekat": "54A3046",
    "kedaluwarsa_terdekat": "2024-12-01",
    "hari_lagi_terdekat": -679,
    "sisa_per_batch": [
      { "no_batch": "54A3046", "expired": "2024-12-01", "hari_lagi": -679,
        "stok": 150, "harga_pokok": 3800 },
      { "no_batch": "54B3041", "expired": "2025-01-01", "hari_lagi": -648,
        "stok": 60,  "harga_pokok": 3800 }
    ] } }
```

| Field | Arti |
|---|---|
| `hari_lagi` | Sisa hari. **Negatif = sudah lewat tanggal kedaluwarsa** |
| `sisa_per_batch[0]` | Batch yang akan keluar lebih dulu saat penjualan |

Obat yang stoknya dari impor lama (tanpa riwayat batch) punya
`kedaluwarsa_terdekat: null` dan `sisa_per_batch` berisi batch `SISA-xxxx`
tanpa tanggal — aplikasi sebaiknya menampilkan "tidak ada data kedaluwarsa",
bukan "aman".

### `GET /batch` — cek konsistensi seluruh obat

```json
{ "sehat": true,
  "pesan": "Seluruh sisa batch cocok dengan stok buku.",
  "data": [] }
```

Kalau `sehat: false`, `data` berisi daftar obat yang selisihnya. Penyebab
umumnya: penjualan lewat web (tidak pernah menyentuh `batch_obat`) atau koreksi
stok manual.

---

## 5. Format error

```json
{ "message": "Stok PARACETAMOL tidak cukup. Tersedia 50, diminta 9999.",
  "errors": { "items": "..." }, "kode": "STOK_KURANG" }
```

| HTTP | Arti |
|---|---|
| `401` | Token hilang / kadaluarsa → paksa login ulang di app |
| `403` | Role tidak punya akses |
| `404` | Endpoint atau data tidak ditemukan |
| `422` | Validasi gagal (`errors` per field) |
| `429` | Terlalu banyak permintaan (throttle) |
| `500` | Error server (pesan generik, detail ada di `storage/logs`) |

> Response API **tidak pernah** memuat stack trace, meski `APP_DEBUG=true`.

---

## 6. Hak akses

| `hak_akses` | `role` | Boleh |
|---|---|---|
| `1` | `admin` | semuanya |
| `2` | `kasir` | transaksi + baca obat |
| `3` | `gudang` | master data + pembelian |

Gunakan field `bisa_transaksi` / `bisa_master` dari response login untuk
menentukan menu mana yang ditampilkan, supaya tidak ada menu yangbakal 403.

---

## 7. Catatan untuk sisi Flutter

- Simpan `access_token` di `flutter_secure_storage`, **jangan** di `shared_preferences`.
- Kirim `Accept: application/json` agar error selalu JSON.
- Tangani `401` secara global di interceptor → lempar ke halaman login.
- Halaman "riwayat" wajib pakai `meta.halaman_terakhir` untuk infinite scroll.
- `stok_status` (`habis`/`menipis`/`aman`) sudah dihitung server pakai `min_stok`
  dari pengaturan — pakai langsung untuk warna badge, jangan hitung ulang di app.
- **Info batch bersifat pelengkap.** Muatnya saat obat dimasukkan keranjang,
  di latar belakang, dan abaikan kalau gagal — kasir tidak boleh terblokir
  gara-gara endpoint batch lambat.
- `hari_lagi` negatif berarti batch **sudah lewat** kedaluwarsa. Jangan
  menyamar jadi "aman"; tampilkan apa adanya supaya kasir tahu.
- `kesehatan_batch` di `GET /dashboard` hanya dikirim ke admin. Sembunyikan
  widget-nya kalau null (kasir).