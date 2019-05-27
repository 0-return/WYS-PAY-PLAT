<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeixinOpenConfig extends Model
{
    //

    protected $table = 'weixin_open_configs';
    protected $fillable = [
        'config_id',
        'app_id',
        'secret',
        'token',
        'aes_key',
        'component_verify_ticket',
    ];
}
