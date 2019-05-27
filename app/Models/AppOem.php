<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppOem extends Model
{
    protected $table = 'app_oems';

    protected $fillable = [
        'config_id',
        'app_id',
        'name',
        'ym',
        'beianhao',
        'phone',
        'keyword',
        'title',
        'body',
        'merchant_app_url',
    ];

}
