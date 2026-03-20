<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'db_barang';
    protected $fillable = [
        'id',
        'code_data',
        'kode',
        'nama',
        'kode_satuan',
        'kode_jenis',
        'kode_brand',
        'kode_supplier',
        'kode_satuan_default',
        'type_produk',
        'harga_beli',
        'margin_jual1',
        'harga_jual1',
        'margin_jual2',
        'harga_jual2',
        'margin_jual3',
        'harga_jual3',
        'margin_jual4',
        'harga_jual4',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
}

