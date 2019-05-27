<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StuMerchantClass extends Model
{
    //
public $timestamps = false;

    protected $table = 'stu_merchant_class';

    protected $fillable = [
'store_id',
'merchant_id',
'type',
'stu_grades_no',
'stu_class_no',
    ];
}


