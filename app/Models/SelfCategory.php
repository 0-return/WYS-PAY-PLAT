<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SelfCategory extends Model
{
    //
    protected $table = 'self_categorys';
    protected $fillable = [
        'pid',
        'erp_type',
        'store_id',
        'category_id',
        'category_name',
        'category_image',
        'ext',
    ];
}
