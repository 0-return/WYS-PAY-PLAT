<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreBank extends Model
{
    //
    protected $fillable = [
        'store_id',
        'store_bank_no',
        'store_bank_name',
        'store_bank_phone',
        'store_bank_type',
        'bank_name',
        'bank_no',
        'sub_bank_name',
        'bank_province_code',
        'bank_city_code',
        'bank_area_code',
        'bank_sfz_time',
        'bank_sfz_stime',
        'bank_sfz_no'
    ];
}
