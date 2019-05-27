<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StuGrade extends Model
{
    //
    protected $fillable = [
        'store_id',
        'stu_grades_name',
        'stu_grades_desc',
        'merchant_id',
        'stu_grades_no'
    ];
}
