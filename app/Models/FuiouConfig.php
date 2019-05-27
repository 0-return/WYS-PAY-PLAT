<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FuiouConfig extends Model
{
    protected $table = 'fuiou_configs';

    protected $fillable = [
        'config_id',
        'ins_cd',
        'my_private_key',
        'fy_public_key',
        'wx_appid',
        'wx_secret',
        'md_key',
    ];

}
