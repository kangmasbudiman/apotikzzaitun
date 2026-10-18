<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Logo apotek, disimpan DI DATABASE — alasan sama dengan `gambar_obat`:
 * storage container produksi ter-reset setiap rebuild image, sedangkan
 * MySQL bervolume dan bertahan. Satu baris saja (id = 1).
 */
class CreateLogoTokoTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('logo_toko')) {
            return;
        }

        Schema::create('logo_toko', function (Blueprint $table) {
            $table->id();
            $table->string('mime', 100);
            $table->binary('data');
            $table->timestamps();
        });

        // Blueprint binary() menghasilkan BLOB (maks 64KB) — logo perlu
        // LONGBLOB supaya tidak terpotong.
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE `logo_toko` MODIFY `data` LONGBLOB NOT NULL');
        }
    }

    public function down()
    {
        Schema::dropIfExists('logo_toko');
    }
}
