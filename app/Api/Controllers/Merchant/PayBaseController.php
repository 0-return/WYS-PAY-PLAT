<?php
/**
 * Created by PhpStorm.
 * User: dmk
 * Date: 2017/3/21
 * Time: 15:48
 */

namespace App\Api\Controllers\Merchant;


use Alipayopen\Sdk\AopClient;
use Alipayopen\Sdk\Request\AlipayTradeCancelRequest;
use Alipayopen\Sdk\Request\AlipayTradePayRequest;
use Alipayopen\Sdk\Request\AlipayTradeQueryRequest;
use App\Api\Controllers\AlipayOpen\PayController;
use App\Api\Controllers\BaseController;
use App\Api\Controllers\Config\AlipayIsvConfigController;
use App\Api\Controllers\Config\FuiouConfigController;
use App\Api\Controllers\Config\HConfigController;
use App\Api\Controllers\Config\JdConfigController;
use App\Api\Controllers\Config\LtfConfigController;
use App\Api\Controllers\Config\MyBankConfigController;
use App\Api\Controllers\Config\NewLandConfigController;
use App\Api\Controllers\Config\PayWaysController;
use App\Api\Controllers\Config\WeixinConfigController;
use App\Api\Controllers\MyBank\QrpayController;
use App\Api\Controllers\MyBank\TradePayController;
use App\Common\MerchantFuwu;
use App\Common\PaySuccessAction;
use App\Common\StoreDayMonthOrder;
use App\Common\UserGetMoney;
use App\Models\AlipayAccount;
use App\Models\AlipayAppOauthUsers;
use App\Models\AlipayIsvConfig;
use App\Models\Merchant;
use App\Models\MerchantStore;
use App\Models\MyBankStore;
use App\Models\Order;
use App\Models\QrPayInfo;
use App\Models\Store;
use App\Models\StorePayWay;
use App\Models\WeixinConfig;
use EasyWeChat\Factory;
use function EasyWeChat\Kernel\Support\get_client_ip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;


class PayBaseController extends BaseController
{

    //扫一扫 提交
    public function scan_pay(Request $request)
    {
        try {

            $merchant = $this->parseToken();
            $config_id = $merchant->config_id;
            $merchant_id = $merchant->merchant_id;
            $merchant_name = $merchant->merchant_name;

            $code = $request->get('code');
            $total_amount = $request->get('total_amount');
            $shop_price = $request->get('shop_price', $total_amount);
            $remark = $request->get('remark', '');
            $device_id = $request->get('device_id', 'app');
            $shop_name = $request->get('shop_name', '扫一扫收款');
            $shop_desc = $request->get('shop_desc', '扫一扫收款');
            $store_id = $request->get('store_id', '');
            $other_no = $request->get('other_no', '');
            $check_data = [
                'total_amount' => '付款金额',
                'code' => '付款码编号',
            ];
            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }


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
            return $this->scan_pay_public($data);

        } catch
        (\Exception $exception) {
            Log::info($exception);
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage()
            ]);
        }
    }


    //扫一扫 公共
    public function scan_pay_public($data)
    {
        try {
            $config_id = $data['config_id'];
            $merchant_id = $data['merchant_id'];
            $merchant_name = $data['merchant_name'];
            $code = $data['code'];
            $total_amount = $data['total_amount'];
            $shop_price = $data['shop_price'];
            $remark = $data['remark'];
            $device_id = $data['device_id'];
            $shop_name = $data['shop_name'];
            $shop_desc = $data['shop_desc'];
            $store_id = $data['store_id'];
            $other_no = $data['other_no'];

            //没有传门店
            if ($store_id == "") {
                $MerchantStore = MerchantStore::where('merchant_id', $merchant_id)
                    ->orderBy('created_at', 'asc')
                    ->select('store_id')
                    ->first();
                if (!$MerchantStore) {
                    return json_encode([
                        'status' => 2,
                        'message' => '请提交门店资料认证'
                    ]);
                }
                $store_id = $MerchantStore->store_id;

            }
            $store = Store::where('store_id', $store_id)
                ->select('store_name', 'pid', 'user_id', 'is_admin_close', 'is_delete', 'is_close')
                ->first();
            if (!$store) {
                return json_encode([
                    'status' => 2,
                    'message' => '门店未认证'
                ]);
            }

            //关闭的商户禁止交易
            if ($store->is_close || $store->is_admin_close || $store->is_delete) {
                return json_encode([
                    'status' => 2,
                    'message' => '商户已经被服务商关闭'
                ]);
            }


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
                'other_no' => $other_no,
            ];


            $str = substr($code, 0, 2);

            /**支付宝渠道开始**/
            if (in_array($str, ['28'])) {

                //读取优先为高级的通道
                $obj_ways = new PayWaysController();
                $ways = $obj_ways->ways_source('alipay', $store_id, $store_pid);

                if (!$ways) {
                    return json_encode([
                        'status' => 2,
                        'message' => '没有开通此类型通道'
                    ]);
                }

                $out_trade_no = 'aliscan' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                //扫码费率入库
                $data_insert['rate'] = $ways->rate;
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
                    $alipay_store_id = $storeInfo->alipay_store_id;
                    $out_store_id = $storeInfo->out_store_id;
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

                        Order::where('out_trade_no', $out_trade_no)->update(
                            [
                                'trade_no' => $trade_no,
                                'buyer_id' => $buyer_id,
                                'buyer_logon_id' => $buyer_logon_id,
                                'status' => 'TRADE_SUCCESS',
                                'pay_status_desc' => '支付成功',
                                'pay_status' => 1,
                                'payment_method' => $payment_method
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
                            'device_id'=>$device_id,
                        ];


                        PaySuccessAction::action($data);


                        return json_encode([
                            'status' => 1,
                            'pay_status' => '1',
                            'message' => '支付成功',
                            'data' => [
                                'out_trade_no' => $out_trade_no,
                                'ways_type' => $ways->ways_type,
                                'ways_source' => $ways->ways_source,
                                'total_amount' => $total_amount,
                                'store_id' => $store_id,
                                'store_name' => $store_name,
                                'config_id' => $config_id,
                                'pay_time' => $gmt_payment,
                            ]
                        ]);


                    }
                    //正在支付
                    if (!empty($resultCode) && $resultCode == 10003) {

                        return json_encode([
                            'status' => 1,
                            'pay_status' => '2',
                            'message' => '正在支付',
                            'data' => [
                                'out_trade_no' => $out_trade_no,
                                'ways_type' => $ways->ways_type,
                                'total_amount' => $total_amount,
                                'store_id' => $store_id,
                                'store_name' => $store_name,
                                'config_id' => $config_id,
                            ]
                        ]);

                    }
                    $msg = $result->$responseNode->sub_msg;//错误信息

                    //其他支付失败
                    return json_encode([
                        'status' => 2,
                        'pay_status' => '3',
                        'message' => $msg,
                        'data' => [
                            'out_trade_no' => $out_trade_no,
                            'ways_type' => $ways->ways_type,
                            'total_amount' => $total_amount,
                            'store_id' => $store_id,
                            'store_name' => $store_name,
                            'config_id' => $config_id,
                        ]
                    ]);
                }

                //京东收银支付宝
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
                        'tg_user_id' => $tg_user_id,
                    ];

                    return $this->jd_pay_public($data_insert, $data_public);

                }

                //网商支付宝
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
                    //
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
                    $data_insert['out_store_id'] = $new_land_merchant->nl_mercId;


                    return $this->newland_pay_public($data_insert, $request_data);
                }

                //和融通支付宝
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
                        'tg_user_id' => $tg_user_id,
                    ];

                    return $this->h_pay_public($data_insert, $data_public);

                }

                //ltf收银支付宝
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
                        'tg_user_id' => $tg_user_id,
                    ];

                    return $this->ltf_pay_public($data_insert, $data_public);

                }

                //富友支付宝
                if ($ways && $ways->ways_type == 11001) {
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
                        'tg_user_id' => $tg_user_id,
                    ];

                    return $this->fuiou_pay_public($data_insert, $data_public);

                }

            }

            /**支付宝渠道结束**/
            if (in_array($str, ['13'])) {
                $obj_ways = new PayWaysController();
                $ways = $obj_ways->ways_source('weixin', $store_id, $store_pid);

                if (!$ways) {
                    return json_encode([
                        'status' => 2,
                        'message' => '没有开通此类型通道'
                    ]);
                }
                //扫码费率入库
                $data_insert['rate'] = $ways->rate;
                $data_insert['fee_amount'] = $ways->rate * $total_amount / 100;

                $out_trade_no = 'wx_scan' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
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
                    $data_insert['company'] = $ways->company;
                    $data_insert['ways_type_desc'] = '微信支付';
                    $data_insert['ways_source'] = 'weixin';
                    $data_insert['ways_source_desc'] = '微信支付';

                    $type = $ways->ways_type;
                    $attach = $store_id . ',' . $config_id;//附加信息原样返回
                    $goods_detail = [];
                    //入库参数
                    $insert_re = Order::create($data_insert);
                    if (!$insert_re) {
                        return json_encode([
                            'status' => 2,
                            'pay_status' => '3',
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
                                'device_id'=>$device_id,
                            ];


                            PaySuccessAction::action($data);

                            return json_encode([
                                'status' => 1,
                                'pay_status' => '1',
                                'message' => '支付成功',
                                'data' => [
                                    'out_trade_no' => $out_trade_no,
                                    'ways_type' => $ways->ways_type,
                                    'ways_source' => $ways->ways_source,
                                    'total_amount' => $total_amount,
                                    'store_id' => $store_id,
                                    'store_name' => $store_name,
                                    'config_id' => $config_id,
                                    'pay_time' => $result['time_end'],
                                ]
                            ]);


                        } else {

                            if ($result['err_code'] == "USERPAYING") {
                                return json_encode([
                                    'status' => 1,
                                    'pay_status' => '2',
                                    'message' => '正在支付',
                                    'data' => [
                                        'out_trade_no' => $out_trade_no,
                                        'ways_type' => $ways->ways_type,
                                        'total_amount' => $total_amount,
                                        'store_id' => $store_id,
                                        'store_name' => $store_name,
                                        'config_id' => $config_id,
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
                        'tg_user_id' => $tg_user_id,
                    ];

                    return $this->jd_pay_public($data_insert, $data_public);
                }
                //网商微信
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
                        'payChannel' => 'WXPAY',
                    ];

                    //入库参数
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['ways_type_desc'] = '微信支付';
                    $data_insert['ways_source'] = $ways->ways_source;
                    $data_insert['ways_source_desc'] = '微信支付';
                    $data_insert['company'] = $ways->company;
                    $data_insert['out_store_id'] = $new_land_merchant->nl_mercId;


                    return $this->newland_pay_public($data_insert, $request_data);
                }
                //和融通微信
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
                        'tg_user_id' => $tg_user_id,
                    ];

                    return $this->h_pay_public($data_insert, $data_public);

                }
                //ltf收银微信
                if ($ways && $ways->ways_type == 10002) {
                    $data_public = [
                        'pay_type' => 'WEIXIN',
                        'return_params' => '原样返回',
                        'title' => '微信支付',
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

                    return $this->ltf_pay_public($data_insert, $data_public);

                }

                //富友微信
                if ($ways && $ways->ways_type == 11002) {
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
                        'tg_user_id' => $tg_user_id,
                    ];

                    return $this->fuiou_pay_public($data_insert, $data_public);

                }

            }


            //银联或者京东支付
            if (in_array($str, ['62'])) {

                $obj_ways = new PayWaysController();
                $ways = $obj_ways->ways_source_un_qr('unionpay', $store_id, $store_pid);

                if (!$ways) {
                    return json_encode([
                        'status' => 2,
                        'message' => '没有开通此类型通道'
                    ]);
                }

                //扫码费率入库
                $data_insert['rate'] = $ways->rate;
                $data_insert['fee_amount'] = $ways->rate * $total_amount / 100;


                $out_trade_no = 'un_scan' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));

                //京东收银-京东支付
                if ($ways && $ways->ways_type == 6003) {
                    $data_public = [
                        'pay_type' => 'JDPAY',
                        'return_params' => '原样返回',
                        'title' => '京东金融',
                        'ways_source_desc' => '京东金融',
                        'ways_type_desc' => '京东金融',
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

                    return $this->jd_pay_public($data_insert, $data_public);

                }

                if ($ways && $ways->ways_type == 6004) {


                    if ($total_amount < 1000) {
                        //扫码费率入库
                        $data_insert['rate'] = $ways->rate_a;
                        $data_insert['fee_amount'] = $ways->rate_a * $total_amount / 100;

                    } else {
                        //扫码费率入库
                        $data_insert['rate'] = $ways->rate_c;
                        $data_insert['fee_amount'] = $ways->rate_c * $total_amount / 100;

                    }


                    $data_public = [
                        'pay_type' => 'JDPAY',
                        'return_params' => '原样返回',
                        'title' => '银联扫码',
                        'ways_source_desc' => '银联扫码',
                        'ways_type_desc' => '银联扫码',
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

                    return $this->jd_pay_public($data_insert, $data_public);

                }
                //新大陆银联
                if ($ways && $ways->ways_type == 8004) {
                    //
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

                    if ($total_amount < 1000) {
                        //扫码费率入库
                        $data_insert['rate'] = $ways->rate_a;
                        $data_insert['fee_amount'] = $ways->rate_a * $total_amount / 100;

                    } else {
                        //扫码费率入库
                        $data_insert['rate'] = $ways->rate_c;
                        $data_insert['fee_amount'] = $ways->rate_c * $total_amount / 100;

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
                        'payChannel' => 'YLPAY',
                    ];

                    //入库参数
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['ways_type_desc'] = '银联扫码';
                    $data_insert['company'] = $ways->company;
                    $data_insert['ways_source'] = $ways->ways_source;
                    $data_insert['ways_source_desc'] = '银联扫码';


                    return $this->newland_pay_public($data_insert, $request_data);
                }

            }


            return json_encode([
                'status' => 2,
                'message' => '暂不支持此二维码'
            ]);


        } catch
        (\Exception $exception) {
            Log::info($exception);
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage()
            ]);
        }
    }

    //生成二维码收款接口
    public function qr_pay(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $config_id = $merchant->config_id;
            $merchant_id = $merchant->merchant_id;
            $store_id = $request->get('store_id', '');
            $total_amount = $request->get('total_amount', '');
            $shop_price = $request->get('shop_price', $total_amount);
            $remark = $request->get('remark', '');
            $device_id = $request->get('device_id', 'app');
            $shop_name = $request->get('shop_name', '二维码收款');
            $shop_desc = $request->get('shop_desc', '二维码收款');
            $ways_source = $request->get('ways_source');
            $ways_type = $request->get('ways_type');
            $notify_url = $request->get('notify_url', '');
            $other_no = $request->get('other_no', '');
            $merchant_name = $merchant->merchant_name;


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

            return $this->qr_pay_public($data);


        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage()
            ]);
        }
    }

    //生成二维码-公共
    public function qr_pay_public($data)
    {
        try {
            $config_id = $data['config_id'];
            $merchant_id = $data['merchant_id'];
            $merchant_name = $data['merchant_name'];
            $total_amount = isset($data['total_amount']) ? $data['total_amount'] : '';
            $shop_price = isset($data['shop_price']) ? $data['shop_price'] : "";
            $remark = $data['remark'];
            $device_id = $data['device_id'];
            $shop_name = $data['shop_name'];
            $shop_desc = $data['shop_desc'];
            $store_id = $data['store_id'];
            $other_no = $data['other_no'];
            $ways_source = $data['ways_source'];
            $ways_type = $data['ways_type'];
            $notify_url = $data['notify_url'];

            //没有传门店
            if ($store_id == "") {
                $MerchantStore = MerchantStore::where('merchant_id', $merchant_id)
                    ->orderBy('created_at', 'asc')
                    ->select('store_id')
                    ->first();
                if (!$MerchantStore) {
                    return json_encode([
                        'status' => 2,
                        'message' => '请提交门店资料认证'
                    ]);
                }
                $store_id = $MerchantStore->store_id;

            }
            $store = Store::where('store_id', $store_id)->first();
            if (!$store) {
                return json_encode([
                    'status' => 2,
                    'message' => '门店未认证'
                ]);
            }
            //关闭的商户禁止交易
            if ($store->is_close || $store->is_admin_close || $store->is_delete) {
                return json_encode([
                    'status' => 2,
                    'message' => '商户已经被服务商关闭'
                ]);
            }

            $store_name = $store->store_short_name;
            $store_pid = $store->pid;
            $tg_user_id = $store->user_id;

            //返回静态码 store_id 必须传
            if ($total_amount == "") {
                //收银员的聚合收款码
                $code_url = url('/qr?store_id=' . $store_id . '&merchant_id=' . $merchant_id . '&notify_url=' . $notify_url . '&other_no=' . $other_no);
                $return = json_encode([
                    'status' => 1,
                    'data' => [
                        'code_url' => $code_url,
                        'store_name' => $store_name,
                        'out_trade_no' => '',
                        'other_no' => $other_no,

                    ]
                ]);

                return $return;
            }


            $obj_ways = new  PayWaysController();
            $ways = $obj_ways->ways_type($ways_type, $store_id, $store_pid);

            if (!$ways) {
                return json_encode([
                    'status' => 2,
                    'message' => '没有开通此类型通道'
                ]);
            }


            $data = [
                'config_id' => $config_id,
                'store_id' => $store_id,
                'merchant_id' => $merchant_id,
                'total_amount' => $total_amount,
                'shop_price' => $shop_price,
                'remark' => $remark,
                'device_id' => $device_id,
                'config_type' => '01',
                'shop_name' => $shop_name,
                'shop_desc' => $shop_desc,
                'store_name' => $store_name,
                'is_fq' => 0,
            ];


            //插入数据库
            $data_insert = [
                'trade_no' => '',
                'store_id' => $store_id,
                'store_name' => $store_name,
                'buyer_id' => '',
                'total_amount' => $total_amount,
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
                'user_id' => $tg_user_id,
                'notify_url' => $notify_url,
                'other_no' => $other_no,
                'company' => $ways->company,
            ];
            $out_trade_no = date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));

            //扫码费率入库
            $data_insert['rate'] = $ways->rate;
            $data_insert['fee_amount'] = $ways->rate * $total_amount / 100;

            /*官方支付宝*/
            if (999 < $ways_type && $ways_type < 1999) {
                $config_type = '01';
                $notify_url = url('/api/alipayopen/qr_pay_notify');
                //配置
                $isvconfig = new AlipayIsvConfigController();
                $storeInfo = $isvconfig->alipay_auth_info($store_id, $store_pid);
                $out_user_id = $storeInfo->user_id;//商户的id
                $alipay_store_id = $storeInfo->alipay_store_id;
                $out_store_id = $storeInfo->out_store_id;
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
                    return json_encode([
                        'status' => 2,
                        'message' => $msg
                    ]);

                }


                $config = $isvconfig->AlipayIsvConfig($config_id, $config_type);


                $pay_obj = new PayController();
                $out_trade_no = 'ali_qr' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                $data['out_trade_no'] = $out_trade_no;
                $data['alipay_store_id'] = $alipay_store_id;
                $data['out_store_id'] = $out_store_id;
                $data['out_user_id'] = $out_user_id;
                $data['app_auth_token'] = $storeInfo->app_auth_token;
                $data['config'] = $config;
                $data['notify_url'] = $notify_url;


                $return = $pay_obj->qr_pay($data);
                $return_aray = json_decode($return, true);
                if ($return_aray['status'] == 1) {
                    //入库参数
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['ways_type_desc'] = '支付宝';
                    $data_insert['ways_source'] = 'alipay';
                    $data_insert['ways_source_desc'] = '支付宝';
                    $data_insert['out_trade_no'] = $out_trade_no;

                    $insert_re = Order::create($data_insert);

                    if (!$insert_re) {
                        return json_encode([
                            'status' => 2,
                            'message' => '订单未入库'
                        ]);
                    }
                }


                return $return;

            }

            /**官方微信*/
            if (1999 < $ways_type && $ways_type < 2999) {
                $out_trade_no = 'wx_qr' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                $data['goods_detail'] = [];
                $data['open_id'] = '';
                $data['out_trade_no'] = $out_trade_no;
                $data['attach'] = $store_id . ',' . $config_id;//附加信息原样返回

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
                $data['wx_sub_merchant_id'] = $wx_sub_merchant_id;
                $data['options'] = $options;


                $pay_obj = new \App\Api\Controllers\Weixin\PayController();
                $return = $pay_obj->qr_pay($data, 'NATIVE');
                $return_array = json_decode($return, true);
                if ($return_array['status'] == 1) {
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['ways_type_desc'] = '微信支付';
                    $data_insert['ways_source'] = 'weixin';
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $data_insert['ways_source_desc'] = '微信支付';
                    $insert_re = Order::create($data_insert);
                    if (!$insert_re) {
                        return json_encode([
                            'status' => 2,
                            'message' => '订单未入库'
                        ]);
                    }

                    $return = json_encode([
                        'status' => 1,
                        'data' => [
                            'code_url' => $return_array['data']['code_url'],
                            'store_name' => $store_name,
                            'out_trade_no' => $out_trade_no
                        ]
                    ]);
                }

                return $return;
            }

            //京东支付二维码
            if (5999 < $ways_type && $ways_type < 6999) {
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
                $obj = new \App\Api\Controllers\Jd\PayController();
                if ($ways_type == 6001) {
                    $out_trade_no = 'ali_qr' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    //入库参数
                    $data_insert['ways_type'] = $ways_type;
                    $data_insert['ways_type_desc'] = '支付宝';
                    $data_insert['ways_source'] = 'alipay';
                    $data_insert['ways_source_desc'] = '支付宝';
                    $data_insert['out_trade_no'] = $out_trade_no;
                }
                if ($ways_type == 6002) {
                    $out_trade_no = 'wx_qr' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    //入库参数
                    $data_insert['ways_type'] = $ways_type;
                    $data_insert['ways_type_desc'] = '微信支付';
                    $data_insert['ways_source'] = 'weixin';
                    $data_insert['ways_source_desc'] = '微信支付';
                    $data_insert['out_trade_no'] = $out_trade_no;
                }
                if ($ways_type == 6003) {
                    $out_trade_no = 'jd_qr' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    //入库参数
                    $data_insert['ways_type'] = $ways_type;
                    $data_insert['ways_type_desc'] = '京东支付';
                    $data_insert['ways_source'] = 'jd';
                    $data_insert['ways_source_desc'] = '京东支付';
                    $data_insert['out_trade_no'] = $out_trade_no;
                }
                if ($ways_type == 6004) {
                    $out_trade_no = 'un_qr' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    //入库参数
                    $data_insert['ways_type'] = $ways_type;
                    $data_insert['ways_type_desc'] = '银联扫码';
                    $data_insert['ways_source'] = 'unionpay';
                    $data_insert['ways_source_desc'] = '银联扫码';
                    $data_insert['out_trade_no'] = $out_trade_no;

                    if ($total_amount < 1000) {
                        //扫码费率入库
                        $data_insert['rate'] = $ways->rate_a;
                        $data_insert['fee_amount'] = $ways->rate_a * $total_amount / 100;

                    } else {
                        //扫码费率入库
                        $data_insert['rate'] = $ways->rate_c;
                        $data_insert['fee_amount'] = $ways->rate_c * $total_amount / 100;

                    }

                }
                $insert_re = Order::create($data_insert);

                if (!$insert_re) {
                    return json_encode([
                        'status' => 2,
                        'message' => '订单未入库'
                    ]);
                }

                $data = [];
                $data['out_trade_no'] = $out_trade_no;
                $data['request_url'] = $obj->send_qr_url;//请求地址;
                $data['notify_url'] = url('/api/jd/notify_url');//地址;
                $data['merchant_no'] = $jd_merchant->merchant_no;
                $data['md_key'] = $jd_merchant->md_key;//
                $data['des_key'] = $jd_merchant->des_key;//
                $data['systemId'] = $jd_config->systemId;//
                $data['total_amount'] = $total_amount;
                $data['remark'] = $remark;
                $data['return_params'] = '原样返回';

                $return = $obj->send_qr($data);
                if ($return['status'] == 1) {
                    return json_encode([
                        'status' => 1,
                        'data' => [
                            'code_url' => $return['code_url'],
                            'store_name' => $store_name,
                            'out_trade_no' => $out_trade_no
                        ]
                    ]);
                } else {
                    return json_encode([
                        'status' => 2,
                        'message' => $return['message']
                    ]);
                }

            }

            //网商银行二维码
            if (2999 < $ways_type && $ways_type < 3999) {
                $config = new MyBankConfigController();

                $mybank_merchant = $config->mybank_merchant($store_id, $store_pid);
                if (!$mybank_merchant) {
                    return json_encode([
                        'status' => 2,
                        'message' => '网商不存在'
                    ]);
                }
                $wx_AppId = $mybank_merchant->wx_AppId;
                $MyBankConfig = $config->MyBankConfig($data['config_id'], $wx_AppId);
                if (!$MyBankConfig) {
                    return json_encode([
                        'status' => 2,
                        'message' => '网商配置不存在请检查配置'
                    ]);
                }

                if ($ways_type == 3001) {
                    $ChannelType = 'ALI';
                    $out_trade_no = 'ali_qr' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    //入库参数
                    $data_insert['ways_type'] = $ways_type;
                    $data_insert['ways_type_desc'] = '支付宝';
                    $data_insert['ways_source'] = 'alipay';
                    $data_insert['ways_source_desc'] = '支付宝';
                    $data_insert['out_trade_no'] = $out_trade_no;
                }
                if ($ways_type == 3002) {
                    $ChannelType = 'WX';
                    $out_trade_no = 'wx_qr' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    //入库参数
                    $data_insert['ways_type'] = $ways_type;
                    $data_insert['ways_type_desc'] = '微信支付';
                    $data_insert['ways_source'] = 'weixin';
                    $data_insert['ways_source_desc'] = '微信支付';
                    $data_insert['out_trade_no'] = $out_trade_no;
                }

                $insert_re = Order::create($data_insert);

                if (!$insert_re) {
                    return json_encode([
                        'status' => 2,
                        'message' => '订单未入库'
                    ]);
                }


                $redata = [
                    'MerchantId' => $mybank_merchant->MerchantId,
                    'Goodsid' => $store_id,
                    'Body' => $data_insert['ways_type_desc'],
                    'TotalAmount' => $total_amount * 100,//金额
                    'OutTradeNo' => $out_trade_no,
                    'Currency' => 'CNY',
                    'ChannelType' => $ChannelType,
                    'OperatorId' => $merchant_id,//操作员id
                    'StoreId' => $store_id,
                    'DeviceId' => $device_id,
                    'DeviceCreateIp' => get_client_ip(),
                    'ExpireExpress' => '1',//过期时间
                    'SettleType' => 'T1',//'T1',//清算方式
                    'Attach' => url('/api/mybank/notify'),//附加信息，原样返回。
                    'PayLimit' => '',//禁用方式
                    'AlipayStoreId' => '',
                    'SysServiceProviderId' => $MyBankConfig->ali_pid,
                ];

                $obj = new QrpayController();
                $return = $obj->gdqr($redata, $config_id);


                if ($return['status'] == 1) {
                    return json_encode([
                        'status' => 1,
                        'data' => [
                            'code_url' => $return['code_url'],
                            'store_name' => $store_name,
                            'out_trade_no' => $out_trade_no
                        ]
                    ]);
                } else {
                    return json_encode([
                        'status' => 2,
                        'message' => $return['message']
                    ]);
                }

            }

            //新大陆支付二维码
            if (7999 < $ways_type && $ways_type < 8999) {
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
                //入库参数
                $data_insert['ways_type'] = $ways->ways_type;
                $data_insert['ways_type_desc'] = '支付宝';
                $data_insert['ways_source'] = $ways->ways_source;
                $data_insert['ways_source_desc'] = '支付宝';
                $data_insert['out_store_id'] = $new_land_merchant->nl_mercId;


                if ($ways_type == 8001) {
                    $payChannel = 'ALIPAY';
                    $out_trade_no = 'ali_qr' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    //入库参数
                    $data_insert['ways_type'] = $ways_type;
                    $data_insert['ways_type_desc'] = '支付宝';
                    $data_insert['ways_source'] = 'alipay';
                    $data_insert['ways_source_desc'] = '支付宝';
                    $data_insert['out_trade_no'] = $out_trade_no;
                }
                if ($ways_type == 8002) {
                    $payChannel = 'WXPAY';
                    $out_trade_no = 'wx_qr' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    //入库参数
                    $data_insert['ways_type'] = $ways_type;
                    $data_insert['ways_type_desc'] = '微信支付';
                    $data_insert['ways_source'] = 'weixin';
                    $data_insert['ways_source_desc'] = '微信支付';
                    $data_insert['out_trade_no'] = $out_trade_no;
                }

                if ($ways_type == 8004) {
                    $payChannel = 'YLPAY';
                    $out_trade_no = 'un_qr' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    //入库参数
                    $data_insert['ways_type'] = $ways_type;
                    $data_insert['ways_type_desc'] = '银联扫码';
                    $data_insert['ways_source'] = 'unionpay';
                    $data_insert['ways_source_desc'] = '银联扫码';
                    $data_insert['out_trade_no'] = $out_trade_no;

                    if ($total_amount < 1000) {
                        //扫码费率入库
                        $data_insert['rate'] = $ways->rate_a;
                        $data_insert['fee_amount'] = $ways->rate_a * $total_amount / 100;

                    } else {
                        //扫码费率入库
                        $data_insert['rate'] = $ways->rate_c;
                        $data_insert['fee_amount'] = $ways->rate_c * $total_amount / 100;

                    }
                }

                $insert_re = Order::create($data_insert);

                if (!$insert_re) {
                    return json_encode([
                        'status' => 2,
                        'message' => '订单未入库'
                    ]);
                }

                $request_data = [
                    'out_trade_no' => $out_trade_no,
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
                    'payChannel' => $payChannel,
                ];


                $obj = new \App\Api\Controllers\Newland\PayController();
                $return = $obj->send_qr($request_data);

                if ($return['status'] == 1) {
                    return json_encode([
                        'status' => 1,
                        'data' => [
                            'code_url' => $return['code_url'],
                            'store_name' => $store_name,
                            'out_trade_no' => $out_trade_no
                        ]
                    ]);
                } else {
                    return json_encode([
                        'status' => 2,
                        'message' => $return['message']
                    ]);
                }

            }

            //和融通支付二维码
            if (8999 < $ways_type && $ways_type < 9999) {
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
                $obj = new \App\Api\Controllers\Huiyuanbao\PayController();
                $payChannel = 'ali';
                //支付宝
                if ($ways_type == 9001) {
                    $out_trade_no = 'ali_qr' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    //入库参数
                    $data_insert['ways_type'] = $ways_type;
                    $data_insert['ways_type_desc'] = '支付宝';
                    $data_insert['ways_source'] = 'alipay';
                    $data_insert['ways_source_desc'] = '支付宝';
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $payChannel = 'ali';

                }
                if ($ways_type == 9002) {
                    $out_trade_no = 'wx_qr' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    //入库参数
                    $data_insert['ways_type'] = $ways_type;
                    $data_insert['ways_type_desc'] = '微信支付';
                    $data_insert['ways_source'] = 'weixin';
                    $data_insert['ways_source_desc'] = '微信支付';
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $payChannel = 'wx';
                }

                $insert_re = Order::create($data_insert);

                if (!$insert_re) {
                    return json_encode([
                        'status' => 2,
                        'message' => '订单未入库'
                    ]);
                }

                $data = [];
                $data['out_trade_no'] = $out_trade_no;
                $data['request_url'] = $obj->send_qr_url;//请求地址;
                $data['notify_url'] = url('/api/huiyuanbao/pay_notify');//地址;
                $data['md_key'] = $h_config->md_key;//
                $data['total_amount'] = $total_amount;
                $data['mid'] = $h_merchant->h_mid;
                $data['md_key'] = $h_config->md_key;//
                $data['orgNo'] = $h_merchant->orgNo;//
                $data['payChannel'] = $payChannel;//
                $data['remark'] = $remark;
                $data['return_params'] = '原样返回';

                $return = $obj->send_qr($data);
                if ($return['status'] == 1) {
                    return json_encode([
                        'status' => 1,
                        'data' => [
                            'code_url' => $return['code_url'],
                            'store_name' => $store_name,
                            'out_trade_no' => $out_trade_no
                        ]
                    ]);
                } else {
                    return json_encode([
                        'status' => 2,
                        'message' => $return['message']
                    ]);
                }

            }
            //联拓富二维码
            if (9999 < $ways_type && $ways_type < 19999) {
                $config = new LtfConfigController();
                $ltf_merchant = $config->ltf_merchant($store_id, $store_pid);
                if (!$ltf_merchant) {
                    return json_encode([
                        'status' => 2,
                        'message' => '商户号不存在'
                    ]);
                }
                $obj = new \App\Api\Controllers\Ltf\PayController();
                if ($ways_type == 10001) {
                    $out_trade_no = 'ali_qr' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    //入库参数
                    $data_insert['ways_type'] = $ways_type;
                    $data_insert['ways_type_desc'] = '支付宝';
                    $data_insert['ways_source'] = 'alipay';
                    $data_insert['ways_source_desc'] = '支付宝';
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $channel = "ALIPAY";
                }
                if ($ways_type == 10002) {
                    $out_trade_no = 'wx_qr' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    //入库参数
                    $data_insert['ways_type'] = $ways_type;
                    $data_insert['ways_type_desc'] = '微信支付';
                    $data_insert['ways_source'] = 'weixin';
                    $data_insert['ways_source_desc'] = '微信支付';
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $channel = "WXPAY";

                }

                $insert_re = Order::create($data_insert);

                if (!$insert_re) {
                    return json_encode([
                        'status' => 2,
                        'message' => '订单未入库'
                    ]);
                }

                $data = [];
                $data['out_trade_no'] = $out_trade_no;
                $data['request_url'] = $obj->send_qr_url;//请求地址;
                $data['notify_url'] = url('/api/ltf/pay_notify');//地址;
                $data['merchant_no'] = $ltf_merchant->merchantCode;
                $data['appId'] = $ltf_merchant->appId;//
                $data['key'] = $ltf_merchant->md_key;//
                $data['total_amount'] = $total_amount;
                $data['remark'] = $remark;
                $data['return_params'] = '原样返回';
                $data['channel'] = $channel;
                $data['tradeType'] = 'NATIVE';

                $return = $obj->send_qr($data);
                if ($return['status'] == 1) {
                    return json_encode([
                        'status' => 1,
                        'data' => [
                            'code_url' => $return['code_url'],
                            'store_name' => $store_name,
                            'out_trade_no' => $out_trade_no
                        ]
                    ]);
                } else {
                    return json_encode([
                        'status' => 2,
                        'message' => $return['message']
                    ]);
                }

            }


            return json_encode([
                'status' => 2,
                'message' => '暂不支持此通道'
            ]);


        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage()
            ]);
        }
    }

    //静态二维码 提交 中转站
    public function qr_auth_pay(Request $request)
    {
        try {
            $merchant_id = $request->get('merchant_id');
            $merchant = Merchant::where('id', $merchant_id)
                ->select('name')
                ->first();
            $merchant_name = '';
            if ($merchant) {
                $merchant_name = $merchant->name;
            }
            $store_id = $request->get('store_id');
            $store = Store::where('store_id', $store_id)
                ->select('config_id', 'store_name', 'pid', 'user_id', 'is_delete', 'is_admin_close', 'is_close')
                ->first();
            if (!$store) {
                return json_encode([
                    'status' => 2,
                    'message' => '没有关联认证门店请联系服务商'
                ]);
            }
            //关闭的商户禁止交易
            if ($store->is_close || $store->is_admin_close || $store->is_delete) {
                return json_encode([
                    'status' => 2,
                    'message' => '商户已经被服务商关闭'
                ]);
            }
            $config_id = $store->config_id;
            $store_name = $store->store_name;
            $store_pid = $store->pid;
            $tg_user_id = $store->user_id;
            $total_amount = $request->get('total_amount');
            $shop_price = $request->get('shop_price', $total_amount);
            $remark = $request->get('remark', '');
            $device_id = $request->get('device_id', 'app');
            $shop_name = $request->get('shop_name', '扫码收款');
            $shop_desc = $request->get('shop_desc', '扫码收款');
            $open_id = $request->get('open_id');
            $ways_type = $request->get('ways_type');
            $other_no = $request->get('other_no', '');
            $notify_url = $request->get('notify_url', '');

            $check_data = [
                'total_amount' => '付款金额',
                'ways_type' => '通道类型',
                'open_id' => '购买者id'
            ];

            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }

            if ($other_no == "" || $other_no == "NULL") {
                $other_no = "";
            }


            if ($notify_url == "" || $notify_url == "NULL") {
                $notify_url = "";
            }

            $obj_ways = new  PayWaysController();
            $ways = $obj_ways->ways_type($ways_type, $store_id, $store_pid);
            if (!$ways) {
                return json_encode([
                    'status' => 2,
                    'message' => '没有开通此类型通道'
                ]);
            }


            //发起请求
            $data = [
                'config_id' => $config_id,
                'store_id' => $store_id,
                'merchant_id' => $merchant_id,
                'total_amount' => $total_amount,
                'shop_price' => $shop_price,
                'remark' => $remark,
                'device_id' => $device_id,
                'config_type' => '01',
                'shop_name' => $shop_name,
                'shop_desc' => $shop_desc,
                'store_name' => $store_name,
                'open_id' => $open_id,
            ];


            //插入数据库
            $data_insert = [
                'trade_no' => '',
                'store_id' => $store_id,
                'user_id' => $tg_user_id,
                'store_name' => $store_name,
                'buyer_id' => '',
                'total_amount' => $total_amount,
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
                'company' => $ways->company,
                'other_no' => $other_no,
                'notify_url' => $notify_url,
            ];


            //扫码费率入库
            $data_insert['rate'] = $ways->rate;
            $data_insert['fee_amount'] = $ways->rate * $total_amount / 100;

            /*官方支付宝*/
            if ($ways_type == '1000') {
                //配置
                $isvconfig = new AlipayIsvConfigController();
                $config_type = '01';
                $notify_url = url('/api/alipayopen/qr_pay_notify');

                $storeInfo = $isvconfig->alipay_auth_info($store_id, $store_pid);
                if (!$storeInfo) {
                    $msg = '支付宝授权信息不存在';
                    return json_encode([
                        'status' => 2,
                        'message' => $msg
                    ]);

                }

                $out_user_id = $storeInfo->alipay_user_id;//商户的id


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
                    return json_encode([
                        'status' => 2,
                        'message' => $msg
                    ]);

                }
                $alipay_store_id = $storeInfo->alipay_store_id;
                $out_store_id = $storeInfo->out_store_id;

                $config = $isvconfig->AlipayIsvConfig($config_id, $config_type);


                $out_trade_no = 'ali_qr' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                $pay_obj = new PayController();

                $data['out_trade_no'] = $out_trade_no;
                $data['alipay_store_id'] = $alipay_store_id;
                $data['out_store_id'] = $out_store_id;
                $data['out_user_id'] = $out_user_id;
                $data['app_auth_token'] = $storeInfo->app_auth_token;
                $data['config'] = $config;
                $data['notify_url'] = $notify_url;


                $return = $pay_obj->qr_auth_pay($data);
                $return_array = json_decode($return, true);
                if ($return_array['status'] == 1) {
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['ways_type_desc'] = '支付宝';
                    $data_insert['ways_source'] = 'alipay';
                    $data_insert['ways_source_desc'] = '支付宝';
                    $data_insert['out_trade_no'] = $out_trade_no;

                    //入库参数
                    $insert_re = Order::create($data_insert);

                    if (!$insert_re) {
                        return json_encode([
                            'status' => 2,
                            'message' => '订单未入库'
                        ]);
                    }
                }

                return $return;

            }

            /**官方微信*/
            if ($ways_type == "2000") {
                $out_trade_no = 'wx_qr' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                $data['goods_detail'] = [];
                $data['out_trade_no'] = $out_trade_no;
                $data['attach'] = $store_id . ',' . $config_id;//附加信息原样返回

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
                $data['wx_sub_merchant_id'] = $wx_sub_merchant_id;
                $data['options'] = $options;


                $pay_obj = new \App\Api\Controllers\Weixin\PayController();
                $return = $pay_obj->qr_pay($data, 'JSAPI');

                $return_array = json_decode($return, true);

                if ($return_array['status'] == 1) {

                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['ways_type_desc'] = '微信支付';
                    $data_insert['ways_source'] = 'weixin';
                    $data_insert['ways_source_desc'] = '微信支付';
                    $data_insert['out_trade_no'] = $out_trade_no;

                    $insert_re = Order::create($data_insert);

                    if (!$insert_re) {
                        return json_encode([
                            'status' => 2,
                            'message' => '订单未入库'
                        ]);
                    }
                }

                return $return;
            }

            /*网商支付宝-微信支付*/
            if ($ways_type == '3001' || $ways_type == '3002') {
                $config = new MyBankConfigController();
                $MyBankConfig = $config->MyBankConfig($config_id);
                if (!$MyBankConfig) {
                    return json_encode([
                        'status' => 2,
                        'message' => '网商配置不存在请检查配置'
                    ]);
                }

                $mybank_merchant = $config->mybank_merchant($store_id, $store_pid);
                if (!$mybank_merchant) {
                    return json_encode([
                        'status' => 2,
                        'message' => '网商商户号不存在'
                    ]);
                }
                $wx_AppId = $mybank_merchant->wx_AppId;
                $MyBankConfig = $config->MyBankConfig($config_id, $wx_AppId);
                if (!$MyBankConfig) {
                    return json_encode([
                        'status' => 2,
                        'message' => '网商配置不存在请检查配置'
                    ]);
                }

                $pay_type = '';
                $out_trade_no = '';
                $SubAppId = $MyBankConfig->wx_AppId;
                $ali_pid = $MyBankConfig->ali_pid;
                if ($ways_type == "3001") {
                    $pay_type = "ALI";
                    $out_trade_no = 'aliQR' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['ways_type_desc'] = '支付宝';
                    $data_insert['ways_source'] = 'alipay';
                    $data_insert['ways_source_desc'] = '支付宝';
                }


                if ($ways_type == "3002") {
                    $pay_type = "WX";
                    $out_trade_no = 'wxQR' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['ways_type_desc'] = '微信支付';
                    $data_insert['ways_source'] = 'weixin';
                    $data_insert['ways_source_desc'] = '微信支付';
                }

                $MerchantId = $mybank_merchant->MerchantId;


                $data['total_amount'] = $total_amount;
                $data['merchant_id'] = $merchant_id;
                $data['store_id'] = $store_id;
                $data['open_id'] = $open_id;
                $data['pay_type'] = $pay_type;
                $data['remark'] = $remark;
                $data['out_trade_no'] = $out_trade_no;
                $data['MerchantId'] = $MerchantId;
                $data['SettleType'] = 'T1';
                $data['PayLimit'] = '';
                $data['Body'] = $shop_name;
                $data['DeviceCreateIp'] = \EasyWeChat\Kernel\Support\get_client_ip();
                $data['Attach'] = url('/api/mybank/notify');//'原样输出';
                $data['NotifyUrl'] = url('/api/mybank/notify');
                $data['ali_pid'] = $ali_pid;
                $data['SubAppId'] = $SubAppId;


                $obj = new QrpayController();
                $return = $obj->auth_qr($data);

                if ($return['status'] == 0) {
                    return json_encode($return);
                }


                $insert_re = Order::create($data_insert);
                if (!$insert_re) {
                    return json_encode([
                        'status' => 2,
                        'message' => '订单未入库'
                    ]);
                }


                //支付宝返回
                if ($ways_type == "3001") {
                    return json_encode([
                        'status' => 1,
                        'message' => 'ok',
                        'trade_no' => $return['data']['PrePayId']
                    ]);
                }
                //微信支付返回
                if ($ways_type == "3002") {
                    return json_encode([
                        'status' => 1,
                        'data' => $return['data']['PayInfo']
                    ]);
                }


            }
            /*京东支付宝-微信支付-京东*/
            if ($ways_type == '6001' || $ways_type == '6002' || $ways_type == '6003') {
                $config = new JdConfigController();
                $jd_config = $config->jd_config($config_id);
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


                $out_trade_no = '';
                $pay_type = "";
                $appId = '';

                if ($ways_type == "6002") {
                    $pay_type = "WX";
                    $appId = $jd_config->wx_appid;
                    $out_trade_no = 'wxQR' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['ways_type_desc'] = '微信支付';
                    $data_insert['ways_source'] = 'weixin';
                    $data_insert['ways_source_desc'] = '微信支付';

                }
                if ($ways_type == "6001") {
                    $pay_type = "ALIPAY";
                    $appId = $jd_config->ali_appid;
                    $out_trade_no = 'aliQR' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['ways_type_desc'] = '支付宝';
                    $data_insert['ways_source'] = 'alipay';
                    $data_insert['ways_source_desc'] = '支付宝';
                }

                if ($ways_type == "6003") {
                    $pay_type = "JDPAY";


                    //是否开通白条
                    if ($jd_merchant->bt_true) {
                        $pay_type = "JIOU";
                    }

                    $appId = $jd_config->ali_appid;
                    $out_trade_no = 'jdQR' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['ways_type_desc'] = '京东金融';
                    $data_insert['ways_source'] = 'jdjr';
                    $data_insert['ways_source_desc'] = '京东金融';
                }


                $obj = new \App\Api\Controllers\Jd\PayController();
                $jd_data = [
                    "out_trade_no" => $out_trade_no,
                    "userId" => $open_id,
                    "total_amount" => $total_amount,
                    "remark" => '' . $remark . '',
                    "device_id" => $device_id,
                    "shop_name" => $store_name,
                    "notify_url" => url("api/jd/notify_url"),
                    "request_url" => $obj->unified_url,
                    "pay_type" => $pay_type,
                    "merchant_no" => $jd_merchant->merchant_no,
                    "return_params" => "原样返回",
                    "des_key" => $jd_merchant->des_key,
                    "md_key" => $jd_merchant->md_key,
                    "systemId" => $jd_config->systemId,
                    'gatewayPayMethod' => 'SUBSCRIPTION',//MINIPROGRAM：小程序 SUBSCRIPTION：公众号，服务号
                    'appId' => $appId,
                ];

                $return = $obj->qr_submit($jd_data);
                Log::info($ways_type);
                Log::info($return);

                if ($return['status'] == 0) {
                    return json_encode($return);
                }
                $insert_re = Order::create($data_insert);
                if (!$insert_re) {
                    return json_encode([
                        'status' => 2,
                        'message' => '订单未入库'
                    ]);
                }

                //支付宝返回
                if ($ways_type == "6001") {
                    return json_encode([
                        'status' => 1,
                        'message' => '',
                        'trade_no' => json_decode($return['data']['payInfo'], true)['tradeNO']
                    ]);
                }

                //支付宝返回
                if ($ways_type == "6002") {
                    return json_encode([
                        'status' => 1,
                        'data' => $return['data']['payInfo'],
                    ]);
                }


                //京东返回
                if ($ways_type == "6003") {
                    return json_encode([
                        'status' => 1,
                        'data' => $return['data']['payInfo'],
                    ]);
                }


            }

            /*新大陆支付宝-微信支付*/
            if ($ways_type == '8001' || $ways_type == '8002') {
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
                $data_insert['out_store_id'] = $new_land_merchant->nl_mercId;
                $request_data = [
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
                    'paysuccurl' => url('/api/newland/pay_action'),//支付以后跳转
                ];


                if ($ways_type == "8001") {
                    $request_data['payChannel'] = "ALIPAY";
                    $out_trade_no = 'aliQR' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['ways_type_desc'] = '支付宝';
                    $data_insert['ways_source'] = 'alipay';
                    $data_insert['ways_source_desc'] = '支付宝';

                }


                if ($ways_type == "8002") {
                    $request_data['payChannel'] = "WXPAY";
                    $out_trade_no = 'wxQR' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['ways_type_desc'] = '微信支付';
                    $data_insert['ways_source'] = 'weixin';
                    $data_insert['ways_source_desc'] = '微信支付';
                }

                $request_data['out_trade_no'] = $out_trade_no;


                $obj = new \App\Api\Controllers\Newland\PayController();
                $return = $obj->qr_submit($request_data);

                if ($return['status'] == 0) {
                    return json_encode($return);
                }


                $insert_re = Order::create($data_insert);
                if (!$insert_re) {
                    return json_encode([
                        'status' => 2,
                        'message' => '订单未入库'
                    ]);
                }

                return json_encode([
                    'status' => 1,
                    'message' => '返回成功',
                    'url' => $return['data']['url'],//跳转的url
                ]);

            }

            /*和融通支付宝-微信支付*/
            if ($ways_type == '9001' || $ways_type == '9002') {
                $config = new HConfigController();
                $h_config = $config->h_config($config_id);

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


                $out_trade_no = '';
                $app_id = '';
                $payChannel = '';
                if ($ways_type == "9002") {
                    $payChannel = "wx";
                    $app_id = $h_config->wx_appid;
                    $app_id = '';
                    $out_trade_no = 'wxQR' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['ways_type_desc'] = '微信支付';
                    $data_insert['ways_source'] = 'weixin';
                    $data_insert['ways_source_desc'] = '微信支付';

                }
                if ($ways_type == "9001") {
                    //暂时读取支付宝官方的appid
                    $isvconfig = new AlipayIsvConfigController();
                    $alipay_config = $isvconfig->AlipayIsvConfig($config_id);
                    $app_id = $alipay_config->app_id;
                    $app_id = '';
                    $payChannel = "ali";
                    $out_trade_no = 'aliQR' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['ways_type_desc'] = '支付宝';
                    $data_insert['ways_source'] = 'alipay';
                    $data_insert['ways_source_desc'] = '支付宝';
                }

                $obj = new \App\Api\Controllers\Huiyuanbao\PayController();

                $request_url = $obj->jsapiPay_url;//有渠道号原生

                $h_data = [
                    "out_trade_no" => $out_trade_no,
                    "userId" => $open_id,
                    "total_amount" => $total_amount,
                    "remark" => '' . $remark . '',
                    "device_id" => $device_id,
                    "shop_name" => $store_name,
                    "notify_url" => url("api/huiyuanbao/pay_notify"),
                    "request_url" => $request_url,
                    "return_params" => "原样返回",
                    "md_key" => $h_config->md_key,
                    "payChannel" => $payChannel,
                    'orgNo' => $h_config->orgNo,
                    'mid' => $h_merchant->h_mid,
                    'app_id' => $app_id,
                    'callBackUrl' => url('api/huiyuanbao/pay_action'),
                ];

                $return = $obj->qr_submit($h_data);

                if ($return['status'] == 0) {
                    return json_encode($return);
                }
                $insert_re = Order::create($data_insert);
                if (!$insert_re) {
                    return json_encode([
                        'status' => 2,
                        'message' => '订单未入库'
                    ]);
                }

                //支付宝返回
                if ($ways_type == "9001") {
                    return json_encode([
                        'status' => 1,
                        'url' => $return['code_url'],
                        'message' => '',
                        'data' => $return,
                    ]);
                }

                //微信返回
                if ($ways_type == "9002") {
                    return json_encode([
                        'status' => 1,
                        'url' => $return['code_url'],
                        'data' => $return,
                    ]);
                }


            }


            /*联拓富支付宝-微信支付*/
            if ($ways_type == '10001' || $ways_type == '10002') {
                $config = new LtfConfigController();

                $ltf_merchant = $config->ltf_merchant($store_id, $store_pid);
                if (!$ltf_merchant) {
                    return json_encode([
                        'status' => 2,
                        'message' => '商户号不存在'
                    ]);
                }
                $data_insert['out_store_id'] = $ltf_merchant->merchantCode;
                $obj = new \App\Api\Controllers\Ltf\PayController();
                $request_data = [
                    'total_amount' => $total_amount,
                    'remark' => $remark,
                    'device_id' => $device_id,
                    'shop_name' => $shop_name,
                    'outTradeNo' => '',
                    'totalAmount' => $total_amount,
                    'merchant_no' => $ltf_merchant->merchantCode,
                    'appId' => $ltf_merchant->appId,
                    'key' => $ltf_merchant->md_key,
                    'returnUrl' => url('/api/ltf/pay_action'),
                    'notify_url' => url('/api/ltf/pay_notify'),
                    'request_url' => $obj->jspay_url,
                    'channel' => '',
                    'return_params' => '',
                    'userId' => $open_id,
                    'tradeType' => 'JSAPI',
                ];


                if ($ways_type == "10001") {
                    $out_trade_no = 'aliQR' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['ways_type_desc'] = '支付宝';
                    $data_insert['ways_source'] = 'alipay';
                    $data_insert['ways_source_desc'] = '支付宝';
                    $request_data['channel'] = 'ALIPAY';


                }


                if ($ways_type == "10002") {
                    $out_trade_no = 'wxQR' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['ways_type_desc'] = '微信支付';
                    $data_insert['ways_source'] = 'weixin';
                    $data_insert['ways_source_desc'] = '微信支付';
                    $request_data['channel'] = 'WXPAY';

                }

                $request_data['out_trade_no'] = $out_trade_no;
                $return = $obj->qr_submit($request_data);

                if ($return['status'] == 0) {
                    return json_encode($return);
                }


                $insert_re = Order::create($data_insert);
                if (!$insert_re) {
                    return json_encode([
                        'status' => 2,
                        'message' => '订单未入库'
                    ]);
                }

                $return_data = [
                    'status' => 1,
                    'message' => '返回成功',
                    'data' => $return['data'],//跳转的url
                ];

                if ($request_data['channel'] == "ALIPAY") {
                    $return_data['tradeNO'] = $return['data']['transactionId'];
                }
                return json_encode($return_data);

            }


            /*富友通支付宝-微信支付*/
            if ($ways_type == '11001' || $ways_type == '11002') {
                $config = new FuiouConfigController();
                $fuiou_config = $config->fuiou_config($data['config_id']);

                if (!$fuiou_config) {
                    return json_encode([
                        'status' => 2,
                        'message' => '富友配置不存在请检查配置'
                    ]);
                }

                $fuiou__merchant = $config->fuiou_merchant($store_id, $store_pid);
                if (!$fuiou__merchant) {
                    return json_encode([
                        'status' => 2,
                        'message' => '富友商户号不存在'
                    ]);
                }

                $trade_type = "";
                $out_trade_no = '';
                if ($ways_type == "11002") {
                    $out_trade_no = '1300' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['ways_type_desc'] = '微信支付';
                    $data_insert['ways_source'] = 'weixin';
                    $data_insert['ways_source_desc'] = '微信支付';
                    $trade_type = "JSAPI";

                }
                if ($ways_type == "11001") {
                    //暂时读取支付宝官方的appid
                    $out_trade_no = '1300' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['ways_type_desc'] = '支付宝';
                    $data_insert['ways_source'] = 'alipay';
                    $data_insert['ways_source_desc'] = '支付宝';
                    $trade_type = "FWC";

                }

                $obj = new \App\Api\Controllers\Fuiou\PayController();

                $fuiou_data = [
                    'ins_cd' => $fuiou_config->ins_cd,//机构号
                    'mchnt_cd' => $fuiou__merchant->mchnt_cd,//商户号
                    'trade_type' => $trade_type,//订单类型订单类型:ALIPAY，WECHAT，UNIONPAY(银联二维码），BESTPAY(翼支付)
                    'goods_des' => $store_id,//商品描述
                    'mchnt_order_no' => $out_trade_no,//商户订单号
                    'order_amt' => $total_amount * 100,//总金额 分
                    'openid' => $open_id,
                    'pem' => $fuiou_config->my_private_key,
                ];

                $return = $obj->qr_submit($fuiou_data);

                if ($return['status'] == 0) {
                    return json_encode($return);
                }
                $insert_re = Order::create($data_insert);
                if (!$insert_re) {
                    return json_encode([
                        'status' => 2,
                        'message' => '订单未入库'
                    ]);
                }

                //支付宝返回
                if ($ways_type == "11001") {
                    return json_encode([
                        'status' => 1,
                        'reserved_transaction_id' => $return['data']['reserved_transaction_id'],
                        'data' => $return,
                    ]);
                }

                //微信返回
                if ($ways_type == "11002") {
                    return json_encode([
                        'status' => 1,
                        'data' => $return,
                        'pay' => $return['data']['reserved_pay_info']
                    ]);
                }


            }


            return json_encode([
                'status' => 2,
                'message' => '暂不支持此通道'
            ]);


        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage() . $exception->getLine()
            ]);
        }
    }


    //学校教育缴费
    public function school_pay(Request $request)
    {

        $data = $request->all();
        $items_array = json_decode($data['items'], true);//item_serial_number

        $other_no = $request->get('out_trade_no');//外部商户号
        $open_id = $request->get('open_id');

        $check_data = [
            'out_trade_no' => '订单号',
            'items' => '缴费项',
            'open_id' => '付款人id'

        ];

        //小项编号
        $items_id = '';
        $total_amount = 0;
        foreach ($items_array as $k => $v) {
            $items_id = $items_id . '-' . $v['item_serial_number'];
            $total_amount = $total_amount + ($v['item_number'] * $v['item_price']);

        }

        $check = $this->check_required($request->except(['token']), $check_data);
        if ($check) {
            return json_encode([
                'status' => 2,
                'message' => $check
            ]);
        }


        $store_id = $request->get('store_id');//'20181815585595187';
        $store = Store::where('store_id', $store_id)->first();
        if (!$store) {
            return json_encode([
                'status' => 2,
                'message' => '门店不存在'
            ]);
        }
        $config_id = $store->config_id;//2345
        $merchant_id = $store->merchant_id;
        $tg_user_id = $store->user_id;
        $shop_price = $total_amount;
        $remark = '';
        $device_id = 'qr';
        $shop_name = $request->get('shop_name', '教育缴费');
        $shop_desc = $request->get('shop_name', '教育缴费');;
        $store_name = $store->store_name;
        $store_pid = $store->pid;
        $merchant_name = '';


        //发起请求
        $data = [
            'config_id' => $config_id,
            'store_id' => $store_id,
            'merchant_id' => $merchant_id,
            'total_amount' => $total_amount,
            'shop_price' => $shop_price,
            'remark' => $remark,
            'device_id' => $device_id,
            'config_type' => '01',
            'shop_name' => $shop_name,
            'shop_desc' => $shop_desc,
            'store_name' => $store_name,
            'open_id' => $open_id,
        ];
        //插入数据库
        $data_insert = [
            'trade_no' => '',
            'other_no' => $other_no,
            'store_id' => $store_id,
            'store_name' => $store_name,
            'buyer_id' => '',
            'total_amount' => $total_amount,
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
            'user_id' => $tg_user_id,
            'rate' => '0.00',
        ];

        $out_trade_no = $other_no; //'wx_qr' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
        $data['goods_detail'] = [];
        $data['out_trade_no'] = $out_trade_no;
        $data['attach'] = $store_id . ',' . $config_id . ',' . $items_id;//附加信息原样返回

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
        $data['wx_sub_merchant_id'] = $wx_sub_merchant_id;
        $data['options'] = $options;
        $notify_url = url('api/weixin/school_pay_notify');

        $pay_obj = new \App\Api\Controllers\Weixin\PayController();
        $return = $pay_obj->qr_pay($data, 'JSAPI', $notify_url);
        $return_array = json_decode($return, true);

        if ($return_array['status'] == 1) {

            $data_insert['ways_type'] = '2005';
            $data_insert['ways_type_desc'] = '微信支付';
            $data_insert['ways_source'] = 'weixin';
            $data_insert['ways_source_desc'] = '微信支付';
            $data_insert['out_trade_no'] = $out_trade_no;

            $insert_re = Order::create($data_insert);

            if (!$insert_re) {
                return json_encode([
                    'status' => 2,
                    'message' => '订单未入库'
                ]);
            }
        }

        return $return;

    }


    //查询支付宝的订单状态
    public function AlipayTradePayQuery($out_trade_no, $app_auth_token, $configs)
    {
        $aop = new AopClient();
        $aop->rsaPrivateKey = $configs->rsa_private_key;
        $aop->appId = $configs->app_id;
        $aop->method = 'alipay.trade.query';

        $aop->signType = "RSA2";//升级算法
        $aop->gatewayUrl = $configs->alipay_gateway;
        $aop->format = "json";
        $aop->charset = "GBK";
        $aop->version = "2.0";
        $requests = new AlipayTradeQueryRequest();
        $requests->setBizContent("{" .
            "    \"out_trade_no\":\"" . $out_trade_no . "\"" .
            "  }");
        $result = $aop->execute($requests, '', $app_auth_token);
        return $result;
    }

    //支付宝取消接口
    public
    function AlipayTradePayCancel($out_trade_no, $app_auth_token, $configs)
    {
        $aop = new AopClient();
        $aop->rsaPrivateKey = $configs->rsa_private_key;
        $aop->appId = $configs->app_id;
        $aop->method = 'alipay.trade.cancel';

        $aop->signType = "RSA2";//升级算法
        $aop->gatewayUrl = $configs->alipay_gateway;
        $aop->format = "json";
        $aop->charset = "GBK";
        $aop->version = "2.0";
        $requests = new AlipayTradeCancelRequest();
        $requests->setBizContent("{" .
            "    \"out_trade_no\":\"" . $out_trade_no . "\"" .
            "  }");
        $result = $aop->execute($requests, '', $app_auth_token);
        return $result;
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
                'device_id'=>isset($data['device_id'])?$data['device_id']:"",

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

                ]
            ]);

        }

        //正在支付
        if ($return['status'] == 2) {
            return json_encode([
                'status' => 1,
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


    //京东收银支付宝微信等扫一扫公共部分
    public function jd_pay_public($data_insert, $data)
    {
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
        $tg_user_id = $data['tg_user_id'];
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
            $pay_time = date('Y-m-d H:i:s', strtotime($return['data']['payFinishTime']));
            $buyer_pay_amount = $return['data']['piAmount'] / 100;
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
                'device_id'=>isset($data['device_id'])?$data['device_id']:"",


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

                ]
            ]);

        }

        //正在支付
        if ($return['status'] == 2) {
            return json_encode([
                'status' => 1,
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
                'message' => '网商商户号不存在'
            ]);
        }
        $wx_AppId = $mybank_merchant->wx_AppId;
        $MyBankConfig = $config->MyBankConfig($data['config_id'], $wx_AppId);
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
            $trade_no = $return['data']['MerchantOrderNo'];//条码订单号
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
                'device_id'=>isset($data['device_id'])?$data['device_id']:"",


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

                ]
            ]);

        }

        //正在支付
        if ($return['status'] == 2) {
            return json_encode([
                'status' => 1,
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
                    'device_id'=>isset($data['device_id'])?$data['device_id']:"",


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
                        'ways_source' => $data_insert['ways_source'],
                    ]
                ]);

            }


            //正在支付
            if ($return['status'] == 2) {
                return json_encode([
                    'status' => 1,
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
            return json_encode([
                'status' => 2,
                'pay_status' => '3',
                'message' => $exception->getMessage()
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
                'device_id'=>isset($data['device_id'])?$data['device_id']:"",


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
                ]
            ]);

        }

        //正在支付
        if ($return['status'] == 2) {
            return json_encode([
                'status' => 1,
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


    //富友
    public function fuiou_pay_public($data_insert, $data)
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
        $code = $data['code'];

        $config = new FuiouConfigController();
        $fuiou_config = $config->fuiou_config($data['config_id']);

        if (!$fuiou_config) {
            return json_encode([
                'status' => 2,
                'message' => '富友配置不存在请检查配置'
            ]);
        }

        $fuiou__merchant = $config->fuiou_merchant($store_id, $store_pid);
        if (!$fuiou__merchant) {
            return json_encode([
                'status' => 2,
                'message' => '富友商户号不存在'
            ]);
        }

        $out_trade_no = '1300' . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));


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

        $obj = new \App\Api\Controllers\Fuiou\PayController();

        $order_type = '';
        if ($data['ways_source'] == "alipay") {
            $order_type = 'ALIPAY';
        }
        if ($data['ways_source'] == "weixin") {
            $order_type = 'WECHAT';
        }
        if ($data['ways_source'] == "unionpay") {
            $order_type = 'UNIONPAY';
        }

        $request = [
            'ins_cd' => $fuiou_config->ins_cd,//机构号
            'mchnt_cd' => $fuiou__merchant->mchnt_cd,//商户号
            'order_type' => $order_type,//订单类型订单类型:ALIPAY，WECHAT，UNIONPAY(银联二维码），BESTPAY(翼支付)
            'goods_des' => $store_id,//商品描述
            'mchnt_order_no' => $out_trade_no,//商户订单号
            'order_amt' => $total_amount * 100,//总金额 分
            'auth_code' => $code,//付款码,
            'pem' => $fuiou_config->my_private_key,
            'url' => $obj->scpay_url,
        ];

        $return = $obj->scan_pay($request);


        if ($return['status'] == 0) {
            return json_encode([
                'status' => 2,
                'message' => $return['message']
            ]);
        }


        //返回支付成功
        if ($return['status'] == 1) {
            $trade_no = $return['data']['transaction_id'];
            $pay_time = date('Y-m-d H:i:s', strtotime($return['data']['reserved_txn_fin_ts']));
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
                'source_type' => '11000',//返佣来源
                'source_desc' => '富友',//返佣来源说明
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
                'device_id'=>isset($data['device_id'])?$data['device_id']:"",


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
                ]
            ]);

        }

        //正在支付
        if ($return['status'] == 2) {
            return json_encode([
                'status' => 1,
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