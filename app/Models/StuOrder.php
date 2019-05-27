<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StuOrder extends Model
{
    //

    protected $table = 'stu_orders';

    protected $fillable = [
        'out_trade_no',
        'store_id',
        'user_id',
        'school_name',
        'batch_name',
        'merchant_id',
        'stu_grades_no',
        'stu_grades_name',
        'stu_class_no',
        'stu_class_name',
        'student_no',
        'student_name',
        'stu_order_batch_no',
        'stu_order_type_no',
        'student_user_mobile',
        'student_user_name',
        'trade_no',
        'amount',
        'pay_amount',
        'receipt_amount',
        'buyer_id',
        'buyer_logon_id',
        'gmt_start',
        'gmt_end',
        'remind',
        'pay_type_source',
        'pay_type_source_desc',
        'pay_type',
        'pay_type_desc',
        'alipay_status',
        'alipay_status_desc',
        'pay_status',
        'pay_time',
        'pay_status_desc',
        'pay_alipay_status',
        'pay_alipay_status_desc',
        'order_create_type',
    ];
}
