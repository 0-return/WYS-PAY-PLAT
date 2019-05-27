<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceOem extends Model
{

    protected $table = 'device_oems';
    protected $fillable = [
        'user_id',
        'device_id',
        'device_type',
        'Request',
        'ScanPay',
        'QrPay',
        'QrAuthPay',
        'PayWays',
        'OrderQuery',
        'Order',
        'OrderList',
        'Refund',
    ];
}
