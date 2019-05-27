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


$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {

    //自助收银售卖机
    $api->group(['namespace' => 'App\Api\Controllers\Self', 'prefix' => 'self'], function ($api) {
        $api->any('get_product', 'ProductController@get_product');
        $api->any('get_category', 'ProductController@get_category');
        $api->any('get_product_list', 'ProductController@get_product_list');
        $api->any('get_products', 'ProductController@get_products');

        $api->any('member', 'SelectController@member');
        $api->any('check_coupon', 'SelectController@check_coupon');


        $api->any('scan_pay', 'IndexController@scan_pay');
        $api->any('face_pay', 'IndexController@face_pay');
        $api->any('wxface_pay', 'IndexController@wxface_pay');



        $api->any('order_query', 'IndexController@order_query');
        $api->any('initialization', 'IndexController@initialization');
        $api->any('start', 'IndexController@start');



        $api->any('smilepay_initialize', 'SelfController@smilepay_initialize');
        $api->any('wxfacepay_initialize', 'SelfController@wxfacepay_initialize');



        //
        $api->any('updateCategory', 'UpdateController@updateCategory');
        $api->any('updateProduct', 'UpdateController@updateProduct');
        $api->any('updateProductImage', 'UpdateController@updateProductImage');



    });
});