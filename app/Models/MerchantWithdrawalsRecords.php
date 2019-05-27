<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantWithdrawalsRecords extends Model
{
    //
    protected $fillable=[
        'config_id',
        'merchant_id',
        'merchant_name',
        'out_trade_no',
        'trade_no',
        'account',
        'account_amount',
        'name',
        'amount',
        'sxf_amount',
        'status',
        'status_desc',
        'remark'
    ];
}
