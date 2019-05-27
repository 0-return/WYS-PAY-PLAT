<?php
/**
 * Created by PhpStorm.
 * User: dmk
 * Date: 2017/1/3
 * Time: 15:16
 */

namespace App\Models;

use Zizaco\Entrust\EntrustPermission;

class Permission extends EntrustPermission
{


    protected $fillable = [
        'pid','name', 'display_name', 'description', 'sort'
    ];
}