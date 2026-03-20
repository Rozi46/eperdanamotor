<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListPenjualan extends Model
{
    protected $table = 'db_penjualand';
    protected $fillable = [
        'id',
        'code_data',
        'nomor',
        'tanggal',
        'kode_barang',
        'jumlah_jual',
        'jumlah_kirim',
        'jumlah_retur',
        'kode_satuan',
        'harga',
        'diskon_persen',
        'diskon_harga',
        'diskon_persen2',
        'diskon_harga2',
        'harga_netto',
        'total_harga',
        'status_ppn',
        'ppn',
        'kode_kantor',
        'kode_user',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class, 'code_data', 'code_data');
    }
    
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'kode_barang', 'id');
    }
    
    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'kode_satuan', 'id');
    }
    
    public function kantor()
    {
        return $this->belongsTo(Kantor::class, 'kode_kantor', 'kode');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'kode_user', 'id');
    }
}

