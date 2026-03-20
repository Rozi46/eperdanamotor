<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    protected $table = 'db_activity';
    protected $fillable = [
        'id',
        'code_data',
        'kode_user',
        'activity',
        'kode_kantor',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
}

