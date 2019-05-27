<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeixinNotify extends Model
{
    //

    protected $table = 'weixin_notifys';
    protected $fillable = [
        'store_id',
        'merchant_id',
        'wx_open_id',
    ];


}
