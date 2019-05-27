<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemberJf extends Model
{

    protected $table="member_jfs";
    protected $fillable = [
        'store_id',
        'mb_id',
        'jf',
        'jf_desc',
        'type',
        'type_desc',
    ];
}
