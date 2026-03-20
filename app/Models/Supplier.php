<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $table = 'db_supplier';
    protected $fillable = [
        'id',
        'code_data',
        'nama',
        'no_telp',
        'alamat',
        'status_data',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
}
