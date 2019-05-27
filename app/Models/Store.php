<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    //
    protected $fillable = [
        'pid',
        'blacklist',
        'config_id',
        'user_id',
        'merchant_id',
        'user_pid',
        'user_pid_name',
        'store_id',
        'store_name',
        'store_type',
        'store_type_name',
        'store_email',
        'store_short_name',
        'people',//负责人
        'people_phone',
        'province_code',
        'city_code',
        'area_code',
        'province_name',
        'city_name',
        'area_name',
        'store_address',
        'head_name',//法人
        'head_sfz_no',
        'head_sfz_time',
        'head_sfz_stime',
        'category_id',
        'category_name',
        'store_license_no',
        'store_license_time',
        'store_license_stime',
        'is_close',
        'is_delete',
        'is_admin_close',
        'disable_pay_channels',
        'is_admin_close_desc',
        'status',
        'weixin_name',
        'weixin_no',
        'status_desc',
        'wx_AppId',
        'wx_Secret',
        'wx_SubscribeAppId',
        'admin_status',
        'admin_status_desc',
        'store_pay_ways_open',
        'pay_ways_type',//0-自己的通道，1-上级的通道
        's_time',
        'e_time'
    ];
}
