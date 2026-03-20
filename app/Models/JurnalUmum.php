<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JurnalUmum extends Model
{
    protected $table = 'db_jurnal_umum';
    protected $fillable = [
        'id',
        'code_data',
        'nomor',
        'tanggal',
        'kode_akun',
        'uraian',
        'debet',
        'kredit',
        'kode_user',
        'kode_kantor',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
}

