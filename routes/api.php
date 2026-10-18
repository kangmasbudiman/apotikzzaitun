<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes  —  Untuk aplikasi mobile (Flutter)
|--------------------------------------------------------------------------
| Prefix otomatis: /api
| Autentikasi  : Sanctum bearer token (Authorization: Bearer <token>)
| Diambil lewat: POST /api/auth/login
|
*/

// ---------------- Publik ----------------
Route::post('auth/login', [App\Http\Controllers\Api\AuthController::class, 'login'])
    ->middleware('throttle:login');

// Identitas toko — publik supaya layar LOGIN (sebelum punya token) sudah
// menampilkan nama yang diatur admin, bukan nama bawaan aplikasi.
Route::get('site', [App\Http\Controllers\Api\SiteController::class, 'show']);

// Logo apotek — publik: dimuat widget gambar di layar login (tanpa token).
Route::get('site/logo', [App\Http\Controllers\Api\SiteController::class, 'tampilkanLogo']);

// Foto produk obat — publik: dimuat widget gambar yang tidak mengirim token.
Route::get('gambar-obat/{id}', [App\Http\Controllers\Api\GambarObatController::class, 'show']);

// ---------------- Terproteksi token ----------------
Route::middleware('auth:sanctum')->group(function () {

    // Profil
    Route::get('auth/me', [App\Http\Controllers\Api\AuthController::class, 'me']);
    Route::post('auth/logout', [App\Http\Controllers\Api\AuthController::class, 'logout']);
    Route::post('auth/logout-all', [App\Http\Controllers\Api\AuthController::class, 'logoutAll']);

    // Ubah identitas toko (nama layar login, dll) — hanya admin.
    Route::middleware('can:isAdmin')->group(function () {
        Route::put('site', [App\Http\Controllers\Api\SiteController::class, 'update']);

        // Logo apotek (upload/ganti & hapus).
        Route::post('site/logo', [App\Http\Controllers\Api\SiteController::class, 'simpanLogo']);
        Route::delete('site/logo', [App\Http\Controllers\Api\SiteController::class, 'hapusLogo']);
    });

    // ---------- Master data: Kategori & Rak (Admin + Gudang) ----------
    Route::middleware('can:isAdminGudang')->group(function () {

        Route::get('kategori', [App\Http\Controllers\Api\CategoryController::class, 'index']);
        Route::post('kategori', [App\Http\Controllers\Api\CategoryController::class, 'store']);
        Route::put('kategori/{id}', [App\Http\Controllers\Api\CategoryController::class, 'update']);
        Route::delete('kategori/{id}', [App\Http\Controllers\Api\CategoryController::class, 'destroy']);

        Route::get('rak', [App\Http\Controllers\Api\RackController::class, 'index']);
        Route::post('rak', [App\Http\Controllers\Api\RackController::class, 'store']);
        Route::put('rak/{id}', [App\Http\Controllers\Api\RackController::class, 'update']);
        Route::delete('rak/{id}', [App\Http\Controllers\Api\RackController::class, 'destroy']);

        // CRUD obat
        Route::post('obat', [App\Http\Controllers\Api\StuffController::class, 'store']);
        Route::put('obat/{id}', [App\Http\Controllers\Api\StuffController::class, 'update']);
        Route::delete('obat/{id}', [App\Http\Controllers\Api\StuffController::class, 'destroy']);

        // Foto produk obat
        Route::post('obat/{id}/gambar', [App\Http\Controllers\Api\GambarObatController::class, 'store']);
        Route::delete('obat/{id}/gambar', [App\Http\Controllers\Api\GambarObatController::class, 'destroy']);
    });

    // ---------- Obat (semua user login boleh baca) ----------
    // Pencarian cepat kasir — WAJIB di atas /obat/{id} supaya tidak tertangkap
    // sebagai parameter {id}.
    Route::get('obat/cari', [App\Http\Controllers\Api\StuffController::class, 'find']);

    Route::get('obat', [App\Http\Controllers\Api\StuffController::class, 'index']);
    Route::get('obat/{id}', [App\Http\Controllers\Api\StuffController::class, 'show']);

    // Ringkasan dashboard
Route::middleware('can:isAdminKasir')->group(function () {
        Route::get('dashboard', [App\Http\Controllers\Api\DashboardController::class, 'index']);
    });

    // Batch obat (FEFO) — semua user login boleh, hanya membaca.
    Route::get('batch/{id}', [App\Http\Controllers\Api\TransactionController::class, 'batch']);
    Route::get('batch', [App\Http\Controllers\Api\TransactionController::class, 'cekBatch']);

    // ---------- Transaksi / Kasir (Admin + Kasir) ----------
    Route::middleware('can:isAdminKasir')->group(function () {

        Route::get('transaksi', [App\Http\Controllers\Api\TransactionController::class, 'index']);
        Route::post('transaksi', [App\Http\Controllers\Api\TransactionController::class, 'store'])
            ->middleware('throttle:kasir');
        Route::get('transaksi/{id}', [App\Http\Controllers\Api\TransactionController::class, 'show']);
        Route::patch('transaksi/{id}/batal', [App\Http\Controllers\Api\TransactionController::class, 'cancel']);
    });
});