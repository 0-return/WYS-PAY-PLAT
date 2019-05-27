<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VConfig extends Model
{
    protected $table = 'v_configs';

    protected $fillable = [
        'config_id',
        'zw_token',
        'zlbz_token',
    ];

}
