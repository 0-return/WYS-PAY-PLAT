<?php

namespace App\Http\Controllers\Deposit;

use App\Http\Controllers\Controller;

/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/7/4
 * Time: 上午11:33
 */
class ViewController extends Controller
{

    public function login()
    {

        return view('deposit.login');
    }
    public function forget()
    {

        return view('deposit.forget');
    }
    public function index()
    {

        return view('deposit.index');
    }
    public function home()
    {

        return view('deposit.home');
    }
    public function cashflow()
    {

        return view('deposit.cashflow');
    }
    public function depositwater()
    {
        return view('deposit.depositwater');
    }

}