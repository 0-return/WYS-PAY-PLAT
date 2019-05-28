<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Adcate extends Model
{
    protected $table = "ads_cate";
    protected $fillable = [
        'id',
        'title',
        'unique',
        'status',
        'config_id',
        'user_id',
    ];

}
