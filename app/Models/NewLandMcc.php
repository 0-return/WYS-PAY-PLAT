<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewLandMcc extends Model
{
    protected $table = 'new_land_mcc';

    protected $fillable = [
        'mcc_cd',
        'mcc_nm',
        'sup_mcc_cd',
        'sup_mcc_nm',
        'mcc_typ',
        'mcc_typ_nm',
    ];

}
