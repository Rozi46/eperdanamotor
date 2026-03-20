<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    protected $table = 'db_penjualan';
    protected $fillable = [
        'id',
        'code_data',
        'nomor',
        'tanggal',
        'kode_customer',
        'ket',
        'jenis_penjualan',
        'sub_total',
        'ppn',
        'total',
        'diskon_persen',
        'diskon_harga',
        'biaya_kirim',
        'grand_total',
        'status_transaksi',
        'kode_gudang',
        'kode_kantor',
        'kode_user',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    public function listPenjualan()
    {
        return $this->hasMany(ListPenjualan::class, 'code_data', 'code_data');
    }

    public function piutang()
    {
        return $this->hasMany(Piutang::class, 'code_data', 'code_data');
    }
    
    public function customer()
    {
        return $this->belongsTo(Customer::class, 'kode_customer', 'id');
    }
    
    public function gudang()
    {
        return $this->belongsTo(Gudang::class, 'kode_gudang', 'id');
    }
    
    public function kantor()
    {
        return $this->belongsTo(Kantor::class, 'kode_kantor', 'kode');
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'kode_user', 'id');
    }
    
    public function getQtyPenjualanAttribute()
    {
        return $this->listPenjualan->sum('jumlah_jual');
    }
}

