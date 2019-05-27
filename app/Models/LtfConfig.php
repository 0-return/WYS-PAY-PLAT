<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LtfConfig extends Model
{
    protected $table = 'ltf_configs';

    protected $fillable = [
        'config_id',
        'ali_appid',
        'wx_appid',
        'wx_secret',
    ];

}
