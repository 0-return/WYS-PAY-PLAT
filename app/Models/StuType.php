<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StuType extends Model
{
    //

    protected $table = 'stu_types';

    protected $fillable = [
        'school_type',
        'school_type_desc',
    ];
}
