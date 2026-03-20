<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReturPembelian extends Model
{
    protected $table = 'db_retur_pembelian';
    protected $fillable = [
        'id',
        'code_data',
        'nomor_retur',
        'nomor_pembelian',
        'tanggal',
        'kode_supplier',
        'ket',
        'total',
        'kode_gudang',
        'kode_user',
        'kode_kantor',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
}

