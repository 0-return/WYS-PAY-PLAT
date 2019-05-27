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

Route::get('/', function () {
    return view('merchantpc.login');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');


Route::group(['namespace' => 'AlipayOpen'], function () {
    Route::get('/alipay_oqr_create', 'AlipayCreateOrderController@alipay_oqr_create')->name('alipay_oqr_create');
});


//授权后跳转成功
Route::group(['namespace' => 'Merchant', 'prefix' => 'merchant'], function () {
    //跳转支付宝app
    Route::get('/appAlipay', 'MerchantController@appAlipay')->name('appAlipay');

});


//单页面
Route::group(['namespace' => 'Page', 'prefix' => 'page'], function () {

    Route::get('/success', 'PageController@success');
    Route::get('/pay_errors', 'PageController@pay_errors');
    Route::get('/pay_success', 'PageController@pay_success');

  //小程序添加员工
    Route::get('/add_merchant', 'PageController@add_merchant');



});


//二维
Route::group(['namespace' => 'Qr'], function () {
    //空码生成
    Route::get('/qr', 'QrController@qr')->name('qr');
    Route::get('/qr_code_hb', 'QrController@qr_code_hb')->name('qr_code_hb');


});
