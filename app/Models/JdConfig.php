<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JdConfig extends Model
{
    protected $table = 'jd_configs';

    protected $fillable = [
        'config_id',
        'systemId',
        'store_md_key',
        'store_des_key',
        'agentNo',
        'ali_appid',
        'wx_appid',
        'wx_secret',
    ];

}
