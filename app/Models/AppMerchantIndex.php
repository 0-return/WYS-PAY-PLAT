<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppMerchantIndex extends Model
{

    protected $table='app_merchant_indexs';
    protected $fillable=['type','pid','config_id','title','icon','url','sort'];
}
