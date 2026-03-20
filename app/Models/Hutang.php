<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hutang extends Model
{
    protected $table = 'db_hutang';
    protected $fillable = [
        'id',
        'code_data',
        'nomor',
        'tanggal',
        'jumlah',
        'bayar',
        'sisa',
        'kode_user',
        'kode_kantor',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
}

