<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Foto produk satu obat (tabel `gambar_obat`).
 *
 * Kolom `data` berupa LONGBLOB — PDO MySQL mengembalikannya sebagai string,
 * dan assignment string juga yang dipakai saat menyimpan.
 */
class GambarObat extends Model
{
    protected $table = 'gambar_obat';
    protected $primaryKey = 'idBuku';

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['idBuku', 'mime', 'data'];
}
