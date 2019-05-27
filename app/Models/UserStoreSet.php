<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStoreSet extends Model
{
    //
    protected $fillable = [
        'user_id',
        'status_check',
        'admin_status_check',
    ];

}
