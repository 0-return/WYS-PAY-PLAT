<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StuStudent extends Model
{
    //

    protected $table = 'stu_students';

    protected $fillable = [
        'store_id',
        'stu_grades_no',
        'stu_class_no',
        'student_no',
        'student_name',
        'student_identify',
        'student_user_mobile',
        'student_user_name',
        'student_user_relation',
        'status',
        'status_desc'
    ];
}
