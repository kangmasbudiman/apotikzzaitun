<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'user'; 
    protected $primaryKey = 'idUser';

    public $incrementing = false;
    public $timestamps = false;

    protected static function booted()
    {
        static::creating(function ($user)
        {
            $lastId = User::latest('idUser')->first()->idUser ?? 0;
            $lastId = intval(substr($lastId, -6));

            $user->idUser = sprintf("%06d", $lastId+1);
        });
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nama',
        'username',
        'alamat',
        'telepon',
        'password',
        'hakAkses',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function setPasswordAttribute($password)
    {
        $this->attributes['password'] = md5($password);
    }
}
