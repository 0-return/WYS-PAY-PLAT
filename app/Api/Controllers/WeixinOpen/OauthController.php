<?php

namespace App\Api\Controllers\WeixinOpen;

use App\Http\Controllers\Controller;
use App\Models\WeixinConfig;
use App\Models\WeixinOpenConfig;
use function EasyWeChat\Kernel\Support\get_client_ip;
use function EasyWeChat\Kernel\Support\get_server_ip;
use Illuminate\Http\Request;
use EasyWeChat\Factory;
use MyBank\Tools;


class OauthController extends Controller
{


    //开放平台下的授权
    public function oauth(Request $request)
    {
        $sub_info = $request->get('state');
        $WeixinOpenConfig = WeixinOpenConfig::first();
        $config = [
            'app_id' => $WeixinOpenConfig->app_id,
            'secret' => $WeixinOpenConfig->secret,
            'token' => $WeixinOpenConfig->token,
            'aes_key' => $WeixinOpenConfig->aes_key,
        ];

        $sub_info = $this->decode($sub_info);
        $openPlatform = Factory::openPlatform($config);
        $officialAccount = $openPlatform->officialAccount($sub_info['authorizer_appid'], $sub_info['authorizer_refresh_token']);


        $sub_info = $this->encode($sub_info);
        $oauth = $officialAccount->oauth;
        $callback_url = url('/api/weixinopen/callback?sub_info=' . $sub_info . '&app_id=' . $WeixinOpenConfig->app_id . '&secret=' . $WeixinOpenConfig->secret);
        $oauth->withRedirectUrl($callback_url);

        return $oauth->redirect();


    }


    public function callback(Request $request)
    {
        $code = $request->get('code');
        $app_id = $request->get('app_id');
        $secret = $request->get('secret');

        $sub_info = $request->get('sub_info');
        $sub_info = $this->decode($sub_info);
        $config = [
            'app_id' => $app_id,
            'secret' => $secret,
            "code" => $code,
            "grant_type" => "authorization_code",
        ];

        $openPlatform = Factory::openPlatform($config);
        $officialAccount = $openPlatform->officialAccount($sub_info['authorizer_appid'], $sub_info['authorizer_refresh_token']);
        $oauth = $officialAccount->oauth;

        $user = $oauth->user();
        $sub_info['open_id'] = $user->id;
        $url = "";
        //正常微信支付
        if ($sub_info['bank_type'] == "weixin") {
            $sub_info = $this->encode($sub_info);
            $url = url('/api/weixin/qr_pay_view?sub_info=' . $sub_info);
            return redirect($url);

        }

        //教育缴费 //微信支付
        if ($sub_info['bank_type'] == "school_weixin") {
            $open_id = $user->id;
            $store_id = $sub_info['store_id'];
            $stu_grades_no = $sub_info['stu_grades_no'];
            $stu_class_no = $sub_info['stu_class_no'];
            $school_name = $sub_info['school_name'];
            $url = url('/school/payeducation?open_id=' . $open_id . '&store_id=' . $store_id . '&school_name=' . $school_name . '&stu_grades_no=' . $stu_grades_no . '&stu_class_no=' . $stu_class_no);
            return redirect($url);
        }

        //网商微信支付
        if ($sub_info['bank_type'] == "mybank_weixin") {
            $data = [
                'store_id' => $sub_info['store_id'],
                'store_name' => $sub_info['store_name'],
                'store_address' => $sub_info['store_address'],
                'open_id' => $user->id,
                'merchant_id' => $sub_info['merchant_id'],
            ];

            $data = base64_encode(json_encode((array)$data));
            return redirect('/api/mybank/weixin/pay_view?data=' . $data);


        }

        //京东金融微信支付
        if ($sub_info['bank_type'] == "jd_weixin") {
            $data = [
                'store_id' => $sub_info['store_id'],
                'store_name' => $sub_info['store_name'],
                'store_address' => $sub_info['store_address'],
                'open_id' => $user->id,
                'merchant_id' => $sub_info['merchant_id'],
            ];

            $data = base64_encode(json_encode((array)$data));
            return redirect('/api/jd/weixin/pay_view?data=' . $data);


        }

        //新大陆微信支付
        if ($sub_info['bank_type'] == "nl_weixin") {
            $data = [
                'store_id' => $sub_info['store_id'],
                'store_name' => $sub_info['store_name'],
                'store_address' => $sub_info['store_address'],
                'open_id' => $user->id,
                'merchant_id' => $sub_info['merchant_id'],
            ];

            $data = base64_encode(json_encode((array)$data));
            return redirect('/api/newland/weixin/pay_view?data=' . $data);


        }


    }

    /*
      通讯数据加密
  */
    public
    static function encode($data)
    {
        return $data = base64_encode(json_encode((array)$data));
    }

    /*
        通讯数据解密
    */
    public
    static function decode($data)
    {
        return json_decode(base64_decode((string)$data), true);
    }


}
