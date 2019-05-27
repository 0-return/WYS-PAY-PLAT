<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/7/23
 * Time: 下午9:35
 */

namespace App\Api\Controllers\WeixinOpen;


use App\Http\Controllers\Controller;
use App\Models\WeixinConfig;
use App\Models\WeixinNotify;
use App\Models\WeixinOpenConfig;
use EasyWeChat\Factory;
use EasyWeChat\OpenPlatform\Server\Guard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotifyController extends Controller
{


    //授权事件接收URL
    public function auth_notify(Request $request)
    {

        $WeixinOpenConfig = WeixinOpenConfig::first();
        $config = [
            'app_id' => $WeixinOpenConfig->app_id,
            'secret' => $WeixinOpenConfig->secret,
            'token' => $WeixinOpenConfig->token,
            'aes_key' => $WeixinOpenConfig->aes_key,
        ];

        $openPlatform = Factory::openPlatform($config);

        $server = $openPlatform->server;

        //处理component_verify_ticket,//SDK 默认会处理事件 component_verify_ticket ，并会缓存 verify_ticket 所以如果你暂时不需要处理其他事件，直接这样使用即可：
        $server->push(function ($message) {
            if (isset($message['InfoType']) && $message['InfoType'] == "component_verify_ticket") {
                WeixinOpenConfig::where('app_id', $message['AppId'])->update([
                    'component_verify_ticket' => $message['ComponentVerifyTicket']
                ]);
            }
        }, Guard::EVENT_COMPONENT_VERIFY_TICKET);


        // 处理授权成功事件
        $server->push(function ($message) {
            // ...

            Log::info('处理授权成功事件');

            Log::info($message);
        }, Guard::EVENT_AUTHORIZED);

        // 处理授权更新事件
        $server->push(function ($message) {
            Log::info('处理授权更新事件');

            Log::info($message);


        }, Guard::EVENT_UPDATE_AUTHORIZED);

        // 处理授权取消事件
        $server->push(function ($message) {

            Log::info('处理授权取消事件');
            Log::info($message);

        }, Guard::EVENT_UNAUTHORIZED);


        return $openPlatform->server->serve(); // Done!


    }

    //app/Api/Controller/WeixinOpen/remind_notify@NotifyController.php
    //代公众号实现业务
    public function remind_notify(Request $request, $appid)
    {
        $WeixinOpenConfig = WeixinOpenConfig::first();
        $config = [
            'app_id' => $WeixinOpenConfig->app_id,
            'secret' => $WeixinOpenConfig->secret,
            'token' => $WeixinOpenConfig->token,
            'aes_key' => $WeixinOpenConfig->aes_key,
        ];

        $openPlatform = Factory::openPlatform($config);

        $WeixinConfig = WeixinConfig::where('authorizer_appid', $appid)->first();
        $refreshToken = $WeixinConfig['authorizer_refresh_token'];
        $officialAccount = $openPlatform->officialAccount($appid, $refreshToken);

        $server = $officialAccount->server;

        //公众号处理事件
        $server->push(function ($message) {
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
        $response = $server->serve();
        $response->send();
    }

}