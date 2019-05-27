<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserRate extends Model
{
    //

    protected $table = 'user_rates';

    protected $fillable = [
        'rate',//代理商的成本
        'rate_a',//代理商贷记卡的成本
        'rate_b',
        'rate_b_top',
        'rate_c',//代理商贷记卡的成本
        'rate_d',
        'rate_d_top',
        'settlement_type',
        'user_id',
        'store_all_rate',
        'store_all_rate_a',
        'store_all_rate_b',
        'store_all_rate_b_top',
        'store_all_rate_c',
        'store_all_rate_d',
        'store_all_rate_d_top',
        'ways_type',
        'company',
        'rate_e',//代理商贷记卡的成本
        'rate_f',
        'rate_f_top',
        'store_all_rate_e',
        'store_all_rate_f',
        'store_all_rate_f_top',
    ];
}
