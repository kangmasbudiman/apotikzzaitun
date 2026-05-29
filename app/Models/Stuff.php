<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stuff extends Model
{
    use HasFactory;

    protected $table = 'buku';
    protected $primaryKey = 'idBuku';

    public $incrementing = false;
    public $timestamps = false;
    
    protected static function booted()
    {
        static::creating(function ($stuff)
        {
            $lastId = Stuff::latest('idBuku')->first()->idBuku ?? 0;
            $lastId = intval(substr($lastId, 2));

            $stuff->idBuku = 'OB'.sprintf("%05d", $lastId+1);
        });
    }

    // protected $fillable = ['code', 'name', 'price', 'stock', 'category_id'];
    protected $fillable = ['idKategori', 'barcode', 'noisbn', 'idRak', 'obat', 'penulis', 'suplier', 'tahun', 'stock', 'hargaPokok', 'hargaJual', 'disc','ppn','margin'];

    protected $guarded = ['idBuku'];

    public function category()
    {
    	return $this->belongsTo(Category::class, 'idKategori', 'idKategori');
    }

    public function rack()
    {
        return $this->belongsTo(Rack::class, 'idRak', 'idRak');
    }
}
