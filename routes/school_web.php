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


Route::group(['namespace' => 'School', 'prefix' => 'school'], function () {
    Route::get('trade_pay', 'PageController@trade_pay');
    Route::get('get_pay_list', 'PageController@get_pay_list');
    Route::get('payeducation', 'ViewController@payeducation');
    Route::get('waitforpay', 'ViewController@waitforpay');
    
    Route::get('paysucceed', 'ViewController@paysucceed');
    Route::get('register', 'ViewController@register');
    Route::get('login', 'ViewController@login');
    Route::get('forget', 'ViewController@forget');
});
