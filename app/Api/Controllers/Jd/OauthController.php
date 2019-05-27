<?php

namespace App\Api\Controllers\Jd;

use App\Api\Controllers\Config\JdConfigController;
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
        //配置
        $Jdconfig = new JdConfigController();
        $Jdconfig = $Jdconfig->jd_config($config_id);

        $config = [
            'app_id' => $Jdconfig->wx_appid,
            'scope' => 'snsapi_base',
            'oauth' => [
                'scopes' => ['snsapi_base'],
                'response_type' => 'code',
                'callback' => url('api/jd/weixin/oauth_callback?sub_info=' . $sub_info . '&wx_appid=' . $Jdconfig->wx_appid . "&wx_secret=" . $Jdconfig->wx_secret . ""),
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
        $wx_AppId = $request->get('wx_appid');
        $wx_Secret = $request->get('wx_secret');

        $sub_info_arr = json_decode(base64_decode((string)$sub_info), true);
        $config_id = $sub_info_arr['config_id'];
        $store_id = $sub_info_arr['store_id'];
        $store_name = $sub_info_arr['store_name'];
        $merchant_id = $sub_info_arr['merchant_id'];
        $store_address = $sub_info_arr['store_address'];

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
            'store_address' => $store_address,
            'open_id' => $open_id,
            'merchant_id' => $merchant_id,
        ];
        $data = base64_encode(json_encode((array)$data));
        return redirect('/api/jd/weixin/pay_view?data=' . $data);


    }

    //网商银行显示页面
    public function pay_view(Request $request)
    {
        $data = json_decode(base64_decode((string)$request->get('data')), true);
        return view('jd.weixin', compact('data'));


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
