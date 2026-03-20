<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kantor extends Model
{
    protected $table = 'db_kantor';
    protected $fillable = [
        'id',
        'kode',
        'kantor',
        'jenis',
        'alamat',
        'email',
        'ket',
        'foto',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    
    public function piutang()
    {
        return $this->hasMany(Piutang::class, 'kode_kantor', 'kode');
    }

    public function penjualan()
    {
        return $this->hasMany(Penjualan::class, 'kode_kantor', 'kode');
    }

    public function listPenjualan()
    {
        return $this->hasMany(ListPenjualan::class, 'kode_kantor', 'kode');
    }
}

