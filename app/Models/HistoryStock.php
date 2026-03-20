<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryStock extends Model
{
    protected $table = 'db_arusstock';
    protected $fillable = [
        'id',
        'code_data',
        'nomor',
        'kode_barang',
        'tanggal',
        'masuk',
        'keluar',
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

