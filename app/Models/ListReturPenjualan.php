<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListReturPenjualan extends Model
{
    protected $table = 'db_retur_penjuland';
    protected $fillable = [
        'id',
        'code_data',
        'nomor_retur',
        'nomor_penjualan',
        'tanggal',
        'kode_barang',
        'jumlah_jual',
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

