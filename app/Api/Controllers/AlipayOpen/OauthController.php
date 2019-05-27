<?php

namespace App\Api\Controllers\AlipayOpen;

use Alipayopen\Sdk\AopClient;
use Alipayopen\Sdk\Request\AlipayOpenAuthTokenAppRequest;
use Alipayopen\Sdk\Request\AlipaySystemOauthTokenRequest;
use App\Api\Controllers\Config\AlipayIsvConfigController;
use App\Models\AlipayAppOauthUsers;
use App\Models\Store;
use App\Models\StorePayWay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OauthController extends BaseController
{


    public function callback(Request $request)
    {
        try {

            $state = $this->decode($request->get('state'));
            if (empty($state)) {
                $message = '授权失败';
                return view('errors.page_errors', compact('message'));
            }

            // 商户授权
            if ($state['auth_type'] == '01') {
                $state['app_auth_code'] = $request->get('app_auth_code');
                return $this->merchantAuth($state);
            }

            // 用户授权
            if ($state['auth_type'] == '02') {
                return $this->memberAuth($request->get('auth_code'), $state);
            }

            // 学校商户第三方授权授权
            if ($state['auth_type'] == '03') {
                $state['app_auth_code'] = $request->get('app_auth_code');
                return $this->merchantAuth($state);
            }
            $message = 'auth_type类型错误';
            return view('errors.page_errors', compact('message'));

        } catch (\Exception $e) {
            $message = '系统错误！' . $e->getMessage() . $e->getFile() . $e->getLine();
            return view('errors.page_errors', compact('message'));
        }

    }

    // 用户授权
    public function memberAuth($app_auth_code, $state)
    {
        $member_return = $this->memberAliInfo($app_auth_code, $state);

        if ($member_return['status'] != 1) {
            exit($member_return['message']);
        }
        $open_id = $member_return['data']['open_id'];

        switch ($state['bank_type']) {
            case 'alipay':
                return self::alipay('alipay', $state, $open_id);
                break;
            case 'mbalipay':
                return self::alipay('mbalipay', $state, $open_id);
                break;
            case 'jdalipay':
                return self::alipay('jdalipay', $state, $open_id);
                break;

            case 'lftalipay':
                return self::alipay('lftalipay', $state, $open_id);
                break;
            case 'nlalipay':
                return self::alipay('nlalipay', $state, $open_id);
                break;

            case 'halipay':
                return self::alipay('halipay', $state, $open_id);
                break;


            case 'fuioualipay':
                return self::alipay('fuioualipay', $state, $open_id);
                break;


            default :
                $message = '银行类型未传递！';
                return view('errors.page_errors', compact('message'));
        }

    }


    // 商户授权
    public function merchantAuth($state)
    {
        $config = $this->isv_config($state['config_id']);
        //1.接入参数初始化
        $c = new AopClient();
        $c->signType = "RSA2";//升级算法
        $c->gatewayUrl = $config->alipay_gateway;
        $c->appId = $config->app_id;
        $c->rsaPrivateKey = $config->rsa_private_key;
        $c->format = "json";
        $c->charset = "GBK";
        $c->version = "2.0";
        //2.执行相应的接口获得相应的业务
        $obj = new AlipayOpenAuthTokenAppRequest();
        $obj->setApiVersion('2.0');
        $obj->setBizContent("{" .
            "    \"grant_type\":\"authorization_code\"," .
            "    \"code\":\"" . $state['app_auth_code'] . "\"," .
            "  }");
        try {
            $data = $c->execute($obj);
            $app_response = $data->alipay_open_auth_token_app_response;
        } catch (\Exception $exception) {
            $message = '配置出错检查一下支付宝应用配置！' . $exception->getMessage();
            return view('errors.page_errors', compact('message'));

        }
        $app_response = (array)$app_response;
        $code = $app_response['code'];
        if ($code == 10000) {
            $model = [
                "alipay_user_id" => $app_response['user_id'],
                'alipay_user_account' => $app_response['user_id'],
                "store_id" => $state['store_id'],
                'merchant_id' => 1,
                "app_auth_token" => $app_response['app_auth_token'],
                "app_refresh_token" => $app_response['app_refresh_token'],
                "expires_in" => $app_response['expires_in'],
                "re_expires_in" => $app_response['re_expires_in'],
                "auth_app_id" => $app_response['auth_app_id'],
            ];
            $alipay_user = AlipayAppOauthUsers::where('store_id', $state['store_id'])
                ->where('config_type', '01')
                ->first();

            //开启事务
            try {
                DB::beginTransaction();
                if ($alipay_user) {
                    AlipayAppOauthUsers::where('store_id', $state['store_id'])->update($model);

                    //新增通道
                    $a_w = StorePayWay::where('store_id', $state['store_id'])
                        ->where('ways_type', 1000)
                        ->first();

                    if ($a_w) {
                        $data_inset = [
                            'store_id' => $state['store_id'],
                            'status' => 1,
                            'status_desc' => '开通成功',
                        ];
                        $a_w->update($data_inset);
                        $a_w->save();
                    } else {

                        $gets1 = StorePayWay::where('store_id', $state['store_id'])->where('ways_source', 'alipay')
                            ->where('status', 1)
                            ->get();
                        $count1 = count($gets1);

                        $data_inset = [
                            'store_id' => $state['store_id'],
                            'settlement_type' => 'D0',
                            'ways_type' => 1000,
                            'company' => 'alipay',
                            'ways_source' => 'alipay',
                            'ways_desc' => '支付宝',
                            'ta_rate' => 0.6,
                            'tb_rate' => 0.6,
                            'sort' => $count1 + 1,
                            'status' => 1,
                            'status_desc' => '开通成功',
                        ];
                        StorePayWay::create($data_inset);
                    }
                } else {
                    AlipayAppOauthUsers::create($model);//新增信息
                }
                DB::commit();
            } catch
            (\Exception $e) {
                DB::rollBack();
                $message = '配置出错检查一下支付宝应用配置！' . $e->getMessage();
                return view('errors.page_errors', compact('message'));
            }
        } else {

            $message = '配置出错检查一下支付宝应用配置！';
            return view('errors.page_errors', compact('message'));
        }


        if ($state['auth_type'] == '03') {
            //支付宝教育缴费平台需要获得学校授权，因为由教育缴费平台代替学校向支付宝发起支付操作
            return redirect($config->alipay_school_auth_url);

        }

        $message = '授权成功！';
        return view('success.success', compact('message'));

    }


    //支付宝视图页面
    public static function alipay($type, $state, $open_id)
    {
        $store_id = $state['store_id'];//门店id
        $merchant_id = $state['merchant_id'];//收银员id
        $store_name = $state['store_name'];
        $store_address = $state['store_address'];
        $data = [
            'store_id' => $store_id,
            'store_name' => $store_name,
            'store_address' => $store_address,
            'open_id' => $open_id,
            'merchant_id' => $merchant_id,
            'other_no' => isset($state['other_no']) ? $state['other_no'] : "",
            'notify_url' => isset($state['notify_url']) ? $state['notify_url'] : "",
        ];

        if ($type == 'alipay') {
            return view('alipayopen.create_alipay_order', compact('data'));

        }
        if ($type == 'mbalipay') {
            return view('mybank.alipay', compact('data'));
        }

        if ($type == 'jdalipay') {
            return view('jd.alipay', compact('data'));
        }


        if ($type == 'lftalipay') {
            return view('lft.alipay', compact('data'));
        }

        if ($type == 'nlalipay') {
            return view('newland.alipay', compact('data'));
        }

        if ($type == 'halipay') {
            return view('huiyuanbao.alipay', compact('data'));
        }

        if ($type == 'fuioualipay') {
            return view('fuiou.alipay', compact('data'));
        }


    }


    public function memberAliInfo($app_auth_code, $state)
    {

        $isvconfig = new AlipayIsvConfigController();
        $config = $isvconfig->AlipayIsvConfig($state['config_id']);

        //1.初始化参数配置
        $aop = new AopClient();
        $aop->apiVersion = "2.0";
        $aop->appId = $config->app_id;
        $aop->rsaPrivateKey = $config->rsa_private_key;
        $aop->alipayrsaPublicKey = $config->alipay_rsa_public_key;
        $aop->signType = "RSA2";//升级算法
        $aop->gatewayUrl = $config->alipay_gateway;
        $aop->format = "json";
        $aop->charset = "GBK";


        $obj = new AlipaySystemOauthTokenRequest();
        $obj->setApiVersion('2.0');
        $obj->setCode($app_auth_code);
        $obj->setGrantType("authorization_code");
        try {
            $data = $aop->execute($obj);
            $re = $data->alipay_system_oauth_token_response;
            return [
                'status' => 1,
                'data' => [
                    'open_id' => $re->user_id
                ]
            ];

        } catch (\Exception $e) {
            return ['status' => 2, 'message' => '<h1>配置出错检查一下支付宝应用配置<h1>' . $e->getMessage() . $e->getFile() . $e->getLine()];
        }
    }

}
