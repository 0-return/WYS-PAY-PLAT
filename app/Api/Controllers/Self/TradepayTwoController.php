<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/9/7
 * Time: 下午12:07
 */

namespace App\Api\Controllers\Self;


use Alipayopen\Sdk\AopClient;
use Alipayopen\Sdk\Request\AlipayTradePayRequest;
use App\Api\Controllers\Config\AlipayIsvConfigController;
use App\Api\Controllers\Config\WeixinConfigController;
use App\Common\MerchantFuwu;
use App\Common\PaySuccessAction;
use App\Common\StoreDayMonthOrder;
use App\Common\UserGetMoney;
use App\Models\AlipayAccount;
use App\Models\Order;
use App\Models\Store;
use App\Models\StorePayWay;
use EasyWeChat\Factory;
use Illuminate\Support\Facades\Log;

class TradepayTwoController
{

    //自助收银扫一扫设备
    public function scan_pay($data)
    {
        try {
            $store_id = $data['store_id'];
            $code = $data['code'];
            $total_amount = $data['total_amount'];
            $pay_amount = $data['pay_amount'];
            $shop_price = $data['shop_price'];
            $remark = $data['remark'];
            $device_id = $data['device_id'];
            $shop_name = $data['shop_name'];
            $shop_desc = $data['shop_desc'];


            $store = Store::where('store_id', $store_id)->first();


            $config_id = $store->config_id;
            $merchant_id = $store->merchant_id;
            $merchant_name = '';
            $store_name = $store->store_name;
            $store_pid = $store->pid;
            $tg_user_id = $store->user_id;


            //插入数据库
            $data_insert = [
                'trade_no' => '',
                'user_id' => $tg_user_id,
                'store_id' => $store_id,
                'store_name' => $store_name,
                'buyer_id' => '',
                'total_amount' => $total_amount,
                'pay_amount' => $pay_amount,
                'shop_price' => $shop_price,
                'payment_method' => '',
                'status' => '',
                'pay_status' => 2,
                'pay_status_desc' => '等待支付',
                'merchant_id' => $merchant_id,
                'merchant_name' => $merchant_name,
                'remark' => $remark,
                'device_id' => $device_id,
                'config_id' => $config_id,
            ];


            $str = substr($code, 0, 2);


            /**支付宝渠道开始**/
            if (in_array($str, ['28'])) {
                $ways = StorePayWay::where('ways_source', 'alipay')
                    ->where('store_id', $store_id)
                    ->where('sort', 1)->first();

                if (!$ways) {
                    return json_encode([
                        'status' => 2,
                        'message' => '通道不存在'
                    ]);
                }
                $data_insert ['rate'] = $ways->rate;

                //官方支付宝扫一扫
                if ($ways && $ways->ways_type == 1000) {
                    $out_trade_no = 'ali_scan' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    $data['out_trade_no'] = $out_trade_no;
                    //入库参数
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['ways_type_desc'] = '支付宝';
                    $data_insert['ways_source'] = 'alipay';
                    $data_insert['ways_source_desc'] = '支付宝';

                    $config_type = '01';


                    //配置
                    $isvconfig = new AlipayIsvConfigController();
                    $storeInfo = $isvconfig->alipay_auth_info($store_id, $store_pid);
                    $out_user_id = $storeInfo->user_id;//商户的id
                    $alipay_store_id = $storeInfo->alipay_store_id;


                    //分成模式 服务商
                    if ($storeInfo->settlement_type == "set_a") {
                        if ($storeInfo->config_type == '02') {
                            $config_type = '02';
                        }
                        $storeInfo = AlipayAccount::where('config_id', $config_id)
                            ->where('config_type', $config_type)
                            ->first();//服务商的
                    }

                    if (!$storeInfo) {
                        $msg = '支付宝授权信息不存在';
                        return [
                            'status' => 2,
                            'message' => $msg
                        ];

                    }

                    $app_auth_token = $storeInfo->app_auth_token;

                    $config = $isvconfig->AlipayIsvConfig($config_id, $config_type);


                    $insert_re = Order::create($data_insert);
                    if (!$insert_re) {
                        return json_encode([
                            'status' => 2,
                            'message' => '订单未入库'
                        ]);
                    }


                    $notify_url = url('/api/alipayopen/qr_pay_notify');
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
                            "    \"total_amount\":" . $pay_amount . "," .
                            "    \"timeout_express\":\"90m\"," .
                            "    \"body\":\"" . $shop_desc . "\"," .
                            "      \"goods_detail\":[{" .
                            "        \"goods_id\":\"" . $store_id . "\"," .
                            "        \"goods_name\":\"" . $shop_name . "\"," .
                            "        \"quantity\":1," .
                            "        \"price\":" . $pay_amount . "," .
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
                            "    \"total_amount\":" . $pay_amount . "," .
                            "    \"timeout_express\":\"90m\"," .
                            "    \"body\":\"" . $shop_desc . "\"," .
                            "      \"goods_detail\":[{" .
                            "        \"goods_id\":\"" . $store_id . "\"," .
                            "        \"goods_name\":\"" . $shop_name . "\"," .
                            "        \"quantity\":1," .
                            "        \"price\":" . $pay_amount . "," .
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
                    //  dd($data_re);
                    $requests->setBizContent($data_re);
                    $result = $aop->execute($requests, null, $app_auth_token);

                    $responseNode = str_replace(".", "_", $requests->getApiMethodName()) . "_response";
                    $resultCode = $result->$responseNode->code;
                    //异常
                    if ($resultCode == 40004) {
                        return json_encode([
                            'status' => 2,
                            'message' => $result->$responseNode->msg . $result->$responseNode->sub_code,
                            'result_code' => $resultCode,
                        ]);
                    }
                    //支付成功
                    if (!empty($resultCode) && $resultCode == 10000) {
                        $buyer_id = $result->$responseNode->buyer_user_id;
                        $buyer_logon_id = $result->$responseNode->buyer_logon_id;
                        $payment_method = $result->$responseNode->fund_bill_list[0]->fund_channel;
                        $trade_no = $result->$responseNode->trade_no;
                        $gmt_payment = $result->$responseNode->gmt_payment;
                        $buyer_pay_amount = $result->$responseNode->buyer_pay_amount;

                        //优惠信息
                        $mdiscount_amount = 0;
                        $discount_amount = 0;

                        if (isset($result->$responseNode->mdiscount_amount)) {
                            $mdiscount_amount = $result->$responseNode->mdiscount_amount;

                        }

                        if (isset($result->$responseNode->discount_amount)) {
                            $discount_amount = $result->$responseNode->discount_amount;
                        }


                        Order::where('out_trade_no', $out_trade_no)->update(
                            [
                                'trade_no' => $trade_no,
                                'buyer_id' => $buyer_id,
                                'buyer_logon_id' => $buyer_logon_id,
                                'status' => 'TRADE_SUCCESS',
                                'pay_status_desc' => '支付成功',
                                'pay_status' => 1,
                                'payment_method' => $payment_method,
                                'buyer_pay_amount' => $buyer_pay_amount,
                                'mdiscount_amount' => $mdiscount_amount,
                                'discount_amount' => $discount_amount,
                            ]);

                        //支付成功后的动作
                        $data = [
                            'ways_type' => $ways->ways_type,
                            'ways_type_desc' => $ways->ways_desc,
                            'source_type' => '1000',//返佣来源
                            'source_desc' => '支付宝',//返佣来源说明
                            'total_amount' => $total_amount,
                            'out_trade_no' => $out_trade_no,
                            'rate' => $data_insert['rate'],
                            'merchant_id' => $merchant_id,
                            'store_id' => $store_id,
                            'user_id' => $tg_user_id,
                            'config_id' => $config_id,
                            'store_name' => $store_name,
                            'ways_source'=>$ways->ways_source,
                            'pay_time' => $gmt_payment,

                        ];


                        PaySuccessAction::action($data);


                        return json_encode([
                            'status' => 1,
                            'message' => '支付成功',
                            'data' => [
                                'ways_type' => $data_insert['ways_type'],
                                'ways_type_desc' => $data_insert['ways_type_desc'],
                                'out_trade_no' => $out_trade_no,
                                'total_amount' => $total_amount,
                                'store_name' => $store_name,
                                'trade_no' => $trade_no,
                                'buyer_logon_id' => $buyer_logon_id,
                                'pay_time' => $gmt_payment,
                                'buyer_pay_amount' => $buyer_pay_amount * 100,//number_format( $pay_amount * 100, 2, '.', ''),
                                'mdiscount_amount' => $mdiscount_amount * 100,//number_format( $mdiscount_amount * 100, 2, '.', '') ,
                                'discount_amount' => $discount_amount * 100,//number_format(  $discount_amount * 100, 2, '.', ''),

                            ]
                        ]);


                    }
                    //正在支付
                    if (!empty($resultCode) && $resultCode == 10003) {
                        return json_encode([
                            'status' => 9,
                            'message' => '等待用户支付',
                            'data' => [
                                'out_trade_no' => $out_trade_no,
                                'total_amount' => $total_amount,
                                'store_name' => $store_name,
                            ]
                        ]);

                    }
                    $msg = $result->$responseNode->sub_msg;//错误信息
                    return json_encode([
                        'status' => 2,
                        'message' => $msg,
                    ]);
                }

            }
            /**支付宝渠道结束**/
            if (in_array($str, ['13'])) {
                $ways = StorePayWay::where('ways_source', 'weixin')
                    ->where('store_id', $store_id)
                    ->where('sort', 1)->first();
                if (!$ways) {
                    return json_encode([
                        'status' => 2,
                        'message' => '通道不存在'
                    ]);
                }
                $data_insert ['rate'] = $ways->rate;

                //官方微信扫一扫
                if ($ways && $ways->ways_type == 2000) {
                    $config = new WeixinConfigController();
                    $options = $config->weixin_config($config_id);
                    $weixin_store = $config->weixin_merchant($store_id, $store_pid);
                    if (!$weixin_store) {
                        return json_encode([
                            'status' => 2,
                            'message' => '微信商户号不存在'
                        ]);
                    }
                    $wx_sub_merchant_id = $weixin_store->wx_sub_merchant_id;


                    $out_trade_no = 'wx_scan' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['ways_type_desc'] = '微信支付';
                    $data_insert['ways_source'] = 'weixin';
                    $data_insert['ways_source_desc'] = '微信支付';

                    $type = 2001;
                    $attach = $store_id . ',' . $config_id;//附加信息原样返回
                    $goods_detail = [];
                    //入库参数
                    $insert_re = Order::create($data_insert);
                    if (!$insert_re) {
                        return json_encode([
                            'status' => 2,
                            'message' => '订单未入库'
                        ]);
                    }

                    $config = [
                        'app_id' => $options['app_id'],
                        'mch_id' => $options['payment']['merchant_id'],
                        'key' => $options['payment']['key'],
                        'cert_path' => $options['payment']['cert_path'], // XXX: 绝对路径！！！！
                        'key_path' => $options['payment']['key_path'],     // XXX: 绝对路径！！！！
                        'sub_mch_id' => $wx_sub_merchant_id,
                    ];

                    $payment = Factory::payment($config);

                    $attributes = [
                        'body' => $shop_name,
                        'detail' => $shop_desc,
                        'out_trade_no' => $out_trade_no,
                        'total_fee' => $total_amount * 100,
                        'auth_code' => $code,
                        'attach' => $attach,//原样返回
                        'device_info' => $device_id,
                        'goods_detail' => $goods_detail,//交易详细数据
                    ];

                    $result = $payment->pay($attributes);
                    Log::info($result);

                    //请求状态
                    if ($result['return_code'] == 'SUCCESS') {
                        //支付成功
                        if ($result['result_code'] == 'SUCCESS') {
                            $data_update = [
                                'receipt_amount' => 0,//商家实际收到的款项
                                'status' => $result['result_code'],
                                'pay_status' => 1,//系统状态
                                'pay_status_desc' => '支付成功',
                                'payment_method' => $result['bank_type'],
                                'buyer_id' => $result['openid'],
                                'trade_no' => $result['transaction_id'],
                            ];
                            $insert_re = Order::where('out_trade_no', $out_trade_no)->update($data_update);

                            if (!$insert_re) {
                                return json_encode([
                                    'status' => 2,
                                    'message' => '订单未入库'
                                ]);
                            }

                            //支付成功后的动作
                            $data = [
                                'ways_type' => $ways->ways_type,
                                'ways_type_desc' => $ways->ways_desc,
                                'source_type' => '2000',//返佣来源
                                'source_desc' => '微信支付',//返佣来源说明
                                'total_amount' => $total_amount,
                                'out_trade_no' => $out_trade_no,
                                'rate' => $data_insert['rate'],
                                'merchant_id' => $merchant_id,
                                'store_id' => $store_id,
                                'user_id' => $tg_user_id,
                                'config_id' => $config_id,
                                'store_name' => $store_name,
                                'ways_source'=>$ways->ways_source,
                                'pay_time' => $result['time_end'],

                            ];


                            PaySuccessAction::action($data);


                            return json_encode([
                                'status' => 1,
                                'data' => [
                                    'out_trade_no' => $out_trade_no,
                                    'store_name' => $store_name,
                                    'ways_type' => $data_insert['ways_type'],
                                    'ways_type_desc' => $data_insert['ways_type_desc'],
                                    'total_amount' => $total_amount,
                                    'trade_no' => $result['transaction_id'],
                                    'buyer_logon_id' => $result['openid'],
                                    'pay_time' => $result['time_end'],
                                    'buyer_pay_amount' => $total_amount * 100,//number_format( $pay_amount * 100, 2, '.', ''),
                                    'mdiscount_amount' => '0',//number_format( $mdiscount_amount * 100, 2, '.', '') ,
                                    'discount_amount' => '0',//number_format(  $discount_amount * 100, 2, '.', ''),

                                ]
                            ]);

                        } else {

                            if ($result['err_code'] == "USERPAYING") {
                                return json_encode([
                                    'status' => 9,
                                    'message' => '等待用户支付',
                                    'data' => [
                                        'out_trade_no' => $out_trade_no,
                                        'total_amount' => $total_amount,
                                        'store_name' => $store_name,
                                    ]
                                ]);

                            } else {
                                $msg = $result['err_code_des'];//错误信息
                                return json_encode([
                                    'status' => 2,
                                    'message' => $msg,
                                ]);
                            }


                        }


                    } else {
                        $data = [
                            'status' => 2,
                            "message" => $result['return_msg'],
                        ];
                    }

                    return json_encode($data);
                }

            }

            return json_encode([
                'status' => 2,
                'message' => '暂不支持此二维码'
            ]);


        } catch
        (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage() . $exception->getLine()
            ]);
        }
    }


    //自助收银刷脸支付公共版
    public function face_pay($data)
    {
        try {
            $store_id = $data['store_id'];
            $face_type = $data['face_type'];
            $ftoken = $data['ftoken'];
            $total_amount = $data['total_amount'];
            $pay_amount = $data['pay_amount'];
            $shop_price = $data['shop_price'];
            $remark = $data['remark'];
            $device_id = $data['device_id'];
            $shop_name = $data['shop_name'];
            $shop_desc = $data['shop_desc'];


            $store = Store::where('store_id', $store_id)->first();
            $config_id = $store->config_id;
            $merchant_id = $store->merchant_id;
            $merchant_name = '';
            $store_name = $store->store_name;
            $store_pid = $store->pid;
            $tg_user_id = $store->user_id;


            //插入数据库
            $data_insert = [
                'trade_no' => '',
                'user_id' => $tg_user_id,
                'store_id' => $store_id,
                'store_name' => $store_name,
                'buyer_id' => '',
                'total_amount' => $total_amount,
                'pay_amount' => $pay_amount,
                'shop_price' => $shop_price,
                'payment_method' => '',
                'status' => '',
                'pay_status' => 2,
                'pay_status_desc' => '等待支付',
                'merchant_id' => $merchant_id,
                'merchant_name' => $merchant_name,
                'remark' => $remark,
                'device_id' => $device_id,
                'config_id' => $config_id,
            ];


            /**支付宝刷脸渠道开始**/
            if ($face_type == "alipay") {
                $ways = StorePayWay::where('ways_source', 'alipay')
                    ->where('store_id', $store_id)
                    ->where('sort', 1)->first();
                //官方支付宝扫一扫
                if ($ways && $ways->ways_type == 1000) {
                    $data_insert['rate'] = $ways['rate'];
                    $out_trade_no = 'security_' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    $data['out_trade_no'] = $out_trade_no;
                    //入库参数
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['ways_type_desc'] = '支付宝';
                    $data_insert['ways_source'] = 'alipay';
                    $data_insert['ways_source_desc'] = '支付宝';

                    $config_type = '01';


                    //配置
                    $isvconfig = new AlipayIsvConfigController();
                    $storeInfo = $isvconfig->alipay_auth_info($store_id, $store_pid);
                    $out_user_id = $storeInfo->user_id;//商户的id
                    $alipay_store_id = $storeInfo->alipay_store_id;


                    //分成模式 服务商
                    if ($storeInfo->settlement_type == "set_a") {
                        if ($storeInfo->config_type == '02') {
                            $config_type = '02';
                        }
                        $storeInfo = AlipayAccount::where('config_id', $config_id)
                            ->where('config_type', $config_type)
                            ->first();//服务商的
                    }

                    if (!$storeInfo) {
                        $msg = '支付宝授权信息不存在';
                        return [
                            'status' => 2,
                            'message' => $msg
                        ];

                    }

                    $app_auth_token = $storeInfo->app_auth_token;

                    $config = $isvconfig->AlipayIsvConfig($config_id, $config_type);


                    $insert_re = Order::create($data_insert);
                    if (!$insert_re) {
                        return json_encode([
                            'status' => 2,
                            'message' => '订单未入库'
                        ]);
                    }


                    $notify_url = url('/api/alipayopen/qr_pay_notify');
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
                            "    \"scene\":\"security_code\"," .
                            "    \"auth_code\":\"" . $ftoken . "\"," .
                            "    \"subject\":\"" . $shop_name . "\"," .
                            "    \"total_amount\":" . $pay_amount . "," .
                            "    \"timeout_express\":\"90m\"," .
                            "    \"body\":\"" . $shop_desc . "\"," .
                            "      \"goods_detail\":[{" .
                            "        \"goods_id\":\"" . $store_id . "\"," .
                            "        \"goods_name\":\"" . $shop_name . "\"," .
                            "        \"quantity\":1," .
                            "        \"price\":" . $pay_amount . "," .
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
                            "    \"scene\":\"security_code\"," .
                            "    \"auth_code\":\"" . $ftoken . "\"," .
                            "    \"subject\":\"" . $shop_name . "\"," .
                            "    \"total_amount\":" . $pay_amount . "," .
                            "    \"timeout_express\":\"90m\"," .
                            "    \"body\":\"" . $shop_desc . "\"," .
                            "      \"goods_detail\":[{" .
                            "        \"goods_id\":\"" . $store_id . "\"," .
                            "        \"goods_name\":\"" . $shop_name . "\"," .
                            "        \"quantity\":1," .
                            "        \"price\":" . $pay_amount . "," .
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
                    //  dd($data_re);
                    $requests->setBizContent($data_re);
                    $result = $aop->execute($requests, null, $app_auth_token);

                    $responseNode = str_replace(".", "_", $requests->getApiMethodName()) . "_response";
                    $resultCode = $result->$responseNode->code;
                    //异常
                    if ($resultCode == 40004) {
                        return json_encode([
                            'status' => 2,
                            'message' => $result->$responseNode->msg . $result->$responseNode->sub_code,
                            'result_code' => $resultCode,
                        ]);
                    }
                    //支付成功
                    if (!empty($resultCode) && $resultCode == 10000) {
                        $buyer_id = $result->$responseNode->buyer_user_id;
                        $buyer_logon_id = $result->$responseNode->buyer_logon_id;
                        $payment_method = $result->$responseNode->fund_bill_list[0]->fund_channel;
                        $trade_no = $result->$responseNode->trade_no;
                        $gmt_payment = $result->$responseNode->gmt_payment;
                        $buyer_pay_amount = $result->$responseNode->buyer_pay_amount;

                        //优惠信息
                        $mdiscount_amount = 0;
                        $discount_amount = 0;

                        if (isset($result->$responseNode->mdiscount_amount)) {
                            $mdiscount_amount = $result->$responseNode->mdiscount_amount;

                        }

                        if (isset($result->$responseNode->discount_amount)) {
                            $discount_amount = $result->$responseNode->discount_amount;
                        }


                        Order::where('out_trade_no', $out_trade_no)->update(
                            [
                                'trade_no' => $trade_no,
                                'buyer_id' => $buyer_id,
                                'buyer_logon_id' => $buyer_logon_id,
                                'status' => 'TRADE_SUCCESS',
                                'pay_status_desc' => '支付成功',
                                'pay_status' => 1,
                                'payment_method' => $payment_method,
                                'buyer_pay_amount' => $buyer_pay_amount,
                                'mdiscount_amount' => $mdiscount_amount,
                                'discount_amount' => $discount_amount,
                            ]);


                        //支付成功后的动作
                        $data = [
                            'ways_type' => $ways->ways_type,
                            'ways_type_desc' => $ways->ways_desc,
                            'source_type' => '1000',//返佣来源
                            'source_desc' => '支付宝',//返佣来源说明
                            'total_amount' => $total_amount,
                            'out_trade_no' => $out_trade_no,
                            'rate' => $data_insert['rate'],
                            'merchant_id' => $merchant_id,
                            'store_id' => $store_id,
                            'user_id' => $tg_user_id,
                            'config_id' => $config_id,
                            'store_name' => $store_name,
                            'ways_source'=>$ways->ways_source,
                            'pay_time' => $gmt_payment,

                        ];


                        PaySuccessAction::action($data);

                        return json_encode([
                            'status' => 1,
                            'message' => '支付成功',
                            'data' => [
                                'ways_type' => $data_insert['ways_type'],
                                'ways_type_desc' => $data_insert['ways_type_desc'],
                                'out_trade_no' => $out_trade_no,
                                'total_amount' => $total_amount,
                                'store_name' => $store_name,
                                'trade_no' => $trade_no,
                                'buyer_logon_id' => $buyer_logon_id,
                                'pay_time' => $gmt_payment,
                                'buyer_pay_amount' => $buyer_pay_amount * 100,//number_format( $pay_amount * 100, 2, '.', ''),
                                'mdiscount_amount' => $mdiscount_amount * 100,//number_format( $mdiscount_amount * 100, 2, '.', '') ,
                                'discount_amount' => $discount_amount * 100,//number_format(  $discount_amount * 100, 2, '.', ''),

                            ]
                        ]);
                    }
                    //正在支付
                    if (!empty($resultCode) && $resultCode == 10003) {
                        return json_encode([
                            'status' => 9,
                            'message' => '等待用户支付',
                            'data' => [
                                'out_trade_no' => $out_trade_no,
                                'total_amount' => $total_amount,
                                'store_name' => $store_name,
                            ]
                        ]);

                    }
                    $msg = $result->$responseNode->sub_msg;//错误信息
                    return json_encode([
                        'status' => 2,
                        'message' => $msg,
                    ]);
                }

            }
            /**支付宝渠道结束**/


            return json_encode([
                'status' => 2,
                'message' => '暂不支持此此收款码'
            ]);


        } catch
        (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage() . $exception->getLine()
            ]);
        }
    }


}