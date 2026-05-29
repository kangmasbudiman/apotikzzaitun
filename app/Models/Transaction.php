<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $table = 'penjualan';
    protected $primaryKey = 'idPenjualan';

    public $timestamps = false;
    public $incrementing = false;

    protected $fillable = ['idPenjualan', 'idUser', 'namaUser', 'total', 'ppn', 'tanggal', 'status','diskon','grandtotal','kembalian','namapasien','tgl_lahir'];

    protected static function booted()
    {
        static::creating(function ($transaction)
        {
            /*
            if (!$transaction->idPenjualan) {
                $lastId = Transaction::latest('idPenjualan')->first()->idPenjualan ?? 0;
                $lastId = (int)substr($lastId, 1, 10);

                $transaction->idPenjualan = sprintf("A%08d-%d-%06d", $lastId+1, auth()->id(), date('dmy'));
            }
            */
            
             if (!$transaction->idPenjualan) {
                // Bagian 1: "A" + angka acak 5 digit (total 6 karakter)
                $prefix = 'A' . str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
        
                // Bagian 2: angka acak 6 digit
                $middle = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        
                // Bagian 3: tanggal sekarang dalam format ddmmyy
                $date = date('dmy');
        
                $transaction->idPenjualan = "$prefix-$middle-$date";
             }
            
            
            
            
        });
    }

    public function getDateAttribute()
    {
        return localDate($this->tanggal);
    }

    public function getStatusBadgeAttribute(): String
    {
        return $this->status ? '<span class="badge badge-success">berhasil</span>' : '<span class="badge badge-danger">batal</span>';
    }

    public function detail()
    {
        return $this->hasMany(DetailTransaction::class, 'idPenjualan');
    }

    public function ppn()
    {
       // return $this->hasOne(PPN::class, 'idPenjualan');
    }

    public function stuffs()
    {
        return $this->belongsToMany(Stuff::class, 'detail_penjualan', 'idPenjualan', 'idBuku')
        ->using(DetailTransaction::class)
        ->withPivot('obat', 'hargaPokok', 'hargaJual', 'jumlah');
    }

    public function user()
    {
    	return $this->belongsTo(User::class, 'idUser');
    }
}
