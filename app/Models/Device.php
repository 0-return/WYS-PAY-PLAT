<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{

    protected $fillable = [
        'store_id',
        'config_id',
        'store_name',
        'merchant_id',
        'merchant_name',
        'device_type',
        'device_name',
        'device_no',
        'device_key',
        'status',
        'type',
    ];
}
