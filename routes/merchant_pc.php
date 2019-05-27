<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::group(['namespace' => 'MerchantPc','prefix' => 'merchantpc'], function () {
    Route::get('home', 'ViewController@home');
    Route::get('login', 'ViewController@login');
    Route::get('forget', 'ViewController@forget');
    Route::get('index', 'ViewController@index');
    Route::get('schoollist', 'ViewController@schoollist');
    Route::get('addschool', 'ViewController@addschool');
    Route::get('editschool', 'ViewController@editschool');
    Route::get('gradelist', 'ViewController@gradelist');
    Route::get('addgrade', 'ViewController@addgrade');
    Route::get('editgrade', 'ViewController@editgrade');
    Route::get('classlist', 'ViewController@classlist');
    Route::get('addclass', 'ViewController@addclass');
    Route::get('studentlist', 'ViewController@studentlist');
    Route::get('editclass', 'ViewController@editclass');
    Route::get('examineschool', 'ViewController@examineschool');
    Route::get('addstudent', 'ViewController@addstudent');
    Route::get('editstudent', 'ViewController@editstudent');
    Route::get('teacherlist', 'ViewController@teacherlist');
    Route::get('addteacher', 'ViewController@addteacher');
    Route::get('paymanagelist', 'ViewController@paymanagelist');
    Route::get('addpaytemplate', 'ViewController@addpaytemplate');
    Route::get('seetemplate', 'ViewController@seetemplate');
    Route::get('edittemplate', 'ViewController@edittemplate');
    Route::get('examinetemplate', 'ViewController@examinetemplate');
    Route::get('paymentitem', 'ViewController@paymentitem');
    Route::get('paymentlist', 'ViewController@paymentlist');
    Route::get('examinepayment', 'ViewController@examinepayment');
    Route::get('seepayment', 'ViewController@seepayment');
    Route::get('payrecord', 'ViewController@payrecord');
    Route::get('paydetail', 'ViewController@paydetail');
    Route::get('paycount', 'ViewController@paycount');
    Route::get('seepayrecord', 'ViewController@seepayrecord');
    Route::get('editpayment', 'ViewController@editpayment');
    Route::get('paystudent', 'ViewController@paystudent');
    Route::get('assignteacher', 'ViewController@assignteacher');
    Route::get('assigntercalss', 'ViewController@assigntercalss');
    Route::get('facepay', 'ViewController@facepay');
    Route::get('minoritem', 'ViewController@minoritem');
    Route::get('exportstudata', 'ViewController@exportstudata');
    Route::get('importbill', 'ViewController@importbill');
    Route::get('alipayauth', 'ViewController@alipayauth');
    Route::get('branchschool', 'ViewController@branchschool');
    Route::get('addbranchsch', 'ViewController@addbranchsch');
});
