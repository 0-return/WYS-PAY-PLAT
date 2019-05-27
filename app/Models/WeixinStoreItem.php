<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeixinStoreItem extends Model
{
    //

    protected $table = 'weixin_store_items';
    protected $fillable = [
        'config_id',
        'applyment_id',
        'store_id',
        'sub_mch_id',
        'store_type',
        'xw_status',
        'qy_status',
    ];


}
