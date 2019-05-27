<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SelfStore extends Model
{
    //
    protected $table = 'self_stores';
    protected $fillable = [
        'erp_info',
        'store_id',
    ];
}
