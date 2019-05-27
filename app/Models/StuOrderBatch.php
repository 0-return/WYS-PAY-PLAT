<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StuOrderBatch extends Model
{
    //

    protected $table = 'stu_order_batchs';

    protected $fillable = [
        'store_id',
        'merchant_id',
        'batch_name',
        'batch_amount',
        'batch_item',
        'batch_create_type',
        'stu_order_batch_no',
        'stu_grades_no',
        'stu_class_no',
        'remove_student_no',
        'stu_order_type_no',
        'gmt_end',
        'status',
        'status_desc'
    ];
}
