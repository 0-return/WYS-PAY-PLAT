<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MyBankStore extends Model
{
    protected $fillable = [
        'status',
        'OrderNo',
        'config_id',
        'smid',
        'RateVersion',
        'OutMerchantId',
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
        'wx_SubscribeAppId',
        'is_yulibao',
    ];
}
