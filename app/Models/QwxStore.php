<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QwxStore extends Model
{

    protected $table = 'qwx_store';

    protected $fillable = [
        'store_id',
        'store_name',
        'merchant_id',
        'merchant_name',
        'device_id',
        'secret_key',
        'client_id',
    ];
}
