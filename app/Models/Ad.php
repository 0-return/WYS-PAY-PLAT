<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{

    protected $fillable = [
        'title',
        'ad_p_id',
        'model_type',
        'created_id',
        'ad_p_desc',
        'user_ids',
        'store_key_ids',
        's_time',
        'e_time',
        'imgs',
        'copy_content',
    ];

}
