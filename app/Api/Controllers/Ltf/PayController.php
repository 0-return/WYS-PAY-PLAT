<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/10/20
 * Time: 11:01 AM
 */

namespace App\Api\Controllers\Ltf;


use function EasyWeChat\Kernel\Support\get_client_ip;
use function EasyWeChat\Kernel\Support\get_server_ip;
use Illuminate\Support\Facades\Log;

class PayController extends BaseController
{


    //扫一扫 0-系统错误 1-成功 2-正在支付 3-失败
    public function scan_pay($data)
    {

        try {
            $out_trade_no = $data['out_trade_no'];
            $code = $data['code'];
            $total_amount = $data['total_amount'];
            $remark = $data['remark'];
            $device_id = $data['device_id'];
            $shop_name = $data['shop_name'];
            $notify_url = $data['notify_url'];
            $url = $data['request_url'];
            $pi_type = $data['pay_type'];
            $merchant_no = $data['merchant_no'];
            $returnParams = $data['return_params'];//原样返回
            $appId = $data['appId'];
            $key = $data['key'];


            //请求数据
            $data = [
                'appId' => $appId,
                'random' => "" . $out_trade_no . time() . "",
                'merchantCode' => $merchant_no,
                'outTradeNo' => $out_trade_no,
                'totalAmount' => $total_amount,
                'authCode' => $code,
                'subject' => $shop_name,
            ];

            $obj = new BaseController();
            $sign = $obj->createSign($data, $key);
            $data["sign"] = $sign;

            $re = $obj->requestAsHttpPOST($data, $url); //发送请求
            $re = json_decode($re, true);


            //支付成功
            if ($re['code'] == "SUCCESS") {
                return [
                    'status' => 1,
                    'message' => '支付成功',
                    'data' => $re,
                ];

            } else {

                //用户输入密码
                if ($re['code'] == "FAILED" && $re['subCode'] == "USER_PAYING") {
                    return [
                        'status' => 2,
                        'message' => '请用户输入密码',
                        'data' => $re,
                    ];
                }

                //其他报错
                return [
                    'status' => 0,
                    'message' => $re['msg']
                ];
            }


        } catch (\Exception $exception) {
            return [
                'status' => 0,
                'message' => $exception->getMessage(),
            ];
        }
    }


    //查询订单 0-系统错误 1-成功 2-正在支付 3-失败 4.已经退款
    public function order_query($data)
    {

        try {
            $out_trade_no = $data['out_trade_no'];
            $url = $data['request_url'];
            $merchant_no = $data['merchant_no'];

            $appId = $data['appId'];
            $key = $data['key'];


            //请求数据
            $data = [
                'appId' => $appId,
                'random' => "" . $out_trade_no . time() . "",
                'merchantCode' => $merchant_no,
                'outTradeNo' => $out_trade_no,
            ];

            $obj = new BaseController();
            $sign = $obj->createSign($data, $key);
            $data["sign"] = $sign;

            $re = $obj->requestAsHttpPOST($data, $url); //发送请求
            $re = json_decode($re, true);


            //支付成功
            if ($re['code'] == "SUCCESS") {
                return [
                    'status' => 1,
                    'message' => '支付成功',
                    'data' => $re,
                ];

            } else {

                //用户输入密码
                if ($re['code'] == "FAILED" && $re['subCode'] == "USER_PAYING") {
                    return [
                        'status' => 2,
                        'message' => '请用户输入密码',
                        'data' => $re,
                    ];
                }

                //其他报错
                return [
                    'status' => 0,
                    'message' => $re['msg']
                ];
            }

        } catch (\Exception $exception) {
            return [
                'status' => 0,
                'message' => $exception->getMessage(),
            ];
        }
    }


    //退款 0-系统错误 1-成功
    public function refund($data)
    {

        try {
            $out_trade_no = $data['out_trade_no'];
            $url = $data['request_url'];
            $merchant_no = $data['merchant_no'];
            $outRefundNo = $data['outRefundNo'];
            $amount = $data['amount'];
            $notifyUrl = $data['notifyUrl'];
            $appId = $data['appId'];
            $key = $data['key'];

            //请求数据
            $data = [
                'appId' => $appId,
                'random' => "" . $out_trade_no . time() . "",
                'merchantCode' => $merchant_no,
                'refundNo' => $outRefundNo,
                'refundAmount' => $amount,
                'outTradeNo' => $out_trade_no,
            ];

            $obj = new BaseController();
            $sign = $obj->createSign($data, $key);
            $data["sign"] = $sign;
            $re = $obj->requestAsHttpPOST($data, $url); //发送请求
            $re = json_decode($re, true);


            //退款成功
            if ($re['code'] == "SUCCESS") {
                return [
                    'status' => 1,
                    'data' => $re
                ];
            } else {
                return [
                    'status' => 0,
                    'message' => $re['msg']
                ];
            }
        } catch (\Exception $exception) {
            return [
                'status' => 0,
                'message' => $exception->getMessage(),
            ];
        }
    }


    //退款查询 0-系统错误 1-成功 2-正在退款 3-失败
    public function refund_query($data)
    {

        try {
            $url = $data['request_url'];
            $merchant_no = $data['merchant_no'];
            $outRefundNo = $data['outRefundNo'];
            $appId = $data['appId'];
            $key = $data['key'];

            //请求数据
            $data = [
                'appId' => $appId,
                'random' => "" . $outRefundNo . time() . "",
                'merchantCode' => $merchant_no,
                'refundNo' => $outRefundNo,
            ];

            $obj = new BaseController();
            $sign = $obj->createSign($data, $key);
            $data["sign"] = $sign;

            $re = $obj->requestAsHttpPOST($data, $url); //发送请求
            $re = json_decode($re, true);


            //退款成功
            if ($re['code'] == "SUCCESS") {
                return [
                    'status' => 1,
                    'message' => '退款成功',
                    'data' => $re,
                ];
            } else {
                return [
                    'status' => 0,
                    'message' => $re['msg']
                ];
            }

        } catch (\Exception $exception) {
            return [
                'status' => 0,
                'message' => $exception->getMessage(),
            ];
        }
    }


    //生成动态二维码-公共
    public function send_qr($data)
    {
        try {
            $out_trade_no = $data['out_trade_no'];
            $total_amount = $data['total_amount'];
            $remark = $data['remark'];
            $notify_url = $data['notify_url'];
            $url = $data['request_url'];
            $merchant_no = $data['merchant_no'];
            $returnParams = $data['return_params'];//原样返回
            $appId = $data['appId'];
            $key = $data['key'];
            $channel = $data['channel'];
            $tradeType = $data['tradeType'];
            //请求数据
            $data = [
                'appId' => $appId,
                'random' => "" . $out_trade_no . time() . "",
                'merchantCode' => $merchant_no,
                'outTradeNo' => $out_trade_no,
                'totalAmount' => $total_amount,
                'channel' => $channel,
                'tradeType' => $tradeType,
                'notifyUrl' => $notify_url,
                'orderRemark' => $remark,
            ];

            $obj = new BaseController();
            $sign = $obj->createSign($data, $key);
            $data["sign"] = $sign;

            $re = $obj->requestAsHttpPOST($data, $url); //发送请求
            $re = json_decode($re, true);


            //业务成功
            if ($re['code'] == "SUCCESS") {
                return [
                    'status' => 1,
                    'message' => '返回成功',
                    'code_url' => $re['qrCode'],
                    'data' => $re
                ];

            } else {
                return [
                    'status' => 0,
                    'message' => $re['msg']
                ];
            }


        } catch (\Exception $exception) {
            return [
                'status' => 0,
                'message' => $exception->getMessage(),
            ];
        }

    }


    //静态码提交-公共 获取到链接
    public function qr_url_submit($data)
    {
        try {
            $out_trade_no = $data['out_trade_no'];
            $userId = $data['userId'];
            $total_amount = $data['total_amount'];
            $remark = $data['remark'];
            $device_id = $data['device_id'];
            $shop_name = $data['shop_name'];
            $notify_url = $data['notify_url'];
            $url = $data['request_url'];
            $pi_type = $data['pay_type'];
            $merchant_no = $data['merchant_no'];
            $returnParams = $data['return_params'];//原样返回

            $appId = $data['appId'];
            $key = $data['key'];
            $returnUrl = $data['returnUrl'];
            //请求数据
            $data = [
                'appId' => $appId,
                'random' => "" . $out_trade_no . time() . "",
                'merchantCode' => $merchant_no,
                'outTradeNo' => $out_trade_no,
                'totalAmount' => $total_amount,
                'returnUrl' => $returnUrl,
                'notifyUrl' => $notify_url,
            ];

            $obj = new BaseController();
            $sign = $obj->createSign($data, $key);
            $data["sign"] = $sign;

            $re = $obj->requestAsHttpPOST($data, $url); //发送请求
            $re = json_decode($re, true);


            //业务成功
            if ($re['code'] == "SUCCESS") {
                return [
                    'status' => 1,
                    'message' => '请求成功',
                    'data' => $re,
                ];

            } else {
                return [
                    'status' => 0,
                    'message' => $re['msg']
                ];
            }


        } catch (\Exception $exception) {
            return [
                'status' => 0,
                'message' => $exception->getMessage(),
            ];
        }
    }


    //静态码提交-公共
    public function qr_submit($data)
    {
        try {
            $out_trade_no = $data['out_trade_no'];
            $userId = $data['userId'];
            $total_amount = $data['total_amount'];
            $remark = $data['remark'];
            $device_id = $data['device_id'];
            $shop_name = $data['shop_name'];
            $notify_url = $data['notify_url'];
            $url = $data['request_url'];
            $merchant_no = $data['merchant_no'];
            $returnParams = $data['return_params'];//原样返回
            $channel = $data['channel'];
            $tradeType = $data['tradeType'];
            $appId = $data['appId'];
            $key = $data['key'];
            $returnUrl = $data['returnUrl'];
            //请求数据
            $data = [
                'appId' => $appId,
                'random' => "" . $out_trade_no . time() . "",
                'merchantCode' => $merchant_no,
                'outTradeNo' => $out_trade_no,
                'totalAmount' => $total_amount,
                'returnUrl' => $returnUrl,
                'notifyUrl' => $notify_url,
                'channel' => $channel,
                'tradeType' => $tradeType,
                'openId' => $userId,
            ];

            $obj = new BaseController();
            $sign = $obj->createSign($data, $key);
            $data["sign"] = $sign;

            $re = $obj->requestAsHttpPOST($data, $url); //发送请求
            $re = json_decode($re, true);
            //业务成功
            if ($re['code'] == "SUCCESS") {
                return [
                    'status' => 1,
                    'message' => '请求成功',
                    'data' => $re,
                ];

            } else {
                return [
                    'status' => 0,
                    'message' => $re['msg']
                ];
            }


        } catch (\Exception $exception) {
            return [
                'status' => 0,
                'message' => $exception->getMessage(),
            ];
        }
    }

}