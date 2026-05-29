<?php 

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class DetailTransaction extends Pivot
{

    protected $table = 'detail_penjualan';
    protected $primaryKey = 'idDetailPenjualan';

    public $timestamps = false;
    public $incrementing = false;

    protected static function booted()
    {
        static::creating(function ($transaction)
        {
            $lastId = DetailTransaction::latest('idDetailPenjualan')->first()->idDetailPenjualan ?? 0;
            $lastId = (int)substr($lastId, 1);

            $transaction->idDetailPenjualan = 'T'.sprintf("%05d", $lastId+1);
        });
    }

    public function getStatusBadgeAttribute(): String
    {
        return $this->status ? '<span class="badge badge-success">berhasil</span>' : '<span class="badge badge-danger">batal</span>';
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'idPenjualan');
    }

    public function stuff()
    {
        return $this->belongsTo(Stuff::class, 'idBuku');
    }

}

 ?>