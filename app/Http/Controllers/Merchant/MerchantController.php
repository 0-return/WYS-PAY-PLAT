<?php
/**
 * Created by PhpStorm.
 * User: dmk
 * Date: 2017/2/26
 * Time: 17:38
 */

namespace App\Http\Controllers\Merchant;

use App\Api\Controllers\Config\AlipayIsvConfigController;
use App\Http\Controllers\Controller;
use App\Models\AlipayIsvConfig;
use App\Models\AppOem;
use App\Models\Store;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class MerchantController extends Controller
{
    //第三方支付宝应用授权跳转到支付宝app里面
    public function appAlipay(Request $request)
    {
        $store_id = $request->get('store_id');
        $merchant_id = $request->get('merchant_id');
        $config_id = $request->get('config_id');
        $auth_type=$request->get('auth_type','01');//01 第三方授权，03 学校第三方授权
        //配置
        $isv_config = new \App\Api\Controllers\AlipayOpen\BaseController();
        $config = $isv_config->isv_config($config_id);


        if (!$config) {
            $message = '服务商没有配置支付宝相关应用信息';
            return view('errors.page_errors', compact('message'));
        }


        $url = urlencode($config->callback);
        $appid = $config->app_id;
        $app_oauth_url = $config->alipay_app_auth_url;

        $state = [
            'auth_type' => $auth_type,//第三方应用授权
            'store_id' => $store_id,
            'config_id' => $config_id,
            'merchant_id' => $merchant_id,
        ];
        $state = $isv_config->encode($state);
        $code_url = $app_oauth_url . '?app_id=' . $appid . '&redirect_uri=' . $url . "&state=" . $state;

        return redirect($code_url);
    }
}