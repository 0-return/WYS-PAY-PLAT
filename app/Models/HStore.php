<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HStore extends Model
{
    protected $table = 'h_stores';

    protected $fillable = [
        'store_id',
        'config_id',
        'h_mid',
        'h_status',
        'h_status_desc',
    ];

}
