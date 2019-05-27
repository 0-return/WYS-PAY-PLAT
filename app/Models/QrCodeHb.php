<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrCodeHb extends Model
{

    protected $table = 'qr_code_hbs';
    protected $fillable = [
        'code_name',
        'user_id',
        'ali_code_url',
        'wx_code_url',
        'ali_url',
        'wx_url',
        'hb_url',
    ];
}
