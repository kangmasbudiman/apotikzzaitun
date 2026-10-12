<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Satu batch fisik obat beserta sisa stoknya.
 *
 * `buku.stock` tetap sumber kebenaran total; tabel ini memecah total itu
 * per batch supaya penjualan bisa memakai urutan FEFO (yang paling cepat
 * kedaluwarsanya keluar lebih dulu).
 */
class BatchObat extends Model
{
    protected $table = 'batch_obat';
    protected $primaryKey = 'idBatch';
    public $timestamps = true;

    protected $fillable = [
        'idBuku', 'noBatch', 'expired', 'hargaPokok',
        'stokMasuk', 'stokKeluar', 'idPembelian', 'idDetailPembelian',
    ];

    protected $casts = [
        'expired'    => 'date',
        'hargaPokok' => 'integer',
        'stokMasuk'  => 'integer',
        'stokKeluar' => 'integer',
    ];

    // Kolom bantu di SQL (generated saat query).
    protected $appends = ['stokTersisa', 'hariLagi'];

    public function getStokTersisaAttribute(): int
    {
        return ((int) $this->stokMasuk) - ((int) $this->stokKeluar);
    }

    public function getHariLagiAttribute(): ?int
    {
        if (! $this->expired) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->expired->startOfDay(), false);
    }

    public function buku()
    {
        return $this->belongsTo(Stuff::class, 'idBuku', 'idBuku');
    }

    public function scopeTersisa($query)
    {
        return $query->whereColumn('stokMasuk', '>', 'stokKeluar');
    }

    /**
     * Urutan FEFO.
     *
     * 1. Batch yang punya tanggal kedaluwarsa, paling awal didahulukan.
     * 2. Batch tanpa tanggal (mis. impor awal) diletakkan paling akhir —
     *    tidak ada alasan prioritizing-nya di depan yang bertanggal.
     */
    public function scopeUrutFefo($query)
    {
        return $query->orderByRaw('expired IS NULL')
            ->orderBy('expired', 'asc')
            ->orderBy('idBatch', 'asc');
    }
}