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
//无需token
    $api->group(['namespace' => 'App\Api\Controllers\Merchant', 'prefix' => 'merchant'], function ($api) {
        $api->any('login', 'LoginController@login');
        $api->any('weixinapp_login', 'LoginController@weixinapp_login');
        $api->any('register', 'LoginController@register');
        $api->any('edit_password', 'LoginController@edit_password');

        //静态二维码请求
        $api->any('qr_auth_pay', 'PayBaseController@qr_auth_pay');


        $api->any('school_pay', 'PayBaseController@school_pay');


    });


    //无需token
    $api->group(['namespace' => 'App\Api\Controllers\AlipayOpen', 'prefix' => 'alipayopen'], function ($api) {
        $api->any('callback', 'OauthController@callback');
    });


//需要token
    $api->group(['namespace' => 'App\Api\Controllers\Merchant', 'prefix' => 'merchant', 'middleware' => 'merchant.api'], function ($api) {

        $api->any('login_list', 'LoginController@login_list');
        $api->any('login_del', 'LoginController@login_del');


        $api->any('index', 'IndexController@index');

        //
        $api->any('get_data', 'IndexController@get_data');


        $api->any('my', 'MyController@my');
        $api->any('add_email', 'MyController@add_email');
        $api->any('add_weixin', 'MyController@add_weixin');


        $api->any('scan', 'IndexController@scan');
        $api->any('discount', 'IndexController@discount');
        $api->any('notice_news', 'InfoController@notice_news');
        $api->any('info_list', 'InfoController@info_list');
        $api->any('fuwu', 'InfoController@fuwu');
        $api->any('order_count_print', 'InfoController@order_count_print');


    });

//需要token 门店相关
    $api->group(['namespace' => 'App\Api\Controllers\Merchant', 'prefix' => 'merchant', 'middleware' => 'merchant.api'], function ($api) {
        $api->any('add_store', 'StoreController@add_store');
        $api->any('store', 'StoreController@store');
        $api->any('store_type', 'StoreController@store_type');
        $api->any('store_category', 'StoreController@store_category');
        $api->any('alipay_auth', 'StoreController@alipay_auth');
        $api->any('store_lists', 'StoreController@store_lists');
        $api->any('merchant_lists', 'StoreController@merchant_lists');
        $api->any('add_merchant', 'StoreController@add_merchant');
        $api->any('add_merchant_qr', 'StoreController@add_merchant_qr');
        $api->any('add_wx_merchant_qr', 'StoreController@add_wx_merchant_qr');


        $api->any('add_sub_store', 'StoreController@add_sub_store');
        $api->any('del_merchant', 'StoreController@del_merchant');
        $api->any('up_merchant', 'StoreController@up_merchant');
        $api->any('up_sub_store', 'StoreController@up_sub_store');
        $api->any('merchant_info', 'StoreController@merchant_info');
        $api->any('del_store', 'StoreController@del_store');
        $api->any('add_store_short_name', 'StoreController@add_store_short_name');

        $api->any('check_store', 'StoreController@check_store');

        $api->any('order', 'OrderController@order');
        $api->any('order_info', 'OrderController@order_info');
        $api->any('refund', 'OrderController@refund');
        $api->any('order_count', 'OrderController@order_count');
        $api->any('data_count', 'OrderController@data_count');
        $api->any('order_data', 'OrderController@order_data');
        $api->any('order_foreach', 'OrderController@order_foreach');
        $api->any('hb_order_foreach', 'OrderController@hb_order_foreach');
        $api->any('weixinapp_index_count', 'OrderController@weixinapp_index_count');


        $api->any('pay_ways_all', 'StoreController@pay_ways_all');
        $api->any('store_all_pay_way_lists', 'StoreController@store_all_pay_way_lists');
        $api->any('company_pay_ways_info', 'StoreController@company_pay_ways_info');
        $api->any('open_company_pay_ways', 'StoreController@open_company_pay_ways');



        $api->any('settle_mode_type', 'StoreController@settle_mode_type');
        $api->any('open_pay_ways', 'StoreController@open_pay_ways');
        $api->any('store_pay_ways', 'StoreController@store_pay_ways');
        $api->any('get_wx_notify', 'StoreController@get_wx_notify');
        $api->any('check_wx_notify', 'StoreController@check_wx_notify');
        $api->any('get_wx_notify_del', 'StoreController@get_wx_notify_del');


        //支付收款
        $api->any('scan_pay', 'PayBaseController@scan_pay');
        $api->any('qr_pay', 'PayBaseController@qr_pay');

        //分期
        $api->any('fq/fq_pay', 'AliFqPayController@fq_pay');
        $api->any('fq/ways_source', 'AliFqPayController@ways_source');
        $api->any('fq/hb_fq_num', 'AliFqPayController@hb_fq_num');
        $api->any('fq/order', 'AlipayFqOrderController@order');
        $api->any('fq/order_info', 'AlipayFqOrderController@order_info');
        $api->any('fq/hbrate', 'AliFqPayController@hbrate');
        $api->any('fq/refund', 'AlipayFqOrderController@refund');


        //设置
        $api->any('set_password', 'SetController@set_password');
        $api->any('edit_login_phone', 'SetController@edit_login_phone');
        $api->any('pay_ways_sort', 'SetController@pay_ways_sort');
        $api->any('pay_ways_sort_edit', 'SetController@pay_ways_sort_edit');
        $api->any('pay_ways_open', 'SetController@pay_ways_open');
        $api->any('bind_store_qr', 'SetController@bind_store_qr');


        $api->any('add_pay_password', 'SetController@add_pay_password');
        $api->any('edit_pay_password', 'SetController@edit_pay_password');
        $api->any('forget_pay_password', 'SetController@forget_pay_password');
        $api->any('check_pay_password', 'SetController@check_pay_password');
        $api->any('is_pay_password', 'SetController@is_pay_password');

        $api->any('me', 'MyController@me');
        $api->any('edit_merchant', 'MyController@edit_merchant');


    });


    //需要token 银行卡相关
    $api->group(['namespace' => 'App\Api\Controllers\Merchant', 'prefix' => 'merchant', 'middleware' => 'merchant.api'], function ($api) {
        $api->any('sub_bank', 'BankController@sub_bank');

    });


});