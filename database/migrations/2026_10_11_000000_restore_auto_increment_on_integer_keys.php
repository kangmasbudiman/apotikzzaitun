<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Pulihkan AUTO_INCREMENT pada tabel yang primary key-nya integer.
 *
 * Latar belakang: pada database live, kolom-kolom berikut kehilangan
 * atribut AUTO_INCREMENT dan sebagian juga kehilangan PRIMARY KEY,
 * padahal file db_toko.sql aslinya memilikinya. Akibatnya INSERT tanpa
 * id eksplisit gagal dengan SQLSTATE 1364 — termasuk menu "Tambah Kategori",
 * "Tambah Rak", "Tambah Kategori Pengeluaran", "Stok Opname" di aplikasi
 * web, dan endpoint API yang baru dibuat.
 *
 * Tabel dengan primary key varchar (buku, penjualan, pembelian, dll) TIDAK
 * disentuh: id-nya memang dibuat manual oleh model lewat event `creating`.
 */
class RestoreAutoIncrementOnIntegerKeys extends Migration
{
    /**
     * kolom integer yang perlu dipulihkan AUTO_INCREMENT-nya.
     */
    protected $targets = [
        'kategori'             => 'idKategori',
        'rak'                  => 'idRak',
        'kategori_pengeluaran' => 'idKategoriPengeluaran',
        'opname'               => 'idOpname',
        'pengaturan'           => 'idPengaturan',
    ];

    public function up()
    {
        foreach ($this->targets as $table => $column) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
                continue;
            }

            // --- Langkah 1: pastikan kolomnya ada PRIMARY KEY -----------
            // MySQL menolak AUTO_INCREMENT pada kolom yang bukan key.
            if (! $this->hasPrimaryKey($table, $column)) {
                DB::statement(sprintf(
                    'ALTER TABLE `%s` ADD PRIMARY KEY (`%s`)',
                    $table,
                    $column
                ));
            }

            // --- Langkah 2: pasang AUTO_INCREMENT bila belum ada -------
            if ($this->hasAutoIncrement($table, $column)) {
                continue;
            }

            DB::statement(sprintf(
                'ALTER TABLE `%s` MODIFY `%s` %s NOT NULL AUTO_INCREMENT',
                $table,
                $column,
                $this->baseType($this->columnType($table, $column))
            ));

            // Lanjutkan penomoran dari data yang sudah ada, supaya tidak
            // bentrok dengan id lama.
            DB::statement(sprintf(
                'ALTER TABLE `%s` AUTO_INCREMENT = %d',
                $table,
                ((int) DB::table($table)->max($column)) + 1
            ));
        }
    }

    public function down()
    {
        // Sengaja dikosongkan.
        // Melepas AUTO_INCREMENT dari primary key bisa merusak data yang
        // sudah bergantung pada kolom itu sebagai penghitung, dan tidak
        // bisa mengembalikan PRIMARY KEY yang tadinya hilang.
    }

    protected function hasPrimaryKey(string $table, string $column): bool
    {
        return (bool) DB::select(
            'SELECT 1 FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ?
               AND COLUMN_NAME = ? AND CONSTRAINT_NAME = ? LIMIT 1',
            [$table, $column, 'PRIMARY']
        );
    }

    protected function hasAutoIncrement(string $table, string $column): bool
    {
        $row = DB::selectOne(
            'SELECT EXTRA FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, $column]
        );

        return $row && strpos((string) $row->EXTRA, 'auto_increment') !== false;
    }

    /**
     * Tipe kolom seperti tertulis di database, mis. "int" / "int unsigned".
     */
    protected function columnType(string $table, string $column): string
    {
        $row = DB::selectOne(
            'SELECT COLUMN_TYPE FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, $column]
        );

        return $row ? (string) $row->COLUMN_TYPE : 'int';
    }

    /**
     * Buang sufiks unsigned/zerofill bila ada, sisakan tipe dasarnya agar
     * aman dipakai ulang di statement ALTER.
     */
    protected function baseType(string $type): string
    {
        return trim(preg_replace('/\s+(unsigned|zerofill)$/i', '', trim($type)));
    }
}