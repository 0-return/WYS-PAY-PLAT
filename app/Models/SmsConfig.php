<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SmsConfig extends Model
{
    //

    protected $table="sms_configs";
    protected $fillable = [
        'config_id',
        'app_key',
        'app_secret',
        'SignName',
        'type',
        'type_desc',
        'TemplateCode',
    ];
}
