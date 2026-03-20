<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mutasi extends Model
{
    protected $table = 'db_mutasi';
    protected $fillable = [
        'id',
        'code_data',
        'nomor',
        'tanggal',
        'ket',
        'kode_barang',
        'qty',
        'kode_satuan',
        'kode_gudang_asal',
        'kode_gudang_tujuan',
        'status_transaksi',
        'kode_kantor',
        'kode_user',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
}

