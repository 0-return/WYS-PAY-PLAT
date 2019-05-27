<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/11/28
 * Time: 10:25 AM
 */

namespace App\Common;


use App\Api\Controllers\Config\WeixinConfigController;
use App\Api\Controllers\Device\YlianyunAopClient;
use App\Api\Controllers\Device\ZlbzController;
use App\Api\Controllers\Push\JpushController;
use App\Api\Controllers\Qmtt\AliBaseController;
use App\Models\Device;
use App\Models\MqttConfig;
use App\Models\VConfig;
use App\Models\WeixinNotify;
use App\Models\WeixinNotifyTemplate;
use EasyWeChat\Factory;
use function GuzzleHttp\Promise\is_settled;
use karpy47\PhpMqttClient\MQTTClient;
use MyBank\Tools;

class DSuccessAction
{


    //支付成功以后的操作
    public static function action($data)
    {
        try {
            //同步
            self::action_tb($data);

            //队列


        } catch
        (\Exception $exception) {
            \Illuminate\Support\Facades\Log::info($exception);
        }
    }


    //同步操作数据库
    public static function action_tb($data)
    {

        //6.打印
        try {

            if (isset($data['no_print'])) {
                //不打印

            } else {
                if (isset($data['remark'])) {

                } else {
                    $data['remark'] = '备注';
                }
                self::print_data($data);

            }

        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::info('打印');
            \Illuminate\Support\Facades\Log::info($exception);

        }

        //7.播报
        try {

            if (isset($data['no_v'])) {
                //不播报

            } else {
                $type = '0';
                if ($data['ways_source'] == "alipay") {
                    $type = '1';
                }

                if ($data['ways_source'] == "weixin") {
                    $type = '2';
                }
                $amount = isset($data['pay_amount']) ? $data['pay_amount'] : $data['total_amount'];
                self::v_send($amount * 100, $data['store_id'], $type, $data['merchant_id'], $data['config_id'], $data['out_trade_no']);

            }

        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::info('播报');
            \Illuminate\Support\Facades\Log::info($exception);

        }

    }


    //微信公众号消息提醒提醒
    public static function weixin_notify($data)
    {
        try {
            $WeixinNotify = WeixinNotify::where('store_id', $data['store_id'])
                ->select('wx_open_id')
                ->get();
            foreach ($WeixinNotify as $k => $v) {
                $config_id = $data['config_id'];
                //判断服务商是否有设置模版消息
                $config = new WeixinConfigController();
                $config_obj = $config->weixin_config_obj($config_id);


                // $weixin_template = $config->weixin_template($config_id, 1);

                $config = [
                    'app_id' => $config_obj->wx_notify_appid,
                    'secret' => $config_obj->wx_notify_secret,
                ];
                $app = Factory::officialAccount($config);
                $app->template_message->send([
                    'touser' => $v->wx_open_id,
                    'template_id' => $config_obj->template_id,
                    'url' => url('/mb/login'),
                    'data' => [
                        'first' => '门店名称:' . $data['store_name'],
                        'keyword1' => $data['total_amount'] . '元',
                        'keyword2' => $data['ways_type_desc'],
                        'keyword3' => date('Y-m-d H:i:s', time()),
                        'keyword4' => $data['out_trade_no'],
                        'remark' => ''
                    ],
                ]);
            }


        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::info($exception);
        }

    }

    //打印
    public static function print_data($data)
    {
        try {

            $p = Device::where('store_id', $data['store_id'])
                ->where('merchant_id', $data['merchant_id'])
                ->where("type", "p")->get();

            //收银员未绑定走门店机器
            if ($p->isEmpty()) {
                $p = Device::where('store_id', $data['store_id'])
                    ->where('merchant_id', '')
                    ->where("type", "p")->get();
            }
            $print_type = isset($data['print_type']) ? $data['print_type'] : "";

            if (!$p->isEmpty()) {


                foreach ($p as $v) {
                    //智联
                    if ($v->device_type == "p_zlbz_1") {
                        $da = new ZlbzController();
                        $store_name = $data['store_name'];
                        $order_no = '' . $data['out_trade_no'] . '';
                        $price = '' . $data['total_amount'] . '';
                        $remark = '' . $data['remark'] . '';
                        $qr_url = 'https:www';
                        $type = $data['ways_type_desc'];
                        try {
                            $device_id = $v->device_no;
                            $da->print_send($device_id, $type, $store_name, $order_no, $price, $remark, $qr_url, $print_type);

                        } catch (\Exception $exception) {
                            \Illuminate\Support\Facades\Log::info($exception);
                            continue;
                        }
                    }

                    //K4
                    if ($v->device_type == "p_yly_k4") {
                        try {
                            $da = new YlianyunAopClient();
                            $push_id = "8978";
                            $push_key = "7a67e62b938e35dffdd1e0eee039bc83060070df";
                            $data['device_key'] = $v->device_key;
                            $data['device_no'] = $v->device_no;
                            $data['push_id'] = $push_id;
                            $data['push_key'] = $push_key;
                            $data['type'] = $data['ways_type_desc'];
                            $da->send_print($data);
                        } catch (\Exception $exception) {
                            \Illuminate\Support\Facades\Log::info('易联云打印');
                            \Illuminate\Support\Facades\Log::info($exception);
                            continue;
                        }
                    }


                }
            }


        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::info($exception);

        }

    }

    //播报设备 金额是分
    public static function v_send($price, $store_id, $type, $merchant_id = "", $config_id = "1234", $out_trade_no = "")
    {
        try {
            if ($merchant_id) {
                $Device = Device::where('store_id', $store_id)
                    ->where('merchant_id', $merchant_id)
                    ->whereIn("device_type", ["v_bp_1", 'v_kd_58', 'v_zw_1', 's_bp_sl51', 'v_zlbz_1'])
                    ->get();
            } else {
                $Device = Device::where('store_id', $store_id)
                    ->where('merchant_id', '')
                    ->whereIn("device_type", ["v_bp_1", 'v_kd_58', 'v_zw_1', 's_bp_sl51', 'v_zlbz_1'])->get();
            }
            if ($Device->isEmpty()) {

            } else {
                foreach ($Device as $k => $v) {

                    //智联博众
                    if ($v->device_type == "v_zlbz_1") {
                        $VConfig = VConfig::where('config_id', $config_id)
                            ->select('zlbz_token')->first();

                        if (!$VConfig) {
                            $VConfig = VConfig::where('config_id', '1234')
                                ->select(
                                    'zlbz_token'
                                )->first();
                        }

                        $datap = [
                            'id' => $v->device_no,
                            'price' => $price,
                            'uid' => $store_id,
                            'token' => $VConfig->zlbz_token,
                            'pt' => $type
                        ];
                        \Illuminate\Support\Facades\Log::info('播报');
                        \Illuminate\Support\Facades\Log::info($v->device_no);
                        \Illuminate\Support\Facades\Log::info($datap);
                        Tools::curl($datap, 'http://39.106.131.149/add.php');
                    }

                    //智网
                    if ($v->device_type == "v_zw_1") {

                        $VConfig = VConfig::where('config_id', $config_id)
                            ->select(
                                'zw_token'
                            )->first();

                        if (!$VConfig) {
                            $VConfig = VConfig::where('config_id', '1234')
                                ->select(
                                    'zw_token'
                                )->first();
                        }

                        $url = 'http://cloudspeaker.smartlinkall.com/add.php?token=' . $VConfig->zw_token . '&id=' . $v->device_no . '&uid=' . $store_id . $v->device_no . '&seq=' . time() . '&price=' . $price . '&pt=' . $type . '&vol=100';
                        $data = Tools::curl_get($url);

                    }

                    //推送卡台小喇叭+波谱sl51
                    if (in_array($v->device_type, ['v_bp_1', 'v_kd_58', 's_bp_sl51'])) {
                        $type_desc = "";
                        if ($type == '1') {
                            $type_desc = "支付宝";
                        }
                        if ($type == '2') {
                            $type_desc = "微信支付";
                        }

                        $message = $type_desc . '到账' . ($price / 100) . '元';
                        $orderNum = date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));

                        self::mqtt($v->device_no, $v->device_type, $message, $orderNum, $config_id, $type, $price);
                    }

                }
            }
        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::info($exception);
        }

    }


//mqtt 推送
    public static function mqtt($device_id, $device_type, $message, $orderNum, $config_id, $type, $price)
    {
        try {

            $MqttConfig = MqttConfig::where('config_id', $config_id)->first();
            if (!$MqttConfig) {
                $MqttConfig = MqttConfig::where('config_id', '1234')->first();
            }

            if (!$MqttConfig) {
                return false;
            }
            $server = $MqttConfig->server;
            $port = $MqttConfig->port;
            $mq_group_id = $MqttConfig->group_id;
            $username = "Signature|" . $MqttConfig->access_key_id . "|" . $MqttConfig->instance_id . "";
            $str = '' . $MqttConfig->group_id . '@@@' . $device_id . '1' . '';
            $key = $MqttConfig->access_key_secret;
            $str = mb_convert_encoding($str, "UTF-8");
            $password = base64_encode(hash_hmac("sha1", $str, $key, true));

            $client_id = $mq_group_id . '@@@' . $device_id;
            $server_client_id = $mq_group_id . '@@@' . $device_id . '1';
            $topic = $MqttConfig->topic . "/p2p/" . $client_id;


            $content = json_encode([
                'message' => $message,
                'orderNum' => $orderNum,
                'type' => $type,//1 支付宝 2 微信
                'price' => '' . $price . ''//分
            ]);

            $data = [
                'message' => $message,
                'orderNum' => $orderNum,
                'type' => $type,//1 支付宝 2 微信
                'price' => '' . $price . ''//分
            ];


            //方式1  v3.1.1

            $mqtt = new AliBaseController($server, $port, $server_client_id);
            if ($mqtt->connect(false, NULL, $username, $password)) {
                $mqtt->publish($topic, $content, 1);
                $mqtt->close();
            } else {

            }


        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::info('errors-1');
            \Illuminate\Support\Facades\Log::info($exception);
        }
    }
}