<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListReturPembelian extends Model
{
    protected $table = 'db_retur_pembeliand';
    protected $fillable = [
        'id',
        'code_data',
        'nomor_retur',
        'nomor_pembelian',
        'tanggal',
        'kode_barang',
        'jumlah_beli',
        'jumlah_retur',
        'kode_satuan',
        'harga',
        'total_harga',
        'kode_user',
        'kode_kantor',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
}

