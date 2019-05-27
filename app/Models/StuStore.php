<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StuStore extends Model
{
    //
    protected $fillable = [
        'store_id',
        'merchant_id',
        'user_id',
        'config_id',
        'pid',
        'school_no',
        'school_name',
        'school_sort_name',
        'school_icon',
        'school_stdcode',
        'school_type',
        'province_code',
        'province_name',
        'city_code',
        'city_name',
        'district_code',
        'district_name',
        'su_store_address',
        'status',
        'status_desc',
        'alipay_status',
        'alipay_status_desc',
    ];
}
