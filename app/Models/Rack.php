<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rack extends Model
{
    use HasFactory;

    public $timestamps = false;
    protected $primaryKey = 'idRak';

    protected $table = 'rak';
    protected $fillable = ['nama_rak'];
}
