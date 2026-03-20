<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cabang extends Model
{
    protected $table = 'db_cabang';
    protected $fillable = [
        'id',
        'code_data',
        'kode_cabang',
        'nama_cabang',
        'nama_pic',
        'nomor_pic',
        'alamat',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
}

