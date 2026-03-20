<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ListAkses extends Model
{
    protected $table = 'db_list_akses_web';
    protected $fillable = [
        'id',
        'no_urut',
        'nama_menu',
        'nama_akses',
        'menu_index',
        'menu',
        'submenu',
        'action',
        'subaction',
        'icon_menu',
        'status_data',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
}
