<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListPembelian extends Model
{
    protected $table = 'db_pembeliand';
    protected $fillable = [
        'id',
        'code_data',
        'nomor',
        'tanggal',
        'kode_barang',
        'jumlah_beli',
        'jumlah_terima',
        'jumlah_retur',
        'kode_satuan',
        'harga',
        'diskon_persen',
        'diskon_harga',
        'diskon_persen2',
        'diskon_harga2',
        'diskon_persen3',
        'diskon_harga3',
        'harga_netto',
        'total_harga',
        'status_ppn',
        'ppn',
        'kode_kantor',
        'kode_user',
        'kode_cabang',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
}

