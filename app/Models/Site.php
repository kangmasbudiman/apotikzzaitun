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

    protected $fillable = ['idUser', 'nama_sistem', 'nama_toko', 'alamat_toko', 'telepon_toko', 'ppn', 'logo', 'pakaiLogo', 'min_stok', 'wa_pemilik', 'laporan_wa_aktif', 'wa_token', 'laporan_wa_terakhir'];
}
