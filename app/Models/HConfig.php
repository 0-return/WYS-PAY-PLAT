<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HConfig extends Model
{
    protected $table = 'h_configs';

    protected $fillable = [
        'config_id',
        'saleId',
        'md_key',
        'orgNo',
        'ali_pid',
        'wx_appid',
        'wx_secret',
    ];

}
