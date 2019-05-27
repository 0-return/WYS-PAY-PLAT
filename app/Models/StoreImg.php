<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreImg extends Model
{
    //
    protected $fillable = [
        'store_id',
        'head_sfz_img_a',
        'head_sfz_img_b',
        'store_license_img',
        'store_industrylicense_img',
        'store_logo_img',
        'store_img_a',
        'store_img_b',
        'store_img_c',
        'bank_img_a',
        'bank_img_b',
        'store_other_img_a',
        'store_other_img_b',
        'store_other_img_c',
        'head_sc_img',
        'head_store_img',
        'bank_sfz_img_a',
        'bank_sfz_img_b',
        'bank_sc_img',
        'store_auth_bank_img',
    ];
}
