<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Foto produk obat, disimpan DI DATABASE (bukan file di storage).
 *
 * Server produksi menjalankan kode yang di-bake ke dalam image Docker;
 * file yang diupload saat runtime ke storage container hilang setiap kali
 * image dibangun ulang. Tabel `mysql_db` bervolume dan bertahan, jadi
 * foto ikut disimpan di sana sebagai LONGBLOB.
 */
class CreateGambarObatTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('gambar_obat')) {
            return;
        }

        Schema::create('gambar_obat', function (Blueprint $table) {
            // idBuku varchar 'OB00001' — PK tabel buku, bukan integer.
            $table->string('idBuku', 50)->primary();
            $table->string('mime', 100);
            $table->binary('data');
            $table->timestamps();
        });

        // Blueprint binary() menghasilkan BLOB (maks 64KB) — foto perlu
        // LONGBLOB supaya tidak terpotong.
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `gambar_obat` MODIFY `data` LONGBLOB NOT NULL');
        }
    }

    public function down()
    {
        Schema::dropIfExists('gambar_obat');
    }
}
