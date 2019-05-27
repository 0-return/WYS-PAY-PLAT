<?php
/**
 * Created by PhpStorm.
 * User: dmk
 * Date: 2017/1/3
 * Time: 14:51
 */

namespace App\Models;

use Zizaco\Entrust\EntrustRole;

class Role extends EntrustRole
{


    protected  $fillable=['name','display_name','description','user_id'];
}