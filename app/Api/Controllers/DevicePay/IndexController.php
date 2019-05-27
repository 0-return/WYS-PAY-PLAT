<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/9/6
 * Time: 下午7:17
 */

namespace App\Api\Controllers\DevicePay;

use App\Api\Controllers\Merchant\OrderController;
use App\Api\Controllers\Merchant\PayBaseController;
use App\Models\Device;
use App\Models\MerchantStore;
use App\Models\MqttConfig;
use App\Models\Order;
use App\Models\RefundOrder;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;


/**扫码设备支付接口
 * Class IndexController
 * @package App\Api\Controllers\DevicePay
 */
class IndexController extends BaseController
{

    //扫一扫收款
    public function scan_pay(Request $request)
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

            $code = $data['code'];
            $total_amount = $data['total_amount'];
            $shop_price = $data['shop_price'];
            $device_id = $data['device_id'];
            $device_type = $data['device_type'];
            $shop_name = isset($data['shop_name']) ? $data['shop_name'] : "扫一扫付款";
            $shop_desc = isset($data['shop_desc']) ? $data['shop_desc'] : "扫一扫付款";
            $other_no = isset($data['other_no']) ? $data['other_no'] : "";
            $remark = isset($data['remark']) ? $data['remark'] : "";


            $check_data = [
                'total_amount' => '付款金额',
                'code' => '付款码编号',
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

            $merchant_id = $Device->merchant_id;
            $merchant_name = $Device->merchant_name;
            $store_id = $Device->store_id;
            $config_id = $Device->config_id;

            //公共返回参数
            $re_data = [
                'return_code' => 'SUCCESS',
                'return_msg' => '返回成功',
                'result_code' => '',
                'result_msg' => '',
                'other_no' => $other_no,
                'store_id' => $store_id,
                'ways_source' => '',
                'ways_source_desc' => '',
            ];

            //请求参数
            $data = [
                'config_id' => $config_id,
                'merchant_id' => $merchant_id,
                'merchant_name' => $merchant_name,
                'code' => $code,
                'total_amount' => $total_amount,
                'shop_price' => $shop_price,
                'remark' => $remark,
                'device_id' => $device_id,
                'shop_name' => $shop_name,
                'shop_desc' => $shop_desc,
                'store_id' => $store_id,
                'other_no' => $other_no,
            ];
            $pay_obj = new PayBaseController();
            $scan_pay_public = $pay_obj->scan_pay_public($data);
            $tra_data_arr = json_decode($scan_pay_public, true);


            if ($tra_data_arr['status'] != 1) {
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => $tra_data_arr['message'],
                ];
                return $this->return_data($err);
            }


            //用户支付成功
            if ($tra_data_arr['pay_status'] == '1') {
                //微信，支付宝支付凭证
                $re_data['result_code'] = 'SUCCESS';
                $re_data['result_msg'] = '支付成功';
                $re_data['out_trade_no'] = $tra_data_arr['data']['out_trade_no'];
                $re_data['out_transaction_id'] = $tra_data_arr['data']['out_trade_no'];
                $re_data['pay_time'] = date('YmdHis', strtotime(isset($tra_data_arr['data']['pay_time']) ? $tra_data_arr['data']['pay_time'] : time()));
                $re_data['ways_source'] = $tra_data_arr['data']['ways_source'];;

                //
                if ($re_data['ways_source'] == 'alipay') {
                    $re_data['ways_source_desc'] = "支付宝";
                }
                if ($re_data['ways_source'] == 'weixin') {
                    $re_data['ways_source_desc'] = "微信支付";
                }
                if ($re_data['ways_source'] == 'jd') {
                    $re_data['ways_source_desc'] = "京东支付";
                }
                if ($re_data['ways_source'] == 'unionpay') {
                    $re_data['ways_source_desc'] = "银联";
                }


            } elseif ($tra_data_arr['pay_status'] == '2') {
                //正在支付
                $re_data['result_code'] = 'USERPAYING';
                $re_data['result_msg'] = '用户正在支付';
                $re_data['out_trade_no'] = $tra_data_arr['data']['out_trade_no'];
            } else {
                //其他错误
                $re_data['result_code'] = 'FALL';
                $re_data['result_msg'] = $tra_data_arr['message'];
            }


            return $this->return_data($re_data);


        } catch (\Exception $exception) {

            $err = [
                'return_code' => 'FALL',
                'return_msg' => $exception->getMessage() . $exception->getLine(),
            ];
            return $this->return_data($err);
        }

    }

    //动态二维码收款
    public function qr_pay(Request $request)
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

            $total_amount = $data['total_amount'];
            $shop_price = $data['shop_price'];
            $device_id = $data['device_id'];
            $device_type = $data['device_type'];
            $ways_source = $data['ways_source'];
            $ways_type = $data['ways_type'];
            $notify_url = isset($data['notify_url']) ? $data['notify_url'] : "";
            $shop_name = isset($data['shop_name']) ? $data['shop_name'] : "扫一扫付款";
            $shop_desc = isset($data['shop_desc']) ? $data['shop_desc'] : "扫一扫付款";
            $other_no = isset($data['other_no']) ? $data['other_no'] : "";
            $remark = isset($data['remark']) ? $data['remark'] : "";


            $check_data = [
                'total_amount' => '付款金额',
                'ways_type' => '支付方式',
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

            $merchant_id = $Device->merchant_id;
            $merchant_name = $Device->merchant_name;
            $store_id = $Device->store_id;
            $config_id = $Device->config_id;

            //公共返回参数
            $re_data = [
                'return_code' => 'SUCCESS',
                'return_msg' => '返回成功',
                'other_no' => $other_no,
                'store_id' => $store_id,
                'ways_source' => $ways_source,
                'ways_source_desc' => '',
            ];

            //请求参数
            $data = [
                'config_id' => $config_id,
                'merchant_id' => $merchant_id,
                'merchant_name' => $merchant_name,
                'total_amount' => $total_amount,
                'shop_price' => $shop_price,
                'remark' => $remark,
                'device_id' => $device_id,
                'shop_name' => $shop_name,
                'shop_desc' => $shop_desc,
                'store_id' => $store_id,
                'other_no' => $other_no,
                'ways_source' => $ways_source,
                'notify_url' => $notify_url,
                'ways_type' => $ways_type,

            ];
            $pay_obj = new PayBaseController();
            $scan_pay_public = $pay_obj->qr_pay_public($data);
            $tra_data_arr = json_decode($scan_pay_public, true);


            if ($tra_data_arr['status'] != 1) {
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => $tra_data_arr['message'],
                ];
                return $this->return_data($err);
            }


            //返回数据成功
            $re_data['out_trade_no'] = $tra_data_arr['data']['out_trade_no'];
            $re_data['code_url'] = $tra_data_arr['data']['code_url'];
            $re_data['store_name'] = $tra_data_arr['data']['store_name'];


            if ($re_data['ways_source'] == 'alipay') {
                $re_data['ways_source_desc'] = "支付宝";
            }
            if ($re_data['ways_source'] == 'weixin') {
                $re_data['ways_source_desc'] = "微信支付";
            }
            if ($re_data['ways_source'] == 'jd') {
                $re_data['ways_source_desc'] = "京东支付";
            }
            if ($re_data['ways_source'] == 'unionpay') {
                $re_data['ways_source_desc'] = "银联";
            }


            return $this->return_data($re_data);


        } catch (\Exception $exception) {

            $err = [
                'return_code' => 'FALL',
                'return_msg' => $exception->getMessage() . $exception->getLine(),
            ];
            return $this->return_data($err);
        }

    }

    //静态二维码
    public function qr_auth_pay(Request $request)
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
            $other_no = isset($data['other_no']) ? $data['other_no'] : "";
            $notify_url = isset($data['notify_url']) ? $data['notify_url'] : '';

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

            $merchant_id = $Device->merchant_id;
            $merchant_name = $Device->merchant_name;
            $store_id = $Device->store_id;
            $store_name = $Device->store_name;

            //公共返回参数
            $re_data = [
                'return_code' => 'SUCCESS',
                'return_msg' => '返回成功',
                'other_no' => $other_no,
                'store_id' => $store_id,
            ];

            $server = $request->server();
            $server['SERVER_NAME'];
            $code_url = 'https://' . $server['SERVER_NAME'] . '/qr?store_id=' . $store_id . '&merchant_id=' . $merchant_id . '';


            //返回数据成功
            $re_data['out_trade_no'] = '';
            $re_data['code_url'] = $code_url;
            $re_data['store_name'] = $store_name;

            return $this->return_data($re_data);


        } catch (\Exception $exception) {

            $err = [
                'return_code' => 'FALL',
                'return_msg' => $exception->getMessage() . $exception->getLine(),];
            return $this->return_data($err);
        }


    }

    //店铺通道开通类型
    public function store_pay_ways(Request $request)
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

            $scan_amount = "";

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
                'scan_amount' => $scan_amount,
                'mq_server' => $mq_server,
                'mq_topic' => $mq_topic,
                'mq_port' => $mq_port,
                'mq_group_id' => $mq_group_id,
                'client_id' => $mq_group_id . '@@@' . $device_id,
                'mq_user_name' => $mq_user_name,
                'mq_user_password' => $mq_user_password,
            ];


            $data = DB::table('store_ways_desc')
                ->join('store_pay_ways', 'store_pay_ways.ways_type', '=', 'store_ways_desc.ways_type')
                ->where('store_pay_ways.store_id', $store_id)
                ->where('store_pay_ways.status', 1)
                ->where('store_pay_ways.is_close', 0)
                ->select('store_pay_ways.ways_type', 'store_pay_ways.ways_desc', 'store_pay_ways.ways_source', 'store_pay_ways.sort', 'store_pay_ways.rate')
                ->orderBy('store_pay_ways.sort', 'asc')
                ->get();

            //如果为空 看下是否为分店
            if ($data->isEmpty()) {
                $store = Store::where('store_id', $store_id)
                    ->select('pid')
                    ->first();
                //查总店
                if ($store && $store->pid) {
                    $store = Store::where('id', $store->pid)
                        ->select('store_id')
                        ->first();
                    if ($store) {
                        $data = DB::table('store_ways_desc')
                            ->join('store_pay_ways', 'store_pay_ways.ways_type', '=', 'store_ways_desc.ways_type')
                            ->where('store_pay_ways.store_id', $store->store_id)
                            ->where('store_pay_ways.status', 1)
                            ->where('store_pay_ways.is_close', 0)
                            ->select('store_pay_ways.ways_type', 'store_pay_ways.ways_desc', 'store_pay_ways.ways_source', 'store_pay_ways.sort', 'store_pay_ways.rate')
                            ->orderBy('store_pay_ways.sort', 'asc')
                            ->get();
                    }
                }
            }


            //返回数据成功
            $re_data['data'] = $data;


            return $this->return_data($re_data);


        } catch (\Exception $exception) {

            $err = [
                'return_code' => 'FALL',
                'return_msg' => $exception->getMessage() . $exception->getLine(),];
            return $this->return_data($err);
        }
    }

    //查询订单号状态
    public function order_query(Request $request)
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

            $other_no = isset($data['other_no']) ? $data['other_no'] : '';
            $out_trade_no = isset($data['out_trade_no']) ? $data['out_trade_no'] : "";
            $device_id = $data['device_id'];
            $device_type = $data['device_type'];


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

            $merchant_id = $Device->merchant_id;
            $merchant_name = $Device->merchant_name;
            $store_id = $Device->store_id;
            $store_name = $Device->store_name;
            $config_id = $Device->config_id;

            //公共返回参数
            $re_data = [
                'return_code' => 'SUCCESS',
                'return_msg' => '返回成功',
                'other_no' => $other_no,
                'store_id' => $store_id,
            ];

            $data = [
                'out_trade_no' => $out_trade_no,
                'other_no' => $other_no,
                'store_id' => $store_id,
                'ways_type' => '',
                'config_id' => $config_id,

            ];

            $order_obj = new OrderController();
            $return = $order_obj->order_foreach_public($data);
            $tra_data_arr = json_decode($return, true);
            if ($tra_data_arr['status'] != 1) {
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => $tra_data_arr['message'],
                ];
                return $this->return_data($err);
            }

            //用户支付成功
            if ($tra_data_arr['pay_status'] == '1') {
                //微信，支付宝支付凭证
                $re_data['result_code'] = 'SUCCESS';
                $re_data['result_msg'] = '支付成功';
                $re_data['out_trade_no'] = $tra_data_arr['data']['out_trade_no'];
                $re_data['out_transaction_id'] = $tra_data_arr['data']['out_trade_no'];
                $re_data['pay_time'] = date('YmdHis', strtotime($tra_data_arr['data']['pay_time']));
                $re_data['ways_source'] = $tra_data_arr['data']['ways_source'];;

                //
                if ($re_data['ways_source'] == 'alipay') {
                    $re_data['ways_source_desc'] = "支付宝";
                }
                if ($re_data['ways_source'] == 'weixin') {
                    $re_data['ways_source_desc'] = "微信支付";
                }
                if ($re_data['ways_source'] == 'jd') {
                    $re_data['ways_source_desc'] = "京东支付";
                }
                if ($re_data['ways_source'] == 'unionpay') {
                    $re_data['ways_source_desc'] = "银联";
                }


            } elseif ($tra_data_arr['pay_status'] == '2') {
                //正在支付
                $re_data['result_code'] = 'USERPAYING';
                $re_data['result_msg'] = '用户正在支付';
                $re_data['out_trade_no'] = $tra_data_arr['data']['out_trade_no'];
            } else {
                //其他错误
                $re_data['result_code'] = 'FALL';
                $re_data['result_msg'] = $tra_data_arr['message'];
            }


            return $this->return_data($re_data);


        } catch (\Exception $exception) {

            $err = [
                'return_code' => 'FALL',
                'return_msg' => $exception->getMessage() . $exception->getLine(),];
            return $this->return_data($err);
        }


    }

    //退款接口
    public function refund(Request $request)
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
            $out_trade_no = $data['out_trade_no'];
            $refund_amount = $data['refund_amount'];


            $check_data = [
                'out_trade_no' => '订单号',
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

            $merchant_id = $Device->merchant_id;
            $merchant_name = $Device->merchant_name;
            $store_id = $Device->store_id;
            $config_id = $Device->config_id;

            //公共返回参数
            $re_data = [
                'return_code' => 'SUCCESS',
                'return_msg' => '返回成功',
                'result_code' => '',
                'result_msg' => '',
                'store_id' => $store_id,
                'out_trade_no' => $out_trade_no,
            ];

            //请求参数
            $data = [
                'merchant_id' => $merchant_id,
                'out_trade_no' => $out_trade_no,
                'refund_amount' => $refund_amount,
            ];
            $pay_obj = new OrderController();
            $scan_pay_public = $pay_obj->refund_public($data);
            $tra_data_arr = json_decode($scan_pay_public, true);


            if ($tra_data_arr['status'] != 1) {
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => $tra_data_arr['message'],
                ];
                return $this->return_data($err);
            }

            $re_data['other_no'] = $tra_data_arr['data']['other_no'];
            $re_data['result_code'] = 'SUCCESS';
            $re_data['result_msg'] = '退款成功';

            return $this->return_data($re_data);


        } catch (\Exception $exception) {

            $err = [
                'return_code' => 'FALL',
                'return_msg' => $exception->getMessage() . $exception->getLine(),
            ];
            return $this->return_data($err);
        }


    }


    //对账查询
    public function order(Request $request)
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


            $time_start = $data['time_start'];
            $time_end = $data['time_end'];
            $device_id = $data['device_id'];
            $device_type = $data['device_type'];


            $check_data = [
                'time_start' => '开始时间',
                'time_end' => '结束时间',
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

            $merchant_id = $Device->merchant_id;
            $merchant_name = $Device->merchant_name;
            $store_id = $Device->store_id;
            $store_name = $Device->store_name;
            $config_id = $Device->config_id;

            //公共返回参数

            $request_data = [
                'store_id' => $store_id,
                'merchant_id' => $merchant_id,
                'time_start' => $time_start,
                'time_end' => $time_end,

            ];
            $data = $this->order_count_public($request_data);
            if ($data['status'] != 1) {
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => $data['message'],
                ];
                return $this->return_data($err);
            }

            $re_data = $data['data'];
            $re_data['return_code'] = 'SUCCESS';
            $re_data['return_msg'] = '返回成功';

            return $this->return_data($re_data);


        } catch (\Exception $exception) {

            $err = [
                'return_code' => 'FALL',
                'return_msg' => $exception->getMessage() . $exception->getLine(),];
            return $this->return_data($err);
        }


    }

    //对账统计-比较全
    public function order_count_public($data)
    {
        try {
            $store_id = $data['store_id'];
            $merchant_id = $data['merchant_id'];
            $time_start = $data['time_start'];
            $time_end = $data['time_end'];

            $where = [];
            $whereIn = [];
            $store_ids = [];

            //条件查询
            if ($store_id) {
                $where[] = ['store_id', '=', $store_id];
                $store_ids = [
                    [
                        'store_id' => $store_id,
                    ]
                ];

            } else {
                $MerchantStore = MerchantStore::where('merchant_id', $merchant_id)
                    ->select('store_id')
                    ->get();

                if (!$MerchantStore->isEmpty()) {
                    $store_ids = $MerchantStore->toArray();
                }

            }


            //是否传收银员ID
            if ($merchant_id) {
                $where[] = ['merchant_id', '=', $merchant_id];
            }

            if ($time_start) {
                $time_start = date('Y-m-d H:i:s', strtotime($time_start));
                $where[] = ['created_at', '>=', $time_start];
            }
            if ($time_end) {
                $time_end = date('Y-m-d H:i:s', strtotime($time_end));
                $where[] = ['created_at', '<=', $time_end];
            }


            //区间
            $e_order = '0.00';

            if ($merchant_id) {

                $order_data = Order::where($where)
                    ->whereIn('pay_status', [1, 6, 3])//成功+退款
                    ->where('merchant_id', $merchant_id)
                    ->select('total_amount', 'refund_amount', 'receipt_amount', 'fee_amount', 'mdiscount_amount');

                $refund_obj = RefundOrder::where('merchant_id', $merchant_id)
                    ->where($where)
                    ->select('refund_amount');

                //支付宝
                $alipay_order_data = Order::where($where)
                    ->whereIn('pay_status', [1, 6, 3])//成功+退款
                    ->where('ways_source', 'alipay')
                    ->where('merchant_id', $merchant_id)
                    ->select('total_amount', 'refund_amount', 'receipt_amount', 'fee_amount', 'mdiscount_amount');

                $alipay_refund_obj = RefundOrder::where('merchant_id', $merchant_id)
                    ->where('ways_source', 'alipay')
                    ->where($where)
                    ->select('refund_amount');

                //微信
                $weixin_order_data = Order::where($where)
                    ->whereIn('pay_status', [1, 6, 3])//成功+退款
                    ->where('ways_source', 'weixin')
                    ->where('merchant_id', $merchant_id)
                    ->select('total_amount', 'refund_amount', 'receipt_amount', 'fee_amount', 'mdiscount_amount');

                $weixin_refund_obj = RefundOrder::where('merchant_id', $merchant_id)
                    ->where('ways_source', 'weixin')
                    ->where($where)
                    ->select('refund_amount');


                //京东
                $jd_order_data = Order::where($where)
                    ->whereIn('pay_status', [1, 6, 3])//成功+退款
                    ->where('ways_source', 'jd')
                    ->where('merchant_id', $merchant_id)
                    ->select('total_amount', 'refund_amount', 'receipt_amount', 'fee_amount', 'mdiscount_amount');

                $jd_refund_obj = RefundOrder::where('merchant_id', $merchant_id)
                    ->where('ways_source', 'jd')
                    ->where($where)
                    ->select('refund_amount');


                //银联刷卡
                $un_order_data = Order::where($where)
                    ->whereIn('pay_status', [1, 6, 3])//成功+退款
                    ->whereIn('ways_type', [6005, 8005])//新大陆+京东刷卡
                    ->where('ways_source', 'unionpay')
                    ->where('merchant_id', $merchant_id)
                    ->select('total_amount', 'refund_amount', 'receipt_amount', 'fee_amount', 'mdiscount_amount');

                $un_refund_obj = RefundOrder::where('merchant_id', $merchant_id)
                    ->whereIn('type', [6005, 8005])//新大陆+京东刷卡
                    ->where('ways_source', 'unionpay')
                    ->where($where)
                    ->select('refund_amount');


                //银联扫码
                $unqr_order_data = Order::where($where)
                    ->whereIn('pay_status', [1, 6, 3])//成功+退款
                    ->whereNotIn('ways_type', [6005, 8005])//新大陆+京东刷卡
                    ->where('ways_source', 'unionpay')
                    ->where('merchant_id', $merchant_id)
                    ->select('total_amount', 'refund_amount', 'receipt_amount', 'fee_amount', 'mdiscount_amount');

                $unqr_refund_obj = RefundOrder::where('merchant_id', $merchant_id)
                    ->whereNotIn('type', [6005, 8005])//新大陆+京东刷卡
                    ->where('ways_source', 'unionpay')
                    ->where($where)
                    ->select('refund_amount');


            } else {
                $order_data = Order::whereIn('store_id', $store_ids)
                    ->where($where)
                    ->whereIn('pay_status', [1, 6, 3])//成功+退款
                    ->select('total_amount', 'refund_amount', 'receipt_amount', 'fee_amount', 'mdiscount_amount');


                $refund_obj = RefundOrder::whereIn('store_id', $store_ids)
                    ->where($where)
                    ->select('refund_amount');

                //支付宝
                $alipay_order_data = Order::whereIn('store_id', $store_ids)
                    ->where($where)
                    ->whereIn('pay_status', [1, 6, 3])//成功+退款
                    ->where('ways_source', 'alipay')
                    ->select('total_amount', 'refund_amount', 'receipt_amount', 'fee_amount', 'mdiscount_amount');


                $alipay_refund_obj = RefundOrder::whereIn('store_id', $store_ids)
                    ->where($where)
                    ->where('ways_source', 'alipay')
                    ->select('refund_amount');


                //微信
                $weixin_order_data = Order::whereIn('store_id', $store_ids)
                    ->where($where)
                    ->whereIn('pay_status', [1, 6, 3])//成功+退款
                    ->where('ways_source', 'weixin')
                    ->select('total_amount', 'refund_amount', 'receipt_amount', 'fee_amount', 'mdiscount_amount');


                $weixin_refund_obj = RefundOrder::whereIn('store_id', $store_ids)
                    ->where($where)
                    ->where('ways_source', 'weixin')
                    ->select('refund_amount');


                //京东
                $jd_order_data = Order::whereIn('store_id', $store_ids)
                    ->where($where)
                    ->whereIn('pay_status', [1, 6, 3])//成功+退款
                    ->where('ways_source', 'jd')
                    ->select('total_amount', 'refund_amount', 'receipt_amount', 'fee_amount', 'mdiscount_amount');


                $jd_refund_obj = RefundOrder::whereIn('store_id', $store_ids)
                    ->where($where)
                    ->where('ways_source', 'jd')
                    ->select('refund_amount');


                //银联刷卡
                $un_order_data = Order::whereIn('store_id', $store_ids)
                    ->where($where)
                    ->whereIn('pay_status', [1, 6, 3])//成功+退款
                    ->whereIn('ways_type', [6005, 8005])//新大陆+京东刷卡
                    ->where('ways_source', 'unionpay')
                    ->select('total_amount', 'refund_amount', 'receipt_amount', 'fee_amount', 'mdiscount_amount');


                $un_refund_obj = RefundOrder::whereIn('store_id', $store_ids)
                    ->where($where)
                    ->where('ways_source', 'unionpay')
                    ->whereIn('type', [6005, 8005])//新大陆+京东刷卡
                    ->select('refund_amount');


                //银联二维码
                $unqr_order_data = Order::whereIn('store_id', $store_ids)
                    ->where($where)
                    ->whereIn('pay_status', [1, 6, 3])//成功+退款
                    ->whereNotIn('ways_type', [6005, 8005])//去除新大陆+京东刷卡
                    ->where('ways_source', 'unionpay')
                    ->select('total_amount', 'refund_amount', 'receipt_amount', 'fee_amount', 'mdiscount_amount');


                $unqr_refund_obj = RefundOrder::whereIn('store_id', $store_ids)
                    ->where($where)
                    ->where('ways_source', 'unionpay')
                    ->whereNotIn('type', [6005, 8005])//去除新大陆+京东刷卡
                    ->select('refund_amount');


            }

            //总的
            $total_amount = $order_data->sum('total_amount');//交易金额
            $refund_amount = $refund_obj->sum('refund_amount');//退款金额
            $fee_amount = $order_data->sum('fee_amount');//结算服务费/手续费
            $mdiscount_amount = $order_data->sum('mdiscount_amount');//商家优惠金额
            $get_amount = $total_amount - $refund_amount - $mdiscount_amount;//商家实收，交易金额-退款金额
            $receipt_amount = $get_amount - $fee_amount;//实际净额，实收-手续费
            $e_order = '' . $e_order . '';
            $total_count = '' . count($order_data->get()) . '';
            $refund_count = count($refund_obj->get());

            //支付宝
            $alipay_total_amount = $alipay_order_data->sum('total_amount');//交易金额
            $alipay_refund_amount = $alipay_refund_obj->sum('refund_amount');//退款金额
            $alipay_fee_amount = $alipay_order_data->sum('fee_amount');//结算服务费/手续费
            $alipay_mdiscount_amount = $alipay_order_data->sum('mdiscount_amount');//商家优惠金额
            $alipay_get_amount = $alipay_total_amount - $alipay_refund_amount - $alipay_mdiscount_amount;//商家实收，交易金额-退款金额
            $alipay_receipt_amount = $alipay_get_amount - $alipay_fee_amount;//实际净额，实收-手续费
            $alipay_total_count = '' . count($alipay_order_data->get()) . '';
            $alipay_refund_count = count($alipay_refund_obj->get());


            //微信
            $weixin_total_amount = $weixin_order_data->sum('total_amount');//交易金额
            $weixin_refund_amount = $weixin_refund_obj->sum('refund_amount');//退款金额
            $weixin_fee_amount = $weixin_order_data->sum('fee_amount');//结算服务费/手续费
            $weixin_mdiscount_amount = $weixin_order_data->sum('mdiscount_amount');//商家优惠金额
            $weixin_get_amount = $weixin_total_amount - $weixin_refund_amount - $weixin_mdiscount_amount;//商家实收，交易金额-退款金额
            $weixin_receipt_amount = $weixin_get_amount - $weixin_fee_amount;//实际净额，实收-手续费
            $weixin_total_count = '' . count($weixin_order_data->get()) . '';
            $weixin_refund_count = count($weixin_refund_obj->get());


            //京东
            $jd_total_amount = $jd_order_data->sum('total_amount');//交易金额
            $jd_refund_amount = $jd_refund_obj->sum('refund_amount');//退款金额
            $jd_fee_amount = $jd_order_data->sum('fee_amount');//结算服务费/手续费
            $jd_mdiscount_amount = $jd_order_data->sum('mdiscount_amount');//商家优惠金额
            $jd_get_amount = $jd_total_amount - $jd_refund_amount - $jd_mdiscount_amount;//商家实收，交易金额-退款金额
            $jd_receipt_amount = $jd_get_amount - $jd_fee_amount;//实际净额，实收-手续费
            $jd_total_count = '' . count($jd_order_data->get()) . '';
            $jd_refund_count = count($jd_refund_obj->get());

            //银联刷卡
            $un_total_amount = $un_order_data->sum('total_amount');//交易金额
            $un_refund_amount = $un_refund_obj->sum('refund_amount');//退款金额
            $un_fee_amount = $un_order_data->sum('fee_amount');//结算服务费/手续费
            $un_mdiscount_amount = $un_order_data->sum('mdiscount_amount');//商家优惠金额
            $un_get_amount = $un_total_amount - $un_refund_amount - $un_mdiscount_amount;//商家实收，交易金额-退款金额
            $un_receipt_amount = $un_get_amount - $un_fee_amount;//实际净额，实收-手续费
            $un_total_count = '' . count($un_order_data->get()) . '';
            $un_refund_count = count($un_refund_obj->get());

            //银联扫码
            $unqr_total_amount = $unqr_order_data->sum('total_amount');//交易金额
            $unqr_refund_amount = $unqr_refund_obj->sum('refund_amount');//退款金额
            $unqr_fee_amount = $unqr_order_data->sum('fee_amount');//结算服务费/手续费
            $unqr_mdiscount_amount = $unqr_order_data->sum('mdiscount_amount');//商家优惠金额
            $unqr_get_amount = $unqr_total_amount - $unqr_refund_amount - $unqr_mdiscount_amount;//商家实收，交易金额-退款金额
            $unqr_receipt_amount = $unqr_get_amount - $unqr_fee_amount;//实际净额，实收-手续费
            $unqr_total_count = '' . count($unqr_order_data->get()) . '';
            $unqr_refund_count = count($unqr_refund_obj->get());


            $data = [
                'total_amount' => number_format($total_amount, 2, '.', ''),//交易金额
                'total_count' => '' . $total_count . '',//交易笔数
                'refund_count' => '' . $refund_count . '',//退款金额
                'get_amount' => number_format($get_amount, 2, '.', ''),//商家实收，交易金额-退款金额
                'refund_amount' => number_format($refund_amount, 2, '.', ''),//退款金额
                'receipt_amount' => number_format($receipt_amount, 2, '.', ''),//实际净额，实收-手续费
                'fee_amount' => number_format($fee_amount, 2, '.', ''),//结算服务费/手续费
                'mdiscount_amount' => number_format($mdiscount_amount, 2, '.', ''),

                'alipay_total_amount' => number_format($alipay_total_amount, 2, '.', ''),//交易金额
                'alipay_total_count' => '' . $alipay_total_count . '',//交易笔数
                'alipay_refund_count' => '' . $alipay_refund_count . '',//退款金额
                'alipay_get_amount' => number_format($alipay_get_amount, 2, '.', ''),//商家实收，交易金额-退款金额
                'alipay_refund_amount' => number_format($alipay_refund_amount, 2, '.', ''),//退款金额
                'alipay_receipt_amount' => number_format($alipay_receipt_amount, 2, '.', ''),//实际净额，实收-手续费
                'alipay_fee_amount' => number_format($alipay_fee_amount, 2, '.', ''),//结算服务费/手续费
                'alipay_mdiscount_amount' => number_format($alipay_mdiscount_amount, 2, '.', ''),

                'weixin_total_amount' => number_format($weixin_total_amount, 2, '.', ''),//交易金额
                'weixin_total_count' => '' . $weixin_total_count . '',//交易笔数
                'weixin_refund_count' => '' . $weixin_refund_count . '',//退款金额
                'weixin_get_amount' => number_format($weixin_get_amount, 2, '.', ''),//商家实收，交易金额-退款金额
                'weixin_refund_amount' => number_format($weixin_refund_amount, 2, '.', ''),//退款金额
                'weixin_receipt_amount' => number_format($weixin_receipt_amount, 2, '.', ''),//实际净额，实收-手续费
                'weixin_fee_amount' => number_format($weixin_fee_amount, 2, '.', ''),//结算服务费/手续费
                'weixin_mdiscount_amount' => number_format($weixin_mdiscount_amount, 2, '.', ''),

                'jd_total_amount' => number_format($jd_total_amount, 2, '.', ''),//交易金额
                'jd_total_count' => '' . $jd_total_count . '',//交易笔数
                'jd_refund_count' => '' . $jd_refund_count . '',//退款金额
                'jd_get_amount' => number_format($jd_get_amount, 2, '.', ''),//商家实收，交易金额-退款金额
                'jd_refund_amount' => number_format($jd_refund_amount, 2, '.', ''),//退款金额
                'jd_receipt_amount' => number_format($jd_receipt_amount, 2, '.', ''),//实际净额，实收-手续费
                'jd_fee_amount' => number_format($jd_fee_amount, 2, '.', ''),//结算服务费/手续费
                'jd_mdiscount_amount' => number_format($jd_mdiscount_amount, 2, '.', ''),

                'un_total_amount' => number_format($un_total_amount, 2, '.', ''),//交易金额
                'un_total_count' => '' . $un_total_count . '',//交易笔数
                'un_refund_count' => '' . $un_refund_count . '',//退款金额
                'un_get_amount' => number_format($un_get_amount, 2, '.', ''),//商家实收，交易金额-退款金额
                'un_refund_amount' => number_format($un_refund_amount, 2, '.', ''),//退款金额
                'un_receipt_amount' => number_format($un_receipt_amount, 2, '.', ''),//实际净额，实收-手续费
                'un_fee_amount' => number_format($un_fee_amount, 2, '.', ''),//结算服务费/手续费
                'un_mdiscount_amount' => number_format($un_mdiscount_amount, 2, '.', ''),

                'unqr_total_amount' => number_format($unqr_total_amount, 2, '.', ''),//交易金额
                'unqr_total_count' => '' . $unqr_total_count . '',//交易笔数
                'unqr_refund_count' => '' . $unqr_refund_count . '',//退款金额
                'unqr_get_amount' => number_format($unqr_get_amount, 2, '.', ''),//商家实收，交易金额-退款金额
                'unqr_refund_amount' => number_format($unqr_refund_amount, 2, '.', ''),//退款金额
                'unqr_receipt_amount' => number_format($unqr_receipt_amount, 2, '.', ''),//实际净额，实收-手续费
                'unqr_fee_amount' => number_format($unqr_fee_amount, 2, '.', ''),//结算服务费/手续费
                'unqr_mdiscount_amount' => number_format($unqr_mdiscount_amount, 2, '.', ''),

            ];

            return ['status' => 1, 'message' => '数据返回成功', 'data' => $data];

        } catch (\Exception $exception) {
            return ['status' => 2, 'message' => $exception->getMessage()];

        }
    }


    //查询订单号状态
    public function order_list(Request $request)
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

            $merchant_id = $Device->merchant_id;
            $merchant_name = $Device->merchant_name;
            $store_id = $Device->store_id;
            $store_name = $Device->store_name;
            $config_id = $Device->config_id;

            //公共返回参数
            $re_data = [
                'return_code' => 'SUCCESS',
                'return_msg' => '返回成功',
                'store_id' => $store_id,
            ];
            $where = [];
            if ($merchant_id) {
                $where[] = ['merchant_id', '=', $merchant_id];
            }

            if ($store_id) {
                $where[] = ['store_id', '=', $store_id];
            }

            $re_data['data'] = Order::where($where)
                ->where('pay_status', 1)
                ->select('total_amount', 'ways_source_desc', 'pay_time')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();


            return $this->return_data($re_data);


        } catch (\Exception $exception) {

            $err = [
                'return_code' => 'FALL',
                'return_msg' => $exception->getMessage() . $exception->getLine(),];
            return $this->return_data($err);
        }


    }


}