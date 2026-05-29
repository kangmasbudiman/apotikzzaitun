<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Distributor extends Model
{
    use HasFactory;

    public $incrementing = false;
    public $timestamps = false;

    protected $table = 'distributor';
    protected $primaryKey = 'idDist';
    protected $fillable = ['namaDist', 'alamat', 'telepon'];

    protected static function booted()
    {
        static::creating(function ($stuff)
        {
            $lastId = Distributor::latest('idDist')->first()->idDist ?? 0;
            $lastId = intval(substr($lastId, 3));

            $stuff->idDist = 'DIS'.sprintf("%04d", $lastId+1);
        });
    }
}
