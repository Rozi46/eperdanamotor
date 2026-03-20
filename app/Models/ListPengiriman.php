<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListPengiriman extends Model
{
    protected $table = 'db_pengiriman_barangd';
    protected $fillable = [
        'id',
        'code_data',
        'nomor_pengiriman',
        'nomor_penjualan',
        'tanggal',
        'kode_barang',
        'jumlah_jual',
        'jumlah_kirim',
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

