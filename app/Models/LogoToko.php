<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Logo apotek (tabel `logo_toko`) — selalu satu baris, id = 1.
 *
 * Kolom `data` berupa LONGBLOB, sama pola dengan [GambarObat].
 */
class LogoToko extends Model
{
    protected $table = 'logo_toko';

    protected $fillable = ['mime', 'data'];
}
