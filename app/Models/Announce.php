<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announce extends Model
{
    //
    protected $table='stu_announce';

    protected $fillable = [

'store_id',
'merchant_id',
'cate_type',
'title',
'content',


    ];
}
