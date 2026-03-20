<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PiutangBayar extends Model
{
    protected $table = 'db_piutang_bayar';
    protected $fillable = [
        'id',
        'code_data',
        'nomor',
        'tanggal',
        'nomor_piutang',
        'jumlah',
        'kode_user',
        'kode_kantor',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';    
    
    public function piutang()
    {
        return $this->belongsTo(Piutang::class, 'nomor_piutang', 'nomor');
    }
}

