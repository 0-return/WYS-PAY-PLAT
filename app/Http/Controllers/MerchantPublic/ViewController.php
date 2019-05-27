<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/6/9
 * Time: 下午4:03
 */

namespace App\Http\Controllers\MerchantPublic;


use App\Http\Controllers\Controller;

class ViewController extends Controller
{
    public function login()
    {
        return view('merchantpublic.login');
    }
    public function forget()
    {
        return view('merchantpublic.forget');
    }
    public function index()
    {
        return view('merchantpublic.index');
    }
    public function store()
    {
        return view('merchantpublic.store');
    }
    public function seestore()
    {
        return view('merchantpublic.seestore');
    }
    public function cashier()
    {
        return view('merchantpublic.cashier');
    }
    public function addcashier()
    {
        return view('merchantpublic.addcashier');
    }
    public function editcashier()
    {
        return view('merchantpublic.editcashier');
    }
    public function waterlist()
    {
        return view('merchantpublic.waterlist');
    }
    public function flowerlist()
    {
        return view('merchantpublic.flowerlist');
    }
    public function home()
    {
        return view('merchantpublic.home');
    }
    public function merchantnumber()
    {
        return view('merchantpublic.merchantnumber');
    }
    public function withdrawrecord()
    {
        return view('merchantpublic.withdrawrecord');
    }
    
}