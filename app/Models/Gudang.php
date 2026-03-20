<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gudang extends Model
{
    protected $table = 'db_gudang';
    protected $fillable = [
        'id',
        'code_data',
        'nama',
        'no_hp',
        'alamat',
        'status_data',
        'jenis_gudang',
        'kode_kantor',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
}

