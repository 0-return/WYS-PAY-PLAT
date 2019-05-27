<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SelfShop extends Model
{
    //
    protected $table = 'self_shops';
    protected $fillable = [
        'store_id',
        'product_id',
        'title',
        'img_url',
        'description',
        'category_id',
        'product_code',
        'sku_list',
        'buy_price',
        'sell_price',
        'spec_id',
        'spec_desc',
        'customer_price',
        'stock',
        'is_cd',
        'supplier',
        'status',
        's_date',
        'e_date',
        'e_day',
        'weight',
        'qc',
        'discount'
    ];
  //隐藏
    protected $hidden = [
//        'product_code',
//        'buy_price',
//        'sell_price',
//        'spec_id',
//        'spec_desc',
//        'customer_price',
//        'stock',
//        'is_cd',
//        'supplier',
//        'status',
//        's_date',
//        'e_date',
//        'e_day',
//        'weight',
//        'qc',
//        'discount'
    ];
}
