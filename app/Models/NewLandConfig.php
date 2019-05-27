<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewLandConfig extends Model
{
    protected $table = 'new_land_configs';

    protected $fillable = [
        'config_id',
        'org_no',
        'nl_key',
        'wx_appid',
        'wx_secret',

    ];

}
