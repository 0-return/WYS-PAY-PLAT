<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MyBankConfig extends Model
{
    //
    protected $fillable = [
        'config_id',
        'Appid',
        'IsvOrgId',
        'partner_private_key',
        'mybank_public_key',
        'SubscribeMerchId',
        'Path',
        'wx_AppId',
        'wx_Secret',
        'SubscribeAppId',
        'ali_pid',
    ];
}
