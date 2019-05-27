<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositRefundOrder extends Model
{
    //
    protected $fillable = [
        'out_trade_no',
        'store_id',
        'merchant_id',
        'ways_source',
        'order_amount',
        'refund_amount',//退款金额
        'refund_no',//退款单号
        'refund_status',
    ];
}
