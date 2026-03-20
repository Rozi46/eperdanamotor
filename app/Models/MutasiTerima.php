<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MutasiTerima extends Model
{
    protected $table = 'db_mutasi_terima';
    protected $fillable = [
        'id',
        'code_data',
        'nomor',
        'tanggal',
        'ket',
        'kode_barang',
        'jumlah_kirim',
        'jumlah_terima',
        'kode_satuan',
        'kode_gudang_asal',
        'kode_gudang_tujuan',
        'kode_kantor',
        'kode_user',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
}

