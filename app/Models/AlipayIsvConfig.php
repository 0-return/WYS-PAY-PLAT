<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlipayIsvConfig extends Model
{
    //
    protected $fillable = [
        'app_id',
        'config_type',
        'isv_name',
        'isv_phone',
        'm_pay_url',
        'hb_pay_url',
        'config_id',
        'alipay_pid',
        'operate_notify_url',
        'rsa_private_key',
        'alipay_rsa_public_key',
        'callback',
        'alipay_app_oauth_url',
        'alipay_app_authorize',
        'alipay_gateway',
        'notify',
        'alipay_school_auth_url',
        'alipay_rsa_old_public_key',
    ];
}
