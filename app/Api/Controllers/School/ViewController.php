<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/6/26
 * Time: 下午5:34
 */

namespace App\Api\School;


use App\Api\Controllers\BaseController;

class ViewController extends BaseController
{

    public function static_code()
    {
        return view('school.static_code');
    }


}