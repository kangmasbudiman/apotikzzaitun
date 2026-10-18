<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pastikan tabel `pengaturan` (yang benar-benar dipakai model Site) punya
 * semua kolom identitas toko.
 *
 * Skema legacy produksi ternyata belum punya kolom `nama_sistem`, sehingga
 * PUT /site dari layar Identitas Toko gagal "unknown column" (500) begitu
 * aplikasi mengirim field itu. Migrasi ini idempoten: hanya menambah kolom
 * yang belum ada, dan membuat tabel lengkap untuk instalasi segar.
 */
class EnsurePengaturanIdentityColumns extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('pengaturan')) {
            Schema::create('pengaturan', function (Blueprint $table) {
                $table->increments('idPengaturan');
                $table->unsignedBigInteger('idUser')->nullable();
                $table->string('nama_sistem', 100)->nullable();
                $table->string('nama_toko', 100)->nullable();
                $table->string('alamat_toko', 255)->nullable();
                $table->string('telepon_toko', 50)->nullable();
                $table->decimal('ppn', 5, 2)->default(0);
                $table->integer('min_stok')->default(0);
                $table->string('logo')->nullable();
                $table->boolean('pakaiLogo')->default(false);
            });

            return;
        }

        Schema::table('pengaturan', function (Blueprint $table) {
            if (! Schema::hasColumn('pengaturan', 'idUser')) {
                $table->unsignedBigInteger('idUser')->nullable();
            }
            if (! Schema::hasColumn('pengaturan', 'nama_sistem')) {
                $table->string('nama_sistem', 100)->nullable();
            }
            if (! Schema::hasColumn('pengaturan', 'nama_toko')) {
                $table->string('nama_toko', 100)->nullable();
            }
            if (! Schema::hasColumn('pengaturan', 'alamat_toko')) {
                $table->string('alamat_toko', 255)->nullable();
            }
            if (! Schema::hasColumn('pengaturan', 'telepon_toko')) {
                $table->string('telepon_toko', 50)->nullable();
            }
            if (! Schema::hasColumn('pengaturan', 'ppn')) {
                $table->decimal('ppn', 5, 2)->default(0);
            }
            if (! Schema::hasColumn('pengaturan', 'min_stok')) {
                $table->integer('min_stok')->default(0);
            }
            if (! Schema::hasColumn('pengaturan', 'logo')) {
                $table->string('logo')->nullable();
            }
            if (! Schema::hasColumn('pengaturan', 'pakaiLogo')) {
                $table->boolean('pakaiLogo')->default(false);
            }
        });
    }

    public function down()
    {
        // Kolom & tabel milik skema legacy produksi — tidak dihapus.
    }
}
