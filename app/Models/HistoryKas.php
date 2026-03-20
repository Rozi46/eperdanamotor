<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoryKas extends Model
{
    protected $table = 'db_aruskas';
    protected $fillable = [
        'id',
        'code_data',
        'nomor',
        'tanggal',
        'debet',
        'kredit',
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

