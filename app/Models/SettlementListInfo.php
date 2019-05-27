<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettlementListInfo extends Model
{
    protected $table = 'settlement_list_infos';

    protected $fillable = [
        'settlement_list_id',
        's_no',
        'config_id',
        'dx',
        's_time',
        'e_time',
        'source_type',
        'source_type_desc',
        'total_amount',
        'get_amount',
        'rate',
        'user_id',
        'is_true',
    ];

}
