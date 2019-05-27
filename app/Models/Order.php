<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    protected $fillable = [
        'config_id',
        'out_trade_no',
        'trade_no',
        'qwx_no',
        'other_no',
        'store_id',
        'out_store_id',
        'merchant_id',
        'store_name',
        'merchant_name',
        'is_settlement_user',
        'user_id',
        'payment_method',//支付方式
        'ways_type',
        'ways_type_desc',
        'ways_source',
        'ways_source_desc',
        'company',//通道方
        'total_amount',
        'shop_price',
        'receipt_amount',//商家实际收到的款项
        'pay_amount',//用户需要支付总金额
        'buyer_pay_amount',//用户实际付款支付金额
        'status',
        'pay_status',//系统状态
        'pay_status_desc',
        'rate',//商户交易时的费率
        'fee_amount',
        'cost_rate',
        'buyer_id',
        'buyer_logon_id',
        "remark",
        'coupon_type',//优惠类型
        'coupon_amount',//优惠金额
        'refund_amount',//退款金额
        'refund_no',//退款单号
        'device_id',
        'notify_url',//异步通知地址
        'pay_time',//支付时间
        'mdiscount_amount',//商家优惠金额
        'discount_amount',//第三方支付公司平台优惠金额
    ];
}
