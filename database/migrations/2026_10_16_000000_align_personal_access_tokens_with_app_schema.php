<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Samakan tabel `personal_access_tokens` di server yang sudah terlanjur ada.
 *
 * LATAR BELAKANG — kenapa migrasi ini ada
 * ---------------------------------------
 * Aplikasi ini sudah dipakai sejak 2019/2021. Tabel `personal_access_tokens`
 * dibuat saat itu oleh migrasi Sanctum bawaan, yang memakai:
 *
 *     $table->morphs('tokenable');   // tokenable_id = unsignedBigInteger
 *
 * Primary key user di tabel `user` bukan integer, melainkan varchar `idUser`
 * (format '000001', diisi manual oleh event `creating` di App\Models\User).
 * Akibatnya bentuk tabel yang benar tidak mungkin dihasilkan oleh
 * `morphs()`, dan `php artisan migrate` pun tidak akan pernah memperbaikinya.
 *
 * Kolom `device_name` / `device_platform` ditambahkan dengan cara MENGUBA
 * file migrasi 2019 tersebut. Itu tidak berlaku di server yang sudah jadi:
 * nama migrasi `2019_12_14_000001_create_personal_access_tokens_table`
 * sudah tercatat di tabel `migrations`, jadi Laravel MELEWATINYA. Server
 * yang sudah dijalankan sejak 2019 tidak akan pernah mendapat kolom baru.
 *
 * Gejalanya persis seperti yang dilaporkan kasir:
 *
 *     password salah -> 422 (berhenti sebelum menyentuh tabel token)
 *     password benar -> 500 (baru meledak saat menyimpan token)
 *
 * Migrasi inilah yang menutup keduanya: install baru sudah benar sejak
 * awal, install lama sekarang ikut disamakan — keduanya berakhir pada
 * bentuk tabel yang sama persis.
 *
 * SEMUA langkah dicek lebih dulu (idempoten), jadi migrasi ini aman
 * dijalankan berulang kali dan aman di database yang kolomnya sudah benar.
 * Tidak memakai `->change()` karena `doctrine/dbal` tidak terpasang di
 * project ini; perubahan tipe kolom dilakukan dengan SQL langsung.
 */
class AlignPersonalAccessTokensWithAppSchema extends Migration
{
    /**
     * Kolom info perangkat yang dipakai AuthController::login().
     */
    protected $deviceColumns = ['device_name', 'device_platform'];

    public function up()
    {
        if (! Schema::hasTable('personal_access_tokens')) {
            // Table itself is created by the original 2019 migration.
            // On a brand new install that file already makes it correctly,
            // and on an install that never ran Sanctum's migration at all
            // there is nothing to align — the 2019 migration will handle it.
            return;
        }

        $this->fixTokenableId();
        $this->addMissingDeviceColumns();
    }

    public function down()
    {
        if (! Schema::hasTable('personal_access_tokens')) {
            return;
        }

        // Buang kolom yang ditambahkan migrasi ini.
        //
        // Tipe `tokenable_id` sengaja TIDAK dikembalikan ke unsignedBigInteger:
        // bentuk aslinya justru tidak bisa dipakai aplikasi ini, dan mengubah
        // kolom varchar yang sudah terisi id transaksi balik ke integer bisa
        // gagal atau memotong data. Membiarkan bentuk yang sudah benar adalah
        // pilihan yang lebih aman daripada mengembalikan ke keadaan rusak.
        $buang = array_values(array_filter(
            $this->deviceColumns,
            fn($column) => Schema::hasColumn('personal_access_tokens', $column)
        ));

        if ($buang === []) {
            return;
        }

        Schema::table('personal_access_tokens', function (Blueprint $table) use ($buang) {
            foreach ($buang as $column) {
                $table->dropColumn($column);
            }
        });
    }

    /**
     * Ubah `tokenable_id` dari integer menjadi varchar bila perlu.
     *
     * Primary key tabel `user` berupa varchar '000001', sedangkan Sanctum 2019
     * membuat kolom ini unsignedBigInteger lewat `morphs()`. MySQL tidak protes
     * untuk id berbentuk '000001' — dia menerimanya dan menyimpannya sebagai 1.
     * Jadi gejalanya bukan 500, melainkan lebih diam-diam dan lebih keras
     * dicari: token tersimpan dengan `tokenable_id` 1, sedangkan Sanctum mencari
     * token dengan `tokenable_id` '000001' — tidak akan pernah cocok, dan
     * setiap request setelah login membalas 401.
     */
    protected function fixTokenableId()
    {
        if (! Schema::hasColumn('personal_access_tokens', 'tokenable_id')) {
            return;
        }

        // Kalau sudah varchar, tidak ada yang perlu dikerjakan.
        if (! $this->isIntegerColumn('personal_access_tokens', 'tokenable_id')) {
            return;
        }

        if (DB::connection()->getDriverName() !== 'mysql') {
            // Skema selain MySQL tidak muncul di server produksi ini; lebih
            // baik dilewati daripada menebak-nebak DDL yang salah.
            return;
        }

        // Token yang sudah tersimpan memakai iduser sebagai nilainya, jadi
        // kolom harus bisa menampung string sebelum tipenya dikecilkan/diubah.
        DB::statement(
            'ALTER TABLE `personal_access_tokens` MODIFY `tokenable_id` VARCHAR(255) NOT NULL'
        );
    }

    /**
     * Tambahkan device_name & device_platform yang belum ada.
     */
    protected function addMissingDeviceColumns()
    {
        $kurang = array_values(array_filter(
            $this->deviceColumns,
            fn($column) => ! Schema::hasColumn('personal_access_tokens', $column)
        ));

        if ($kurang === []) {
            return;
        }

        Schema::table('personal_access_tokens', function (Blueprint $table) use ($kurang) {
            foreach ($kurang as $column) {
                $table->string($column)->nullable();
            }
        });
    }

    /**
     * Apakah kolom ini bertipe integer di MySQL?
     */
    protected function isIntegerColumn(string $table, string $column): bool
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return false;
        }

        $row = DB::selectOne(
            'SELECT DATA_TYPE FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, $column]
        );

        return $row
            && in_array(
                strtolower((string) $row->DATA_TYPE),
                ['tinyint', 'smallint', 'mediumint', 'int', 'integer', 'bigint'],
                true
            );
    }
}