<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppUpdate extends Model
{
    //
    protected $fillable = [
        'app_id',
        'version',
        'type',
        'UpdateUrl',
        'msg',
        'created_at',
        'updated_at'
    ];
}
