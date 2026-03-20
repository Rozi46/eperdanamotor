<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HutangBayar extends Model
{
    protected $table = 'db_hutang_bayar';
    protected $fillable = [
        'id',
        'code_data',
        'nomor',
        'tanggal',
        'nomor_hutang',
        'jumlah',
        'kode_user',
        'kode_kantor',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
}

