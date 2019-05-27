<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreMonthOrder extends Model
{
    //
    protected $fillable=['store_id','month','type','source_type','total_amount','order_sum'];

}
