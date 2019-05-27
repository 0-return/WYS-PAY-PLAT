<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreDayOrder extends Model
{
    //
    protected $fillable=['store_id','day','type','source_type','total_amount','order_sum'];
}
