<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListPenerimaan extends Model
{
    protected $table = 'db_penerimaan_barangd';
    protected $fillable = [
        'id',
        'code_data',
        'nomor_penerimaan',
        'nomor_pembelian',
        'tanggal',
        'kode_barang',
        'jumlah_beli',
        'jumlah_terima',
        'kode_satuan',
        'kode_kantor',
        'kode_user',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
}

