<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'db_setting';
    protected $fillable = [
        'id',
        'database_name',
        'penyimpanan_excel',
        'backup_database',
        'backup_database_name',
        'printer_kasir',
        'report_dsn',
        'manual_book',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
}
