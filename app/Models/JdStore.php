<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JdStore extends Model
{
    protected $table = 'jd_stores';

    protected $fillable = [
        'pid',
        'store_id',
        'config_id',
        'merchant_no',
        'serialNo',
        'md_key',
        'des_key',
        'status',
        'store_true',
        'pay_true',
        'bt_true',
    ];

}
