<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'idPengaturan';

    protected $table = 'pengaturan';

    protected $fillable = ['idUser', 'nama_toko', 'alamat_toko', 'telepon_toko', 'ppn', 'logo', 'pakaiLogo', 'min_stok'];
}
