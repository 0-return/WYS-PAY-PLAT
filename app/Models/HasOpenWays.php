<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasOpenWays extends Model
{
    protected $fillable=['store_id','type','status','status_desc','admin_status','admin_status_desc'];
}
