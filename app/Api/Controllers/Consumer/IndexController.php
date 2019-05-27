<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/6/19
 * Time: 下午3:16
 */

namespace App\Api\Controllers\Consumer;


use App\Api\Controllers\BaseController;
use Illuminate\Http\Request;

class IndexController extends BaseController
{

    public function index(Request $request)
    {

        $merchant = $this->parseToken($request->get('token'));
        dd($merchant);

    }

}