<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LevelAdmin extends Model
{
    protected $table = 'db_level_admin_web';
    protected $fillable = [
        'id',
        'code_data',
        'level_name',
        'data_menu',
        'access_rights',
        'created_at',
        'updated_at'
    ];
    
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    public function user()
    {
        return $this->hasMany(User::class, 'level', 'code_Data');
    }
}
