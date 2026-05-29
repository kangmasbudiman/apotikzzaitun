<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PPN extends Model
{
    use HasFactory;

    public $timestamps = false;
    public $incrementing = false;

    protected $table = 'ppn';
    protected $primaryKey = 'idPajak';

    protected $fillable = ['idPajak', 'idPenjualan', 'idUser', 'jenis', 'nominal', 'keterangan', 'tanggal'];

    protected static function booted()
    {
        static::creating(function ($ppn)
        {
            if (!$ppn->idPajak) {
                $lastId = PPN::latest('idPajak')->first()->idPajak ?? 0;
                $lastId = (int)substr($lastId, 4);

                $ppn->idPajak = sprintf("PPN%04d-%d-%06d", $lastId+1, auth()->id(), date('dmy'));
            }

            if (!$ppn->tanggal) {
                $ppn->tanggal = date('Y-m-d H:i:s');
            }
        });
    }

    public function transaction()
    {
       return $this->belongsTo(Transaction::class, 'idPenjualan');
    }

    public function expend()
    {
        return $this->hasOne(Expend::class, 'idPajak');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'idUser');
    }
}
