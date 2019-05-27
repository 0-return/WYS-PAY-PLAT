<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/7/4
 * Time: 上午11:41
 */

namespace App\Http\Controllers\School;


use App\Api\Controllers\Config\WeixinConfigController;
use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\StuStore;
use App\Models\WeixinConfig;
use Illuminate\Http\Request;

class PageController extends Controller
{


    //当面付/**/学校的链接
    public function trade_pay(Request $request)
    {

        $pay_type = "other";
        $store_id = $request->get('store_id');
        $stu_grades_no = $request->get('stu_grades_no');
        $stu_class_no = $request->get('stu_class_no');

        //判断是不是微信
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger') !== false) {
            $pay_type = 'weixin';
        }
        //判断是不是支付宝
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'AlipayClient') !== false) {
            $pay_type = 'alipay';
        }
        //判断是不是翼支付
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'Bestpay') !== false) {
            $pay_type = 'Bestpay';
        }
        //判断是不是京东
        if (strpos($_SERVER['HTTP_USER_AGENT'], 'WalletClient') !== false || strpos($_SERVER['HTTP_USER_AGENT'], 'JDJR-App') !== false) {
            $pay_type = 'jd';
        }
        $store = StuStore::where('store_id', $store_id)
            ->select('school_name', 'config_id')
            ->first();

        if (!$store) {
            $message = "门店不存在";
            return view('errors.page_errors', compact('message'));

        }

        $school_name = $store->school_name;


        if ($pay_type == 'weixin') {
            $state = [
                'store_id' => $store_id,
                'school_name' => $school_name,
                'config_id' => $store->config_id,
                'merchant_id' => $store->merchant_id,
                'bank_type' => 'school_weixin',
                'auth_type' => '02',
                'scope_type' => 'snsapi_base',
                'stu_grades_no' => $stu_grades_no,
                'stu_class_no' => $stu_class_no,
            ];

            $config = new WeixinConfigController();
            $WeixinConfig = $config->weixin_config_obj($store->config_id);

            if (!$WeixinConfig) {
                $message = "微信配置不存在";
                return view('errors.page_errors', compact('message'));

            }

            //开放平台代替授权
            if ($WeixinConfig->config_type=='2') {
                $state['authorizer_appid'] = $WeixinConfig->authorizer_appid;
                $state['authorizer_refresh_token'] = $WeixinConfig->authorizer_refresh_token;
                $state = \App\Common\TransFormat::encode($state);
                $code_url = url('api/weixinopen/oauth?state=' . $state);
            } //服务商特约
            else {
                $state = \App\Common\TransFormat::encode($state);
                $code_url = url('api/weixin/oauth?state=' . $state);
            }

            return redirect($code_url);

        }

        $code_url = 'https://openauth.alipay.com/oauth2/publicAppAuthorize.htm?app_id=2016112803504802&scope=auth_base&redirect_uri=https%3a%2f%2fk12jiaofei.alipay-eco.com%2fcallback%2fhome%3ftype%3d5&__webview_options__=showOptionMenu%3DYES%26backBehavior%3Dback';
        return redirect($code_url);
    }


}