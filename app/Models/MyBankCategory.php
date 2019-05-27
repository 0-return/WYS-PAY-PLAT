<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MyBankCategory extends Model
{
    //
    protected $table = 'my_bank_categories';

    protected $fillable = [
        'category_id',
        'category_name',
        'link',
        'level',

    ];


}

