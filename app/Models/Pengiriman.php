<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengiriman extends Model
{
    protected $table = 'db_pengiriman_barang';
    protected $fillable = [
        'id',
        'code_data',
        'nomor_pengiriman',
        'nomor_penjualan',
        'tanggal',
        'ket',
        'kode_gudang',
        'kode_kantor',
        'kode_user',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
}

