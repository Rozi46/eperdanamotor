<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PenyesuaianStock extends Model
{
    protected $table = 'db_penyesuaian_stock';
    protected $fillable = [
        'id',
        'code_data',
        'code_transaksi',
        'tanggal_transaksi',
        'kode_gudang',
        'kode_barang',
        'stock_awal',
        'stock_penyesuaian',
        'stock_akhir',
        'keterangan',
        'kode_kantor',
        'kode_user',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
}

