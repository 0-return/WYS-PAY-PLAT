<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWalletDetail extends Model
{
    //
    protected $fillable = [
        'config_id',
        'store_id',
        'user_id',
        'user_name',
        'phone',
        'money',
        'out_trade_no',
        'trade_no',
        'source_type',
        'source_desc',
        'settlement',
        'settlement_desc',
        'settlement_time',
    ];

}
