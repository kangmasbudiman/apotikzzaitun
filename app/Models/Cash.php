<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cash extends Model
{
    use HasFactory;

    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'kas_harian';
    protected $primaryKey = 'idCash';
    protected $fillable = ['tanggal', 'total'];

    protected static function booted()
    {
        static::creating(function ($stuff)
        {
            $lastId = Cash::latest('idCash')->first()->idCash ?? 0;
            $lastId = intval(substr($lastId, 4));

            $stuff->idCash = 'Cash'.sprintf("%04d", $lastId+1);
        });
    }
}
