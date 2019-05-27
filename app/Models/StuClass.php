<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StuClass extends Model
{
    //

    protected $table='stu_class';

    protected $fillable = [
        'store_id',
        'merchant_id',
        'stu_class_name',
        'stu_class_desc',
        'stu_grades_no',
        'stu_class_no',
    ];
}
