<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StorePayWay extends Model
{
    //
    protected $fillable = [
        'store_id',
        'status',
        'rate',
        'rate_a',
        'rate_b',
        'rate_b_top',
        'rate_c',
        'rate_d',
        'rate_d_top',
        'rate_e',//刷卡贷记卡
        'rate_f',
        'rate_f_top',
        'settlement_type',
        'status_desc',
        'ways_type',
        'ways_source',
        'company',
        'ways_desc',
        'pcredit',
        'credit',
        'is_close',
        'is_close_desc',
        'sort'
    ];
}
