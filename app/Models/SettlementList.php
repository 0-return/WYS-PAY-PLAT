<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettlementList extends Model
{
    protected $table = 'settlement_lists';

    protected $fillable = [
        'config_id',
        's_no',
        'dx',
        's_time',
        'e_time',
        'source_type',
        'source_type_desc',
        'total_amount',
        'get_amount',
        'rate',
        'is_true',
    ];

}
