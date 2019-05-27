<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/9/7
 * Time: 下午3:27
 */

namespace App\Api\Controllers\DevicePay;


use Alipayopen\Sdk\AopClient;
use Alipayopen\Sdk\Request\AlipayTradeQueryRequest;
use App\Models\Device;
use App\Models\DeviceOem;
use App\Models\MqttConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SelectController extends BaseController
{

    //获得设备的接口地址
    public function GetRequestApiUrl(Request $request)
    {
        try {
            //获取请求参数
            $data = $request->getContent();
            $data = json_decode($data, true);

            //验证签名
            $check = $this->check_md5($data);
            if ($check['return_code'] == 'FALL') {
                return $this->return_data($check);
            }


            $device_id = $data['device_id'];
            $device_type = $data['device_type'];
            $return_type = isset($data['return_type']) ? $data['return_type'] : "";

            if (!$device_id) {
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '设备device_id不能为空',
                ];
                return $this->return_data($err);
            }
            if (!$device_type) {

                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '设备device_type不能为空',
                ];
                return $this->return_data($err);
            }


            $DeviceOem = DeviceOem::where('device_id', $device_id)
                ->where('device_type', $device_type)
                ->select('Request')
                ->first();

            if (!$DeviceOem) {
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '设备不存在',
                ];
                return $this->return_data($err);
            }

            //只返回mq初始化连接
            if ($return_type && $return_type == 'MQ') {
                $data = [
                    'return_code' => "SUCCESS",
                    'return_msg' => "数据返回成功",
                    'Request' => $DeviceOem->Request,
                    "MQ" => '/api/devicepay/get_mq_info',
                ];
            } else {
                $data = [
                    'return_code' => "SUCCESS",
                    'return_msg' => "数据返回成功",
                    'Request' => $DeviceOem->Request,
                    'ScanPay' => '/api/devicepay/scan_pay',
                    'QrPay' => '/api/devicepay/qr_pay',
                    'QrAuthPay' => '/api/devicepay/qr_auth_pay',
                    'PayWays' => '/api/devicepay/store_pay_ways',
                    'OrderQuery' => '/api/devicepay/order_query',
                    'Order' => '/api/devicepay/order',
                    'OrderList' => '/api/devicepay/order_list',
                    'Refund' => '/api/devicepay/refund',
                    "MQ" => '/api/devicepay/get_mq_info',
                ];
            }


            return $this->return_data($data);


        } catch (\Exception $exception) {

            $err = [
                'return_code' => 'FALL',
                'return_msg' => $exception->getMessage() . $exception->getLine(),
            ];
            return $this->return_data($err);
        }

    }

    //获取阿里云mqtt
    public function get_mq_info(Request $request)
    {
        try {
            //获取请求参数
            $data = $request->getContent();
            $data = json_decode($data, true);
            //验证签名
            $check = $this->check_md5($data);
            if ($check['return_code'] == 'FALL') {
                return $this->return_data($check);
            }

            $device_id = $data['device_id'];
            $device_type = $data['device_type'];

            $check_data = [
                'device_id' => '设备编号',
                'device_type' => '设备类型'
            ];
            $check = $this->check_required($data, $check_data);
            if ($check) {
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => $check,
                ];
                return $this->return_data($err);
            }

            $Device = Device::where('device_type', $device_type)
                ->where('device_no', $device_id)
                ->first();

            if (!$Device) {


                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '设备未绑定',
                ];
                return $this->return_data($err);
            }

            $store_id = $Device->store_id;
            $store_name = $Device->store_name;
            $config_id = $Device->config_id;
            //
            $MqttConfig = MqttConfig::where('config_id', $config_id)->first();
            if (!$MqttConfig) {
                $MqttConfig = MqttConfig::where('config_id', '1234')->first();
            }

            if (!$MqttConfig) {
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '未配置消息推送',
                ];
                return $this->return_data($err);
            }

            $mq_server = $MqttConfig->server;
            $mq_topic = $MqttConfig->topic;
            $mq_port = $MqttConfig->port;
            $mq_group_id = $MqttConfig->group_id;
            $mq_user_name = "Signature|" . $MqttConfig->access_key_id . "|" . $MqttConfig->instance_id . "";

            $str = '' . $MqttConfig->group_id . '@@@' . $device_id . '';
            $key = $MqttConfig->access_key_secret;
            $str = mb_convert_encoding($str, "UTF-8");
            $mq_user_password = base64_encode(hash_hmac("sha1", $str, $key, true));


            //公共返回参数
            $re_data = [
                'return_code' => 'SUCCESS',
                'return_msg' => '返回成功',
                'store_id' => $store_id,
                'store_name' => $store_name,
                'mq_server' => $mq_server,
                'mq_topic' => $mq_topic,
                'mq_port' => $mq_port,
                'mq_group_id' => $mq_group_id,
                'client_id' => $mq_group_id . '@@@' . $device_id,
                'mq_user_name' => $mq_user_name,
                'mq_user_password' => $mq_user_password,
            ];
            return $this->return_data($re_data);


        } catch (\Exception $exception) {

            $err = [
                'return_code' => 'FALL',
                'return_msg' => $exception->getMessage() . $exception->getLine(),];
            return $this->return_data($err);
        }
    }


    //设备升级
    public function update(Request $request)
    {
        try {
            //获取请求参数
            $data = $request->getContent();
            $data = json_decode($data, true);
            //验证签名
            $check = $this->check_md5($data);
            if ($check['return_code'] == 'FALL') {
                return $this->return_data($check);
            }
            $check_data = [
                'device_id' => '设备编号',
                // 'version' => '版本号',
                'device_type' => '设备类型'
            ];
            $check = $this->check_required($data, $check_data);
            if ($check) {
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => $check,
                ];
                return $this->return_data($err);
            }


            $device_id = $data['device_id'];
            $device_type = $data['device_type'];
            $version = $data['version'];


            $DeviceOem = DeviceOem::where('device_id', $device_id)
                ->where('device_type', $device_type)
                ->select('id')
                ->first();

            if (!$DeviceOem) {
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '设备未绑定',
                ];
                return $this->return_data($err);
            }


            $file = '';
            $true = "0";
            if ($device_id == "KD58T000001") {
                $true = "1";
                $file = url('') . "/VA4NJYMY190218900.zip";
            };
            if ($device_id == "0100001582" || $device_id == "0100007047") {
                $true = "1";
                $file = url('') . "/bopu/MengXiangTechYun_sign-120s.img";
            };
            //公共返回参数
            $re_data = [
                'return_code' => 'SUCCESS',
                'return_msg' => '返回成功',
                'new_version' => $version,
                'version' => $version,
                'true' => $true,
                'file' => $file
            ];

            return $this->return_data($re_data);


        } catch (\Exception $exception) {

            $err = [
                'return_code' => 'FALL',
                'return_msg' => $exception->getMessage() . $exception->getLine(),];
            return $this->return_data($err);
        }
    }


}