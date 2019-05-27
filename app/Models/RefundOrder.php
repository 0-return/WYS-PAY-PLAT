<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RefundOrder extends Model
{
    //
    protected $fillable = [
        'out_trade_no',
        'trade_no',
        'store_id',
        'merchant_id',
        'type',
        'ways_source',
        'refund_amount',//退款金额
        'refund_no',//退款单号
    ];
}
