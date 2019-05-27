<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeixinConfig extends Model
{
    //

    protected $table = 'weixin_config';
    protected $fillable = [
        'config_id',
        'app_id',
        'app_name',
        'secret',
        'wx_merchant_id',
        'key',
        'face_md_key',
        'cert_path',
        'key_path',
        'auth_path',
        'notify_url',
        'config_type',
        'authorizer_appid',
        'authorizer_refresh_token',
        'authorizer_time',
        'wx_notify_appid',
        'wx_notify_secret',
        'template_id',
    ];


}
