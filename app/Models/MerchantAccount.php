<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MerchantAccount extends Model
{
    //
    protected $fillable = [
        'merchant_id',
        'alipay_account',
        'alipay_name',
    ];

}
