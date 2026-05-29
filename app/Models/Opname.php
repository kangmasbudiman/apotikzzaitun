<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Opname extends Model
{
    use HasFactory;
    
    public $timestamps = false;
    public $incrementing = true;

    protected $table = 'opname';
    protected $primaryKey = 'idOpname';

    protected $fillable = ['idOpname', 'idBuku', 'obat', 'tanggal', 'stokSistem', 'stokNyata', 'hargaPokok', 'keterangan'];

    protected static function booted()
    {
        static::creating(function ($stock)
        {
            if (!$stock->tanggal) {
                $stock->tanggal = date('Y-m-d H:i:s');
            }
        });
    }

    public function expend()
    {
        return $this->hasOne(Expend::class, 'idOpname');
    }

    public function book()
    {
        return $this->belongsTo(Stuff::class, 'idBuku');
    }

    public function getSelisihAttribute(): Int
    {
        return abs($this->stokNyata - $this->stokSistem);
    }

    public function getTotalAttribute(): Int
    {
        return $this->selisih * $this->hargaPokok;
    }

}
