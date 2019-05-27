<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoticeNew extends Model
{
    protected $table = 'notice_news';

    protected $fillable = [
        'title',
        'desc',
        'type',
        'type_desc',
        'redirect_url',
        'icon_url',
        'user_id',
    ];
}
