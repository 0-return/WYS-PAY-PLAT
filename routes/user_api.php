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
    $api->group(['namespace' => 'App\Api\Controllers\User', 'prefix' => 'user'], function ($api) {
        $api->any('login', 'LoginController@login');
        $api->any('register', 'LoginController@register');
        $api->any('edit_password', 'LoginController@edit_password');

        $api->any('sub_code_url', 'SelectController@sub_code_url');
        $api->any('s_code_url', 'SelectController@s_code_url');


    });

//需要token
    $api->group(['namespace' => 'App\Api\Controllers\User', 'prefix' => 'user', 'middleware' => 'user.api'], function ($api) {
        $api->any('index', 'LoginController@index');

        $api->any('ranking', 'SelectController@ranking');
        $api->any('dk_select', 'SelectController@dk_select');


        $api->any('get_sub_users', 'UserController@get_sub_users');
        $api->any('add_sub_user', 'UserController@add_sub_user');
        $api->any('del_sub_user', 'UserController@del_sub_user');
        $api->any('user_info', 'UserController@user_info');
        $api->any('my_info', 'UserController@my_info');
        $api->any('up_user', 'UserController@up_user');
        $api->any('set_password', 'UserController@set_password');
        $api->any('edit_login_phone', 'UserController@edit_login_phone');

        $api->any('add_pay_password', 'UserController@add_pay_password');
        $api->any('edit_pay_password', 'UserController@edit_pay_password');
        $api->any('forget_pay_password', 'UserController@forget_pay_password');
        $api->any('check_pay_password', 'UserController@check_pay_password');
        $api->any('is_pay_password', 'UserController@is_pay_password');


        $api->any('get_my_data', 'UserController@get_my_data');


        $api->any('user_ways_all', 'UserController@user_ways_all');

        $api->any('user_ways_default', 'UserController@user_ways_default');

        $api->any('user_ways_info', 'UserController@user_ways_info');
        $api->any('edit_user_rate', 'UserController@edit_user_rate');

        $api->any('edit_user_un_rate', 'UserController@edit_user_un_rate');
        $api->any('edit_user_unqr_rate', 'UserController@edit_user_unqr_rate');


        $api->any('edit_user_store_all_rate', 'UserController@edit_user_store_all_rate');

        $api->any('edit_user_un_store_all_rate', 'UserController@edit_user_un_store_all_rate');
        $api->any('edit_user_unqr_store_all_rate', 'UserController@edit_user_unqr_store_all_rate');


        $api->any('alipay_isv_config', 'ConfigController@alipay_isv_config');
        $api->any('weixin_config', 'ConfigController@weixin_config');
        $api->any('jd_config', 'ConfigController@jd_config');
        $api->any('new_land_config', 'ConfigController@new_land_config');
        $api->any('h_config', 'ConfigController@h_config');
        $api->any('mqtt_config', 'ConfigController@mqtt_config');


        $api->any('pay_ways_sort', 'SetController@pay_ways_sort');
        $api->any('pay_ways_sort_edit', 'SetController@pay_ways_sort_edit');
        $api->any('user_store_set_status', 'SetController@user_store_set_status');
        $api->any('pay_ways_sort_start', 'SetController@pay_ways_sort_start');


        //门店列表
        $api->any('store_lists', 'StoreController@store_lists');
        $api->any('store_pc_lists', 'StoreController@store_pc_lists');
        $api->any('store_all_lists', 'StoreController@store_all_lists');


        $api->any('store', 'StoreController@store');
        $api->any('up_store', 'StoreController@up_store');
        $api->any('pay_ways_all', 'StoreController@pay_ways_all');
        $api->any('store_all_pay_way_lists', 'StoreController@store_all_pay_way_lists');
        $api->any('company_pay_ways_info', 'StoreController@company_pay_ways_info');
        $api->any('open_company_pay_ways', 'StoreController@open_company_pay_ways');




        $api->any('pay_ways_info', 'StoreController@pay_ways_info');

        $api->any('edit_store_rate', 'StoreController@edit_store_rate');
        $api->any('edit_store_un_rate', 'StoreController@edit_store_un_rate');
        $api->any('edit_store_unqr_rate', 'StoreController@edit_store_unqr_rate');

        $api->any('del_store', 'StoreController@del_store');
        $api->any('col_store', 'StoreController@col_store');
        $api->any('ope_store', 'StoreController@ope_store');
        $api->any('clear_store', 'StoreController@clear_store');
        $api->any('rec_store', 'StoreController@rec_store');
        $api->any('update_user', 'StoreController@update_user');


        $api->any('open_ways_type', 'StoreController@open_ways_type');
        $api->any('add_sub_store', 'StoreController@add_sub_store');
        $api->any('check_store', 'StoreController@check_store');
        $api->any('alipay_auth', 'StoreController@alipay_auth');


        $api->any('store_pay_qr', 'StoreController@store_pay_qr');


        $api->any('notice_news', 'InfoController@notice_news');
        $api->any('add_notice_news', 'InfoController@add_notice_news');
        $api->any('del_notice_news', 'InfoController@del_notice_news');
        $api->any('toutiao', 'InfoController@toutiao');
        $api->any('toutiao_top', 'InfoController@toutiao_top');
        $api->any('notice_news_type', 'InfoController@notice_news_type');


        $api->any('banners', 'BannerController@banners');
        $api->any('add_banners', 'BannerController@add_banners');
        $api->any('del_banners', 'BannerController@del_banners');
        $api->any('banner_type', 'BannerController@banner_type');


        $api->any('order', 'OrderController@order');
        $api->any('order_info', 'OrderController@order_info');

        $api->any('order_count', 'OrderController@order_count');


        //花呗分期
        $api->any('fq/order', 'AlipayFqOrderController@order');
        $api->any('fq/order_info', 'AlipayFqOrderController@order_info');


        //空码管理
        $api->any('QrLists', 'QrController@QrLists');
        $api->any('QrListinfos', 'QrController@QrListinfos');
        $api->any('DownloadQr', 'QrController@DownloadQr');
        $api->any('createQr', 'QrController@createQr');
        $api->any('bindQr', 'QrController@bindQr');
        $api->any('unbindQr', 'QrController@unbindQr');

        $api->any('qr_code_hb_list', 'QrController@qr_code_hb_list');
        $api->any('qr_code_hb_add', 'QrController@qr_code_hb_add');
        $api->any('qr_code_hb_del', 'QrController@qr_code_hb_del');



    });


});