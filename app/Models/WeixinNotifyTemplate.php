<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeixinNotifyTemplate extends Model
{
    //

    protected $table = 'weixin_notify_templates';
    protected $fillable = [
        'config_id',
        'type',
        'template_id',
    ];


}
