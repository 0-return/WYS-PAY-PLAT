<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MqttConfig extends Model
{
    //

    protected $table = 'mqtt_configs';

    protected $fillable = [
        'config_id',
        'access_key_id',
        'access_key_secret',
        'server',
        'port',
        'instance_id',
        'group_id',
        'topic',
    ];
}
