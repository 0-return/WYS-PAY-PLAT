<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LtfStore extends Model
{
    protected $table = 'ltf_stores';

    protected $fillable = [
        'store_id',
        'config_id',
        'appId',
        'md_key',
        'merchantCode'
    ];

}
