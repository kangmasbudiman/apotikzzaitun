<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Piutang (bon) pelanggan: penjualan yang barangnya sudah dibawa pulang
 * tapi uangnya belum diterima.
 *
 * Satu baris = satu bon. `status` 1 berarti belum lunas; saat dilunasi
 * kolomnya dinolkan dan `tanggal_lunas` diisi (baris TIDAK dihapus —
 * pemilik butuh jejak siapa pernah bon dan kapan lunasnya).
 */
class CreatePiutangTable extends Migration
{
    public function up()
    {
        if (Schema::hasTable('piutang')) {
            return;
        }

        Schema::create('piutang', function (Blueprint $table) {
            $table->increments('id');
            $table->string('idPenjualan', 30)->nullable()->index();
            $table->string('idUser', 15)->index();
            $table->string('nama_user', 100);
            $table->string('nama_pelanggan', 100);
            $table->string('telepon', 25)->nullable();
            $table->integer('nominal');
            $table->boolean('status')->default(true)->index();
            $table->dateTime('tanggal');
            $table->dateTime('tanggal_lunas')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('piutang');
    }
}
