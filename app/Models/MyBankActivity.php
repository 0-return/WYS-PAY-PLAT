<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MyBankActivity extends Model
{

    protected $table = 'my_bank_activitys';
    protected $fillable = [
        'store_id',
        'activity_store_id',
        'activity_type',
        'activity_status',
        'activity_status_desc',
        'activity_order_id',
    ];
}
