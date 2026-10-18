<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Langganan (masa aktif) aplikasi — dasar model bisnis berbayar.
 *
 * Dua tabel:
 * - paket_langganan : PILIHAN paket yang bisa diatur bebas lewat database
 *   (tambah/ganti harga/durasi/nonaktifkan) tanpa ubah kode. Inilah daftar
 *   yang tampil di layar Langganan aplikasi.
 * - langganan       : riwayat periode aktif. Baris TERAKHIR = masa aktif
 *   yang sedang berjalan. Menambah baris = mengaktifkan/memperpanjang.
 *
 * Status TIDAK disimpan sebagai kolom — dihitung dari `berakhir` +
 * `masa_tenggang_hari` paket, supaya tidak ada selisih dengan jam server.
 */
class CreateLanggananTables extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('paket_langganan')) {
            Schema::create('paket_langganan', function (Blueprint $table) {
                $table->increments('id');
                $table->string('nama', 50);
                $table->integer('durasi_hari');
                $table->integer('harga')->default(0);
                $table->integer('masa_tenggang_hari')->default(3);
                $table->boolean('aktif')->default(true)->index();
                $table->integer('urutan')->default(0);
                $table->string('keterangan')->nullable();
            });
        }

        if (! Schema::hasTable('langganan')) {
            Schema::create('langganan', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('id_paket')->index();
                $table->string('idUser', 15);
                $table->string('nama_user', 100);
                $table->dateTime('mulai');
                $table->dateTime('berakhir')->index();
                // Identitas perangkat yang dipakai mengaktifkan — catatan
                // informasi untuk pemilik lisensi, bukan penguncian keras.
                $table->string('id_perangkat', 100)->nullable();
                $table->string('nama_perangkat', 150)->nullable();
            });
        }

        // Isi pilihan paket awal hanya saat tabel masih kosong — pemilik
        // bebas mengubah harga/durasinya langsung di database setelah ini.
        if (DB::table('paket_langganan')->count() === 0) {
            DB::table('paket_langganan')->insert([
                [
                    'nama'              => 'Uji Coba',
                    'durasi_hari'       => 14,
                    'harga'             => 0,
                    'masa_tenggang_hari' => 3,
                    'aktif'             => 1,
                    'urutan'            => 1,
                    'keterangan'        => 'Gratis mencoba semua fitur',
                ],
                [
                    'nama'              => 'Bulanan',
                    'durasi_hari'       => 30,
                    'harga'             => 149000,
                    'masa_tenggang_hari' => 3,
                    'aktif'             => 1,
                    'urutan'            => 2,
                    'keterangan'        => 'Per outlet, per bulan',
                ],
                [
                    'nama'              => 'Tahunan',
                    'durasi_hari'       => 365,
                    'harga'             => 1499000,
                    'masa_tenggang_hari' => 7,
                    'aktif'             => 1,
                    'urutan'            => 3,
                    'keterangan'        => 'Hemat ±2 bulan dibanding bulanan',
                ],
            ]);
        }
    }

    public function down()
    {
        Schema::dropIfExists('langganan');
        Schema::dropIfExists('paket_langganan');
    }
}
