<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettlementConfig extends Model
{
    protected $table = 'settlement_configs';

    protected $fillable = [
        'config_id',
        'dx',
        's_amount',
        'e_amount',
        'sxf_amount',
        'tx_remark',
        'out_type',
    ];

}
