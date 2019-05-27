<?php

namespace App\Api\Controllers\WeixinOpen;

use App\Http\Controllers\Controller;
use App\Models\WeixinConfig;
use App\Models\WeixinOpenConfig;
use Illuminate\Http\Request;
use EasyWeChat\Factory;
use Tymon\JWTAuth\Facades\JWTAuth;


class OpenOauthController extends Controller
{

    public function openoauth(Request $request)
    {
        JWTAuth::setToken(JWTAuth::getToken());
        $data = JWTAuth::getPayload();//数组
        if ($data['sub']['type'] == "merchant") {
            dd('你是商户账户暂时不允许授权,请登录服务商账户');
        }

        if ($data['sub']['level'] > 1) {
            dd('你的账户暂时不允许授权,请联系服务商');

        }
        $config_id = $data['sub']['config_id'];

        $WeixinOpenConfig = WeixinOpenConfig::first();
        $config = [
            'app_id' => $WeixinOpenConfig->app_id,
            'secret' => $WeixinOpenConfig->secret,
            'token' => $WeixinOpenConfig->token,
            'aes_key' => $WeixinOpenConfig->aes_key,
        ];

        $callback_url = url('/api/weixinopen/opencallback?config_id=' . $config_id);
        $openPlatform = Factory::openPlatform($config);
        $url = $openPlatform->getPreAuthorizationUrl($callback_url); // 传入回调URI即可


        echo "<div style='margin:0 auto;'><h2><a href='$url'>请再次确认授权</a></h2></div>";

    }


    public function opencallback(Request $request)
    {

        $config_id = $request->get('config_id');

        $WeixinOpenConfig = WeixinOpenConfig::first();

        $config = [
            'app_id' => $WeixinOpenConfig->app_id,
            'secret' => $WeixinOpenConfig->secret,
            'token' => $WeixinOpenConfig->token,
            'aes_key' => $WeixinOpenConfig->aes_key,
        ];

        $openPlatform = Factory::openPlatform($config);

        $data = $openPlatform->handleAuthorize();

        $WeixinConfig = WeixinConfig::where('config_id', $config_id)->first();

        $in_data = [
            'config_id' => $config_id,
            'authorizer_appid' => $data['authorization_info']['authorizer_appid'],
            'authorizer_refresh_token' => $data['authorization_info']['authorizer_refresh_token'],
            'authorizer_time' => date('Y-m-d H:i:s', time()),
        ];

        if ($WeixinConfig) {
            $WeixinConfig->update($in_data);
            $WeixinConfig->save();
        } else {
            WeixinConfig::create($in_data);
        }
        $message = '授权成功';
        return view('success.success', compact('message'));

    }


}
