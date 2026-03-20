<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KasKeluar extends Model
{
    protected $table = 'db_kaskeluar';
    protected $fillable = [
        'id',
        'code_data',
        'nomor',
        'tanggal',
        'kode_akun',
        'jenis',
        'keterangan',
        'nilai',
        'kode_kantor',
        'kode_user',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
}

