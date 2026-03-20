<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FetchData extends Model
{
    protected $table = 'db_fetchdata';
    protected $fillable = [
        'id',
        'code_data',
        'title',
        'content',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
}

