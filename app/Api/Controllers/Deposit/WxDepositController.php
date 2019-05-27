<?php

namespace App\Api\Controllers\Deposit;

use App\Api\Controllers\Weixin\BaseController;
use App\Http\Controllers\Controller;
use function EasyWeChat\Kernel\Support\get_client_ip;
use Illuminate\Support\Facades\Log;

/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2019/3/30
 * Time: 3:45 PM
 */
class WxDepositController extends Controller
{

    //微信扫码预授权  0 系统错 1成功 2 等待用户确认 3失败
    public function base_fund_freeze($data)
    {
        try {
            $url = isset($data['request_url']) ? $data['request_url'] : 'https://api.mch.weixin.qq.com/deposit/micropay';
            $key = isset($data['key']) ? $data['key'] : '';
            $deposit = isset($data['deposit']) ? $data['deposit'] : 'Y';
            $useCert = false;
            $appid = isset($data['app_id']) ? $data['app_id'] : '';
            $sub_appid = isset($data['sub_app_id']) ? $data['sub_app_id'] : '';
            $mch_id = isset($data['mch_id']) ? $data['mch_id'] : 'Y';
            $sub_mch_id = isset($data['sub_mch_id']) ? $data['sub_mch_id'] : '';
            $device_info = isset($data['device_info']) ? $data['device_info'] : '';
            $out_order_no = isset($data['out_order_no']) ? $data['out_order_no'] : '';
            $body = isset($data['body']) ? $data['body'] : '';
            $attach = isset($data['attach']) ? $data['attach'] : '备注';
            $total_fee = isset($data['total_fee']) ? $data['total_fee'] : '';
            $auth_code = isset($data['auth_code']) ? $data['auth_code'] : '';


            //公共配置
            $re_data = [
                'deposit' => $deposit,
                'appid' => $appid,
                'mch_id' => $mch_id,
                'sub_mch_id' => $sub_mch_id,
                'device_info' => $device_info,
                'nonce_str' => "" . time() . "",
                'body' => $body,
                'attach' => $attach,
                'out_trade_no' => $out_order_no,
                'total_fee' => number_format($total_fee * 100, 0, '.', ''),
                'fee_type' => 'CNY',
                'spbill_create_ip' => get_client_ip(),
                'sign_type' => 'HMAC-SHA256',
            ];

            //微信扫码
            if ($auth_code) {
                $re_data['auth_code'] = $auth_code;

            } else {
                //微信刷脸
                $url = 'https://api.mch.weixin.qq.com/deposit/facepay';
                $re_data['face_code'] = $auth_code;
            }
            $obj = new BaseController();
            $re_data['sign'] = $obj->MakeSign($re_data, $key, 'HMAC-SHA256');
            $xml = $obj->ToXml($re_data);
            $return_data = $obj::postXmlCurl($re_data, $xml, $url, $useCert, $second = 30);
            $return_data = $obj::xml_to_array($return_data);

            if ($return_data['return_code'] == "FAIL") {
                //授权失败
                return [
                    'status' => 3,
                    'message' => $return_data['return_msg'],
                ];
            }

            //押金支付成功
            if ($return_data['result_code'] == "SUCCESS") {
                return [
                    'status' => 1,
                    'message' => '押金支付成功',
                    'data' => [
                        'amount' => $total_fee,
                        'out_order_no' => $out_order_no,
                        'gmt_trans' => "" . date('Y-m-d H:i:s', strtotime($return_data['time_end'])) . "",
                        'transaction_id' => $return_data['transaction_id'],
                        'openid' => isset($return_data['openid']) ? $return_data['openid'] : "",
                        'sub_openid' => isset($return_data['openid']) ? $return_data['openid'] : "",
                    ]

                ];

            } else {
                //等待支付+失败
                if ($return_data['err_code'] == "USERPAYING") {
                    //等待中
                    return [
                        'status' => 2,
                        'message' => $return_data['err_code_des'],
                        'data' => [
                            'out_order_no' => $out_order_no,
                            'amount' => $total_fee,
                        ]

                    ];
                } else {
                    //失败
                    return [
                        'status' => 3,
                        'message' => $return_data['err_code_des']
                    ];
                }
            }


        } catch (\Exception $exception) {

            return [
                'status' => 3,
                'message' => $exception->getMessage()
            ];
        }
    }


    //微信预授权查询  0 系统错 1成功 2 等待用户确认 3失败
    public function base_fund_order_query($data)
    {
        try {
            $url = isset($data['request_url']) ? $data['request_url'] : 'https://api.mch.weixin.qq.com/deposit/orderquery';
            $key = isset($data['key']) ? $data['key'] : '';
            $useCert = false;
            $appid = isset($data['app_id']) ? $data['app_id'] : '';
            $mch_id = isset($data['mch_id']) ? $data['mch_id'] : 'Y';
            $sub_mch_id = isset($data['sub_mch_id']) ? $data['sub_mch_id'] : '';
            $out_order_no = isset($data['out_order_no']) ? $data['out_order_no'] : '';


            //公共配置
            $config = [
                'appid' => $appid,
                'mch_id' => $mch_id,
                'sub_mch_id' => $sub_mch_id,
                'nonce_str' => $out_order_no,
                'out_trade_no' => $out_order_no,
                'sign_type' => 'HMAC-SHA256'
            ];
            $obj = new BaseController();
            $config['sign'] = $obj->MakeSign($config, $key, 'HMAC-SHA256');
            $xml = $obj->ToXml($config);
            $return_data = $obj::postXmlCurl($config, $xml, $url, $useCert, $second = 30);
            $return_data = $obj::xml_to_array($return_data);

            if ($return_data['return_code'] == "FAIL") {
                //查询失败
                return [
                    'status' => 3,
                    'message' => $return_data['return_msg'],
                ];
            }

            //成功
            if ($return_data['trade_state'] == 'SUCCESS') {
                return [
                    'status' => 1,
                    'message' => '押金支付成功',
                    'data' => [
                        'out_order_no' => $out_order_no,
                        'gmt_trans' => "" . "" . date('Y-m-d H:i:s', strtotime($return_data['time_end'])) . "" . "",
                        'transaction_id' => $return_data['transaction_id'],
                        'openid' => isset($return_data['openid']) ? $return_data['openid'] : "",
                        'sub_openid' => isset($return_data['openid']) ? $return_data['openid'] : "",
                    ]
                ];

                //等待用户输入密码
            } elseif ($return_data['trade_state'] == "USERPAYING") {
                return [
                    'status' => 2,
                    'message' => '等待用户输入密码',
                    'data' => [
                        'out_order_no' => $out_order_no,
                    ]
                ];
                //用户取消支付
            } elseif ($return_data['trade_state'] == "NOTPAY") {
                return [
                    'status' => 3,
                    'message' => '用户取消支付',
                ];

                //订单关闭
            } elseif ($return_data['trade_state'] == "CLOSED") {
                return [
                    'status' => 3,
                    'message' => '订单关闭',
                ];

                //订单撤销
            } elseif ($return_data['trade_state'] == "REVOKED") {
                return [
                    'status' => 3,
                    'message' => '订单撤销',
                ];
            } else {
                return [
                    'status' => 3,
                    'message' => '押金订单失败',
                ];

            }


        } catch (\Exception $exception) {

            return [
                'status' => 0,
                'message' => $exception->getMessage()
            ];
        }

    }


    //撤销  0 系统异常 1 成功 3 失败
    public function base_fund_cancel($data)
    {
        try {
            $url = isset($data['request_url']) ? $data['request_url'] : 'https://api.mch.weixin.qq.com/deposit/reverse';
            $key = isset($data['key']) ? $data['key'] : '';
            $useCert = true;
            $appid = isset($data['app_id']) ? $data['app_id'] : '';
            $mch_id = isset($data['mch_id']) ? $data['mch_id'] : 'Y';
            $sub_mch_id = isset($data['sub_mch_id']) ? $data['sub_mch_id'] : '';
            $out_order_no = isset($data['out_order_no']) ? $data['out_order_no'] : '';
            $sslKeyPath = isset($data['key_path']) ? $data['key_path'] : '';
            $sslCertPath = isset($data['cert_path']) ? $data['cert_path'] : '';

            //公共配置
            $config = [
                'appid' => $appid,
                'mch_id' => $mch_id,
                'sub_mch_id' => $sub_mch_id,
                'nonce_str' => $out_order_no,
                'out_trade_no' => $out_order_no,
                'sign_type' => 'HMAC-SHA256'
            ];

            $obj = new BaseController();
            $config['sign'] = $obj->MakeSign($config, $key, 'HMAC-SHA256');
            $xml = $obj->ToXml($config);

            $config['sslCertPath'] = $sslCertPath;
            $config['sslKeyPath'] = $sslKeyPath;


            $return_data = $obj::postXmlCurl($config, $xml, $url, $useCert, $second = 30);
            $return_data = $obj::xml_to_array($return_data);
            Log::info($return_data);
            if ($return_data['return_code'] == "FAIL") {
                //撤销失败
                return [
                    'status' => 3,
                    'message' => $return_data['return_msg'],
                ];
            }

            //撤销成功
            if ($return_data['result_code'] == 'SUCCESS') {
                return [
                    'status' => 1,
                    'message' => '撤销成功',
                    'data' => $return_data
                ];
            } else {
                return [
                    'status' => 3,
                    'message' => '押金订单失败',
                    'data' => $return_data,
                ];

            }


        } catch (\Exception $exception) {

            return [
                'status' => 0,
                'message' => $exception->getMessage()
            ];
        }

    }


    //转支付 0 系统错 1成功 2 等待用户确认 3失败
    public function base_fund_pay($data)
    {
        try {
            $url = isset($data['request_url']) ? $data['request_url'] : 'https://api.mch.weixin.qq.com/deposit/consume';
            $key = isset($data['key']) ? $data['key'] : '';
            $useCert = true;
            $appid = isset($data['app_id']) ? $data['app_id'] : '';
            $mch_id = isset($data['mch_id']) ? $data['mch_id'] : 'Y';
            $sub_mch_id = isset($data['sub_mch_id']) ? $data['sub_mch_id'] : '';
            $out_order_no = isset($data['out_order_no']) ? $data['out_order_no'] : '';
            $total_fee = isset($data['total_fee']) ? $data['total_fee'] : '';
            $consume_fee = isset($data['consume_fee']) ? $data['consume_fee'] : '';
            $sslKeyPath = isset($data['key_path']) ? $data['key_path'] : '';
            $sslCertPath = isset($data['cert_path']) ? $data['cert_path'] : '';
            $trade_no = isset($data['trade_no']) ? $data['trade_no'] : '';


            //公共配置
            $re_data = [
                'appid' => $appid,
                'mch_id' => $mch_id,
                'sub_mch_id' => $sub_mch_id,
                'nonce_str' => "" . time() . "",
                'transaction_id' => $trade_no,
                'total_fee' => number_format($total_fee * 100, 0, '.', ''),
                'consume_fee' => number_format($consume_fee * 100, 0, '.', ''),
                'fee_type' => 'CNY',
                'sign_type' => 'HMAC-SHA256',
            ];

            $obj = new BaseController();
            $re_data['sign'] = $obj->MakeSign($re_data, $key, 'HMAC-SHA256');
            $xml = $obj->ToXml($re_data);

            $re_data['sslCertPath'] = $sslCertPath;
            $re_data['sslKeyPath'] = $sslKeyPath;

            $return_data = $obj::postXmlCurl($re_data, $xml, $url, $useCert, $second = 30);
            $return_data = $obj::xml_to_array($return_data);

            if ($return_data['return_code'] == "FAIL") {
                //授权失败
                return [
                    'status' => 3,
                    'message' => $return_data['return_msg'],
                ];
            }

            //押金支付成功
            if ($return_data['result_code'] == "SUCCESS") {
                return [
                    'status' => 1,
                    'message' => '押金支付成功',
                    'data' => [
                        'amount' => $total_fee,
                        'out_order_no' => $out_order_no,
                        'transaction_id' => $return_data['transaction_id'],
                    ]

                ];

            } else {
                //等待支付+失败
                if ($return_data['err_code'] == "USERPAYING") {
                    //等待中
                    return [
                        'status' => 2,
                        'message' => $return_data['err_code_des'],
                        'data' => [
                            'out_order_no' => $out_order_no,
                            'amount' => $total_fee,
                        ]

                    ];
                } else {
                    //失败
                    return [
                        'status' => 3,
                        'message' => $return_data['err_code_des']
                    ];
                }
            }


        } catch (\Exception $exception) {

            return [
                'status' => 3,
                'message' => $exception->getMessage()
            ];
        }
    }


    //微信押金转支付查询  0 系统错 1成功 2 等待用户确认 3失败
    public function base_fund_pay_query($data)
    {
        try {
            $url = isset($data['request_url']) ? $data['request_url'] : 'https://api.mch.weixin.qq.com/deposit/orderquery';
            $key = isset($data['key']) ? $data['key'] : '';
            $useCert = false;
            $appid = isset($data['app_id']) ? $data['app_id'] : '';
            $mch_id = isset($data['mch_id']) ? $data['mch_id'] : 'Y';
            $sub_mch_id = isset($data['sub_mch_id']) ? $data['sub_mch_id'] : '';
            $out_order_no = isset($data['out_order_no']) ? $data['out_order_no'] : '';


            //公共配置
            $config = [
                'appid' => $appid,
                'mch_id' => $mch_id,
                'sub_mch_id' => $sub_mch_id,
                'nonce_str' => $out_order_no,
                'out_trade_no' => $out_order_no,
                'sign_type' => 'HMAC-SHA256'
            ];
            $obj = new BaseController();
            $config['sign'] = $obj->MakeSign($config, $key, 'HMAC-SHA256');
            $xml = $obj->ToXml($config);
            $return_data = $obj::postXmlCurl($config, $xml, $url, $useCert, $second = 30);
            $return_data = $obj::xml_to_array($return_data);

            if ($return_data['return_code'] == "FAIL") {
                //查询失败
                return [
                    'status' => 3,
                    'message' => $return_data['return_msg'],
                ];
            }

            //成功
            if ($return_data['result_code'] == 'SUCCESS') {
                return [
                    'status' => 1,
                    'message' => '支付成功',
                    'data' => [
                        'out_order_no' => $out_order_no,
                        'gmt_trans' => "" . "" . date('Y-m-d H:i:s', strtotime($return_data['time_end'])) . "" . "",
                        'transaction_id' => $return_data['transaction_id'],
                        'openid' => isset($return_data['openid']) ? $return_data['openid'] : "",
                        'sub_openid' => isset($return_data['openid']) ? $return_data['openid'] : "",
                    ]
                ];

                //等待用户输入密码
            } else {
                return [
                    'status' => 3,
                    'message' => $return_data['err_code_des'],
                ];
            }


        } catch (\Exception $exception) {

            return [
                'status' => 0,
                'message' => $exception->getMessage()
            ];
        }

    }


    //退款  0 系统异常 1 成功 3 失败
    public function refund($data)
    {
        try {
            $url = isset($data['request_url']) ? $data['request_url'] : 'https://api.mch.weixin.qq.com/deposit/refund';
            $key = isset($data['key']) ? $data['key'] : '';
            $useCert = true;
            $appid = isset($data['app_id']) ? $data['app_id'] : '';
            $mch_id = isset($data['mch_id']) ? $data['mch_id'] : 'Y';
            $sub_mch_id = isset($data['sub_mch_id']) ? $data['sub_mch_id'] : '';
            $trade_no = isset($data['trade_no']) ? $data['trade_no'] : '';
            $out_refund_no = isset($data['out_refund_no']) ? $data['out_refund_no'] : '';
            $total_fee = isset($data['total_fee']) ? $data['total_fee'] : '';
            $refund_fee = isset($data['refund_fee']) ? $data['refund_fee'] : '';
            $sslKeyPath = isset($data['key_path']) ? $data['key_path'] : '';
            $sslCertPath = isset($data['cert_path']) ? $data['cert_path'] : '';
            //公共配置
            $config = [
                'appid' => $appid,
                'mch_id' => $mch_id,
                'sub_mch_id' => $sub_mch_id,
                'nonce_str' => time(),
                'transaction_id' => $trade_no,
                'out_refund_no' => $out_refund_no,
                'total_fee' => number_format($total_fee * 100, 0, '.', ''),
                'refund_fee' => number_format($refund_fee * 100, 0, '.', ''),
                'sign_type' => 'HMAC-SHA256'
            ];

            $obj = new BaseController();
            $config['sign'] = $obj->MakeSign($config, $key, 'HMAC-SHA256');
            $xml = $obj->ToXml($config);

            $config['sslCertPath'] = $sslCertPath;
            $config['sslKeyPath'] = $sslKeyPath;

            $return_data = $obj::postXmlCurl($config, $xml, $url, $useCert, $second = 30);
            $return_data = $obj::xml_to_array($return_data);

            if ($return_data['return_code'] == "FAIL") {
                //撤销失败
                return [
                    'status' => 3,
                    'message' => $return_data['return_msg'],
                ];
            }

            //退款成功
            if ($return_data['result_code'] == 'SUCCESS') {
                return [
                    'status' => 1,
                    'message' => '退款成功',
                    'data' => $return_data
                ];
            } else {
                return [
                    'status' => 3,
                    'message' => '退款失败',
                    'data' => $return_data,
                ];

            }


        } catch (\Exception $exception) {

            return [
                'status' => 0,
                'message' => $exception->getMessage()
            ];
        }

    }


}