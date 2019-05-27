<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeixinStore extends Model
{
    //

    protected $table = 'weixin_stores';
    protected $fillable = [
        'config_id',
        'store_id',
        'secret',
        'wx_sub_merchant_id',
        'status',
        'status_desc',
    ];


}
