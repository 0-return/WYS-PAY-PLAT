<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {

    //user 端无需token
    $api->group(['namespace' => 'App\Api\Controllers\Deposit', 'prefix' => 'deposit'], function ($api) {


    });

//商户端需要token
    $api->group(['namespace' => 'App\Api\Controllers\Deposit', 'prefix' => 'deposit', 'middleware' => 'merchant.api'], function ($api) {
        //扫码授权
        $api->any('micropay', 'DepositController@micropay');
        $api->any('fund_order_query', 'DepositController@fund_order_query');
        $api->any('fund_cancel', 'DepositController@fund_cancel');
        $api->any('fund_pay', 'DepositController@fund_pay');
        $api->any('pay_order_query', 'DepositController@pay_order_query');
        $api->any('refund', 'DepositController@refund');
        $api->any('print_tpl', 'DepositController@print_tpl');
    });


    //公共端
    $api->group(['namespace' => 'App\Api\Controllers\Deposit', 'prefix' => 'deposit', 'middleware' => 'public.api'], function ($api) {
        $api->any('pay_order_list', 'DepositController@pay_order_list');
        $api->any('pay_order_info', 'DepositController@pay_order_info');
        $api->any('pay_order_count', 'DepositController@pay_order_count');

    });


});