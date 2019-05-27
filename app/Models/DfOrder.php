<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DfOrder extends Model
{
    //

    protected $table = 'df_order';
    protected $fillable = [
        'config_id',
        'order_number',
        'merchant_number',
        'order_id',
        'in_order_id',
        'deal_time',
        'amount',
        'pay_status',
        'pay_status_desc',
        'account_number',
        'customer_name',
        'issue_bank_name',
        'memo',
    ];
}
