<?php

namespace App\Api\Controllers\Deposit;

use Alipayopen\Sdk\AopClient;
use Alipayopen\Sdk\Request\AlipayFundAuthOperationCancelRequest;
use Alipayopen\Sdk\Request\AlipayFundAuthOperationDetailQueryRequest;
use Alipayopen\Sdk\Request\AlipayFundAuthOrderFreezeRequest;
use Alipayopen\Sdk\Request\AlipayTradePayRequest;
use Alipayopen\Sdk\Request\AlipayTradeQueryRequest;
use Alipayopen\Sdk\Request\AlipayTradeRefundRequest;
use App\Http\Controllers\Controller;

/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2019/3/30
 * Time: 3:45 PM
 */
class AliDepositController extends Controller
{

    //支付宝扫码预授权  0 系统错 1成功 2 等待用户确认 3失败
    public function base_fund_freeze($data)
    {
        try {
            $app_id = isset($data['app_id']) ? $data['app_id'] : "";
            $rsa_private_key = isset($data['rsa_private_key']) ? $data['rsa_private_key'] : "";
            $alipay_rsa_public_key = isset($data['alipay_rsa_public_key']) ? $data['alipay_rsa_public_key'] : "";
            $alipay_gateway = isset($data['alipay_gateway']) ? $data['alipay_gateway'] : "";
            $notify_url = isset($data['notify_url']) ? $data['notify_url'] : "";
            $app_auth_token = isset($data['app_auth_token']) ? $data['app_auth_token'] : "";
            $out_order_no = isset($data['out_order_no']) ? $data['out_order_no'] : "";
            $out_request_no = isset($data['out_request_no']) ? $data['out_request_no'] : "";
            $auth_code = isset($data['auth_code']) ? $data['auth_code'] : "";
            $auth_code_type = isset($data['auth_code_type']) ? $data['auth_code_type'] : "bar_code";
            $order_title = isset($data['order_title']) ? $data['order_title'] : "";
            $amount = isset($data['amount']) ? $data['amount'] : "";
            $pay_timeout = isset($data['pay_timeout']) ? $data['pay_timeout'] : "";
            $payee_user_id = isset($data['payee_user_id']) ? $data['payee_user_id'] : "";
            $product_code = isset($data['product_code']) ? $data['product_code'] : "PRE_AUTH";
            $sys_service_provider_id = isset($data['sys_service_provider_id']) ? $data['sys_service_provider_id'] : "";

            $aop = new AopClient();
            $aop->apiVersion = "2.0";
            $aop->appId = $app_id;
            $aop->rsaPrivateKey = $rsa_private_key;
            $aop->alipayrsaPublicKey = $alipay_rsa_public_key;
            $aop->method = 'alipay.fund.auth.order.freeze';
            $aop->signType = "RSA2";//升级算法
            $aop->gatewayUrl = $alipay_gateway;
            $aop->format = "json";
            $aop->charset = "GBK";
            $aop->version = "2.0";


            $requests = new AlipayFundAuthOrderFreezeRequest();
            $requests->setNotifyUrl($notify_url);
            //提交到支付宝
            $re_data = [
                'out_order_no' => $out_order_no,
                'out_request_no' => $out_request_no,
                'auth_code' => $auth_code,
                'auth_code_type' => $auth_code_type,
                'order_title' => $order_title,
                'amount' => number_format($amount, 2, '.', ''),
                'pay_timeout' => $pay_timeout,
                'payee_user_id' => $payee_user_id,
                'product_code' => $product_code,
                'extend_params' => [
                    'sys_service_provider_id' => $sys_service_provider_id,
                ]
            ];

            //  dd($re_data);
            $requests->setBizContent(json_encode($re_data));
            $result = $aop->execute($requests, null, $app_auth_token);
            $responseNode = str_replace(".", "_", $requests->getApiMethodName()) . "_response";
            $resultCode = $result->$responseNode->code;

            //授权成功
            if ($resultCode == '10000') {
                return [
                    'status' => 1,
                    'message' => '授权成功',
                    'data' => [
                        'amount' => $amount,
                        'auth_no' => $result->$responseNode->auth_no,
                        'operation_id' => $result->$responseNode->operation_id,
                        'out_order_no' => $result->$responseNode->out_order_no,
                        'out_request_no' => $result->$responseNode->out_request_no,
                        'gmt_trans' => $result->$responseNode->gmt_trans,
                        'payer_user_id' => $result->$responseNode->payer_user_id,
                        'payer_logon_id' => $result->$responseNode->payer_logon_id,
                    ]
                ];
            } elseif ($resultCode == '10003') {
                //等待用户授权
                return [
                    'status' => 2,
                    'message' => '等待用户确认授权',
                    'data' => [
                        'amount' => $amount,
                        'auth_no' => $result->$responseNode->auth_no,
                        'operation_id' => $result->$responseNode->operation_id,
                        'out_order_no' => $result->$responseNode->out_order_no,
                        'out_request_no' => $result->$responseNode->out_request_no,
                    ]
                ];

            } elseif ($resultCode == '40004') {
                //授权失败
                return [
                    'status' => 3,
                    'message' => $result->$responseNode->sub_msg
                ];

            } else {
                //未知异常
                return [
                    'status' => 3,
                    'message' => '未知异常'
                ];
            }

        } catch (\Exception $exception) {

            return [
                'status' => 0,
                'message' => $exception->getMessage()
            ];
        }

    }


    //支付宝扫码预授权查询  0 系统错 1成功 2 等待用户确认 3失败
    public function base_fund_order_query($data)
    {
        try {
            $app_id = isset($data['app_id']) ? $data['app_id'] : "";
            $rsa_private_key = isset($data['rsa_private_key']) ? $data['rsa_private_key'] : "";
            $alipay_rsa_public_key = isset($data['alipay_rsa_public_key']) ? $data['alipay_rsa_public_key'] : "";
            $alipay_gateway = isset($data['alipay_gateway']) ? $data['alipay_gateway'] : "";
            $notify_url = isset($data['notify_url']) ? $data['notify_url'] : "";
            $app_auth_token = isset($data['app_auth_token']) ? $data['app_auth_token'] : "";
            $out_order_no = isset($data['out_order_no']) ? $data['out_order_no'] : "";
            $out_request_no = isset($data['out_request_no']) ? $data['out_request_no'] : "";
            $operation_id = isset($data['operation_id']) ? $data['operation_id'] : "";
            $auth_no = isset($data['auth_no']) ? $data['auth_no'] : "bar_code";

            $aop = new AopClient();
            $aop->apiVersion = "2.0";
            $aop->appId = $app_id;
            $aop->rsaPrivateKey = $rsa_private_key;
            $aop->alipayrsaPublicKey = $alipay_rsa_public_key;
            $aop->method = 'alipay.fund.auth.operation.detail.query';
            $aop->signType = "RSA2";//升级算法
            $aop->gatewayUrl = $alipay_gateway;
            $aop->format = "json";
            $aop->charset = "GBK";
            $aop->version = "2.0";


            $requests = new AlipayFundAuthOperationDetailQueryRequest();
            $requests->setNotifyUrl($notify_url);
            //提交到支付宝
            $re_data = [
                'out_order_no' => $out_order_no,
                'operation_id' => $operation_id,
                'auth_no' => $auth_no,
                'out_request_no' => $out_request_no,
            ];

            //  dd($re_data);
            $requests->setBizContent(json_encode($re_data));
            $result = $aop->execute($requests, null, $app_auth_token);
            $responseNode = str_replace(".", "_", $requests->getApiMethodName()) . "_response";
            $resultCode = $result->$responseNode->code;


            //查询成功
            if ($resultCode == '10000') {
                $status = $result->$responseNode->status;
                //授权成功
                if ($status == "SUCCESS") {

                    return [
                        'status' => 1,
                        'message' => '授权成功',
                        'data' => [
                            'auth_no' => $result->$responseNode->auth_no,
                            'operation_id' => $result->$responseNode->operation_id,
                            'out_order_no' => $result->$responseNode->out_order_no,
                            'out_request_no' => $result->$responseNode->out_request_no,
                            'gmt_trans' => $result->$responseNode->gmt_trans,
                            'payer_user_id' => $result->$responseNode->payer_user_id,
                            'payer_logon_id' => $result->$responseNode->payer_logon_id,
                        ]
                    ];
                } elseif ($status == "INIT") {
                    return [
                        'status' => 2,
                        'message' => '等待用户确认授权',
                        'data' => [
                            'auth_no' => $auth_no,
                            'operation_id' => $operation_id,
                            'out_order_no' => $out_order_no,
                            'out_request_no' => $out_request_no,
                        ]
                    ];
                } else {
                    return [
                        'status' => 3,
                        'message' => '用户关闭授权',
                        'data' => [
                            'auth_no' => $auth_no,
                            'operation_id' => $operation_id,
                            'out_order_no' => $out_order_no,
                            'out_request_no' => $out_request_no,
                        ]
                    ];
                }

            } elseif ($resultCode == '10003') {
                //等待用户授权
                return [
                    'status' => 2,
                    'message' => '等待用户确认授权',
                    'data' => [
                        'auth_no' => $auth_no,
                        'operation_id' => $operation_id,
                        'out_order_no' => $out_order_no,
                        'out_request_no' => $out_request_no,
                    ]
                ];

            } elseif ($resultCode == '40004') {
                //查询失败
                return [
                    'status' => 3,
                    'message' => $result->$responseNode->sub_msg
                ];

            } else {
                //未知异常
                return [
                    'status' => 3,
                    'message' => '未知异常'
                ];
            }

        } catch (\Exception $exception) {

            return [
                'status' => 0,
                'message' => $exception->getMessage()
            ];
        }

    }


    // 支付宝扫码预授权查询 资金授权撤销接口 alipay.fund.auth.operation.cancel
    // 0 系统异常 1 成功 3 失败
    public function base_fund_cancel($data)
    {
        try {
            $app_id = isset($data['app_id']) ? $data['app_id'] : "";
            $rsa_private_key = isset($data['rsa_private_key']) ? $data['rsa_private_key'] : "";
            $alipay_rsa_public_key = isset($data['alipay_rsa_public_key']) ? $data['alipay_rsa_public_key'] : "";
            $alipay_gateway = isset($data['alipay_gateway']) ? $data['alipay_gateway'] : "";
            $notify_url = isset($data['notify_url']) ? $data['notify_url'] : "";
            $app_auth_token = isset($data['app_auth_token']) ? $data['app_auth_token'] : "";
            $out_order_no = isset($data['out_order_no']) ? $data['out_order_no'] : "";
            $out_request_no = isset($data['out_request_no']) ? $data['out_request_no'] : "";
            $operation_id = isset($data['operation_id']) ? $data['operation_id'] : "";
            $auth_no = isset($data['auth_no']) ? $data['auth_no'] : "";
            $remark = isset($data['remark']) ? $data['remark'] : "预授权撤销";


            $aop = new AopClient();
            $aop->apiVersion = "2.0";
            $aop->appId = $app_id;
            $aop->rsaPrivateKey = $rsa_private_key;
            $aop->alipayrsaPublicKey = $alipay_rsa_public_key;
            $aop->method = 'alipay.fund.auth.operation.cancel';
            $aop->signType = "RSA2";//升级算法
            $aop->gatewayUrl = $alipay_gateway;
            $aop->format = "json";
            $aop->charset = "GBK";
            $aop->version = "2.0";


            $requests = new AlipayFundAuthOperationCancelRequest();
            $requests->setNotifyUrl($notify_url);
            //提交到支付宝
            $re_data = [
                'auth_no' => $auth_no,
                'operation_id' => $operation_id,
                'out_order_no' => $out_order_no,
                'out_request_no' => $out_request_no,
                'remark' => $remark,
            ];

            // dd($re_data);
            $requests->setBizContent(json_encode($re_data));
            $result = $aop->execute($requests, null, $app_auth_token);
            $responseNode = str_replace(".", "_", $requests->getApiMethodName()) . "_response";
            $resultCode = $result->$responseNode->code;

            //异常
            if ($resultCode == 40004) {
                return [
                    'status' => 3,
                    'message' => $result->$responseNode->msg . $result->$responseNode->sub_code,
                ];
            }
            //撤销成功
            if (!empty($resultCode) && $resultCode == 10000) {
                $re_data['action'] = $result->$responseNode->action;
                return [
                    'status' => 1,
                    'message' => '撤销成功',
                    'data' => $re_data
                ];
            } else {
                return [
                    'status' => 3,
                    'message' => $result->$responseNode->sub_msg,
                ];
            }


        } catch (\Exception $exception) {

            return [
                'status' => 0,
                'message' => $exception->getMessage()
            ];
        }

    }

    //押金解冻转支付，交易创建并支付接口 alipay.trade.pay
    public function base_fund_pay($data)
    {
        try {
            $app_id = isset($data['app_id']) ? $data['app_id'] : "";
            $rsa_private_key = isset($data['rsa_private_key']) ? $data['rsa_private_key'] : "";
            $alipay_rsa_public_key = isset($data['alipay_rsa_public_key']) ? $data['alipay_rsa_public_key'] : "";
            $alipay_gateway = isset($data['alipay_gateway']) ? $data['alipay_gateway'] : "";
            $notify_url = isset($data['notify_url']) ? $data['notify_url'] : "";
            $app_auth_token = isset($data['app_auth_token']) ? $data['app_auth_token'] : "";
            $out_trade_no = isset($data['out_trade_no']) ? $data['out_trade_no'] : "";
            $auth_no = isset($data['auth_no']) ? $data['auth_no'] : "bar_code";
            $total_amount = isset($data['total_amount']) ? $data['total_amount'] : "";
            $product_code = isset($data['product_code']) ? $data['product_code'] : "PRE_AUTH";
            $buyer_id = isset($data['buyer_id']) ? $data['buyer_id'] : "";
            $seller_id = isset($data['seller_id']) ? $data['seller_id'] : "押金转支付";
            $subject = isset($data['subject']) ? $data['subject'] : "押金转支付";
            $auth_confirm_mode = isset($data['auth_confirm_mode']) ? $data['auth_confirm_mode'] : "COMPLETE";
            $pay_amount = isset($data['pay_amount']) ? $data['pay_amount'] : "";

            $aop = new AopClient();
            $aop->apiVersion = "2.0";
            $aop->appId = $app_id;
            $aop->rsaPrivateKey = $rsa_private_key;
            $aop->alipayrsaPublicKey = $alipay_rsa_public_key;
            $aop->method = 'alipay.trade.pay';
            $aop->signType = "RSA2";//升级算法
            $aop->gatewayUrl = $alipay_gateway;
            $aop->format = "json";
            $aop->charset = "GBK";
            $aop->version = "2.0";


            $requests = new AlipayTradePayRequest();
            $requests->setNotifyUrl($notify_url);
            //提交到支付宝
            $re_data = [
                'out_trade_no' => $out_trade_no,
                'total_amount' => number_format($pay_amount, 2, '.', ''),
                'product_code' => $product_code,
                'auth_no' => $auth_no,
                'subject' => $subject,
                'buyer_id' => $buyer_id,
                'seller_id' => $seller_id,
                'auth_confirm_mode' => $auth_confirm_mode
            ];

            $requests->setBizContent(json_encode($re_data));
            $result = $aop->execute($requests, null, $app_auth_token);
            $responseNode = str_replace(".", "_", $requests->getApiMethodName()) . "_response";
            $resultCode = $result->$responseNode->code;

            //异常
            if ($resultCode == 40004) {
                return [
                    'status' => 3,
                    'message' => $result->$responseNode->msg . $result->$responseNode->sub_code,
                ];
            }
            //支付成功
            if (!empty($resultCode) && $resultCode == 10000) {
                $buyer_id = $result->$responseNode->buyer_user_id;
                $buyer_logon_id = $result->$responseNode->buyer_logon_id;
                $payment_method = $result->$responseNode->fund_bill_list[0]->fund_channel;
                $trade_no = $result->$responseNode->trade_no;
                $gmt_payment = $result->$responseNode->gmt_payment;
                $invoice_amount = $result->$responseNode->invoice_amount;//交易中可给用户开具发票的金额

                return [
                    'status' => 1,
                    'message' => '支付成功',
                    'data' => [
                        'out_trade_no' => $out_trade_no,
                        'trade_no' => $trade_no,
                        'total_amount' => $total_amount,
                        'invoice_amount' => $invoice_amount,
                        'pay_time' => $gmt_payment,
                        'buyer_user_id' => $buyer_id,
                        'buyer_logon_id' => $buyer_logon_id,
                        'payment_method' => $payment_method,
                    ]
                ];
            }
            //正在支付
            if (!empty($resultCode) && $resultCode == 10003) {
                return [
                    'status' => 2,
                    'message' => '正在支付',
                    'data' => [
                        'out_trade_no' => $out_trade_no,
                    ]
                ];

            }
            $msg = $result->$responseNode->sub_msg;//错误信息

            //其他支付失败
            return [
                'status' => 3,
                'message' => $msg,
                'data' => [
                    'out_trade_no' => $out_trade_no,
                ]
            ];


        } catch (\Exception $exception) {

            return [
                'status' => 0,
                'message' => $exception->getMessage()
            ];
        }

    }


    //押金解冻转支付，交易查询
    public function base_fund_pay_query($data)
    {
        try {
            $app_id = isset($data['app_id']) ? $data['app_id'] : "";
            $rsa_private_key = isset($data['rsa_private_key']) ? $data['rsa_private_key'] : "";
            $alipay_rsa_public_key = isset($data['alipay_rsa_public_key']) ? $data['alipay_rsa_public_key'] : "";
            $alipay_gateway = isset($data['alipay_gateway']) ? $data['alipay_gateway'] : "";
            $notify_url = isset($data['notify_url']) ? $data['notify_url'] : "";
            $app_auth_token = isset($data['app_auth_token']) ? $data['app_auth_token'] : "";
            $out_trade_no = isset($data['out_trade_no']) ? $data['out_trade_no'] : "";
            $total_amount = isset($data['total_amount']) ? $data['total_amount'] : "";

            $aop = new AopClient();
            $aop->apiVersion = "2.0";
            $aop->appId = $app_id;
            $aop->rsaPrivateKey = $rsa_private_key;
            $aop->alipayrsaPublicKey = $alipay_rsa_public_key;
            $aop->method = 'alipay.trade.query';
            $aop->signType = "RSA2";//升级算法
            $aop->gatewayUrl = $alipay_gateway;
            $aop->format = "json";
            $aop->charset = "GBK";
            $aop->version = "2.0";


            $requests = new AlipayTradeQueryRequest();
            $requests->setNotifyUrl($notify_url);
            //提交到支付宝
            $re_data = [
                'out_trade_no' => $out_trade_no,
            ];

            // dd($re_data);
            $requests->setBizContent(json_encode($re_data));
            $result = $aop->execute($requests, null, $app_auth_token);
            $responseNode = str_replace(".", "_", $requests->getApiMethodName()) . "_response";
            $resultCode = $result->$responseNode->code;

            //异常
            if ($resultCode == 40004) {
                return [
                    'status' => 3,
                    'message' => $result->$responseNode->msg . $result->$responseNode->sub_code,
                ];
            }
            //支付成功
            if ($result->$responseNode->trade_status == "TRADE_SUCCESS") {
                $buyer_id = $result->$responseNode->buyer_user_id;
                $buyer_logon_id = $result->$responseNode->buyer_logon_id;
                $payment_method = $result->$responseNode->fund_bill_list[0]->fund_channel;
                $trade_no = $result->$responseNode->trade_no;
                $gmt_payment = $result->$responseNode->send_pay_date;
                $invoice_amount = $result->$responseNode->invoice_amount;//交易中可给用户开具发票的金额

                return [
                    'status' => 1,
                    'message' => '支付成功',
                    'data' => [
                        'out_trade_no' => $out_trade_no,
                        'trade_no' => $trade_no,
                        'total_amount' => $total_amount,
                        'invoice_amount' => $invoice_amount,
                        'pay_time' => $gmt_payment,
                        'buyer_user_id' => $buyer_id,
                        'buyer_logon_id' => $buyer_logon_id,
                        'payment_method' => $payment_method,
                    ]
                ];
            }
            //正在支付
            if ($result->$responseNode->trade_status == "WAIT_BUYER_PAY") {
                return [
                    'status' => 2,
                    'message' => '正在支付',
                    'data' => [
                        'out_trade_no' => $out_trade_no,
                    ]
                ];

            }
            //未付款交易超时关闭，或支付完成后全额退款
            if ($result->$responseNode->trade_status == "TRADE_CLOSED") {
                return [
                    'status' => 3,
                    'message' => '交易超时关闭',
                    'data' => [
                        'out_trade_no' => $out_trade_no,
                    ]
                ];

            }

            $msg = $result->$responseNode->sub_msg;//错误信息

            //其他支付失败
            return [
                'status' => 3,
                'message' => $msg,
                'data' => [
                    'out_trade_no' => $out_trade_no,
                ]
            ];


        } catch (\Exception $exception) {

            return [
                'status' => 0,
                'message' => $exception->getMessage()
            ];
        }

    }


    //退款接口
    public function refund($data)
    {
        try {
            $app_id = isset($data['app_id']) ? $data['app_id'] : "";
            $rsa_private_key = isset($data['rsa_private_key']) ? $data['rsa_private_key'] : "";
            $alipay_rsa_public_key = isset($data['alipay_rsa_public_key']) ? $data['alipay_rsa_public_key'] : "";
            $alipay_gateway = isset($data['alipay_gateway']) ? $data['alipay_gateway'] : "";
            $notify_url = isset($data['notify_url']) ? $data['notify_url'] : "";
            $app_auth_token = isset($data['app_auth_token']) ? $data['app_auth_token'] : "";
            $out_trade_no = isset($data['out_trade_no']) ? $data['out_trade_no'] : "";
            $refund_amount = isset($data['refund_amount']) ? $data['refund_amount'] : "";


            $aop = new AopClient();
            $aop->apiVersion = "2.0";
            $aop->appId = $app_id;
            $aop->rsaPrivateKey = $rsa_private_key;
            $aop->alipayrsaPublicKey = $alipay_rsa_public_key;
            $aop->method = 'alipay.trade.refund';
            $aop->signType = "RSA2";//升级算法
            $aop->gatewayUrl = $alipay_gateway;
            $aop->format = "json";
            $aop->charset = "GBK";
            $aop->version = "2.0";


            $requests = new AlipayTradeRefundRequest();
            $requests->setNotifyUrl($notify_url);
            //提交到支付宝
            $re_data = [
                'out_trade_no' => $out_trade_no,
                'refund_amount' => number_format($refund_amount, 2, '.', ''),
                'out_request_no' => $out_trade_no . rand(100, 999),
            ];


            $requests->setBizContent(json_encode($re_data));
            $result = $aop->execute($requests, null, $app_auth_token);
            $responseNode = str_replace(".", "_", $requests->getApiMethodName()) . "_response";
            $resultCode = $result->$responseNode->code;

            //异常
            if ($resultCode == 40004) {
                return [
                    'status' => 3,
                    'message' => $result->$responseNode->msg . $result->$responseNode->sub_code,
                ];
            }
            //退款成功
            if (!empty($resultCode) && $resultCode == 10000) {
                return [
                    'status' => 1,
                    'message' => '退款成功',
                    'data' => $re_data
                ];
            } else {
                return [
                    'status' => 3,
                    'message' => $result->$responseNode->sub_msg,
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