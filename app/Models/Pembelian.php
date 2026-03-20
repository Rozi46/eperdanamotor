<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    protected $table = 'db_pembelian';
    protected $fillable = [
        'id',
        'code_data',
        'nomor',
        'tanggal',
        'kode_supplier',
        'ket',
        'jenis_pembelian',
        'sub_total',
        'jenis_ppn',
        'ppn',
        'total',
        'diskon_persen',
        'diskon_harga',
        'diskonCash_persen',
        'diskonCash_harga',
        'biaya_kirim',
        'grand_total',
        'status_transaksi',
        'kode_gudang',
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

