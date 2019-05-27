<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlipayHongba extends Model
{

    protected $fillable = [
        'store_id',
        'store_name',
        'create_user_id',
        'create_user_name',
        'content',
        'remark'
    ];
}
