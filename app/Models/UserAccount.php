<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAccount extends Model
{
    //
    protected $fillable = [
        'user_id',
        'alipay_account',
        'alipay_name',
    ];

}
