<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListPenjualanMekanik extends Model
{
    protected $table = 'db_penjualan_mekanik';
    protected $fillable = [
        'id',
        'code_data',
        'nomor',
        'tanggal',
        'code_mekanik',
        'kode_kantor',
        'kode_user',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
}

