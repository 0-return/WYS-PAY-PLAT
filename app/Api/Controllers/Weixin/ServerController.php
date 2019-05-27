<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/11/27
 * Time: 6:27 PM
 */

namespace App\Api\Controllers\Weixin;


use App\Api\Controllers\Config\WeixinConfigController;
use App\Models\WeixinNotify;
use EasyWeChat\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MyBank\Tools;

class ServerController extends BaseController
{


    //接受微信服务器消息
    public function server(Request $request)
    {

        $data = $request->getContent();
        $data = Tools::xml_to_array_weixin($data);
        //只接受特定程序
        if (isset($data['EventKey'])) {
            $arr = explode('&', $data['EventKey']);
            $config_id = $arr[1];
            $config = new WeixinConfigController();
            $config_obj = $config->weixin_config_obj($config_id);
            $config = [
                'app_id' => $config_obj->wx_notify_appid,
                'secret' => $config_obj->wx_notify_secret,
                'response_type' => 'array',
            ];
            $app = Factory::officialAccount($config);
            $app->server->push(function ($message) {
                $message['EventKey'];
                $arr = explode('&', $message['EventKey']);
                //
                $type = $arr[0];

                //关注提醒
                if ($type == 'gztx') {
                    $store_id = $arr[2];
                    $merchant_id = $arr[3];
                    $open_id = $message['FromUserName']; // 用户的 openid
                    $WeixinNotify = WeixinNotify::where('store_id', $store_id)
                        ->where('merchant_id', $merchant_id)
                        ->where('wx_open_id', $open_id)
                        ->first();
                    if (!$WeixinNotify) {
                        WeixinNotify::create([
                            'store_id' => $store_id,
                            'merchant_id' => $merchant_id,
                            'wx_open_id' => $open_id,
                        ]);
                    }
                    return '收款提醒设置成功';
                }
            });

            $response = $app->server->serve();
            $response->send();


        } else {
            $config = new WeixinConfigController();
            $config_obj = $config->weixin_config_obj('1234');
            $config = [
                'app_id' => $config_obj->wx_notify_appid,
                'secret' => $config_obj->wx_notify_secret,
                'response_type' => 'array',
            ];

            $app = Factory::officialAccount($config);

            $app->server->push(function ($message) {
                return "您好！欢迎使用";
            });

            // 在 laravel 中：
            $response = $app->server->serve();
            return $response;
        }
    }

}