<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppConfigMsg extends Model
{

    protected $table = 'app_config_msgs';

    protected $fillable = [
        'config_id',
        'app_id',
        'app_icon',
        'app_name',
        'app_phone',
        'about_title',
        'about_body',
        'ym',
        'company',
    ];

}
