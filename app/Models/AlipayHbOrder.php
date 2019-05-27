<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlipayHbOrder extends Model
{
    //
    protected $fillable = [
        'id',
        'config_id',
        'store_id',
        'merchant_id',
        'user_id',
        'is_settlement_user',
        'store_name',
        'merchant_name',
        'trade_no',
        'out_trade_no',
        'buyer_user',
        'buyer_alipay_id',
        'buyer_alipay_account',
        'buyer_phone',
        'shop_name',
        'shop_desc',
        'shop_imei',
        'total_amount',
        'receipt_amount',
        'refund_amount',
        'shop_price',
        'hb_fq_num',
        'hb_fq_seller_percent',
        'pay_status',
        "pay_status_desc",
        'hb_fq_sxf',
        'pay_sxf',
        'xy_rate',
        'total_amount_out',
        'out_status',
        'ways_type',
        'ways_type_desc',
        'ways_source',
        'ways_source_desc',
        'device_id',
    ];


}
