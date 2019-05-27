<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/6/14
 * Time: 下午5:04
 */

namespace App\Api\Controllers\AlipayOpen;


use Alipayopen\Sdk\AopClient;
use Alipayopen\Sdk\Request\AlipayTradeCancelRequest;
use Alipayopen\Sdk\Request\AlipayTradeCreateRequest;
use Alipayopen\Sdk\Request\AlipayTradePayRequest;
use Alipayopen\Sdk\Request\AlipayTradePrecreateRequest;
use Alipayopen\Sdk\Request\AlipayTradeQueryRequest;
use App\Api\Controllers\Config\AlipayIsvConfigController;
use App\Common\MerchantFuwu;
use App\Common\StoreDayMonthOrder;
use App\Models\AlipayAccount;
use App\Models\AlipayAppOauthUsers;
use App\Models\AlipayHbOrder;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class PayController extends BaseController
{

    //官方支付宝扫一扫
    public function scan_pay($data)
    {
        $out_trade_no = $data['out_trade_no'];
        $config_id = $data['config_id'];
        $store_id = $data['store_id'];
        $merchant_id = $data['merchant_id'];
        $code = $data['code'];
        $total_amount = $data['total_amount'];
        $shop_price = $data['shop_price'];
        $remark = $data['remark'];
        $device_id = $data['device_id'];
        $config_type = $data['config_type'];
        $shop_name = $data['shop_name'];
        $shop_desc = $data['shop_desc'];
        $is_fq = $data['is_fq'];

        $alipay_store_id = $data['alipay_store_id'];
        $out_user_id = $data['out_user_id'];
        $app_auth_token = $data['app_auth_token'];
        $config = $data['config'];
        $notify_url = $data['notify_url'];

        $disable_pay_channels = '';//仅用方式
        $aop = new AopClient();
        $aop->apiVersion = "2.0";
        $aop->appId = $config->app_id;
        $aop->rsaPrivateKey = $config->rsa_private_key;
        $aop->alipayrsaPublicKey = $config->alipay_rsa_public_key;
        $aop->method = 'alipay.trade.pay';
        $aop->notify_url = $notify_url;

        $aop->signType = "RSA2";//升级算法
        $aop->gatewayUrl = $config->alipay_gateway;
        $aop->format = "json";
        $aop->charset = "GBK";
        $aop->version = "2.0";

        $requests = new AlipayTradePayRequest();
        $requests->setNotifyUrl($notify_url);
        //提交到支付宝
        if ($alipay_store_id) {
            $data_re = "{" .
                "\"out_trade_no\":\"" . $out_trade_no . "\"," .
                "\"seller_id\":\"" . $out_user_id . "\"," .//商户收款账号
                "\"disable_pay_channels\":\"" . $disable_pay_channels . "\"," .
                "    \"scene\":\"bar_code\"," .
                "    \"auth_code\":\"" . $code . "\"," .
                "    \"subject\":\"" . $shop_name . "\"," .
                "    \"total_amount\":" . $total_amount . "," .
                "    \"timeout_express\":\"90m\"," .
                "    \"body\":\"" . $shop_desc . "\"," .
                "      \"goods_detail\":[{" .
                "        \"goods_id\":\"" . $store_id . "\"," .
                "        \"goods_name\":\"" . $shop_name . "\"," .
                "        \"quantity\":1," .
                "        \"price\":" . $total_amount . "," .
                "        \"body\":\"" . $shop_name . "\"" .
                "        }]," .
                "    \"store_id\":\"" . $store_id . "\"," .
                "    \"shop_id\":\"" . $alipay_store_id . "\"," .
                "    \"terminal_id\":\"" . $device_id . "\"," .
                "    \"operator_id\":\"D_001_" . $merchant_id . "\"," .
                "    \"extend_params\":{" .
                "      \"sys_service_provider_id\":\"" . $config->alipay_pid . "\"" .
                "}" .
                "  }";
        } else {
            $data_re = "{" .
                "\"out_trade_no\":\"" . $out_trade_no . "\"," .
                "\"seller_id\":\"" . $out_user_id . "\"," .//商户收款账号
                "\"disable_pay_channels\":\"" . $disable_pay_channels . "\"," .
                "    \"scene\":\"bar_code\"," .
                "    \"auth_code\":\"" . $code . "\"," .
                "    \"subject\":\"" . $shop_name . "\"," .
                "    \"total_amount\":" . $total_amount . "," .
                "    \"timeout_express\":\"90m\"," .
                "    \"body\":\"" . $shop_desc . "\"," .
                "      \"goods_detail\":[{" .
                "        \"goods_id\":\"" . $store_id . "\"," .
                "        \"goods_name\":\"" . $shop_name . "\"," .
                "        \"quantity\":1," .
                "        \"price\":" . $total_amount . "," .
                "        \"body\":\"" . $shop_name . "\"" .
                "        }]," .
                "    \"store_id\":\"" . $store_id . "\"," .
                "    \"terminal_id\":\"" . $device_id . "\"," .
                "    \"operator_id\":\"D_001_" . $merchant_id . "\"," .
                "    \"extend_params\":{" .
                "      \"sys_service_provider_id\":\"" . $config->alipay_pid . "\"" .
                "}" .
                "  }";
        }


        //分期
        if ($is_fq) {
            $hb_fq_num = $data['is_fq_data']['hb_fq_num'];
            $hb_fq_seller_percent = $data['is_fq_data']['hb_fq_seller_percent'];
            $type = 1006;//分期
            $data_re = "{" .
                "\"out_trade_no\":\"" . $out_trade_no . "\"," .
                "\"seller_id\":\"" . $out_user_id . "\"," .//商户收款账号
                "\"disable_pay_channels\":\"" . $disable_pay_channels . "\"," .
                "    \"scene\":\"bar_code\"," .
                "    \"auth_code\":\"" . $code . "\"," .
                "    \"subject\":\"" . $shop_name . "\"," .
                "    \"total_amount\":" . $total_amount . "," .
                "    \"timeout_express\":\"90m\"," .
                "    \"body\":\"" . $shop_desc . "\"," .
                "      \"goods_detail\":[{" .
                "        \"goods_id\":\"" . $store_id . "\"," .
                "        \"goods_name\":\"" . $shop_name . "\"," .
                "        \"quantity\":1," .
                "        \"price\":" . $total_amount . "," .
                "        \"body\":\"" . $shop_name . "\"" .
                "        }]," .
                "    \"store_id\":\"" . $store_id . "\"," .
                "    \"terminal_id\":\"" . $device_id . "\"," .
                "    \"operator_id\":\"D_001_" . $merchant_id . "\"," .
                "\"extend_params\":{" .
                "\"sys_service_provider_id\":\"" . $config->alipay_pid . "\"," .
                "\"hb_fq_num\":\"" . $hb_fq_num . "\"," .
                "\"hb_fq_seller_percent\":\"" . $hb_fq_seller_percent . "\"" .
                "}" .
                "  }";
        }


        $requests->setBizContent($data_re);


        try {
            $result = $aop->execute($requests, null, $app_auth_token);
            $responseNode = str_replace(".", "_", $requests->getApiMethodName()) . "_response";
            $resultCode = $result->$responseNode->code;
            //异常
            if ($resultCode == 40004) {
                return [
                    'status' => 2,
                    'message' => $result->$responseNode->msg . $result->$responseNode->sub_code,
                    'result_code' => $resultCode,
                ];
            }


            if (!empty($resultCode) && $resultCode == 10000) {
                $buyer_id = $result->$responseNode->buyer_user_id;
                $buyer_logon_id = $result->$responseNode->buyer_logon_id;
                $payment_method = $result->$responseNode->fund_bill_list[0]->fund_channel;
                $trade_no = $result->$responseNode->trade_no;

                return [
                    'status' => 1,
                    'message' => '支付成功',
                    'trade_no' => $trade_no,
                    'result_code' => $resultCode,
                    'buyer_id' => $buyer_id,
                    'buyer_logon_id' => $buyer_logon_id,
                    'payment_method' => $payment_method,
                ];

            }

            //正在支付
            if (!empty($resultCode) && $resultCode == 10003) {
                return [
                    'status' => 3,
                    'message' => '用户输入密码',
                    'result_code' => $resultCode,
                ];
            } else {
                $msg = $result->$responseNode->sub_msg;//错误信息
                return [
                    'status' => 2,
                    'message' => $msg,
                ];
            }
        } catch (\Exception $exception) {
            return [
                'status' => 2,
                'message' => $exception->getMessage() . $exception->getLine()
            ];
        }
    }

    //官方支付宝固定金额二维码
    public function qr_pay($data)
    {
        try {
            $out_trade_no = $data['out_trade_no'];
            $config_id = $data['config_id'];
            $store_id = $data['store_id'];
            $merchant_id = $data['merchant_id'];
            $total_amount = $data['total_amount'];
            $shop_price = $data['shop_price'];
            $remark = $data['remark'];
            $device_id = $data['device_id'];
            $config_type = $data['config_type'];
            $shop_name = $data['shop_name'];
            $shop_desc = $data['shop_desc'];
            $store_name = $data['store_name'];
            $is_fq = $data['is_fq'];

            $alipay_store_id = $data['alipay_store_id'];
            $out_store_id=$data['out_store_id'];
            $out_user_id = $data['out_user_id'];
            $app_auth_token = $data['app_auth_token'];
            $config = $data['config'];
            $notify_url = $data['notify_url'];

            if ($config) {
                //1.接入参数初始化
                $aop = new AopClient();
                $aop->apiVersion = "2.0";
                $aop->appId = $config->app_id;
                $aop->method = "alipay.trade.precreate";
                $aop->rsaPrivateKey = $config->rsa_private_key;
                $aop->alipayrsaPublicKey = $config->alipay_rsa_public_key;
                $aop->signType = "RSA2";//升级算法
                $aop->gatewayUrl = $config->alipay_gateway;
                $aop->format = "json";
                $aop->charset = "GBK";
                $disable_pay_channels = '';
                //2.调用接口
                $requests = new AlipayTradePrecreateRequest();
                $requests->setNotifyUrl($notify_url);
                if ($alipay_store_id) {
                    $datare = "{" .
                        "\"out_trade_no\":\"" . $out_trade_no . "\"," .
                        "\"seller_id\":\"" . $out_user_id . "\"," .//商户收款账号
                        "\"disable_pay_channels\":\"" . $disable_pay_channels . "\"," .
                        "\"total_amount\":" . $total_amount . "," .
                        "\"subject\":\"" . $shop_name . "\"," .
                        "\"store_id\":\"" . $out_store_id . "\"," .
                        "    \"terminal_id\":\"" . $device_id . "\"," .
                        "    \"operator_id\":\"D_001_" . $merchant_id . "\"," .
                        "\"shop_id\":\"" . $alipay_store_id . "\"," .
                        "    \"body\":\"" . $shop_desc . "\"," .
                        "      \"goods_detail\":[{" .
                        "        \"goods_id\":\"" . $store_id . "\"," .
                        "        \"goods_name\":\"" . $shop_name . "\"," .
                        "        \"quantity\":1," .
                        "        \"price\":" . $total_amount . "" .
                        "        }]," .
                        "\"extend_params\":{" .
                        "\"sys_service_provider_id\":\"" . $config->alipay_pid . "\"" .
                        "}," .
                        "\"timeout_express\":\"90m\"" .
                        "  }";
                } else {
                    $datare = "{" .
                        "\"out_trade_no\":\"" . $out_trade_no . "\"," .
                        "\"seller_id\":\"" . $out_user_id . "\"," .//商户收款账号
                        "\"disable_pay_channels\":\"" . $disable_pay_channels . "\"," .
                        "\"total_amount\":" . $total_amount . "," .
                        "\"subject\":\"" . $shop_name . "\"," .
                        "\"store_id\":\"" . $store_id . "\"," .

                        "    \"body\":\"" . $shop_desc . "\"," .
                        "      \"goods_detail\":[{" .
                        "        \"goods_id\":\"" . $store_id . "\"," .
                        "        \"goods_name\":\"" . $shop_name . "\"," .
                        "        \"quantity\":1," .
                        "        \"price\":" . $total_amount . "" .
                        "        }]," .


                        "\"extend_params\":{" .
                        "\"sys_service_provider_id\":\"" . $config->alipay_pid . "\"" .
                        "}," .
                        "\"timeout_express\":\"90m\"" .
                        "  }";
                }
                //分期
                if ($is_fq) {
                    $hb_fq_num = $data['is_fq_data']['hb_fq_num'];
                    $hb_fq_seller_percent = $data['is_fq_data']['hb_fq_seller_percent'];
                    $datare = "{" .
                        "\"out_trade_no\":\"" . $out_trade_no . "\"," .
                        "\"seller_id\":\"" . $out_user_id . "\"," .//商户收款账号
                        "\"disable_pay_channels\":\"" . $disable_pay_channels . "\"," .
                        "\"total_amount\":" . $total_amount . "," .
                        "\"subject\":\"" . $shop_name . "\"," .
                        "\"store_id\":\"" . $store_id . "\"," .
                        "    \"body\":\"" . $shop_desc . "\"," .
                        "      \"goods_detail\":[{" .
                        "        \"goods_id\":\"" . $store_id . "\"," .
                        "        \"goods_name\":\"" . $shop_name . "\"," .
                        "        \"quantity\":1," .
                        "        \"price\":" . $total_amount . "" .
                        "        }]," .
                        "\"extend_params\":{" .
                        "\"sys_service_provider_id\":\"" . $config->alipay_pid . "\"," .
                        "\"hb_fq_num\":\"" . $hb_fq_num . "\"," .
                        "\"hb_fq_seller_percent\":\"" . $hb_fq_seller_percent . "\"" .
                        "}," .
                        "\"timeout_express\":\"90m\"" .
                        "  }";
                }

                $requests->setBizContent($datare);
                $result = $aop->execute($requests, NULL, $app_auth_token);

                if ($result && $result->alipay_trade_precreate_response) {
                    $qr = $result->alipay_trade_precreate_response;
                    if ($qr->code == 10000) {
                        $code_url = $qr->qr_code;
                        $data = [
                            'code_url' => $code_url,
                            'out_trade_no' => $out_trade_no,
                            'store_name' => $store_name,
                            'total_amount' => $total_amount,
                        ];
                        return json_encode(
                            [
                                'status' => 1,
                                'data' => $data
                            ]
                        );

                    } else {
                        $info = $qr->sub_msg;
                        return json_encode([
                            'status' => 2,
                            'message' => $info
                        ]);
                    }
                } else {
                    $info = '生成预订单失败,请联系服务商';
                    return json_encode([
                        'status' => 2,
                        'message' => $info
                    ]);
                }
            } else {
                $info = '请联系服务商,检查ISV配置';
                return json_encode([
                    'status' => -1,
                    'message' => $info
                ]);
            }


        } catch (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'message' => $exception->getMessage() . $exception->getLine()
            ]);
        }
    }

    //静态码接口
    public function qr_auth_pay($data)
    {
        try {
            $out_trade_no = $data['out_trade_no'];
            $config_id = $data['config_id'];
            $store_id = $data['store_id'];
            $merchant_id = $data['merchant_id'];
            $total_amount = $data['total_amount'];
            $shop_price = $data['shop_price'];
            $remark = $data['remark'];
            $device_id = $data['device_id'];
            $config_type = $data['config_type'];
            $shop_name = $data['shop_name'];
            $shop_desc = $data['shop_desc'];
            $store_name = $data['store_name'];
            $open_id = $data['open_id'];

            $alipay_store_id = $data['alipay_store_id'];
            $out_store_id = $data['out_store_id'];
            $out_user_id = $data['out_user_id'];
            $app_auth_token = $data['app_auth_token'];
            $config = $data['config'];
            $notify_url = $data['notify_url'];


            if ($config) {
                //1.接入参数初始化
                $aop = new AopClient();
                $aop->apiVersion = "2.0";
                $aop->appId = $config->app_id;
                $aop->method = "alipay.trade.create";
                $aop->rsaPrivateKey = $config->rsa_private_key;
                $aop->alipayrsaPublicKey = $config->alipay_rsa_public_key;
                $aop->signType = "RSA2";//升级算法
                $aop->gatewayUrl = $config->alipay_gateway;
                $aop->format = "json";
                $aop->charset = "GBK";

                $requests = new AlipayTradeCreateRequest();
                $requests->setNotifyUrl($notify_url);
                $disable_pay_channels = '';
                if ($alipay_store_id && strlen($alipay_store_id) > 6) {
                    $data_re = "{" .
                        "\"out_trade_no\":\"" . $out_trade_no . "\"," .
                        "\"seller_id\":\"" . $out_user_id . "\"," .//商户收款账号
                        "\"total_amount\":\"" . $total_amount . "\"," .
                        "\"subject\":\"" . $shop_name . "\"," .
                        "\"body\":\"" . $shop_desc . "\"," .
                        "\"buyer_id\":\"" . $open_id . "\"," .
                        "\"goods_detail\":[{" .
                        "\"goods_id\":\"" . $store_id . "\"," .
                        "\"goods_name\":\"" . $shop_name . "\"," .
                        "\"price\":\"" . $total_amount . "\"," .
                        "\"body\":\"" . $shop_name . "\"" .
                        "}]," .
                        "\"store_id\":\"" . $out_store_id . "\"," .
                        "\"shop_id\":\"" . $alipay_store_id . "\"," .
                        "\"terminal_id\":\"" . $device_id . "\"," .
                        "\"operator_id\":\"D_001_" . $merchant_id . "\"," .
                        "\"extend_params\":{" .
                        "\"sys_service_provider_id\":\"" . $config->alipay_pid . "\"" .
                        "}," .
                        "\"timeout_express\":\"90m\"" .
                        "}";
                } else {
                    $data_re = "{" .
                        "\"out_trade_no\":\"" . $out_trade_no . "\"," .
                        "\"seller_id\":\"" . $out_user_id . "\"," .//商户收款账号
                        "\"total_amount\":\"" . $total_amount . "\"," .
                        "\"subject\":\"" . $shop_name . "\"," .
                        "\"body\":\"" . $shop_desc . "\"," .
                        "\"buyer_id\":\"" . $open_id . "\"," .
                        "\"goods_detail\":[{" .
                        "\"goods_id\":\"" . $store_id . "\"," .
                        "\"goods_name\":\"" . $shop_name . "\"," .
                        "\"price\":\"" . $total_amount . "\"," .
                        "\"body\":\"" . $shop_name . "\"" .
                        "}]," .
                        "\"store_id\":\"" . $store_id . "\"," .
                        "\"terminal_id\":\"" . $device_id . "\"," .
                        "\"operator_id\":\"D_001_" . $merchant_id . "\"," .
                        "\"extend_params\":{" .
                        "\"sys_service_provider_id\":\"" . $config->alipay_pid . "\"" .
                        "}," .
                        "\"timeout_express\":\"90m\"" .
                        "}";
                }
                $requests->setBizContent($data_re);
                $result = $aop->execute($requests, null, $app_auth_token);
                $responseNode = str_replace(".", "_", $requests->getApiMethodName()) . "_response";
                $resultCode = $result->$responseNode->code;
                if (!empty($resultCode) && $resultCode == 10000) {
                    $trade_no = $result->$responseNode->trade_no;//订单号
                    $data = [
                        'status' => 1,
                        "message" => "OK",
                        'data' => [
                            "trade_no" => $trade_no,
                            "out_trade_no" => $out_trade_no,
                        ]

                    ];
                } else {
                    $data = [
                        'status' => 2,
                        "message" => $result->$responseNode->sub_msg . '-' . $result->$responseNode->msg,
                    ];
                }

                return json_encode($data);

            } else {
                $info = '请联系服务商,检查ISV配置';
                return json_encode([
                    'status' => -1,
                    'message' => $info
                ]);
            }


        } catch (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'message' => $exception->getMessage() . $exception->getLine()
            ]);
        }
    }


}