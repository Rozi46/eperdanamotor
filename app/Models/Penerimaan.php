<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penerimaan extends Model
{
    protected $table = 'db_penerimaan_barang';
    protected $fillable = [
        'id',
        'code_data',
        'nomor_penerimaan',
        'nomor_pembelian',
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

