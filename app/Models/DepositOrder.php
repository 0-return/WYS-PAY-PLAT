<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositOrder extends Model
{
    //
    protected $fillable = [
        'store_id',
        'store_name',
        'config_id',
        'user_id',
        'seller_id',
        'merchant_id',
        'merchant_name',
        'out_trade_no',//平台交易系统单号
        'trade_no',//支付宝或者微信交易单号
        'out_order_no',//商户的授权资金订单号
        'out_request_no',//商户本次资金操作的请求流水号
        'operation_id',//支付宝或者微信资金操作流水号
        'auth_no',
        'amount',
        'pay_amount',//用户需要支付总金额
        'refund_amount',//退款金额
        'pay_status',//系统状态
        'pay_status_desc',
        'deposit_status',
        'deposit_status_desc',
        'deposit_time',//冻结成功时间
        'refund_time',//退款时间
        'payer_user_id',
        'payer_logon_id',
        'rate',//商户交易时的费率
        'fee_amount',
        'device_id',
        "remark",
        'ways_type',
        'ways_source',
        'ways_source_desc',
        'ways_company',//通道方
    ];
}
