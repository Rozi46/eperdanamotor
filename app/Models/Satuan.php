<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Satuan extends Model
{
    protected $table = 'db_satuan_barang';
    protected $fillable = [
        'id',
        'code_user',
        'code_data',
        'nama',
        'isi',
        'status_pecahan',
        'kode_pecahan',
        'satuan_pecahan',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    public function listPenjualan()
    {
        return $this->hasMany(ListPenjualan::class, 'kode_satuan', 'id');
    }

    public function barang()
    {
        return $this->hasMany(Barang::class, 'kode_satuan', 'id');
    }
    
    public function pecahan()
    {
        return $this->hasMany(Satuan::class, 'kode_pecahan', 'id');
    }
}

