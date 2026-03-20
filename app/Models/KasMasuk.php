<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KasMasuk extends Model
{
    protected $table = 'db_kasmasuk';
    protected $fillable = [
        'id',
        'code_data',
        'nomor',
        'tanggal',
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

