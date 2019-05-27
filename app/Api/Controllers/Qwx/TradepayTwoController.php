<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/9/7
 * Time: 下午12:07
 */

namespace App\Api\Controllers\Qwx;


use Alipayopen\Sdk\AopClient;
use Alipayopen\Sdk\Request\AlipayTradePayRequest;
use App\Api\Controllers\Config\AlipayIsvConfigController;
use App\Api\Controllers\Config\HConfigController;
use App\Api\Controllers\Config\JdConfigController;
use App\Api\Controllers\Config\LtfConfigController;
use App\Api\Controllers\Config\MyBankConfigController;
use App\Api\Controllers\Config\NewLandConfigController;
use App\Api\Controllers\Config\PayWaysController;
use App\Api\Controllers\Config\WeixinConfigController;
use App\Api\Controllers\MyBank\TradePayController;
use App\Common\MerchantFuwu;
use App\Common\PaySuccessAction;
use App\Common\StoreDayMonthOrder;
use App\Common\UserGetMoney;
use App\Models\AlipayAccount;
use App\Models\MyBankStore;
use App\Models\Order;
use App\Models\QwxStore;
use App\Models\Store;
use App\Models\StorePayWay;
use EasyWeChat\Factory;
use Illuminate\Support\Facades\Log;

class TradepayTwoController
{

    //2.0配合微收银的扫一扫
    public function scan_pay($data)
    {
        try {
            $store_id = $data['store_id'];
            $code = $data['code'];
            $total_amount = $data['total_amount'];
            $shop_price = $data['shop_price'];
            $remark = $data['remark'];
            $device_id = $data['device_id'];
            $shop_name = $data['shop_name'];
            $shop_desc = $data['shop_desc'];
            $qwx_no = $data['qwx_no'];


            $store = Store::where('store_id', $store_id)
                ->select('config_id', 'store_name', 'pid', 'user_id')
                ->first();

            //app
            if ($device_id == 'app') {
                $merchant_id = $data['merchant_id'];
                $merchant_name = $data['merchant_name'];
            } else {
                //微收银终端
                $QwxStore = QwxStore::where('device_id', $device_id)
                    ->select('merchant_id', 'merchant_name')
                    ->first();
                if (!$QwxStore) {
                    $msg = '设备ID不存在';
                    return json_encode([
                        'status' => 2,
                        'message' => $msg,
                    ]);
                }

                $merchant_id = $QwxStore->merchant_id;
                $merchant_name = $QwxStore->merchant_name;
            }

            $config_id = $store->config_id;
            $store_name = $store->store_name;
            $store_pid = $store->pid;
            $tg_user_id = $store->user_id;


            //插入数据库
            $data_insert = [
                'trade_no' => '',
                'qwx_no' => $qwx_no,
                'user_id' => $tg_user_id,
                'store_id' => $store_id,
                'store_name' => $store_name,
                'buyer_id' => '',
                'total_amount' => $total_amount,
                'pay_amount' => $total_amount,
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
            if ($str == "28") {
                //读取优先为高级的通道
                $obj_ways = new PayWaysController();
                $ways = $obj_ways->ways_source('alipay', $store_id, $store_pid);

                $out_trade_no = 'aliscan' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                if (!$ways) {
                    $msg = '此类型通道没有开通';
                    return [
                        'status' => 2,
                        'message' => $msg
                    ];

                }

                $data_insert ['rate'] = $ways->rate;
                $data_insert['fee_amount'] = $ways->rate * $total_amount / 100;

                //官方支付宝扫一扫
                if ($ways && $ways->ways_type == 1000) {
                    $data['out_trade_no'] = $out_trade_no;
                    //入库参数
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['company'] = $ways->company;
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
                    $out_store_id = $storeInfo->out_store_id;

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
                    // $aop->notify_url = $notify_url;
                    $aop->signType = "RSA2";//升级算法
                    $aop->gatewayUrl = $config->alipay_gateway;
                    $aop->format = "json";
                    $aop->charset = "GBK";
                    $aop->version = "2.0";


                    $requests = new AlipayTradePayRequest();
                    // $requests->setNotifyUrl($notify_url);
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
                            "    \"store_id\":\"" . $out_store_id . "\"," .
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
                        $buy_pay_amount = $result->$responseNode->buyer_pay_amount;
                        Order::where('out_trade_no', $out_trade_no)->update(
                            [
                                'trade_no' => $trade_no,
                                'buyer_id' => $buyer_id,
                                'buyer_logon_id' => $buyer_logon_id,
                                'status' => 'TRADE_SUCCESS',
                                'pay_status_desc' => '支付成功',
                                'pay_status' => 1,
                                'payment_method' => $payment_method,
                                'pay_time' => $gmt_payment,
                                'buyer_pay_amount' => $buy_pay_amount,
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
                            'ways_source' => $ways->ways_source,
                            'pay_time' => $gmt_payment,
                            'no_push' => '1',//不推送
                            'no_fuwu' => '1',//不服务消息
                            'no_print' => '1',//不打印
                            //'no_v' => '1',//不小盒子播报

                        ];


                        PaySuccessAction::action($data);

                        return json_encode([
                            'status' => 1,
                            'message' => '支付成功',
                            'data' => [
                                'out_trade_no' => $out_trade_no,
                                'out_transaction_id' => $out_trade_no,
                                'total_amount' => $total_amount,
                                'store_name' => $store_name,
                                'trade_no' => $trade_no,
                                'pay_time' => $gmt_payment,
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
                //京东收银支付宝扫一扫
                if ($ways && $ways->ways_type == 6001) {
                    $data_public = [
                        'pay_type' => 'ALIPAY',
                        'return_params' => '原样返回',
                        'title' => '支付宝收款',
                        'ways_source_desc' => '支付宝',
                        'ways_type_desc' => '支付宝',
                        'code' => $code,
                        'out_trade_no' => $out_trade_no,
                        'config_id' => $config_id,
                        'store_id' => $store_id,
                        'store_pid' => $store_pid,
                        'ways_type' => '' . $ways->ways_type . '',
                        'ways_source' => $ways->ways_source,
                        'company' => $ways->company,
                        'total_amount' => $total_amount,
                        'remark' => $remark,
                        'device_id' => $device_id,
                        'shop_name' => $shop_name,
                        'merchant_id' => $merchant_id,
                        'store_name' => $store_name,
                        'tg_user_id' => $store->user_id,
                    ];

                    return $this->jd_pay_public($data_insert, $data_public);

                }

                //联拓富收银支付宝扫一扫
                if ($ways && $ways->ways_type == 10001) {
                    $data_public = [
                        'pay_type' => 'ALIPAY',
                        'return_params' => '原样返回',
                        'title' => '支付宝收款',
                        'ways_source_desc' => '支付宝',
                        'ways_type_desc' => '支付宝',
                        'code' => $code,
                        'out_trade_no' => $out_trade_no,
                        'config_id' => $config_id,
                        'store_id' => $store_id,
                        'store_pid' => $store_pid,
                        'ways_type' => '' . $ways->ways_type . '',
                        'ways_source' => $ways->ways_source,
                        'company' => $ways->company,
                        'total_amount' => $total_amount,
                        'remark' => $remark,
                        'device_id' => $device_id,
                        'shop_name' => $shop_name,
                        'merchant_id' => $merchant_id,
                        'store_name' => $store_name,
                        'tg_user_id' => $store->user_id,
                    ];

                    return $this->ltf_pay_public($data_insert, $data_public);

                }

                //和融通收银支付宝
                if ($ways && $ways->ways_type == 9001) {
                    $data_public = [
                        'pay_type' => 'ali',
                        'return_params' => '原样返回',
                        'title' => '支付宝收款',
                        'ways_source_desc' => '支付宝',
                        'ways_type_desc' => '支付宝',
                        'code' => $code,
                        'out_trade_no' => $out_trade_no,
                        'config_id' => $config_id,
                        'store_id' => $store_id,
                        'store_pid' => $store_pid,
                        'ways_type' => '' . $ways->ways_type . '',
                        'ways_source' => $ways->ways_source,
                        'company' => $ways->company,
                        'total_amount' => $total_amount,
                        'remark' => $remark,
                        'device_id' => $device_id,
                        'shop_name' => $shop_name,
                        'merchant_id' => $merchant_id,
                        'store_name' => $store_name,
                        'tg_user_id' => $store->user_id,
                    ];

                    return $this->h_pay_public($data_insert, $data_public);

                }
                //网商支付宝支付宝扫一扫
                if ($ways && $ways->ways_type == 3001) {
                    $data_public = [
                        'pay_type' => 'alipay',
                        'return_params' => '原样返回',
                        'title' => '支付宝收款',
                        'ways_source_desc' => '支付宝',
                        'ways_type_desc' => '支付宝',
                        'code' => $code,
                        'out_trade_no' => $out_trade_no,
                        'config_id' => $config_id,
                        'store_id' => $store_id,
                        'store_pid' => $store_pid,
                        'ways_type' => '' . $ways->ways_type . '',
                        'ways_source' => $ways->ways_source,
                        'company' => $ways->company,
                        'total_amount' => $total_amount,
                        'remark' => $remark,
                        'device_id' => $device_id,
                        'shop_name' => $shop_name,
                        'merchant_id' => $merchant_id,
                        'store_name' => $store_name,
                        'tg_user_id' => $tg_user_id,
                    ];
                    return $this->mybank_pay_public($data_insert, $data_public);
                }

                //新大陆支付宝
                if ($ways && $ways->ways_type == 8001) {
                    $config = new NewLandConfigController();
                    $new_land_config = $config->new_land_config($config_id);
                    if (!$new_land_config) {
                        return json_encode([
                            'status' => 2,
                            'message' => '新大陆配置不存在请检查配置'
                        ]);
                    }

                    $new_land_merchant = $config->new_land_merchant($store_id, $store_pid);
                    if (!$new_land_merchant) {
                        return json_encode([
                            'status' => 2,
                            'message' => '商户新大陆通道未开通'
                        ]);
                    }
                    $request_data = [
                        'out_trade_no' => $out_trade_no,
                        'code' => $code,
                        'total_amount' => $total_amount,
                        'remark' => $remark,
                        'device_id' => $device_id,
                        'shop_name' => $shop_name,
                        'key' => $new_land_merchant->nl_key,
                        'org_no' => $new_land_config->org_no,
                        'merc_id' => $new_land_merchant->nl_mercId,
                        'trm_no' => $new_land_merchant->trmNo,
                        'op_sys' => '3',
                        'opr_id' => $merchant_id,
                        'trm_typ' => 'T',
                        'payChannel' => 'ALIPAY',
                    ];

                    //入库参数
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['ways_type_desc'] = '支付宝';
                    $data_insert['ways_source'] = $ways->ways_source;
                    $data_insert['ways_source_desc'] = '支付宝';
                    $data_insert['company'] = $ways->company;


                    return $this->newland_pay_public($data_insert, $request_data);
                }


            }
            /**支付宝渠道结束**/
            if ($str == "13"||$str == "14") {
                //读取优先为高级的通道
                $obj_ways = new PayWaysController();
                $ways = $obj_ways->ways_source('weixin', $store_id, $store_pid);

                if (!$ways) {
                    $msg = '此类型通道没有开通';
                    return [
                        'status' => 2,
                        'message' => $msg
                    ];

                }
                $data_insert ['rate'] = $ways->rate;
                $data_insert['fee_amount'] = $ways->rate * $total_amount / 100;
                $out_trade_no = 'wxscan' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));

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


                    $data_insert['out_trade_no'] = $out_trade_no;
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['ways_type_desc'] = '微信支付';
                    $data_insert['ways_source'] = 'weixin';
                    $data_insert['company'] = $ways->company;
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
                                'ways_source' => $ways->ways_source,
                                'pay_time' => $result['time_end'],
                                'no_push' => '1',//不推送
                                'no_fuwu' => '1',//不服务消息
                                'no_print' => '1',//不打印
                                //'no_v' => '1',//不小盒子播报

                            ];


                            PaySuccessAction::action($data);

                            return json_encode([
                                'status' => 1,
                                'data' => [
                                    'out_trade_no' => $out_trade_no,
                                    'store_name' => $store_name,
                                    'trade_no' => $result['transaction_id'],
                                    'total_amount' => $total_amount,
                                    'pay_time' => $result['time_end'],
                                    'out_transaction_id' => $out_trade_no,
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

                //京东收银-微信
                if ($ways && $ways->ways_type == 6002) {
                    $data_public = [
                        'pay_type' => 'WX',
                        'return_params' => '原样返回',
                        'title' => '微信收款',
                        'ways_source_desc' => '微信支付',
                        'ways_type_desc' => '微信支付',
                        'code' => $code,
                        'out_trade_no' => $out_trade_no,
                        'config_id' => $config_id,
                        'store_id' => $store_id,
                        'store_pid' => $store_pid,
                        'ways_type' => '' . $ways->ways_type . '',
                        'ways_source' => $ways->ways_source,
                        'company' => $ways->company,
                        'total_amount' => $total_amount,
                        'remark' => $remark,
                        'device_id' => $device_id,
                        'shop_name' => $shop_name,
                        'merchant_id' => $merchant_id,
                        'store_name' => $store_name,
                        'tg_user_id' => $store->user_id,
                    ];

                    return $this->jd_pay_public($data_insert, $data_public);
                }

                //联拓富收银-微信
                if ($ways && $ways->ways_type == 10002) {
                    $data_public = [
                        'pay_type' => 'WX',
                        'return_params' => '原样返回',
                        'title' => '微信收款',
                        'ways_source_desc' => '微信支付',
                        'ways_type_desc' => '微信支付',
                        'code' => $code,
                        'out_trade_no' => $out_trade_no,
                        'config_id' => $config_id,
                        'store_id' => $store_id,
                        'store_pid' => $store_pid,
                        'ways_type' => '' . $ways->ways_type . '',
                        'ways_source' => $ways->ways_source,
                        'company' => $ways->company,
                        'total_amount' => $total_amount,
                        'remark' => $remark,
                        'device_id' => $device_id,
                        'shop_name' => $shop_name,
                        'merchant_id' => $merchant_id,
                        'store_name' => $store_name,
                        'tg_user_id' => $store->user_id,
                    ];

                    return $this->ltf_pay_public($data_insert, $data_public);
                }


                //网商微信支付扫一扫
                if ($ways && $ways->ways_type == 3002) {
                    $data_public = [
                        'pay_type' => 'weixin',
                        'return_params' => '原样返回',
                        'title' => '微信收款',
                        'ways_source_desc' => '微信支付',
                        'ways_type_desc' => '微信支付',
                        'code' => $code,
                        'out_trade_no' => $out_trade_no,
                        'config_id' => $config_id,
                        'store_id' => $store_id,
                        'store_pid' => $store_pid,
                        'ways_type' => '' . $ways->ways_type . '',
                        'ways_source' => $ways->ways_source,
                        'company' => $ways->company,
                        'total_amount' => $total_amount,
                        'remark' => $remark,
                        'device_id' => $device_id,
                        'shop_name' => $shop_name,
                        'merchant_id' => $merchant_id,
                        'store_name' => $store_name,
                        'tg_user_id' => $tg_user_id,
                    ];
                    return $this->mybank_pay_public($data_insert, $data_public);
                }

                //和融通-微信
                if ($ways && $ways->ways_type == 9002) {
                    $data_public = [
                        'pay_type' => 'wx',
                        'return_params' => '原样返回',
                        'title' => '微信收款',
                        'ways_source_desc' => '微信支付',
                        'ways_type_desc' => '微信支付',
                        'code' => $code,
                        'out_trade_no' => $out_trade_no,
                        'config_id' => $config_id,
                        'store_id' => $store_id,
                        'store_pid' => $store_pid,
                        'ways_type' => '' . $ways->ways_type . '',
                        'ways_source' => $ways->ways_source,
                        'company' => $ways->company,
                        'total_amount' => $total_amount,
                        'remark' => $remark,
                        'device_id' => $device_id,
                        'shop_name' => $shop_name,
                        'merchant_id' => $merchant_id,
                        'store_name' => $store_name,
                        'tg_user_id' => $store->user_id,
                    ];

                    return $this->h_pay_public($data_insert, $data_public);
                }

                //新大陆微信
                if ($ways && $ways->ways_type == 8002) {
                    //
                    $config = new NewLandConfigController();
                    $new_land_config = $config->new_land_config($config_id);
                    if (!$new_land_config) {
                        return json_encode([
                            'status' => 2,
                            'message' => '新大陆配置不存在请检查配置'
                        ]);
                    }

                    $mybank_merchant = $config->new_land_merchant($store_id, $store_pid);
                    if (!$mybank_merchant) {
                        return json_encode([
                            'status' => 2,
                            'message' => '商户新大陆通道未开通'
                        ]);
                    }
                    $request_data = [
                        'out_trade_no' => $out_trade_no,
                        'code' => $code,
                        'total_amount' => $total_amount,
                        'remark' => $remark,
                        'device_id' => $device_id,
                        'shop_name' => $shop_name,
                        'key' => $mybank_merchant->nl_key,
                        'org_no' => $new_land_config->org_no,
                        'merc_id' => $mybank_merchant->nl_mercId,
                        'trm_no' => $mybank_merchant->trmNo,
                        'op_sys' => '3',
                        'opr_id' => $merchant_id,
                        'trm_typ' => 'T',
                        'payChannel' => 'WXPAY',
                    ];

                    //入库参数
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['company'] = $ways->company;
                    $data_insert['ways_type_desc'] = '微信支付';
                    $data_insert['ways_source'] = $ways->ways_source;
                    $data_insert['ways_source_desc'] = '微信支付';


                    return $this->newland_pay_public($data_insert, $request_data);
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
                'message' => $exception->getMessage()
            ]);
        }
    }

    //京东收银支付宝微信等扫一扫公共部分
    public function jd_pay_public($data_insert, $data)
    {
        try {
//        $data = [
//            'config_id' => '',
//            'store_id' => '',
//            'store_pid' => '',
//            'out_trade_no' => '',
//            'ways_type' => '',
//            'ways_type_desc' => '',
//            'ways_source' => '',
//            'ways_source_desc' => '',
//            'total_amount' => '',
//            'remark' => '',
//            'device_id' => '',
//            'shop_name' => '',
//            'pay_type' => 'ALIPAY',
//            'return_params' => '原样返回',
//            'merchant_id' => '',
//            'store_name' => '',
//            'title' => '支付宝收款',
//        ];
            //读取配置
            $store_id = $data['store_id'];
            $store_pid = $data['store_pid'];
            $merchant_id = $data['merchant_id'];
            $total_amount = $data['total_amount'];
            $ways_type = $data['ways_type'];
            $store_name = $data['store_name'];
            $config_id = $data['config_id'];
            $desc = $data['ways_type_desc'];
            $title = $data['title'];
            $tg_user_id = $data['tg_user_id'];//推广员ID;
            $config = new JdConfigController();
            $jd_config = $config->jd_config($data['config_id']);
            if (!$jd_config) {
                return json_encode([
                    'status' => 2,
                    'message' => '京东配置不存在请检查配置'
                ]);
            }

            $jd_merchant = $config->jd_merchant($store_id, $store_pid);
            if (!$jd_merchant) {
                return json_encode([
                    'status' => 2,
                    'message' => '京东商户号不存在'
                ]);
            }

            $out_trade_no = $data['out_trade_no'];

            //入库参数
            $data_insert['out_trade_no'] = $out_trade_no;
            $data_insert['ways_type'] = $data['ways_type'];
            $data_insert['company'] = $data['company'];
            $data_insert['ways_type_desc'] = $data['ways_type_desc'];
            $data_insert['ways_source'] = $data['ways_source'];
            $data_insert['ways_source_desc'] = $data['ways_source_desc'];

            $insert_re = Order::create($data_insert);
            if (!$insert_re) {
                return json_encode([
                    'status' => 2,
                    'message' => '订单未入库'
                ]);
            }

            $obj = new \App\Api\Controllers\Jd\PayController();
            $data['notify_url'] = url('/api/jd/pay_notify_url');//回调地址
            $data['request_url'] = $obj->scan_url;//请求地址;
            $data['merchant_no'] = $jd_merchant->merchant_no;
            $data['md_key'] = $jd_merchant->md_key;//
            $data['des_key'] = $jd_merchant->des_key;//
            $data['systemId'] = $jd_config->systemId;//

            $return = $obj->scan_pay($data);
            if ($return['status'] == 0) {
                return json_encode([
                    'status' => 2,
                    'message' => $return['message']
                ]);
            }


            //返回支付成功
            if ($return['status'] == 1) {
                $trade_no = $return['data']['tradeNo'];
                $buyer_pay_amount = $return['data']['piAmount'] / 100;
                $pay_time = date('Y-m-d H:i:s', strtotime($return['data']['payFinishTime']));
                $buyer_pay_amount = number_format($buyer_pay_amount, 2, '.', '');
                Order::where('out_trade_no', $out_trade_no)->update(
                    [
                        'trade_no' => $trade_no,
                        'buyer_id' => '',
                        'buyer_logon_id' => '',
                        'status' => '1',
                        'pay_status_desc' => '支付成功',
                        'pay_status' => 1,
                        'payment_method' => '',
                        'pay_time' => $pay_time,
                        'buyer_pay_amount' => $buyer_pay_amount,//用户实际支付
                    ]);

                //支付成功后的动作
                $data = [
                    'ways_type' => $data['ways_type'],
                    'ways_type_desc' => $data['ways_type_desc'],
                    'source_type' => '6000',//返佣来源
                    'source_desc' => '京东金融',//返佣来源说明
                    'total_amount' => $total_amount,
                    'out_trade_no' => $out_trade_no,
                    'rate' => $data_insert['rate'],
                    'merchant_id' => $merchant_id,
                    'store_id' => $store_id,
                    'user_id' => $tg_user_id,
                    'config_id' => $config_id,
                    'store_name' => $store_name,
                    'ways_source' => $data['ways_source'],
                    'pay_time' => $pay_time,
                    'no_push' => '1',//不推送
                    'no_fuwu' => '1',//不服务消息
                    'no_print' => '1',//不打印
                    //'no_v' => '1',//不小盒子播报

                ];


                PaySuccessAction::action($data);


                return json_encode([
                    'status' => 1,
                    'pay_status' => '1',
                    'message' => '支付成功',
                    'data' => [
                        'out_trade_no' => $out_trade_no,
                        'ways_type' => $ways_type,
                        'total_amount' => $total_amount,
                        'store_id' => $store_id,
                        'store_name' => $store_name,
                        'config_id' => $config_id,
                        'pay_time' => $pay_time,
                        'trade_no' => $trade_no,
                        'out_transaction_id' => $out_trade_no,
                    ]
                ]);

            }

            //正在支付
            if ($return['status'] == 2) {
                return json_encode([
                    'status' => 9,//正在支付 app 和微收银 状态不一样
                    'pay_status' => '2',
                    'message' => '正在支付',
                    'data' => [
                        'out_trade_no' => $out_trade_no,
                        'ways_type' => $ways_type,
                        'total_amount' => $total_amount,
                        'store_id' => $store_id,
                        'store_name' => $store_name,
                        'config_id' => $config_id,
                    ]
                ]);
            }

            //支付失败
            if ($return['status'] == 3) {
                return json_encode([
                    'status' => 2,
                    'pay_status' => '3',
                    'message' => $return['message'],
                    'data' => [
                        'out_trade_no' => $out_trade_no,
                        'ways_type' => $ways_type,
                        'total_amount' => $total_amount,
                        'store_id' => $store_id,
                        'store_name' => $store_name,
                        'config_id' => $config_id,
                    ]
                ]);
            }

        } catch
        (\Exception $exception) {
            Log::info($exception);
            return json_encode([
                'status' => 2,
                'message' => $exception->getMessage()
            ]);
        }
    }


    //网商收银支付宝微信等扫一扫公共部分
    public function mybank_pay_public($data_insert, $data)
    {
        //读取配置
        $store_id = $data['store_id'];
        $store_pid = $data['store_pid'];
        $merchant_id = $data['merchant_id'];
        $total_amount = $data['total_amount'];
        $ways_type = $data['ways_type'];
        $store_name = $data['store_name'];
        $config_id = $data['config_id'];
        $desc = $data['ways_type_desc'];
        $title = $data['title'];
        $tg_user_id = $data['tg_user_id'];
        $remark = $data['remark'];
        $code = $data['code'];
        $type_source = $data['pay_type'];
        $config = new MyBankConfigController();


        $mybank_merchant = $config->mybank_merchant($store_id, $store_pid);
        if (!$mybank_merchant) {
            return json_encode([
                'status' => 2,
                'message' => '网商不存在'
            ]);
        }
        $wx_AppId=$mybank_merchant->wx_AppId;
        $MyBankConfig = $config->MyBankConfig($data['config_id'],$wx_AppId);
        if (!$MyBankConfig) {
            return json_encode([
                'status' => 2,
                'message' => '网商配置不存在请检查配置'
            ]);
        }
        $out_trade_no = $data['out_trade_no'];

        //入库参数
        $data_insert['out_trade_no'] = $out_trade_no;
        $data_insert['ways_type'] = $data['ways_type'];
        $data_insert['ways_type_desc'] = $data['ways_type_desc'];
        $data_insert['ways_source'] = $data['ways_source'];
        $data_insert['company'] = $data['company'];
        $data_insert['ways_source_desc'] = $data['ways_source_desc'];

        $insert_re = Order::create($data_insert);
        if (!$insert_re) {
            return json_encode([
                'status' => 2,
                'message' => '订单未入库'
            ]);
        }


        $data['type_source'] = $type_source;//alipay,weixin
        $data['out_trade_no'] = $out_trade_no;
        $data['mybank_merchant_id'] = $mybank_merchant->MerchantId;
        $data['merchant_id'] = $merchant_id;
        $data['code'] = $code;
        $data['TotalAmount'] = $total_amount * 100;//单位分
        $data['is_fq'] = 0;//0
        $data['fq_num'] = 3;//3
        $data['hb_fq_seller_percent'] = 0;//0
        $data['buydata'] = [];//数组
        $data['SettleType'] = 'T1';//T1
        $data['remark'] = $remark;
        $data['body'] = $title;
        $data['store_id'] = $store_id;
        $data['attach'] = $remark;//附加信息，原样返回。
        $data['PayLimit'] = "";//禁用方式
        $data['config_id'] = $config_id;


        $obj = new TradePayController();
        $return = $obj->TradePay($data);


        if ($return['status'] == 0) {
            return json_encode([
                'status' => 2,
                'message' => $return['message']
            ]);
        }

        //返回支付成功
        if ($return['status'] == 1) {
            $trade_no = $return['data']['MerchantOrderNo'];//条码订单
            $pay_time = date('Y-m-d H:i:s', strtotime($return['data']['GmtPayment']));
            $payment_method = strtolower($return['data']['Credit']);
            $buyer_id = '';
            //微信付款的id
            if ($data['type_source'] == 'weixin') {
                $buyer_id = $return['data']['SubOpenId'];
            }
            if ($data['type_source'] == 'alipay') {
                $buyer_id = $return['data']['BuyerUserId'];
            }

            Order::where('out_trade_no', $out_trade_no)->update(
                [
                    'trade_no' => $trade_no,
                    'buyer_id' => $buyer_id,
                    'status' => '1',
                    'pay_status_desc' => '支付成功',
                    'pay_status' => 1,
                    'payment_method' => $payment_method,
                    'pay_time' => $pay_time,
                ]);

            //支付成功后的动作
            $data = [
                'ways_type' => $data['ways_type'],
                'ways_type_desc' => $data['ways_type_desc'],
                'source_type' => '3000',//返佣来源
                'source_desc' => '网商银行',//返佣来源说明
                'total_amount' => $total_amount,
                'out_trade_no' => $out_trade_no,
                'rate' => $data_insert['rate'],
                'merchant_id' => $merchant_id,
                'store_id' => $store_id,
                'user_id' => $tg_user_id,
                'config_id' => $config_id,
                'store_name' => $store_name,
                'ways_source' => $data['ways_source'],
                'pay_time' => $pay_time,
                'no_push' => '1',//不推送
                'no_fuwu' => '1',//不服务消息
                'no_print' => '1',//不打印
                //'no_v' => '1',//不小盒子播报


            ];


            PaySuccessAction::action($data);

            $out_transaction_id = $out_trade_no;
            if ($type_source == "weixin") {
                $out_transaction_id = $trade_no;
            }

            return json_encode([
                'status' => 1,
                'pay_status' => '1',
                'message' => '支付成功',
                'data' => [
                    'out_trade_no' => $out_trade_no,
                    'ways_type' => $ways_type,
                    'total_amount' => $total_amount,
                    'store_id' => $store_id,
                    'store_name' => $store_name,
                    'config_id' => $config_id,
                    'pay_time' => $pay_time,
                    'trade_no' => $trade_no,
                    'out_transaction_id' => $out_transaction_id,

                ]
            ]);

        }

        //正在支付
        if ($return['status'] == 2) {
            return json_encode([
                'status' => 9,
                'pay_status' => '2',
                'message' => '正在支付',
                'data' => [
                    'out_trade_no' => $out_trade_no,
                    'ways_type' => $ways_type,
                    'total_amount' => $total_amount,
                    'store_id' => $store_id,
                    'store_name' => $store_name,
                    'config_id' => $config_id,
                ]
            ]);
        }

        //支付失败
        if ($return['status'] == 3) {
            return json_encode([
                'status' => 2,
                'pay_status' => '3',
                'message' => '支付失败',
                'data' => [
                    'out_trade_no' => $out_trade_no,
                    'ways_type' => $ways_type,
                    'total_amount' => $total_amount,
                    'store_id' => $store_id,
                    'store_name' => $store_name,
                    'config_id' => $config_id,
                ]
            ]);
        }


    }


    //和融通
    public function h_pay_public($data_insert, $data)
    {
        //读取配置
        $store_id = $data['store_id'];
        $store_pid = $data['store_pid'];
        $merchant_id = $data['merchant_id'];
        $total_amount = $data['total_amount'];
        $ways_type = $data['ways_type'];
        $store_name = $data['store_name'];
        $config_id = $data['config_id'];
        $desc = $data['ways_type_desc'];
        $title = $data['title'];
        $tg_user_id = $data['tg_user_id'];

        $config = new HConfigController();
        $h_config = $config->h_config($data['config_id']);

        if (!$h_config) {
            return json_encode([
                'status' => 2,
                'message' => '和融通配置不存在请检查配置'
            ]);
        }

        $h_merchant = $config->h_merchant($store_id, $store_pid);
        if (!$h_merchant) {
            return json_encode([
                'status' => 2,
                'message' => '和融通商户号不存在'
            ]);
        }

        $out_trade_no = $data['out_trade_no'];

        //入库参数
        $data_insert['out_trade_no'] = $out_trade_no;
        $data_insert['ways_type'] = $data['ways_type'];
        $data_insert['company'] = $data['company'];
        $data_insert['ways_type_desc'] = $data['ways_type_desc'];
        $data_insert['ways_source'] = $data['ways_source'];
        $data_insert['ways_source_desc'] = $data['ways_source_desc'];

        $insert_re = Order::create($data_insert);
        if (!$insert_re) {
            return json_encode([
                'status' => 2,
                'message' => '订单未入库'
            ]);
        }

        $obj = new \App\Api\Controllers\Huiyuanbao\PayController();
        $data['notify_url'] = url('/api/huiyuanbao/pay_notify');//回调地址
        $data['request_url'] = $obj->scan_url;//请求地址;
        $data['mid'] = $h_merchant->h_mid;
        $data['md_key'] = $h_config->md_key;//
        $data['orgNo'] = $h_config->orgNo;//
        $return = $obj->scan_pay($data);
        if ($return['status'] == 0) {
            return json_encode([
                'status' => 2,
                'message' => $return['message']
            ]);
        }


        //返回支付成功
        if ($return['status'] == 1) {
            $trade_no = '112121' . $return['data']['transactionId'];
            $pay_time = date('Y-m-d H:i:s', time());
            $buyer_pay_amount = $total_amount;
            $buyer_pay_amount = number_format($buyer_pay_amount, 2, '.', '');
            Order::where('out_trade_no', $out_trade_no)->update(
                [
                    'trade_no' => $trade_no,
                    'buyer_id' => '',
                    'buyer_logon_id' => '',
                    'status' => '1',
                    'pay_status_desc' => '支付成功',
                    'pay_status' => 1,
                    'payment_method' => '',
                    'pay_time' => $pay_time,
                    'buyer_pay_amount' => $buyer_pay_amount,//用户实际支付
                ]);


            //支付成功后的动作
            $data = [
                'ways_type' => $data['ways_type'],
                'ways_type_desc' => $data['ways_type_desc'],
                'source_type' => '9000',//返佣来源
                'source_desc' => '和融通',//返佣来源说明
                'total_amount' => $total_amount,
                'out_trade_no' => $out_trade_no,
                'rate' => $data_insert['rate'],
                'merchant_id' => $merchant_id,
                'store_id' => $store_id,
                'user_id' => $tg_user_id,
                'config_id' => $config_id,
                'store_name' => $store_name,
                'ways_source' => $data['ways_source'],
                'pay_time' => $pay_time,
                'no_push' => '1',//不推送
                'no_fuwu' => '1',//不服务消息
                'no_print' => '1',//不打印
                //'no_v' => '1',//不小盒子播报

            ];


            PaySuccessAction::action($data);


            return json_encode([
                'status' => 1,
                'pay_status' => '1',
                'message' => '支付成功',
                'data' => [
                    'out_trade_no' => $out_trade_no,
                    'ways_type' => $ways_type,
                    'total_amount' => $total_amount,
                    'store_id' => $store_id,
                    'store_name' => $store_name,
                    'config_id' => $config_id,
                    'pay_time' => $pay_time,
                    'trade_no' => $trade_no,
                    'out_transaction_id' => $trade_no,
                ]
            ]);

        }

        //正在支付
        if ($return['status'] == 2) {
            return json_encode([
                'status' => 9,
                'pay_status' => '2',
                'message' => '正在支付',
                'data' => [
                    'out_trade_no' => $out_trade_no,
                    'ways_type' => $ways_type,
                    'total_amount' => $total_amount,
                    'store_id' => $store_id,
                    'store_name' => $store_name,
                    'config_id' => $config_id,
                ]
            ]);
        }

        //支付失败
        if ($return['status'] == 3) {
            return json_encode([
                'status' => 2,
                'pay_status' => '3',
                'message' => '支付失败',
                'data' => [
                    'out_trade_no' => $out_trade_no,
                    'ways_type' => $ways_type,
                    'total_amount' => $total_amount,
                    'store_id' => $store_id,
                    'store_name' => $store_name,
                    'config_id' => $config_id,
                ]
            ]);
        }


    }


    //新大陆
    public function newland_pay_public($data_insert, $data_request)
    {
        try {

            $obj = new \App\Api\Controllers\Newland\PayController();
            $return = $obj->scan_pay($data_request);

            if ($return['status'] == 0) {
                return json_encode([
                    'status' => 2,
                    'message' => $return['message']
                ]);
            }

            $insert_re = Order::create($data_insert);
            if (!$insert_re) {
                return json_encode([
                    'status' => 2,
                    'message' => '订单未入库'
                ]);
            }
            //返回支付成功
            if ($return['status'] == 1) {
                $trade_no = $return['data']['orderNo'];
                $pay_time = date('Y-m-d H:i:s', strtotime($return['data']['sysTime']));
                $payment_method = '';
                $buyer_id = '';

                Order::where('out_trade_no', $data_insert['out_trade_no'])->update(
                    [
                        'trade_no' => $trade_no,
                        'buyer_id' => $buyer_id,
                        'status' => '1',
                        'pay_status_desc' => '支付成功',
                        'pay_status' => 1,
                        'payment_method' => $payment_method,
                        'pay_time' => $pay_time,
                    ]);

                //支付成功后的动作
                $data = [
                    'ways_type' => $data_insert['ways_type'],
                    'ways_type_desc' => $data_insert['ways_type_desc'],
                    'source_type' => '8000',//返佣来源
                    'source_desc' => '新大陆',//返佣来源说明
                    'total_amount' => $data_insert['total_amount'],
                    'out_trade_no' => $data_insert['out_trade_no'],
                    'rate' => $data_insert['rate'],
                    'merchant_id' => $data_insert['merchant_id'],
                    'store_id' => $data_insert['store_id'],
                    'user_id' => $data_insert['user_id'],
                    'config_id' => $data_insert['config_id'],
                    'store_name' => $data_insert['store_name'],
                    'ways_source' => $data_insert['ways_source'],
                    'pay_time' => $pay_time,
                    'no_push' => '1',//不推送
                    'no_fuwu' => '1',//不服务消息
                    'no_print' => '1',//不打印
                    //'no_v' => '1',//不小盒子播报

                ];


                PaySuccessAction::action($data);


                return json_encode([
                    'status' => 1,
                    'pay_status' => '1',
                    'message' => '支付成功',
                    'data' => [
                        'out_trade_no' => $data_insert['out_trade_no'],
                        'ways_type' => $data_insert['ways_type'],
                        'total_amount' => $data_insert['total_amount'],
                        'store_id' => $data_insert['store_id'],
                        'store_name' => $data_insert['store_name'],
                        'config_id' => $data_insert['config_id'],
                        'pay_time' => $pay_time,
                        'trade_no' => $trade_no,
                        'out_transaction_id' => $trade_no,
                    ]
                ]);

            }


            //正在支付
            if ($return['status'] == 2) {
                return json_encode([
                    'status' => 9,
                    'pay_status' => '2',
                    'message' => '正在支付',
                    'data' => [
                        'out_trade_no' => $data_insert['out_trade_no'],
                        'ways_type' => $data_insert['ways_type'],
                        'total_amount' => $data_insert['total_amount'],
                        'store_id' => $data_insert['store_id'],
                        'store_name' => $data_insert['store_name'],
                        'config_id' => $data_insert['config_id'],
                    ]
                ]);
            }

            //支付失败
            if ($return['status'] == 3) {
                return json_encode([
                    'status' => 2,
                    'pay_status' => '3',
                    'message' => '支付失败',
                    'data' => [
                        'out_trade_no' => $data_insert['out_trade_no'],
                        'ways_type' => $data_insert['ways_type'],
                        'total_amount' => $data_insert['total_amount'],
                        'store_id' => $data_insert['store_id'],
                        'store_name' => $data_insert['store_name'],
                        'config_id' => $data_insert['config_id'],
                    ]
                ]);
            }


        } catch (\Exception $exception) {
            Log::info($exception);
            return json_encode([
                'status' => 2,
                'pay_status' => '3',
                'message' => $exception->getMessage()
            ]);
        }

    }


    //联拓富收银支付宝微信等扫一扫公共部分
    public function ltf_pay_public($data_insert, $data)
    {

        //读取配置
        $store_id = $data['store_id'];
        $store_pid = $data['store_pid'];
        $merchant_id = $data['merchant_id'];
        $total_amount = $data['total_amount'];
        $ways_type = $data['ways_type'];
        $store_name = $data['store_name'];
        $config_id = $data['config_id'];
        $desc = $data['ways_type_desc'];
        $title = $data['title'];
        $tg_user_id = $data['tg_user_id'];
        $config = new LtfConfigController();

        $ltf_merchant = $config->ltf_merchant($store_id, $store_pid);
        if (!$ltf_merchant) {
            return json_encode([
                'status' => 2,
                'message' => '商户号不存在'
            ]);
        }

        $out_trade_no = $data['out_trade_no'];

        //入库参数
        $data_insert['out_trade_no'] = $out_trade_no;
        $data_insert['ways_type'] = $data['ways_type'];
        $data_insert['ways_type_desc'] = $data['ways_type_desc'];
        $data_insert['company'] = $data['company'];
        $data_insert['ways_source'] = $data['ways_source'];
        $data_insert['ways_source_desc'] = $data['ways_source_desc'];

        $insert_re = Order::create($data_insert);
        if (!$insert_re) {
            return json_encode([
                'status' => 2,
                'message' => '订单未入库'
            ]);
        }

        $obj = new \App\Api\Controllers\Ltf\PayController();
        $data['notify_url'] = url('/api/ltf/pay_notify_url');//回调地址
        $data['request_url'] = $obj->scan_url;//请求地址;
        $data['merchant_no'] = $ltf_merchant->merchantCode;
        $data['appId'] = $ltf_merchant->appId;//
        $data['key'] = $ltf_merchant->md_key;//


        $return = $obj->scan_pay($data);
        if ($return['status'] == 0) {
            return json_encode([
                'status' => 2,
                'message' => $return['message']
            ]);
        }


        //返回支付成功
        if ($return['status'] == 1) {
            $trade_no = $return['data']['outTransactionId'];
            $buyer_id = $return['data']['buyerId'];
            $pay_time = date('Y-m-d H:i:s', strtotime($return['data']['payTime']));
            $buyer_pay_amount = $return['data']['receiptAmount'];
            $buyer_pay_amount = number_format($buyer_pay_amount, 2, '.', '');
            Order::where('out_trade_no', $out_trade_no)->update(
                [
                    'trade_no' => $trade_no,
                    'buyer_id' => $buyer_id,
                    'buyer_logon_id' => '',
                    'status' => '1',
                    'pay_status_desc' => '支付成功',
                    'pay_status' => 1,
                    'payment_method' => '',
                    'pay_time' => $pay_time,
                    'buyer_pay_amount' => $buyer_pay_amount,//用户实际支付
                ]);


            //支付成功后的动作
            $data = [
                'ways_type' => $data['ways_type'],
                'ways_type_desc' => $data['ways_type_desc'],
                'source_type' => '10000',//返佣来源
                'source_desc' => '联拓富',//返佣来源说明
                'total_amount' => $total_amount,
                'out_trade_no' => $out_trade_no,
                'rate' => $data_insert['rate'],
                'merchant_id' => $merchant_id,
                'store_id' => $store_id,
                'user_id' => $tg_user_id,
                'config_id' => $config_id,
                'store_name' => $store_name,
                'ways_source' => $data['ways_source'],
                'pay_time' => $pay_time,

            ];


            PaySuccessAction::action($data);


            return json_encode([
                'status' => 1,
                'pay_status' => '1',
                'message' => '支付成功',
                'data' => [
                    'out_trade_no' => $out_trade_no,
                    'ways_type' => $ways_type,
                    'total_amount' => $total_amount,
                    'store_id' => $store_id,
                    'store_name' => $store_name,
                    'config_id' => $config_id,
                    'pay_time' => $pay_time,
                    'trade_no' => $trade_no,
                    'ways_source' => $data['ways_source'],
                    'out_transaction_id' => $trade_no,


                ]
            ]);

        }

        //正在支付
        if ($return['status'] == 2) {
            return json_encode([
                'status' => 9,
                'pay_status' => '2',
                'message' => '正在支付',
                'data' => [
                    'out_trade_no' => $out_trade_no,
                    'ways_type' => $ways_type,
                    'total_amount' => $total_amount,
                    'store_id' => $store_id,
                    'store_name' => $store_name,
                    'config_id' => $config_id,
                ]
            ]);
        }

        //支付失败
        if ($return['status'] == 3) {
            return json_encode([
                'status' => 2,
                'pay_status' => '3',
                'message' => '支付失败',
                'data' => [
                    'out_trade_no' => $out_trade_no,
                    'ways_type' => $ways_type,
                    'total_amount' => $total_amount,
                    'store_id' => $store_id,
                    'store_name' => $store_name,
                    'config_id' => $config_id,

                ]
            ]);
        }


    }


}