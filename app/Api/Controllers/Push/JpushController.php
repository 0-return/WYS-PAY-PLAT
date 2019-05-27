<?php
/**
 * Created by PhpStorm.
 * User: dmk
 * Date: 2017/5/16
 * Time: 18:40
 */

namespace App\Api\Controllers\Push;


use App\Models\JpushConfig;
use App\Models\Merchant;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use JPush\Client;

class JpushController
{


    //收款语音播报推送
    public function push($type, $price, $out_trade_no, $merchant_id, $store_id, $config_id = "", $app_id = 'skxq')
    {
        try {

            $config = JpushConfig::where('config_id', $config_id)->first();
            if (!$config) {
                $config = JpushConfig::where('config_id', '1234')->first();
            }
            $client = new Client($config->DevKey, $config->API_DevSecret);
            $push = $client->push();

            $merchant = Merchant::where('id', $merchant_id)
                ->select('jpush_id', 'config_id', 'device_type')
                ->get();

            if ($merchant->isEmpty()) {
                $merchant = DB::table('merchant_stores')
                    ->join('merchants', 'merchant_stores.merchant_id', 'merchants.id')
                    ->where('merchant_stores.store_id', $store_id)
                    ->whereNotNull('merchants.jpush_id')
                    ->select('merchants.jpush_id', 'merchants.device_type', 'merchants.config_id')
                    ->get();
            }


            foreach ($merchant as $k => $v) {

                if ($v->device_type == 'pos' || $v->device_type == "pos_newland91001") {
                    //有POS机的话推送打印
                    $push->setPlatform(array('android'))
                        ->addRegistrationId($v->jpush_id)
                        ->message($out_trade_no . '=' . $type . '到账' . $price . '元')
                        ->send();
                }


                $message = '' . $type . '到账' . $price . '元';

                if ($v->device_type == "ios") {
                    $push = $client->push()
                        ->setPlatform(array('ios', 'android'))
                        ->iosNotification($message, array(
                            'sound' => 'default',
                            'badge' => '+1',
                            'content-available' => true,
                            'mutable-content' => true,
                            'category' => '01',
                            'extras' => array('ext' => '备用参数0', '备用参数1'),
                        ))
                        ->addRegistrationId($v->jpush_id)
                        ->message($message)
                        ->options(array(
                            'apns_production' => 0,
                        ))
                        ->send();
                }


                if ($v->device_type == 'android') {
                    //message
                    $push = $client->push()
                        ->setPlatform(array('android'))
                        ->addRegistrationId($v->jpush_id)
                        ->message($message)
                        ->send();

                    $data = [
                        'type' => 'app',
                        'app_id' => $app_id,
                        'url' => '',
                        'out_trade_no' => $out_trade_no,
                    ];

                    //消息弹框提醒
                    $this->notify($config, '' . $type . '到账' . $price . '元', $data, $v->jpush_id);

                }

            }


        } catch (\Exception $exception) {
            Log::info('极光推送基础文件');
            Log::info($exception);
        }
    }


    //弹框
    public function notify($config, $title, $data, $RegistrationId = '')
    {
        try {
            $client = new Client($config->DevKey, $config->API_DevSecret);
            $push = $client->push();
            if ($RegistrationId) {
                //弹框
                $alert = $push->setPlatform(['ios', 'android'])
                    ->iosNotification($title, [
                        'extras' => [
                            'type' => $data['type'],
                            'app_id' => $data['app_id'],
                            'url' => $data['url'],
                            'out_trade_no' => $data['out_trade_no'],
                        ]
                    ])
                    ->androidNotification($title, [
                        'extras' => [
                            'type' => $data['type'],
                            'app_id' => $data['app_id'],
                            'url' => $data['url'],
                            'out_trade_no' => $data['out_trade_no'],
                        ]
                    ])
                    ->addRegistrationId($RegistrationId)
                    ->options(array(
                        'apns_production' => 0,
                    ))
                    ->send();
            } else {
                //弹框
                $alert = $push->setPlatform(['ios', 'android'])
                    ->iosNotification($title, [
                        'extras' => [
                            'type' => $data['type'],
                            'app_id' => $data['app_id'],
                            'url' => $data['url'],
                            'out_trade_no' => $data['out_trade_no'],
                        ]
                    ])
                    ->androidNotification($title, [
                        'extras' => [
                            'type' => $data['type'],
                            'app_id' => v['app_id'],
                            'url' => $data['url'],
                            'out_trade_no' => $data['out_trade_no'],
                        ]
                    ])
                    ->addAllAudience()
                    ->send();
            }
            return $alert;
        } catch (\Exception $exception) {
            Log::info($exception);
        }
    }

    public function push_out($RegistrationId, $config_id)
    {

        try {
//            $config = JpushConfig::where('config_id', $config_id)->first();
//            if ($config) {
//                $config = JpushConfig::where('config_id', '1234')->first();
//            }
//            $client = new Client($config->DevKey, $config->API_DevSecre);
//            $message = '你的账户在其他地方登陆';
//            $push = $client->push()
//                ->setPlatform(array('ios', 'android'))
//                ->iosNotification($message, array(
//                    'sound' => 'default',
//                    'badge' => '+1',
//                    'content-available' => true,
//                    'mutable-content' => true,
//                    'category' => '02',
//                    'extras' => [
//                        'push_type' => 'login_out'
//                    ],
//                ))
//                ->addRegistrationId($RegistrationId)
//                ->options(array(
//                    'apns_production' => 0,
//                ))
//                ->message($message)
//                ->send();
        } catch (\Exception $exception) {

        }
    }
}