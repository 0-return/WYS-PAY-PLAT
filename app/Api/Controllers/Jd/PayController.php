<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/10/20
 * Time: 11:01 AM
 */

namespace App\Api\Controllers\Jd;


use function EasyWeChat\Kernel\Support\get_client_ip;
use function EasyWeChat\Kernel\Support\get_server_ip;
use Illuminate\Support\Facades\Log;

class PayController extends BaseController
{


    //扫一扫 0-系统错误 1-成功 2-正在支付 3-失败
    public function scan_pay($data)
    {

        //请求数据
//        $data = [
//            "out_trade_no" => '1540209834',
//            "code" => "",
//            "total_amount" => "1",
//            "remark" => "备注",
//            "device_id" => "设备id",
//            "shop_name" => "购买商品名称",
//            "notify_url" => "http://pay.umxnt.com",
//            "request_url" => "http://testapipayx.jd.com/m/pay",
//            "pay_type" => "WX",
//            "merchant_no" => "110826750",
//            "return_params" => "原样返回",
//        ];

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
            $des_key = $data['des_key'];
            $md_key = $data['md_key'];
            $systemId = $data['systemId'];
            //请求数据
            $data = [
                'merchantNo' => $merchant_no,
                'businessCode' => 'AGGRE',
                'version' => '3.0',
                'outTradeNo' => $out_trade_no,
                'amount' => $total_amount * 100,
                'currency' => 'RMB',
                'authCode' => $code,
                'piType' => $pi_type,
                'virtualType' => '01',
                'remark' => $remark,//
                'returnParams' => $returnParams,
                'deviceInfo' => json_encode([
                    'type' => 'DT03',
                    'ip' => get_server_ip(),
                    'imei' => $merchant_no,

                ]),
                'outTradeIp' => get_client_ip(),
                'productName' => $shop_name,//购买的商品
                // 'notifyUrl' => $notify_url, //扫一扫 去掉异步
            ];

            $obj = new BaseController();
            $obj->des_key = $des_key;
            $obj->md_key = $md_key;
            $obj->systemId = $systemId;
            $re = $obj->execute($data, $url);


            //系统错误
            if ($re['status'] == 0) {
                return $re;
            }

            //业务成功
            if ($re['data']['resultCode'] == "SUCCESS") {

                //交易失败
                if ($re['data']['payStatus'] == "CLOSE") {
                    return [
                        'status' => 3,
                        'message' => $re['data']['errCodeDes'],
                        'data' => $re['data'],
                    ];
                }

                //交易成功
                if ($re['data']['payStatus'] == "FINISH") {
                    return [
                        'status' => 1,
                        'message' => '交易成功',
                        'data' => $re['data'],
                    ];
                }

                //用户输入密码
                if ($re['data']['payStatus'] == "PROCESSING") {
                    return [
                        'status' => 2,
                        'message' => '请用户输入密码',
                        'data' => $re['data'],
                    ];
                }


            } else {
                return [
                    'status' => 0,
                    'message' => $re['data']['errCodeDes']
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
            $des_key = $data['des_key'];
            $md_key = $data['md_key'];
            $systemId = $data['systemId'];
            //请求数据
            $data = [
                'merchantNo' => $merchant_no,
                'businessCode' => 'AGGRE',
                'version' => '3.0',
                'outTradeNo' => $out_trade_no,
            ];

            $obj = new BaseController();
            $obj->des_key = $des_key;
            $obj->md_key = $md_key;
            $obj->systemId = $systemId;
            $re = $obj->execute($data, $url);

            //系统错误
            if ($re['status'] == 0) {
                return $re;
            }
            //业务成功
            if ($re['data']['resultCode'] == "SUCCESS") {
                //交易失败
                if ($re['data']['payStatus'] == "CLOSE") {
                    return [
                        'status' => 3,
                        'message' => $re['data']['errCodeDes'],
                        'data' => $re['data'],
                    ];
                }
                //交易成功
                if ($re['data']['payStatus'] == "FINISH") {
                    return [
                        'status' => 1,
                        'message' => '交易成功',
                        'data' => $re['data'],
                    ];
                }

                //用户输入密码
                if ($re['data']['payStatus'] == "PROCESSING") {
                    return [
                        'status' => 2,
                        'message' => '请用户输入密码',
                        'data' => $re['data'],
                    ];
                }


                //已经退款
                if ($re['data']['payStatus'] == "REFUND") {
                    return [
                        'status' => 4,
                        'message' => '已经退款',
                        'data' => $re['data'],
                    ];
                }


            } else {
                return [
                    'status' => 0,
                    'message' => $re['data']['errCodeDes']
                ];
            }


            return [
                'status' => 0,
                'message' => '其他错误',

            ];


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
            $des_key = $data['des_key'];
            $md_key = $data['md_key'];
            $systemId = $data['systemId'];
            $outRefundNo = $data['outRefundNo'];
            $amount = $data['amount'];
            $notifyUrl = $data['notifyUrl'];
            //请求数据
            $data = [
                'merchantNo' => $merchant_no,
                'businessCode' => 'AGGRE',
                'version' => '3.0',
                'outTradeNo' => $out_trade_no,
                'outRefundNo' => $outRefundNo,
                'amount' => $amount * 100,//分
                'notifyUrl' => $notifyUrl,
            ];

            $obj = new BaseController();
            $obj->des_key = $des_key;
            $obj->md_key = $md_key;
            $obj->systemId = $systemId;
            $re = $obj->execute($data, $url);

            //系统错误
            if ($re['status'] == 0) {
                return $re;
            }

            //业务成功
            if ($re['data']['resultCode'] == "SUCCESS") {
                return [
                    'status' => 1,
                    'data' => $re['data']
                ];
            } else {
                return [
                    'status' => 0,
                    'message' => $re['data']['errCodeDes']
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
            $des_key = $data['des_key'];
            $md_key = $data['md_key'];
            $systemId = $data['systemId'];
            $outRefundNo = $data['outRefundNo'];
            //请求数据
            $data = [
                'merchantNo' => $merchant_no,
                'businessCode' => 'AGGRE',
                'version' => '3.0',
                'outRefundNo' => $outRefundNo,
            ];

            $obj = new BaseController();
            $obj->des_key = $des_key;
            $obj->md_key = $md_key;
            $obj->systemId = $systemId;
            $re = $obj->execute($data, $url);

            //系统错误
            if ($re['status'] == 0) {
                return $re;
            }

            //业务成功
            if ($re['data']['resultCode'] == "SUCCESS") {
                //退款失败
                if ($re['data']['payStatus'] == "CLOSE") {
                    return [
                        'status' => 3,
                        'message' => $re['data']['errCodeDes'],
                        'data' => $re['data'],
                    ];
                }
                //退款成功
                if ($re['data']['payStatus'] == "FINISH") {
                    return [
                        'status' => 1,
                        'message' => '退款成功',
                        'data' => $re['data'],
                    ];
                }

                //退款申请中
                if ($re['data']['payStatus'] == "PROCESSING") {
                    return [
                        'status' => 2,
                        'message' => '退款申请中',
                        'data' => $re['data'],
                    ];
                }

            } else {
                return [
                    'status' => 0,
                    'message' => $re['data']['errCodeDes']
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
            $des_key = $data['des_key'];
            $md_key = $data['md_key'];
            $systemId = $data['systemId'];
            //请求数据
            $data = [
                'merchantNo' => $merchant_no,
                'businessCode' => 'AGGRE',
                'version' => '3.0',
                'outTradeNo' => $out_trade_no,
                'amount' => $total_amount * 100,
                'remark' => $remark,//
                'returnParams' => $returnParams,
                'businessType' => '00',//返回二维码
                'successNotifyUrl' => $notify_url,
            ];

            $obj = new BaseController();
            $obj->des_key = $des_key;
            $obj->md_key = $md_key;
            $obj->systemId = $systemId;
            $re = $obj->execute($data, $url);
            //系统错误
            if ($re['status'] == 0) {
                return $re;
            }

            //业务成功
            if ($re['data']['resultCode'] == "SUCCESS") {
                return [
                    'status' => 1,
                    'message' => '返回成功',
                    'code_url' => $re['data']['scanUrl'],
                    'data' => $re['data']
                ];

            } else {
                return [
                    'status' => 0,
                    'message' => $re['data']['errCodeDes']
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
            $pi_type = $data['pay_type'];
            $merchant_no = $data['merchant_no'];
            $returnParams = $data['return_params'];//原样返回
            $des_key = $data['des_key'];
            $md_key = $data['md_key'];
            $systemId = $data['systemId'];
            //请求数据
            $data = [
                'merchantNo' => $merchant_no,
                'businessCode' => 'AGGRE',
                'version' => '3.0',
                'outTradeNo' => $out_trade_no,
                'amount' => $total_amount * 100,
                'currency' => 'RMB',
                'openId' => $userId,
                'piType' => $pi_type,
                'virtualType' => '01',
                'remark' => $remark,//
                'returnParams' => $returnParams,
                'deviceInfo' => json_encode([
                    'type' => 'DT03',
                    'ip' => get_server_ip(),
                    'imei' => $merchant_no,

                ]),
                'outTradeIp' => get_client_ip(),
                'productName' => $shop_name,//购买的商品
                'notifyUrl' => $notify_url,
                'gatewayPayMethod' => $data['gatewayPayMethod'],
                'appId' => $data['appId'],
            ];

            $obj = new BaseController();
            $obj->des_key = $des_key;
            $obj->md_key = $md_key;
            $obj->systemId = $systemId;
            $re = $obj->execute($data, $url);

            //系统错误
            if ($re['status'] == 0) {
                return $re;
            }

            //业务成功
            if ($re['data']['resultCode'] == "SUCCESS") {
                return [
                    'status' => 1,
                    'message' => '请求成功',
                    'data' => $re['data'],
                ];

            } else {
                return [
                    'status' => 0,
                    'message' => $re['data']['errCodeDes']
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