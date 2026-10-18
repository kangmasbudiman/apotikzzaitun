<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pastikan tabel `site` punya kolom identitas yang dipakai aplikasi.
 *
 * Skema produksi (legacy `indata_pos`) sudah punya semua kolom ini sejak
 * awal, tetapi instalasi segar yang hanya menjalankan migrasi bawaan
 * repositori hanya mendapat kolom `name`/`address` — sehingga PUT /site
 * gagal dengan "unknown column". Migrasi ini menambahkan kolom yang
 * hilang saja (idempoten): no-op di produksi, membenahi dev/test.
 */
class EnsureSiteIdentityColumns extends Migration
{
    public function up()
    {
        Schema::table('site', function (Blueprint $table) {
            if (! Schema::hasColumn('site', 'idUser')) {
                $table->unsignedBigInteger('idUser')->nullable();
            }
            if (! Schema::hasColumn('site', 'nama_sistem')) {
                $table->string('nama_sistem', 100)->nullable();
            }
            if (! Schema::hasColumn('site', 'nama_toko')) {
                $table->string('nama_toko', 100)->nullable();
            }
            if (! Schema::hasColumn('site', 'alamat_toko')) {
                $table->string('alamat_toko', 255)->nullable();
            }
            if (! Schema::hasColumn('site', 'telepon_toko')) {
                $table->string('telepon_toko', 50)->nullable();
            }
            if (! Schema::hasColumn('site', 'ppn')) {
                $table->decimal('ppn', 5, 2)->default(0);
            }
            if (! Schema::hasColumn('site', 'min_stok')) {
                $table->integer('min_stok')->default(0);
            }
            if (! Schema::hasColumn('site', 'logo')) {
                $table->string('logo')->nullable();
            }
            if (! Schema::hasColumn('site', 'pakaiLogo')) {
                $table->boolean('pakaiLogo')->default(false);
            }
        });
    }

    public function down()
    {
        // Kolom legacy milik skema produksi — tidak dihapus.
    }
}
