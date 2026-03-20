<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Piutang extends Model
{
    protected $table = 'db_piutang';
    protected $fillable = [
        'id',
        'code_data',
        'nomor',
        'tanggal',
        'jumlah',
        'bayar',
        'sisa',
        'kode_user',
        'kode_kantor',
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
    
    public function user()
    {
        return $this->belongsTo(User::class, 'kode_user', 'id');
    }
    
    public function kantor()
    {
        return $this->belongsTo(Kantor::class, 'kode_kantor', 'kode');
    }
    
    public function piutangBayar()
    {
        return $this->hasMany(PiutangBayar::class, 'nomor_piutang', 'nomor');
    }
}

