<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StuOrderItem extends Model
{
    //

    protected $table = 'stu_order_items';

    protected $fillable = [
        'out_trade_no',
        'item_serial_number',
        'item_name',
        'item_price',
        'item_mandatory',
        'item_number',
        'pay_status',
        'pay_status_desc',
    ];
}
