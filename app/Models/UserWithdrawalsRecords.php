<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWithdrawalsRecords extends Model
{
    //
    protected $fillable=[
        'config_id',
        'user_id',
        'out_trade_no',
        'user_name',
        'trade_no',
        'account',
        'name',
        'amount',
        'sxf_amount',
        'account_amount',
        'status',
        'status_desc',
        'remark'
    ];
}
