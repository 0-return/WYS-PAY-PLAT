<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlipayAppOauthUsers extends Model
{
    //
    protected $fillable = [
        'alipay_user_id',
        'alipay_store_id',
        'out_store_id',
        'merchant_id',
        'alipay_user_account',
        'alipay_user_account_name',
        'config_type',
        'pid',
        'store_id',
        'is_delete',
        'auth_app_id',
        'app_auth_token',
        'app_refresh_token',
        'expires_in',
        're_expires_in',
        'trade_pay_rate',
    ];

}
