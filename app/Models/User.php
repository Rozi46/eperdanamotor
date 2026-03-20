<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    // protected $connection = 'mysql';
    // protected $connection = 'pgsql';
    
    protected $table = 'db_users_web';
    protected $fillable = [
        'id',
        'code_data',
        'full_name',
        'email',
        'password',
        'phone_number',
        'level',
        'image',
        'status_data',
        'key_token',
        'tipe_user',
        'tipe_login',
        'kode_kantor',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */

    protected $hidden = [
       'password', 'remember_token',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }    
    
    public function piutang()
    {
        return $this->hasMany(Piutang::class, 'kode_user', 'id');
    }

    public function penjualan()
    {
        return $this->hasMany(Penjualan::class, 'kode_user', 'id');
    }

    public function listPenjualan()
    {
        return $this->hasMany(ListPenjualan::class, 'kode_user', 'id');
    }
}
