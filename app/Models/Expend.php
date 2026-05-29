<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expend extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $incrementing = false;
    protected $primaryKey = 'idPengeluaran';

    protected $table = 'pengeluaran';

    protected $fillable = ['idPengeluaran', 'idPembelian', 'idOpname', 'idKategoriPengeluaran', 'namaKategori', 'pengeluaran', 'keterangan', 'tanggal', 'idUser', 'namaUser'];

    protected static function booted()
    {
        static::creating(function ($stock)
        {
            if (!$stock->idPengeluaran) {
                $lastId = Expend::latest('idPengeluaran')->first()->idPengeluaran ?? 0;
                $lastId = (int)substr($lastId, 3, 3);

                $stock->idPengeluaran = sprintf("BOP%03d-%d-%06d", $lastId+1, auth()->id(), date('dmy'));
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(ExpendCategory::class, 'idKategoriPengeluaran');
    }

    public function getPengeluaranConvertedAttribute(): String
    {
        return 'Rp '.number_format($this->pengeluaran);
    }
}
