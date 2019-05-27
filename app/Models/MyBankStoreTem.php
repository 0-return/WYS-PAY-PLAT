<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MyBankStoreTem extends Model
{
    //
    protected $fillable = [
        'OrderNo',
        'config_id',
        'OutMerchantId',
        'RateVersion',
        'MerchantId',
        'MerchantName',
        'MerchantType',
        'DealType',
        'SupportPrepayment',
        'SettleMode',
        'Mcc',
        'MerchantDetail',
        'TradeTypeList',
        'PrincipalCertType',
        'PayChannelList',
        'DeniedPayToolList',
        'FeeParamList',
        'BankCardParam',
        'SupportStage',
        'PartnerType',
        'wx_Path',
        'wx_AppId',
        'wx_Secret',
        'wx_SubscribeAppId'
    ];
}
