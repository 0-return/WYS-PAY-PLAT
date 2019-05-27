<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantFuwu extends Model
{

    protected $fillable = [
        'store_id',
        'merchant_id',
        'title',
        'desc',
        'amount',
        'out_trade_no',
    ];
}
