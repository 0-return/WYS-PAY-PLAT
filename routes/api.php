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
    $api->group(['namespace' => 'App\Api\Controllers\Basequery', 'prefix' => 'basequery'], function ($api) {
        $api->any('city', 'CityController@city');
        $api->any('test', 'TestController@test');
        $api->any('test1', 'TestController@test1');
        $api->any('appUpdate', 'SelectController@appUpdate');
        $api->any('settle_mode_type', 'SelectController@settle_mode_type');
        $api->any('alipay_isv_info', 'SelectController@alipay_isv_info');
        $api->any('openways', 'StorePayWaysController@openways');

    });

    //无需token
    $api->group(['namespace' => 'App\Api\Controllers\AlipayOpen', 'prefix' => 'alipayopen'], function ($api) {
        $api->any('callback', 'OauthController@callback');
        $api->any('qr_pay_notify', 'NotifyController@qr_pay_notify');
        $api->any('get_user_infos', 'UserController@get_user_infos');
        $api->any('get_invoice_title', 'InvoiceController@get_invoice_title');

    });
    //网商无需token
    $api->group(['namespace' => 'App\Api\Controllers\MyBank', 'prefix' => 'mybank'], function ($api) {
        $api->any('notify', 'NotifyController@notify');
        $api->any('notifyPayResult', 'NotifyController@notifyPayResult');

        //静态码提交接口
        $api->any('qr_pay_submit', 'QrpayController@qr_pay_submit');
        $api->any('weixin/oauth', 'OauthController@oauth');
        $api->any('weixin/oauth_callback', 'OauthController@oauth_callback');
        $api->any('weixin/pay_view', 'OauthController@pay_view');


        //获取到open_id
        $api->any('weixin/oauth_openid', 'OauthController@oauth_openid');
        $api->any('weixin/oauth_callback_openid', 'OauthController@oauth_callback_openid');

        $api->any('test', 'TestController@test');
    });


    //fuiou无需token
    $api->group(['namespace' => 'App\Api\Controllers\Fuiou', 'prefix' => 'fuiou'], function ($api) {
        $api->any('pay_notify', 'NotifyController@pay_notify');
        $api->any('notifyPayResult', 'NotifyController@notifyPayResult');

        //静态码提交接口
        $api->any('qr_pay_submit', 'QrpayController@qr_pay_submit');
        $api->any('weixin/oauth', 'OauthController@oauth');
        $api->any('weixin/oauth_callback', 'OauthController@oauth_callback');
        $api->any('weixin/pay_view', 'OauthController@pay_view');


        //获取到open_id
        $api->any('weixin/oauth_openid', 'OauthController@oauth_openid');
        $api->any('weixin/oauth_callback_openid', 'OauthController@oauth_callback_openid');

        $api->any('test', 'TestController@test');
    });

//fuiou需token
    $api->group(['namespace' => 'App\Api\Controllers\Fuiou', 'prefix' => 'fuiou', 'middleware' => 'public.api'], function ($api) {
        $api->any('import_mercId', 'SelectController@import_mercId');
        $api->any('store_list', 'SelectController@store_list');
        $api->any('del_store', 'SelectController@del_store');
        $api->any('open_da', 'SelectController@open_da');
        $api->any('select_money', 'SelectController@select_money');
        $api->any('out_money', 'SelectController@out_money');


    });

    //联拓富无需token
    $api->group(['namespace' => 'App\Api\Controllers\Ltf', 'prefix' => 'ltf'], function ($api) {
        $api->any('pay_notify', 'NotifyController@pay_notify');
        //静态码提交接口
        $api->any('qr_pay_submit', 'QrpayController@qr_pay_submit');
        $api->any('weixin/oauth', 'OauthController@oauth');
        $api->any('weixin/oauth_callback', 'OauthController@oauth_callback');
        $api->any('weixin/pay_view', 'OauthController@pay_view');


        //获取到open_id
        $api->any('weixin/oauth_openid', 'OauthController@oauth_openid');
        $api->any('weixin/oauth_callback_openid', 'OauthController@oauth_callback_openid');


    });

    //传化无需token
    $api->group(['namespace' => 'App\Api\Controllers\Tfpay', 'prefix' => 'tfpay'], function ($api) {
        $api->any('store_notify', 'NotifyController@store_notify');

    });

    //需token
    $api->group(['namespace' => 'App\Api\Controllers\MyBank', 'prefix' => 'mybank', 'middleware' => 'public.api'], function ($api) {
        $api->any('payResultQuery', 'SelectController@payResultQuery');
        $api->any('storeinfo', 'SelectController@storeinfo');
        $api->any('set_weixin_path', 'SelectController@set_weixin_path');

        $api->any('import_mercId', 'SelectController@import_mercId');
        $api->any('store_list', 'SelectController@store_list');
        $api->any('del_store', 'SelectController@del_store');


    });

    //无需token
    $api->group(['namespace' => 'App\Api\Controllers\Huiyuanbao', 'prefix' => 'huiyuanbao'], function ($api) {
        $api->any('pay_notify', 'NotifyController@pay_notify');
        $api->any('store_notify', 'NotifyController@store_notify');
        $api->any('pay_action', 'QrPayController@pay_action');
        $api->any('weixin/oauth', 'OauthController@oauth');
        $api->any('weixin/oauth_callback', 'OauthController@oauth_callback');
        $api->any('weixin/pay_view', 'OauthController@pay_view');

    });
    //新大陆
    $api->group(['namespace' => 'App\Api\Controllers\Newland', 'prefix' => 'newland'], function ($api) {
        $api->any('pay_notify', 'NotifyController@pay_notify');
        $api->any('store_query', 'SelectController@store_query');
        $api->any('mcc_query', 'SelectController@mcc_query');


    });


    //无需token
    $api->group(['namespace' => 'App\Api\Controllers\Weixin', 'prefix' => 'weixin'], function ($api) {
        $api->any('oauth', 'OauthController@oauth');
        $api->any('callback', 'OauthController@callback');
        $api->any('qr_pay_notify', 'NotifyController@qr_pay_notify');
        $api->any('school_pay_notify', 'NotifyController@school_pay_notify');


        //支付静态页面
        $api->any('qr_pay_view', 'OauthController@qr_pay_view');
        $api->any('paydetails', 'OauthController@paydetails');
        $api->any('server', 'ServerController@server');


    });


    //微信开放平台
    $api->group(['namespace' => 'App\Api\Controllers\WeixinOpen', 'prefix' => 'weixinopen'], function ($api) {
        //公众号授权平台
        $api->any('openoauth', 'OpenOauthController@openoauth');
        $api->any('opencallback', 'OpenOauthController@opencallback');
        $api->any('auth_notify', 'NotifyController@auth_notify');
        $api->any('oauth', 'OauthController@oauth');
        $api->any('callback', 'OauthController@callback');
        $api->any('remind_notify/{appid}', 'NotifyController@remind_notify');


    });


    //需token
    $api->group(['namespace' => 'App\Api\Controllers\Basequery', 'prefix' => 'basequery', 'middleware' => 'public.api'], function ($api) {
        $api->any('upload', 'UploadController@upload');
        $api->any('webupload', 'UploadController@webupload');
        $api->any('contact', 'AppIndexController@contact');


        $api->any('merchant_lists', 'SelectController@merchant_lists');
        $api->any('store_type', 'SelectController@store_type');
        $api->any('store_category', 'SelectController@store_category');
        $api->any('bank', 'SelectController@bank');
        $api->any('sub_bank', 'SelectController@sub_bank');
        $api->any('order_query_b', 'SelectController@order_query_b');
        $api->any('app_oem_info', 'SelectController@app_oem_info');
        $api->any('j_push_info', 'SelectController@j_push_info');
        $api->any('sms_info', 'SelectController@sms_info');
        $api->any('sms_type', 'SelectController@sms_type');
        $api->any('update_order', 'SelectController@update_order');


        $api->any('/updateInfo', 'AppController@updateInfo')->name("updateInfo");
        $api->any('/appUpdateFile', 'AppController@appUpdateFile')->name("appUpdateFile");
        $api->any('/setApp', 'AppController@setApp')->name("setApp");
        $api->any('/setAppPost', 'AppController@setAppPost')->name("setAppPost");
    });


    //需token 角色权限
    $api->group(['namespace' => 'App\Api\Controllers\RolePermission', 'prefix' => 'role_permission', 'middleware' => 'public.api'], function ($api) {
        $api->any('role_list', 'RoleController@role_list');
        $api->any('permission_list', 'RoleController@permission_list');
        $api->any('assign_role', 'RoleController@assign_role');
        $api->any('assign_permission', 'RoleController@assign_permission');
        $api->any('add_role', 'RoleController@add_role');
        $api->any('add_permission', 'RoleController@add_permission');
        $api->any('user_role_list', 'RoleController@user_role_list');
        $api->any('role_permission_list', 'RoleController@role_permission_list');
        $api->any('del_role', 'RoleController@del_role');
        $api->any('del_permission', 'RoleController@del_permission');
    });


    // 短信接口
    $api->group(['namespace' => 'App\Api\Controllers\Sms', 'prefix' => 'Sms'], function ($api) {
        $api->any('send', 'SmsController@send');
    });


    //设备管理
    $api->group(['namespace' => 'App\Api\Controllers\Device', 'prefix' => 'device', 'middleware' => 'public.api'], function ($api) {
        $api->any('device_type', 'DeviceController@device_type');
        $api->any('add', 'DeviceController@add');
        $api->any('lists', 'DeviceController@lists');
        $api->any('up', 'DeviceController@up');
        $api->any('del', 'DeviceController@del');
        $api->any('select', 'DeviceController@select');
        $api->any('get_device', 'DeviceController@get_device');
        $api->any('v_config', 'DeviceController@v_config');
        $api->any('print_tpl', 'PrintController@print_tpl');
        $api->any('order_tpl', 'PrintController@order_tpl');


        $api->any('device_oem_lists', 'SelectController@device_oem_lists');
        $api->any('device_oem_del', 'SelectController@device_oem_del');
        $api->any('device_oem_import', 'SelectController@device_oem_import');


    });

    //设备管理
    $api->group(['namespace' => 'App\Api\Controllers\Export', 'prefix' => 'export', 'middleware' => 'public.api'], function ($api) {
        $api->any('MerchantOrderExcelDown', 'OrderExportController@MerchantOrderExcelDown');
        $api->any('UserOrderExcelDown', 'OrderExportController@UserOrderExcelDown');

    });


    //赏金管理
    $api->group(['namespace' => 'App\Api\Controllers\Wallet', 'prefix' => 'wallet', 'middleware' => 'public.api'], function ($api) {
        $api->any('source_type', 'SelectController@source_type');
        $api->any('source_query', 'SelectController@source_query');
        $api->any('source_query_info', 'SelectController@source_query_info');
        $api->any('records_query_info', 'SelectController@records_query_info');


        $api->any('account', 'SelectController@account');
        $api->any('add_account', 'SelectController@add_account');
        $api->any('out_wallet', 'SelectController@out_wallet');
        $api->any('out_wallet_list', 'SelectController@out_wallet_list');
        $api->any('settlement', 'SelectController@settlement');


        $api->any('settlement_configs', 'setController@settlement_configs');
        $api->any('settlement_lists', 'SelectController@settlement_lists');
        $api->any('settlement_list_infos', 'SelectController@settlement_list_infos');
        $api->any('settlement_list_del', 'SelectController@settlement_list_del');
        $api->any('settlement_list_true', 'SelectController@settlement_list_true');


    });

    //广告分类管理
    $api->group(['namespace' => 'App\Api\Controllers\Adcate', 'prefix' => 'adcate'], function ($api) {
        $api->any('adcate_lists', 'AdcateController@adcate_lists');
        $api->any('adcate_add', 'AdcateController@adcate_add');
        $api->any('adcate_edit', 'AdcateController@adcate_edit');
        $api->any('adcate_del', 'AdcateController@adcate_del');
        $api->any('adcate_info', 'AdcateController@adcate_info');
    });

    //广告管理
    $api->group(['namespace' => 'App\Api\Controllers\Ad', 'prefix' => 'ad'], function ($api) {
        $api->any('ad_lists', 'AdController@ad_lists');
        $api->any('ad_create', 'AdController@ad_create');
        $api->any('ad_up', 'AdController@ad_up');
        $api->any('ad_del', 'AdController@ad_del');
        $api->any('ad_p_id', 'AdController@ad_p_id');
        $api->any('ad_info', 'AdController@ad_info');
    });



    //活动
    $api->group(['namespace' => 'App\Api\Controllers\Huodong', 'prefix' => 'huodong', 'middleware' => 'public.api'], function ($api) {
        $api->any('get_list', 'AlipayHongbao@get_list');
        $api->any('add', 'AlipayHongbao@add');
        $api->any('del', 'AlipayHongbao@del');


        //拉新
        $api->any('hd_list', 'SelectController@hd_list');
        $api->any('old_hd_list', 'SelectController@old_hd_list');
        $api->any('get_info', 'SelectController@get_info');


        $api->any('jdbt', 'JdbtController@jdbt');


    });


    //微收银 支付
    $api->group(['namespace' => 'App\Api\Controllers\Qwx', 'prefix' => 'qwx'], function ($api) {
        $api->any('scan_pay', 'IndexController@scan_pay');
        $api->any('order_query', 'IndexController@order_query');
        $api->any('refund', 'IndexController@refund');
        $api->any('refund_query', 'IndexController@refund_query');
    });


    //微收银 token
    $api->group(['namespace' => 'App\Api\Controllers\Qwx', 'prefix' => 'qwx', 'middleware' => 'public.api'], function ($api) {
        $api->any('add_store', 'StoreController@add_store');
        $api->any('add_code', 'StoreController@add_code');
        $api->any('del_code', 'StoreController@del_code');
        $api->any('code_list', 'StoreController@code_list');
    });


    //京东支付
    $api->group(['namespace' => 'App\Api\Controllers\Jd', 'prefix' => 'jd'], function ($api) {
        $api->any('notify_url', 'NotifyController@notify_url');
        $api->any('refund_url', 'NotifyController@refund_url');
        $api->any('weixin/oauth', 'OauthController@oauth');
        $api->any('weixin/oauth_callback', 'OauthController@oauth_callback');
        $api->any('weixin/pay_view', 'OauthController@pay_view');

    });

    //新大陆支付
    $api->group(['namespace' => 'App\Api\Controllers\Newland', 'prefix' => 'newland'], function ($api) {
        $api->any('refund_url', 'NotifyController@refund_url');
        $api->any('pay_notify', 'NotifyController@pay_notify');
        $api->any('store_query', 'SelectController@store_query');
        $api->any('mcc_query', 'SelectController@mcc_query');
        $api->any('weixin/oauth', 'OauthController@oauth');
        $api->any('weixin/oauth_callback', 'OauthController@oauth_callback');
        $api->any('weixin/pay_view', 'OauthController@pay_view');
        $api->any('pay_action', 'QrPayController@pay_action');


    });

    //新大陆支付 token
    $api->group(['namespace' => 'App\Api\Controllers\Newland', 'prefix' => 'newland', 'middleware' => 'public.api'], function ($api) {
        $api->any('open_da', 'SelectController@open_da');
        $api->any('get_da_info', 'SelectController@get_da_info');
        $api->any('da_out_select', 'SelectController@da_out_select');
        $api->any('get_da', 'SelectController@get_da');
        $api->any('set_da_rate', 'SelectController@set_da_rate');
        $api->any('import_mercId', 'SelectController@import_mercId');
        $api->any('store_list', 'SelectController@store_list');
        $api->any('del_store', 'SelectController@del_store');
    });


    //新大陆支付 token
    $api->group(['namespace' => 'App\Api\Controllers\Newland', 'prefix' => 'newland', 'middleware' => 'public.api'], function ($api) {
        $api->any('PayInOrder', 'QrPayController@PayInOrder');


    });


    //系统报错上报接口
    $api->group(['namespace' => 'App\Api\Controllers\Errors', 'prefix' => 'errors'], function ($api) {
        $api->any('self_errors', 'SelfErrorsController@self_errors');
    });


    //设备接口
    $api->group(['namespace' => 'App\Api\Controllers\DevicePay', 'prefix' => 'devicepay'], function ($api) {

        $api->any('scan_pay', 'IndexController@scan_pay');

        $api->any('qr_pay', 'IndexController@qr_pay');

        $api->any('qr_auth_pay', 'IndexController@qr_auth_pay');
        $api->any('store_pay_ways', 'IndexController@store_pay_ways');
        $api->any('order', 'IndexController@order');
        $api->any('order_list', 'IndexController@order_list');
        $api->any('get_mq_info', 'SelectController@get_mq_info');
        $api->any('order_query', 'IndexController@order_query');
        $api->any('refund', 'IndexController@refund');
        $api->any('refund_query', 'IndexController@refund_query');

        $api->any('GetRequestApiUrl', 'SelectController@GetRequestApiUrl');
        $api->any('update', 'SelectController@update');


        $api->any('face_device_start', 'DeviceFaceController@face_device_start');
        $api->any('face_pay_start', 'DeviceFaceController@face_pay_start');
        $api->any('wxfacepay_initialize', 'DeviceFaceController@wxfacepay_initialize');
        $api->any('all_pay', 'DeviceFaceController@all_pay');
        $api->any('all_pay_query', 'DeviceFaceController@all_pay_query');


    });


    //代付 token
    $api->group(['namespace' => 'App\Api\Controllers\DfPay', 'prefix' => 'dfpay', 'middleware' => 'public.api'], function ($api) {
        $api->any('info_import', 'IndexController@info_import');
        $api->any('order_list', 'IndexController@order_list');
    });

    //代付
    $api->group(['namespace' => 'App\Api\Controllers\DfPay', 'prefix' => 'dfpay'], function ($api) {
        $api->any('pay_notify', 'NotifyController@pay_notify');
    });
});