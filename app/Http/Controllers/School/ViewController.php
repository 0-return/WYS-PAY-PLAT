<?php

namespace App\Http\Controllers\School;

use App\Http\Controllers\Controller;

/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/7/4
 * Time: 上午11:33
 */
class ViewController extends Controller
{
    public function payeducation()
    {

        return view('school.payeducation');
    }

    public function waitforpay()
    {

        return view('school.waitforpay');
    }


    public function paysucceed()
    {

        return view('school.paysucceed');
    }

    public function register()
    {
        //判断是不是支付宝扫码
        // if (strpos($_SERVER['HTTP_USER_AGENT'], 'AlipayClient') == false) {
        //     $message = '请使用支付宝扫码';
        //     return view('errors.page_errors', compact('message' ));
        // }
        return view('school.register');
    }
    public function login()
    {

        return view('school.login');
    }
    public function forget()
    {

        return view('school.forget');
    }


}