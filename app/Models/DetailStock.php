<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class DetailStock extends Pivot
{

    protected $table = 'detail_pembelian';

    public $timestamps = false;
    public $incrementing = false;

    protected static function booted()
    {
        static::creating(function ($stock)
        {
            $lastId = DetailStock::latest('idDetailPembelian')->first()->idDetailPembelian ?? 0;
            $lastId = (int)substr($lastId, 1);

            $stock->idDetailPembelian = 'P'.sprintf("%05d", $lastId+1);
        });
    }

    public function stock()
    {
        return $this->belongsTo(Stock::class, 'idPembelian');
    }

    public function stuff()
    {
        return $this->belongsTo(Stuff::class, 'idBuku');
    }

}

 ?>