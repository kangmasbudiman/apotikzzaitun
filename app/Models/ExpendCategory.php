<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExpendCategory extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $primaryKey = 'idKategoriPengeluaran';

    protected $table = 'kategori_pengeluaran';
    protected $fillable = ['nama'];

    protected static function booted()
    {
        static::updated(function ($category)
        {
            $category->expend()->update(['namaKategori' => $category->nama]);
        });
    }

    public function expend()
    {
        return $this->hasMany(Expend::class, 'idKategoriPengeluaran');
    }
}
