<?php

namespace App\Api\Controllers\MyBank;

use App\Api\Controllers\Config\MyBankConfigController;
use EasyWeChat\Factory;
use Illuminate\Http\Request;

class OauthController extends BaseController
{
    //授权
    public function oauth(Request $request)
    {
        $sub_info = $request->get('state');
        $sub_info_arr = json_decode(base64_decode((string)$sub_info), true);
        $config_id = $sub_info_arr['config_id'];
        //网商配置
        $mbconfig = new MyBankConfigController();
        $MyBankConfig = $mbconfig->MyBankConfig($config_id);
        $config = [
            'app_id' => $MyBankConfig->wx_AppId,
            'scope' => 'snsapi_base',
            'oauth' => [
                'scopes' => ['snsapi_base'],
                'response_type' => 'code',
                'callback' => url('api/mybank/weixin/oauth_callback?sub_info=' . $sub_info . '&wx_AppId=' . $MyBankConfig->wx_AppId . '&wx_Secret=' . $MyBankConfig->wx_Secret . ''),
            ],

        ];
        $app = Factory::officialAccount($config);
        $oauth = $app->oauth;
        return $oauth->redirect();

    }

    public function oauth_callback(Request $request)
    {
        $sub_info = $request->get('sub_info');
        $code = $request->get('code');
        $wx_AppId = $request->get('wx_AppId');
        $wx_Secret = $request->get('wx_Secret');

        $sub_info_arr = json_decode(base64_decode((string)$sub_info), true);
        $config_id = $sub_info_arr['config_id'];
        $store_id = $sub_info_arr['store_id'];
        $store_name = $sub_info_arr['store_name'];
        $merchant_id = $sub_info_arr['merchant_id'];

        $config = [
            'app_id' => $wx_AppId,
            "secret" => $wx_Secret,
            "code" => $code,
            "grant_type" => "authorization_code",
        ];

        $app = Factory::officialAccount($config);
        $oauth = $app->oauth;
        $user = $oauth->user();
        $open_id = $user->getId();

        $data = [
            'store_id' => $store_id,
            'store_name' => $store_name,
            'store_address' => '',
            'open_id' => $open_id,
            'merchant_id' => $merchant_id,
        ];

        $data = base64_encode(json_encode((array)$data));
        return redirect('/api/mybank/weixin/pay_view?data=' . $data);


    }

    //网商银行显示页面
    public function pay_view(Request $request)
    {
        $data = json_decode(base64_decode((string)$request->get('data')), true);
        return view('mybank.weixin', compact('data'));


    }


    //第三方非想用平台来获取网商的openid
    public function oauth_openid(Request $request)
    {
        $sub_info = $request->get('state');
        //第三方传过来的信息
        $sub_info_arr = json_decode(base64_decode((string)$sub_info), true);
        //网商配置
        $mbconfig = new MyBankConfigController();
        $MyBankConfig = $mbconfig->MyBankConfig('1234');
        $config = [
            'app_id' => $MyBankConfig->wx_AppId,
            'scope' => 'snsapi_base',
            'oauth' => [
                'scopes' => ['snsapi_base'],
                'response_type' => 'code',
                'callback' => url('api/mybank/weixin/oauth_callback_openid?sub_info=' . $sub_info . '&wx_AppId=' . $MyBankConfig->wx_AppId . '&wx_Secret=' . $MyBankConfig->wx_Secret . ''),
            ],

        ];
        $app = Factory::officialAccount($config);
        $oauth = $app->oauth;
        return $oauth->redirect();

    }

    //第三方非想用平台来获取网商的openid
    public function oauth_callback_openid(Request $request)
    {
        $sub_info = $request->get('sub_info');
        $code = $request->get('code');
        $wx_AppId = $request->get('wx_AppId');
        $wx_Secret = $request->get('wx_Secret');

        $sub_info_arr = json_decode(base64_decode((string)$sub_info), true);


        $config = [
            'app_id' => $wx_AppId,
            "secret" => $wx_Secret,
            "code" => $code,
            "grant_type" => "authorization_code",
        ];

        $app = Factory::officialAccount($config);
        $oauth = $app->oauth;
        $user = $oauth->user();
        $open_id = $user->getId();

        $sub_info_arr['open_id'] = $open_id;
        $sub_info_arr['store_address'] = '';
        $callback_url = $sub_info_arr['callback_url'];

        $data = base64_encode(json_encode((array)$sub_info_arr));
        return redirect($callback_url . '?data=' . $data);


    }


    public function Options()
    {
        $options = [
            'app_id' => '',
            'payment' => [
                'merchant_id' => '',
                'key' => '',
                'cert_path' => '', // XXX: 绝对路径！！！！
                'key_path' => '',      // XXX: 绝对路径！！！！
                'notify_url' => '',       // 你也可以在下单时单独设置来想覆盖它
            ],
        ];

        return $options;
    }
}
