<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StuOrderType extends Model
{
    //

    protected $table = 'stu_order_types';

    protected $fillable = [
        'store_id',
        'merchant_id',
        'stu_order_type_no',
        'charge_name',
        'charge_desc',
        'charge_item',
        'amount',
        'status',
        'status_desc',
    ];
}
