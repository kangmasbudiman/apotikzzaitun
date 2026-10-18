<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Pilihan paket langganan — dikelola pemilik lisensi langsung lewat
 * database (harga, durasi, aktif/nonaktif) tanpa perlu ubah kode aplikasi.
 */
class PaketLangganan extends Model
{
    public $timestamps = false;

    protected $table = 'paket_langganan';

    protected $fillable = [
        'nama',
        'durasi_hari',
        'harga',
        'masa_tenggang_hari',
        'aktif',
        'urutan',
        'keterangan',
    ];

    protected $casts = [
        'durasi_hari'        => 'integer',
        'harga'              => 'integer',
        'masa_tenggang_hari' => 'integer',
        'aktif'              => 'boolean',
        'urutan'             => 'integer',
    ];

    public function langganan()
    {
        return $this->hasMany(Langganan::class, 'id_paket');
    }
}
