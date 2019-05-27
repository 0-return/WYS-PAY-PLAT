<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/6/9
 * Time: 下午4:03
 */

namespace App\Http\Controllers\Phone;


use App\Http\Controllers\Controller;

class ViewController extends Controller
{


    public function identfirst()
    {
        return view('phone.identfirst');
    }
    public function identsecond()
    {
        return view('phone.identsecond');
    }
    public function bindbank()
    {
        return view('phone.bindbank');
    }
    public function success()
    {
        return view('phone.success');
    }
    public function bankis()
    {
        return view('phone.bankis');
    }

}