<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Stock extends Model
{
    use HasFactory;

    protected $table = 'pembelian';
    protected $primaryKey = 'idPembelian';

    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = ['idPembelian', 'idDist', 'idUser', 'total', 'tanggal', 'namaUser', 'namaDist','faktur','jatuh_tempo','tempo'];

    protected static function booted()
    {
        static::creating(function ($stock)
        {
            /*
            if (!$stock->idPembelian) {
                $lastId = Stock::latest('idPembelian')->first()->idPembelian ?? 0;
                $lastId = (int)substr($lastId, 1, 4);

                $stock->idPembelian = sprintf("P%04d-%d-%06d", $lastId+1, auth()->id(), date('dmy'));
            }
            */
            
              if (!$stock->idPembelian) {
                // Bagian 1: "A" + angka acak 5 digit (total 6 karakter)
                $prefix = 'A' . str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
        
                // Bagian 2: angka acak 6 digit
                $middle = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
                // Bagian 3: tanggal sekarang dalam format ddmmyy
                $date = date('dmy');
        
                $stock->idPembelian = "$prefix-$middle-$date";
             }
            
            
            
            
            
        });
    }

    public function expend()
    {
        return $this->hasOne(Expend::class, 'idPembelian');
    }

    public function detail()
    {
        return $this->hasMany(DetailStock::class, 'idPembelian');
    }

    public function stuffs()
    {
    	return $this->belongsToMany(Stuff::class, 'detail_pembelian', 'idPembelian', 'idBuku')->using(DetailStock::class)->withPivot('obat', 'hargaPokok', 'jumlah');
    }

    public function distributor()
    {
        return $this->belongsTo(Distributor::class, 'idDist');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'idUser');
    }
}
