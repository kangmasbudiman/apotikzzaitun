<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Piutang extends Model
{
    public $timestamps = false;

    protected $table = 'piutang';

    protected $fillable = [
        'idPenjualan',
        'idUser',
        'nama_user',
        'nama_pelanggan',
        'telepon',
        'nominal',
        'status',
        'tanggal',
        'tanggal_lunas',
    ];

    protected $casts = [
        'status'        => 'boolean',
        'tanggal'       => 'datetime',
        'tanggal_lunas' => 'datetime',
    ];

    public function getLunasAttribute(): bool
    {
        return ! $this->status;
    }
}
