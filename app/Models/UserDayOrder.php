<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserDayOrder extends Model
{
    //
    protected $fillable = [
        'user_id',
        'day',
        'type',
        'source_type',
        'total_amount',
        'order_sum'
    ];
}
