<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserMonthOrder extends Model
{
    //
    protected $fillable = [
        'user_id',
        'month',
        'type',
        'source_type',
        'total_amount',
        'order_sum'
    ];

}
