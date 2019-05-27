<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeixinAppConfig extends Model
{
    //

    protected $table = 'weixin_app_configs';
    protected $fillable = [
        'config_id',
        'wx_appid',
        'wx_secret',
    ];


}
