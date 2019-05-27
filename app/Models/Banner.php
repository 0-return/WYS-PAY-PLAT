<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    //
    protected $fillable = [
        'title',
        'banner_desc',
        'img_url',
        'banner_time_s',
        'banner_time_e',
        'status',
        'banner_url',
        'config_id',
        'user_id',
        'sort',
        'action_url',
        'type',
        'type_desc',
    ];
}
