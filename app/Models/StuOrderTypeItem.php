<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StuOrderTypeItem extends Model
{
    //

    protected $table = 'stu_order_type_items';

    protected $fillable = [
        'stu_order_type_no',
        'item_serial_number',
        'item_name',
        'item_price',
        'item_mandatory',
        'item_number',
    ];
}
