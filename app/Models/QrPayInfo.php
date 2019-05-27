<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrPayInfo extends Model
{
    //
    protected $fillable = [
        'user_id',
        'code_number',
        'code_type',
        'store_id',
        'merchant_id',
        'cno'
    ];

}
