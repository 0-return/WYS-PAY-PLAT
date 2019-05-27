<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlipayAccount extends Model
{
    //
    protected $fillable = [
        'alipay_user_id',
        'store_id',
        'settlement_type',
        'alipay_account',
        'account_name',
        'config_id',
        'config_type',
        'auth_app_id',
        'app_auth_token',
        'app_refresh_token',
        'expires_in',
        're_expires_in',
        'trade_pay_rate',
    ];

}
