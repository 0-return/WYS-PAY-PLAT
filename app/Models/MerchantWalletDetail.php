<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantWalletDetail extends Model
{
    //
    protected $fillable = [
        'config_id',
        'store_id',
        'merchant_id',
        'merchant_name',
        'phone',
        'money',
        'out_trade_no',
        'trade_no',
        'wallet_type',
        'wallet_type_desc',
        'settlement',
        'settlement_desc',
        'settlement_time'
    ];

}
