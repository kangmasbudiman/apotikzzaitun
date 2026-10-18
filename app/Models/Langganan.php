<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu periode langganan aktif. Baris TERAKHIR (id paling besar) adalah
 * masa aktif yang sedang berjalan — mengaktifkan ulang tidak mengubah
 * baris lama, hanya menambah baris baru (jejak histori tersimpan).
 */
class Langganan extends Model
{
    public $timestamps = false;

    protected $table = 'langganan';

    protected $fillable = [
        'id_paket',
        'idUser',
        'nama_user',
        'mulai',
        'berakhir',
        'id_perangkat',
        'nama_perangkat',
    ];

    protected $casts = [
        'mulai'     => 'datetime',
        'berakhir'  => 'datetime',
    ];

    public function paket(): BelongsTo
    {
        return $this->belongsTo(PaketLangganan::class, 'id_paket');
    }
}
