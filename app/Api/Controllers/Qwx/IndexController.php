<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/9/6
 * Time: 下午7:17
 */

namespace App\Api\Controllers\Qwx;


use Alipayopen\Sdk\AopClient;
use Alipayopen\Sdk\Request\AlipayTradeFastpayRefundQueryRequest;
use Alipayopen\Sdk\Request\AlipayTradeQueryRequest;
use Alipayopen\Sdk\Request\AlipayTradeRefundRequest;
use App\Api\Controllers\Config\AlipayIsvConfigController;
use App\Api\Controllers\Config\HConfigController;
use App\Api\Controllers\Config\JdConfigController;
use App\Api\Controllers\Config\LtfConfigController;
use App\Api\Controllers\Config\MyBankConfigController;
use App\Api\Controllers\Config\NewLandConfigController;
use App\Api\Controllers\Config\WeixinConfigController;
use App\Api\Controllers\MyBank\TradePayController;
use App\Api\Controllers\Newland\PayController;
use App\Common\PaySuccessAction;
use App\Common\StoreDayMonthOrder;
use App\Common\UserGetMoney;
use App\Models\AlipayAppOauthUsers;
use App\Models\AlipayIsvConfig;
use App\Models\Order;
use App\Models\RefundOrder;
use App\Models\Store;
use EasyWeChat\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MyBank\Tools;

class IndexController extends BaseController
{

    //扫一扫收款
    public function scan_pay(Request $request)
    {
        try {
            //获取请求参数
            $data = $request->getContent();
            //  $data_all = $request->all();

            $data = json_decode($data, true);


            //验证签名
            $check = $this->check_md5($data);
            if ($check['return_code'] == 'FALL') {
                return $this->return_data($check);
            }

            Log::info('交易请求');
            Log::info($data);

            //调用系统前参数
            $ro_data = [
                'store_id' => $data['merchantId'],
                'code' => $data['authCode'],
                'total_amount' => $data['totalFee'] / 100,
                'shop_price' => $data['totalFee'] / 100,
                'qwx_no' => $data['outTradeNo'],
                'remark' => '',
                'device_id' => $data['device_id'],
                'shop_name' => '商品',
                'shop_desc' => '商品',
            ];

            //发起交易
            $order = new TradepayTwoController();
            $tra_data = $order->scan_pay($ro_data);
            $tra_data_arr = json_decode($tra_data, true);
            $out_transaction_id = '';
            $time_end = '';
            $re_data = [
                'return_code' => 'SUCCESS',//SUCCESS/FALL 此字段是通信标识，非交易标识，交易是否成功需要查看result_code来判断
                'return_msg' => null,
                'result_code' => '',
                'result_msg' => '',
                'outTradeNo' => $data['outTradeNo'],//微收银唯一订单号
                'tradeNo' => '',//第三方平台订单号即你们的订单号
                'totalFee' => number_format($data['totalFee'], 0, '.', ''),//交易金额，以分
                'channel' => $data['channel'],//支付类型 微信主扫：wx_barcode_pay 支付宝：ali_barcode_pay
                'out_transaction_id' => $out_transaction_id,//微信，支付宝支付凭证上的退款条形码
                'time_end' => $time_end,//支付完成时间为yyyyMMddHHmmss,20141030133525
            ];


            //用户支付成功
            if ($tra_data_arr['status'] == 1) {
                //微信，支付宝支付凭证
                $out_transaction_id = $tra_data_arr['data']['out_transaction_id'];

                //网商微信处理
                $mybank_weixin = substr($out_transaction_id, 0, 4);
                if ($mybank_weixin == "MYBK") {
                    $out_transaction_id = strtolower($out_transaction_id);//大写转小写
                }
                $re_data['result_code'] = 'SUCCESS';
                $re_data['result_msg'] = '支付成功';
                $re_data['tradeNo'] = $tra_data_arr['data']['out_trade_no'];
                $re_data['out_transaction_id'] = $out_transaction_id;
                $re_data['time_end'] = date('YmdHis', strtotime($tra_data_arr['data']['pay_time']));


            } elseif ($tra_data_arr['status'] == 9) {
                //正在支付
                $re_data['result_code'] = 'USERPAYING';
                $re_data['result_msg'] = '用户正在支付';
                $re_data['tradeNo'] = $tra_data_arr['data']['out_trade_no'];
            } else {
                //其他错误
                $re_data['result_code'] = 'FALL';
                $re_data['result_msg'] = $tra_data_arr['message'];
            }

            return $this->return_data($re_data);


        } catch (\Exception $exception) {
            Log::info($exception);

            $err = [
                'return_code' => 'FALL',
                'return_msg' => $exception->getMessage() . $exception->getLine(),
            ];
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


            //调用系统前参数

            $where = [];
            if (isset($data['tradeNo'])) {
                $where[] = ['out_trade_no', '=', $data['tradeNo']];
            }
            if (isset($data['out_transaction_id'])) {
                //  $where[] = ['trade_no', '=', $data['out_transaction_id']];
                $where[] = ['out_trade_no', '=', $data['out_transaction_id']];

            }
            if (isset($data['outTradeNo'])) {
                $where[] = ['qwx_no', '=', $data['outTradeNo']];
            }
            //发起查询
            $order = Order::where('store_id', $data['merchantId'])
                ->where($where)
                ->first();

            //如果订单号为空或者不存在
            if (!$order) {
                $re_data['result_code'] = 'FALL';
                $re_data['result_msg'] = '订单号不存在';
                return $this->return_data($re_data);
            }

            $out_transaction_id = '';
            $time_end = '';

            $channel = "";
            if ($order->ways_source == "alipay") {
                $channel = "ali_barcode_pay";
            }

            if ($order->ways_source == "weixin") {
                $channel = "wx_barcode_pay";
            }

            $re_data = [
                'return_code' => 'SUCCESS',//SUCCESS/FALL 此字段是通信标识，非交易标识，交易是否成功需要查看result_code来判断
                'return_msg' => null,
                'result_code' => '',
                'result_msg' => '',
                'tradeNo' => $order->out_trade_no,
                'outTradeNo' => '',//微收银唯一订单号
                'out_transaction_id' => $out_transaction_id,//微信，支付宝支付凭证上的退款条形码
                'totalFee' => number_format($order->total_amount * 100, 0, '.', ''),//交易金额，以分
                'channel' => $channel,//支付类型 微信主扫：wx_barcode_pay 支付宝：ali_barcode_pay
                'time_end' => $time_end,//支付完成时间为yyyyMMddHHmmss,20141030133525
            ];


            $type = $order->ways_type;

            $store = Store::where('store_id', $data['merchantId'])
                ->select('config_id','merchant_id', 'pid')
                ->first();
            $config_id = $store->config_id;
            $store_id = $data['merchantId'];
            $store_pid = $store->pid;
            //官方支付宝查询
            if (999 < $type && $type < 1999) {
                //配置
                $isvconfig = new AlipayIsvConfigController();
                $storeInfo = $isvconfig->alipay_auth_info($store_id, $store_pid);
                $config = $isvconfig->AlipayIsvConfig($config_id);


                $aop = new AopClient();
                $aop->apiVersion = "2.0";
                $aop->appId = $config->app_id;
                $aop->rsaPrivateKey = $config->rsa_private_key;
                $aop->alipayrsaPublicKey = $config->alipay_rsa_public_key;
                $aop->signType = "RSA2";//升级算法
                $aop->gatewayUrl = $config->alipay_gateway;
                $aop->format = "json";
                $aop->charset = "GBK";
                $aop->version = "2.0";
                $aop->method = 'alipay.trade.query';
                $requests = new AlipayTradeQueryRequest();
                $requests->setBizContent("{" .
                    "    \"out_trade_no\":\"" . $order->out_trade_no . "\"" .
                    "  }");
                $status = $aop->execute($requests, '', $storeInfo->app_auth_token);
                //支付成功
                if ($status->alipay_trade_query_response->trade_status == "TRADE_SUCCESS") {
                    $re_data['result_code'] = 'SUCCESS';
                    $re_data['result_msg'] = '支付成功';
                    $re_data['outTradeNo'] = $order->qwx_no;
                    $re_data['out_transaction_id'] = $order->out_trade_no; //$status->alipay_trade_query_response->trade_no;
                    $re_data['time_end'] = date('YmdHis', strtotime($status->alipay_trade_query_response->send_pay_date));

                    if ($order->pay_status != 1) {
                        //改变数据库状态
                        $order->status = 'TRADE_SUCCESS';
                        $order->pay_status = 1;
                        $order->pay_status_desc = '支付成功';
                        $order->buyer_logon_id = $status->alipay_trade_query_response->buyer_user_id;
                        $order->trade_no = $status->alipay_trade_query_response->trade_no;
                        $order->pay_time = $status->alipay_trade_query_response->send_pay_date;
                        $order->save();


                        //支付成功后的动作
                        $data = [
                            'ways_type' => $order->ways_type,
                            'ways_type_desc' => $order->ways_type_desc,
                            'source_type' => '1000',//返佣来源
                            'source_desc' => '支付宝',//返佣来源说明
                            'total_amount' => $order->total_amount,
                            'out_trade_no' => $order->out_trade_no,
                            'rate' => $order->rate,
                            'merchant_id' => $order->merchant_id,
                            'store_id' => $order->store_id,
                            'user_id' => $order->user_id,
                            'config_id' => $config_id,
                            'store_name' => $order->store_name,
                            'ways_source' => $order->ways_source,
                            'pay_time' => $status->alipay_trade_query_response->send_pay_date,
                            'no_push' => '1',//不推送
                            'no_fuwu' => '1',//不服务消息
                            'no_print' => '1',//不打印
                            //'no_v' => '1',//不小盒子播报

                        ];


                        PaySuccessAction::action($data);

                    }

                    return $this->return_data($re_data);

                } //等待付款
                elseif ($status->alipay_trade_query_response->trade_status == "WAIT_BUYER_PAY") {
                    $re_data['result_code'] = 'USERPAYING';
                    $re_data['result_msg'] = '等待用户付款';
                    $re_data['outTradeNo'] = $order->qwx_no;

                    return $this->return_data($re_data);

                } //订单关闭
                elseif ($status->alipay_trade_query_response->trade_status == 'TRADE_CLOSED') {
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = '订单关闭';
                    $re_data['outTradeNo'] = $order->qwx_no;
                    return $this->return_data($re_data);

                } else {
                    //其他情况
                    $message = $status->alipay_trade_query_response->sub_msg;
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;
                    $re_data['outTradeNo'] = $order->qwx_no;

                    return $this->return_data($re_data);
                }


            }
            //官方微信查询
            if (1999 < $type && $type < 2999) {
                $config = new WeixinConfigController();
                $options = $config->weixin_config($config_id);
                $weixin_store = $config->weixin_merchant($store_id, $store_pid);
                $wx_sub_merchant_id = $weixin_store->wx_sub_merchant_id;


                $config = [
                    'app_id' => $options['app_id'],
                    'mch_id' => $options['payment']['merchant_id'],
                    'key' => $options['payment']['key'],
                    'cert_path' => $options['payment']['cert_path'], // XXX: 绝对路径！！！！
                    'key_path' => $options['payment']['key_path'],     // XXX: 绝对路径！！！！
                    'sub_mch_id' => $wx_sub_merchant_id,
                    // 'device_info'     => '013467007045764',
                    // 'sub_app_id'      => '',
                    // ...
                ];

                $payment = Factory::payment($config);
                $query = $payment->order->queryByOutTradeNumber($order->out_trade_no);
                //成功
                if ($query['trade_state'] == 'SUCCESS') {
                    $re_data['result_code'] = 'SUCCESS';
                    $re_data['result_msg'] = '支付成功';
                    $re_data['outTradeNo'] = $order->qwx_no;
                    $re_data['out_transaction_id'] = $order->out_trade_no;//$query['transaction_id'];
                    $re_data['time_end'] = $query['time_end'];

                    if ($order->pay_status != 1) {
                        //改变数据库状态
                        $order->status = 'TRADE_SUCCESS';
                        $order->pay_status = 1;
                        $order->pay_status_desc = '支付成功';
                        $order->buyer_logon_id = $query['openid'];
                        $order->trade_no = $query['transaction_id'];
                        $order->pay_time = date('Y-m-d H:i:s', strtotime($query['time_end']));
                        $order->save();

                        //支付成功后的动作
                        $data = [
                            'ways_type' => $order->ways_type,
                            'ways_type_desc' => $order->ways_type_desc,
                            'source_type' => '2000',//返佣来源
                            'source_desc' => '微信支付',//返佣来源说明
                            'total_amount' => $order->total_amount,
                            'out_trade_no' => $order->out_trade_no,
                            'rate' => $order->rate,
                            'merchant_id' => $order->merchant_id,
                            'store_id' => $order->store_id,
                            'user_id' => $order->user_id,
                            'config_id' => $config_id,
                            'store_name' => $order->store_name,
                            'ways_source' => $order->ways_source,
                            'pay_time' => date('Y-m-d H:i:s', strtotime($query['time_end'])),
                            'no_push' => '1',//不推送
                            'no_fuwu' => '1',//不服务消息
                            'no_print' => '1',//不打印
                            //'no_v' => '1',//不小盒子播报

                        ];


                        PaySuccessAction::action($data);

                    }

                } elseif ($query['trade_state'] == "USERPAYING") {
                    $re_data['result_code'] = 'USERPAYING';
                    $re_data['result_msg'] = '等待用户付款';
                    $re_data['outTradeNo'] = $order->qwx_no;
                    return $this->return_data($re_data);


                } else {
                    //其他情况
                    $message = $query['trade_state_desc'];
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;
                    $re_data['outTradeNo'] = $order->qwx_no;
                    return $this->return_data($re_data);
                }

            }
            //京东收银支付
            if (5999 < $type && $type < 6999) {
                //读取配置
                $config = new JdConfigController();
                $jd_config = $config->jd_config($config_id);
                if (!$jd_config) {

                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = '京东配置不存在请检查配置';
                    return $this->return_data($re_data);
                }

                $jd_merchant = $config->jd_merchant($store_id, $store_pid);
                if (!$jd_merchant) {
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = '京东商户号不存在';
                    return $this->return_data($re_data);
                }
                $obj = new \App\Api\Controllers\Jd\PayController();
                $data = [];
                $data['out_trade_no'] = $order->out_trade_no;
                $data['request_url'] = $obj->order_query_url;//请求地址;
                $data['merchant_no'] = $jd_merchant->merchant_no;
                $data['md_key'] = $jd_merchant->md_key;//
                $data['des_key'] = $jd_merchant->des_key;//
                $data['systemId'] = $jd_config->systemId;//
                $return = $obj->order_query($data);

                //支付成功
                if ($return["status"] == 1) {
                    //改变数据库状态
                    if ($order->pay_status != 1) {
                        $trade_no = $return['data']['tradeNo'];
                        $pay_time = date('Y-m-d H:i:s', strtotime($return['data']['payFinishTime']));
                        $buyer_pay_amount = $return['data']['piAmount'] / 100;
                        $buyer_pay_amount = number_format($buyer_pay_amount, 2, '.', '');
                        $order->update([
                            'status' => '1',
                            'pay_status' => 1,
                            'pay_status_desc' => '支付成功',
                            'buyer_logon_id' => '',
                            'trade_no' => $trade_no,
                            'pay_time' => $pay_time,
                            'buyer_pay_amount' => $buyer_pay_amount,
                        ]);
                        $order->save();

                        //支付成功后的动作
                        $data = [
                            'ways_type' => $order->ways_type,
                            'ways_type_desc' => $order->ways_type_desc,
                            'source_type' => '6000',//返佣来源
                            'source_desc' => '京东金融',//返佣来源说明
                            'total_amount' => $order->total_amount,
                            'out_trade_no' => $order->out_trade_no,
                            'rate' => $order->rate,
                            'merchant_id' => $order->merchant_id,
                            'store_id' => $order->store_id,
                            'user_id' => $order->user_id,
                            'config_id' => $config_id,
                            'store_name' => $order->store_name,
                            'ways_source' => $order->ways_source,
                            'pay_time' => $pay_time,
                            'no_push' => '1',//不推送
                            'no_fuwu' => '1',//不服务消息
                            'no_print' => '1',//不打印
                            //'no_v' => '1',//不小盒子播报

                        ];


                        PaySuccessAction::action($data);

                    }

                    $re_data['result_code'] = 'SUCCESS';
                    $re_data['result_msg'] = '支付成功';
                    $re_data['outTradeNo'] = $order->qwx_no;
                    $re_data['out_transaction_id'] = $order->out_trade_no;//$query['transaction_id'];
                    $re_data['time_end'] = date('YmdHis', strtotime($return['data']['payFinishTime']));

                } //等待付款
                elseif ($return["status"] == 2) {
                    $re_data['result_code'] = 'USERPAYING';
                    $re_data['result_msg'] = '等待用户付款';
                    $re_data['outTradeNo'] = $order->qwx_no;
                    return $this->return_data($re_data);

                } //订单失败关闭
                elseif ($return["status"] == 3) {
                    //其他情况
                    $message = '订单支付失败';
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;
                    $re_data['outTradeNo'] = $order->qwx_no;
                    return $this->return_data($re_data);

                }//订单退款
                elseif ($return["status"] == 4) {

                    $message = '订单已经退款';
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;
                    $re_data['outTradeNo'] = $order->qwx_no;
                    return $this->return_data($re_data);

                } else {
                    //其他情况
                    $message = $return['message'];
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;
                    $re_data['outTradeNo'] = $order->qwx_no;
                    return $this->return_data($re_data);
                }


            }
            //网商银行
            if (2999 < $type && $type < 3999) {
                //读取配置
                $MyBankobj = new MyBankConfigController();

                $mybank_merchant = $MyBankobj->mybank_merchant($store_id, $store_pid);
                if (!$mybank_merchant) {
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = '网商商户号不存在';
                    return $this->return_data($re_data);
                }
                $MerchantId = $mybank_merchant->MerchantId;
                $wx_AppId=$mybank_merchant->wx_AppId;

                $MyBankConfig = $MyBankobj->MyBankConfig($config_id,$wx_AppId);
                if (!$MyBankConfig) {
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = '网商配置不存在请检查配置';
                    return $this->return_data($re_data);
                }


                $obj = new TradePayController();
                $return = $obj->mybankOrderQuery($MerchantId, $config_id, $order->out_trade_no);
                if ($return['status'] == 0) {

                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $return['message'];
                    return $this->return_data($re_data);
                }
                $body = $return['data']['document']['response']['body'];
                $TradeStatus = $body['TradeStatus'];

                //成功
                if ($TradeStatus == 'succ') {
                    $OrderNo = $body['MerchantOrderNo'];
                    $GmtPayment = $body['GmtPayment'];
                    $buyer_id = '';
                    if ($type == 3004) {
                        $buyer_id = $body['SubOpenId'];
                    }
                    if ($type == 3003) {
                        $buyer_id = $body['BuyerUserId'];
                    }

                    $pay_time = date('Y-m-d H:i:s', strtotime($GmtPayment));
                    $payment_method = strtolower($body['Credit']);

                    if ($order->pay_status != 1) {
                        //改变数据库状态
                        $order->status = 1;
                        $order->pay_status = 1;
                        $order->pay_status_desc = '支付成功';
                        $order->buyer_id = $buyer_id;
                        $order->trade_no = $OrderNo;
                        $order->payment_method = $payment_method;
                        $order->pay_time = $pay_time;
                        $order->save();


                        //支付成功后的动作
                        $data = [
                            'ways_type' => $order->ways_type,
                            'ways_type_desc' => $order->ways_type_desc,
                            'source_type' => '3000',//返佣来源
                            'source_desc' => '网商银行',//返佣来源说明
                            'total_amount' => $order->total_amount,
                            'out_trade_no' => $order->out_trade_no,
                            'rate' => $order->rate,
                            'merchant_id' => $order->merchant_id,
                            'store_id' => $order->store_id,
                            'user_id' => $order->user_id,
                            'config_id' => $config_id,
                            'store_name' => $order->store_name,
                            'ways_source' => $order->ways_source,
                            'pay_time' => $pay_time,
                            'no_push' => '1',//不推送
                            'no_fuwu' => '1',//不服务消息
                            'no_print' => '1',//不打印
                            //'no_v' => '1',//不小盒子播报

                        ];


                        PaySuccessAction::action($data);
                    }


                    $re_data['result_code'] = 'SUCCESS';
                    $re_data['result_msg'] = '支付成功';
                    $re_data['outTradeNo'] = $order->qwx_no;
                    $re_data['out_transaction_id'] = $order->out_trade_no;//$query['transaction_id'];
                    $re_data['time_end'] = date('YmdHis', strtotime($pay_time));


                } elseif ($TradeStatus == 'paying') {

                    $re_data['result_code'] = 'USERPAYING';
                    $re_data['result_msg'] = '等待用户付款';
                    $re_data['outTradeNo'] = $order->qwx_no;
                    return $this->return_data($re_data);


                } else {
                    //其他情况
                    $message = '请重新扫码';
                    //其他情况
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;
                    $re_data['outTradeNo'] = $order->qwx_no;
                    return $this->return_data($re_data);
                }


            }

            //新大陆收银支付
            if (7999 < $type && $type < 8999) {
                //读取配置
                $config = new NewLandConfigController();
                $new_land_config = $config->new_land_config($config_id);
                if (!$new_land_config) {

                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = '新大陆配置不存在请检查配置';
                    return $this->return_data($re_data);
                }

                $new_land_merchant = $config->new_land_merchant($store_id, $store_pid);
                if (!$new_land_merchant) {
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = '和融通商户号不存在';
                    return $this->return_data($re_data);
                }
                $request_data = [
                    'out_trade_no' => $order->out_trade_no,
                    'key' => $new_land_merchant->nl_key,
                    'org_no' => $new_land_config->org_no,
                    'merc_id' => $new_land_merchant->nl_mercId,
                    'trm_no' => $new_land_merchant->trmNo,
                    'op_sys' => '3',
                    'opr_id' => $store->merchant_id,
                    'trm_typ' => 'T',
                ];
                $obj = new PayController();
                $return = $obj->order_query($request_data);

                //支付成功
                if ($return["status"] == 1) {
                    //改变数据库状态
                    if ($order->pay_status != 1) {
                        $trade_no = $return['data']['orderNo'];
                        $pay_time = date('Y-m-d H:i:s', strtotime($return['data']['sysTime']));
                        $buyer_pay_amount = $return['data']['amount'] / 100;
                        $buyer_pay_amount = number_format($buyer_pay_amount, 2, '.', '');
                        $order->update([
                            'status' => '1',
                            'pay_status' => 1,
                            'pay_status_desc' => '支付成功',
                            'buyer_logon_id' => '',
                            'trade_no' => $trade_no,
                            'pay_time' => $pay_time,
                            'buyer_pay_amount' => $buyer_pay_amount,
                        ]);
                        $order->save();

                        //支付成功后的动作
                        $data = [
                            'ways_type' => $order->ways_type,
                            'ways_type_desc' => $order->ways_type_desc,
                            'source_type' => '8000',//返佣来源
                            'source_desc' => '新大陆',//返佣来源说明
                            'total_amount' => $order->total_amount,
                            'out_trade_no' => $order->out_trade_no,
                            'rate' => $order->rate,
                            'merchant_id' => $order->merchant_id,
                            'store_id' => $order->store_id,
                            'user_id' => $order->user_id,
                            'config_id' => $config_id,
                            'store_name' => $order->store_name,
                            'ways_source' => $order->ways_source,
                            'pay_time' => $pay_time,
                            'no_push' => '1',//不推送
                            'no_fuwu' => '1',//不服务消息
                            'no_print' => '1',//不打印
                            // 'no_v' => '1',//不小盒子播报

                        ];


                        PaySuccessAction::action($data);

                    }

                    $re_data['result_code'] = 'SUCCESS';
                    $re_data['result_msg'] = '支付成功';
                    $re_data['outTradeNo'] = $order->qwx_no;
                    $re_data['out_transaction_id'] = $order->trade_no;//$query['transaction_id'];
                    $re_data['time_end'] = date('YmdHis', strtotime($return['data']['payFinishTime']));

                } //等待付款
                elseif ($return["status"] == 2) {
                    $re_data['result_code'] = 'USERPAYING';
                    $re_data['result_msg'] = '等待用户付款';
                    $re_data['outTradeNo'] = $order->qwx_no;
                    return $this->return_data($re_data);

                } //订单失败关闭
                elseif ($return["status"] == 3) {
                    //其他情况
                    $message = '订单支付失败';
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;
                    $re_data['outTradeNo'] = $order->qwx_no;
                    return $this->return_data($re_data);

                }//订单退款
                elseif ($return["status"] == 4) {

                    $message = '订单已经退款';
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;
                    $re_data['outTradeNo'] = $order->qwx_no;
                    return $this->return_data($re_data);

                } else {
                    //其他情况
                    $message = $return['message'];
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;
                    $re_data['outTradeNo'] = $order->qwx_no;
                    return $this->return_data($re_data);
                }


            }
            //和融通收银支付
            if (8999 < $type && $type < 9999) {
                //读取配置
                $config = new HConfigController();
                $h_config = $config->h_config($config_id);
                if (!$h_config) {

                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = '和融通配置不存在请检查配置';
                    return $this->return_data($re_data);
                }

                $h_merchant = $config->h_merchant($store_id, $store_pid);
                if (!$h_merchant) {
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = '和融通商户号不存在';
                    return $this->return_data($re_data);
                }
                $obj = new \App\Api\Controllers\Huiyuanbao\PayController();
                $data = [];
                $data['out_trade_no'] = $order->out_trade_no;
                $data['request_url'] = $obj->order_query_url;//请求地址;
                $data['md_key'] = $h_config->md_key;//
                $data['mid'] = $h_merchant->h_mid;//
                $data['orgNo'] = $h_config->orgNo;//

                $return = $obj->order_query($data);

                //支付成功
                if ($return["status"] == 1) {
                    //改变数据库状态
                    if ($order->pay_status != 1) {
                        $trade_no = $return['data']['transactionId'];
                        $pay_time = date('Y-m-d H:i:s', strtotime($return['data']['timeEnd']));
                        $buyer_pay_amount = $return['data']['totalFee'];
                        $buyer_pay_amount = number_format($buyer_pay_amount, 2, '.', '');
                        $order->update([
                            'status' => '1',
                            'pay_status' => 1,
                            'pay_status_desc' => '支付成功',
                            'buyer_logon_id' => '',
                            'trade_no' => $trade_no,
                            'pay_time' => $pay_time,
                            'buyer_pay_amount' => $buyer_pay_amount,
                        ]);
                        $order->save();

                        //支付成功后的动作
                        $data = [
                            'ways_type' => $order->ways_type,
                            'ways_type_desc' => $order->ways_type_desc,
                            'source_type' => '9000',//返佣来源
                            'source_desc' => '和融通',//返佣来源说明
                            'total_amount' => $order->total_amount,
                            'out_trade_no' => $order->out_trade_no,
                            'rate' => $order->rate,
                            'merchant_id' => $order->merchant_id,
                            'store_id' => $order->store_id,
                            'user_id' => $order->user_id,
                            'config_id' => $config_id,
                            'store_name' => $order->store_name,
                            'ways_source' => $order->ways_source,
                            'pay_time' => $pay_time,
                            'no_push' => '1',//不推送
                            'no_fuwu' => '1',//不服务消息
                            'no_print' => '1',//不打印
                            //'no_v' => '1',//不小盒子播报

                        ];


                        PaySuccessAction::action($data);

                    }

                    $re_data['result_code'] = 'SUCCESS';
                    $re_data['result_msg'] = '支付成功';
                    $re_data['outTradeNo'] = $order->qwx_no;
                    $re_data['out_transaction_id'] = $order->trade_no;//$query['transaction_id'];
                    $re_data['time_end'] = date('YmdHis', strtotime($return['data']['timeEnd']));

                } //等待付款
                elseif ($return["status"] == 2) {
                    $re_data['result_code'] = 'USERPAYING';
                    $re_data['result_msg'] = '等待用户付款';
                    $re_data['outTradeNo'] = $order->qwx_no;
                    return $this->return_data($re_data);

                } //订单失败关闭
                elseif ($return["status"] == 3) {
                    //其他情况
                    $message = '订单支付失败';
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;
                    $re_data['outTradeNo'] = $order->qwx_no;
                    return $this->return_data($re_data);

                }//订单退款
                elseif ($return["status"] == 4) {

                    $message = '订单已经退款';
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;
                    $re_data['outTradeNo'] = $order->qwx_no;
                    return $this->return_data($re_data);

                } else {
                    //其他情况
                    $message = $return['message'];
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;
                    $re_data['outTradeNo'] = $order->qwx_no;
                    return $this->return_data($re_data);
                }


            }

            //联拓富收银支付
            if (9999 < $type && $type < 19999) {
                //读取配置
                $config = new LtfConfigController();


                $ltf_merchant = $config->ltf_merchant($store_id, $store_pid);
                if (!$ltf_merchant) {
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = '商户号不存在';
                    return $this->return_data($re_data);
                }
                $obj = new \App\Api\Controllers\Ltf\PayController();
                $data = [];
                $data['out_trade_no'] = $order->out_trade_no;
                $data['request_url'] = $obj->order_query_url;//请求地址;
                $data['merchant_no'] = $ltf_merchant->merchantCode;
                $data['appId'] = $ltf_merchant->appId;//
                $data['key'] = $ltf_merchant->md_key;//

                $return = $obj->order_query($data);

                //支付成功
                if ($return["status"] == 1) {
                    //改变数据库状态
                    if ($order->pay_status != 1) {
                        $trade_no = $return['data']['outTransactionId'];
                        $pay_time = date('Y-m-d H:i:s', strtotime($return['data']['payTime']));
                        $buyer_pay_amount = $return['data']['receiptAmount'];
                        $buyer_pay_amount = number_format($buyer_pay_amount, 2, '.', '');
                        $order->update([
                            'status' => '1',
                            'pay_status' => 1,
                            'pay_status_desc' => '支付成功',
                            'buyer_logon_id' => '',
                            'trade_no' => $trade_no,
                            'pay_time' => $pay_time,
                            'buyer_pay_amount' => $buyer_pay_amount,
                        ]);
                        $order->save();

                        //支付成功后的动作
                        $data = [
                            'ways_type' => $order->ways_type,
                            'ways_type_desc' => $order->ways_type_desc,
                            'source_type' => '10000',//返佣来源
                            'source_desc' => '联拓覆',//返佣来源说明
                            'total_amount' => $order->total_amount,
                            'out_trade_no' => $order->out_trade_no,
                            'rate' => $order->rate,
                            'merchant_id' => $order->merchant_id,
                            'store_id' => $order->store_id,
                            'user_id' => $order->user_id,
                            'config_id' => $config_id,
                            'store_name' => $order->store_name,
                            'ways_source' => $order->ways_source,
                            'pay_time' => $pay_time,
                            'no_push' => '1',//不推送
                            'no_fuwu' => '1',//不服务消息
                            'no_print' => '1',//不打印
                            //'no_v' => '1',//不小盒子播报

                        ];


                        PaySuccessAction::action($data);

                    }

                    $re_data['result_code'] = 'SUCCESS';
                    $re_data['result_msg'] = '支付成功';
                    $re_data['outTradeNo'] = $order->qwx_no;
                    $re_data['out_transaction_id'] = $order->out_trade_no;//$query['transaction_id'];
                    $re_data['time_end'] = date('YmdHis', strtotime($return['data']['payTime']));

                } //等待付款
                elseif ($return["status"] == 2) {
                    $re_data['result_code'] = 'USERPAYING';
                    $re_data['result_msg'] = '等待用户付款';
                    $re_data['outTradeNo'] = $order->qwx_no;
                    return $this->return_data($re_data);

                } //订单失败关闭
                elseif ($return["status"] == 3) {
                    //其他情况
                    $message = '订单支付失败';
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;
                    $re_data['outTradeNo'] = $order->qwx_no;
                    return $this->return_data($re_data);

                }//订单退款
                elseif ($return["status"] == 4) {

                    $message = '订单已经退款';
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;
                    $re_data['outTradeNo'] = $order->qwx_no;
                    return $this->return_data($re_data);

                } else {
                    //其他情况
                    $message = $return['message'];
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;
                    $re_data['outTradeNo'] = $order->qwx_no;
                    return $this->return_data($re_data);
                }


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


            $where = [];
            if (isset($data['tradeNo'])) {
                $where[] = ['out_trade_no', '=', $data['tradeNo']];
            }
            if (isset($data['out_transaction_id'])) {
                // $where[] = ['trade_no', '=', $data['out_transaction_id']];

                $where[] = ['out_trade_no', '=', $data['out_transaction_id']];

            }
            if (isset($data['outTradeNo'])) {
                $where[] = ['qwx_no', '=', $data['outTradeNo']];
            }

            //发起查询
            $order = Order::where('store_id', $data['merchantId'])
                ->where($where)
                // ->select('out_trade_no','','', 'config_id', 'trade_no', 'total_amount', 'ways_type')//2.0 ways_type 1.0 type
                ->first();

            //如果订单号为空或者不存在
            if (!$order) {
                $re_data['result_code'] = 'FALL';
                $re_data['result_msg'] = '订单号不存在';
                return $this->return_data($re_data);
            } else {
                //判断有没有退款全部完成
                if ($order->refund_amount == $order->total_amount) {
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = '此订单号已经全部退款';
                    return $this->return_data($re_data);
                }

            }

            $OutRefundNo = $data['refundNo'];//$order->out_trade_no . '123';
            $out_transaction_id = $order->trade_no;

            $refundFee_fen = $data['refundFee'];//退款金额单位 /分
            $refundFee_yuan = number_format($refundFee_fen / 100, 2, '.', '');
            //暂时只支持退全款
            if ($refundFee_yuan != $order->total_amount) {
                $re_data['result_code'] = 'FALL';
                $re_data['result_msg'] = '只支持退全额';
                return $this->return_data($re_data);
            }


            $time_end = '';
            $re_data = [
                'return_code' => 'SUCCESS',//SUCCESS/FALL 此字段是通信标识，非交易标识，交易是否成功需要查看result_code来判断
                'return_msg' => null,
                'result_code' => '',
                'result_msg' => '',
                'tradeNo' => $order->out_trade_no,
                'outTradeNo' => $order->qwx_no,//微收银唯一订单号
                'out_transaction_id' => $out_transaction_id,//微信，支付宝支付凭证上的退款条形码
                'refundNo' => '',//退款订单号
                'refundFee' => '',// $order->total_amount * 100,//交易金额，以分
                'channel' => $data['channel'],//支付类型 微信主扫：wx_barcode_pay 支付宝：ali_barcode_pay
            ];


            $type = $order->ways_type;
            $store = Store::where('store_id', $data['merchantId'])
                ->select('config_id', 'merchant_id', 'pid')
                ->first();
            $config_id = $store->config_id;
            $store_id = $data['merchantId'];
            $store_pid = $store->pid;

            //支付宝官方
            if (999 < $type && $type < 1999) {
                //配置
                $isvconfig = new AlipayIsvConfigController();
                $config_type = '01';
                $config = $isvconfig->AlipayIsvConfig($config_id, $config_type);


                //获取token
                $storeInfo = AlipayAppOauthUsers::where('store_id', $data['merchantId'])
                    ->select('app_auth_token')
                    ->first();

                $aop = new AopClient();
                $aop->apiVersion = "2.0";
                $aop->appId = $config->app_id;
                $aop->rsaPrivateKey = $config->rsa_private_key;
                $aop->alipayrsaPublicKey = $config->alipay_rsa_public_key;
                $aop->signType = "RSA2";//升级算法
                $aop->gatewayUrl = $config->alipay_gateway;
                $aop->format = "json";
                $aop->charset = "GBK";
                $aop->version = "2.0";
                $aop->method = "alipay.trade.refund";


                $requests = new AlipayTradeRefundRequest();
                $data_req_ali = "{" .
                    "\"out_trade_no\":\"" . $order->out_trade_no . "\"," .
                    "\"refund_amount\":\"" . $order->total_amount . "\"," .
                    "\"out_request_no\":\"" . $OutRefundNo . "\"," .
                    "\"refund_reason\":\"正常退款\"" .
                    "}";
                $requests->setBizContent($data_req_ali);
                $result = $aop->execute($requests, null, $storeInfo->app_auth_token);
                $responseNode = str_replace(".", "_", $requests->getApiMethodName()) . "_response";
                $resultCode = $result->$responseNode->code;

                //退款成功
                if (!empty($resultCode) && $resultCode == 10000) {

                    $order->pay_status_desc = '已退款';
                    $order->pay_status = 6;
                    $order->status = 6;
                    $order->fee_amount = 0;//手续费
                    $order->save();


                    RefundOrder::create([
                        'pay_status' => 3,
                        'status' => 3,
                        'type' => $type,
                        'ways_source' => $order->ways_source,
                        'status_desc' => '退款',
                        'refund_amount' => $order->total_amount,//退款金额
                        'refund_no' => $OutRefundNo,//退款单号
                        'store_id' => $data['merchantId'],
                        'merchant_id' => $store->merchant_id,
                        'out_trade_no' => $order->out_trade_no,
                        'trade_no' => $order->trade_no
                    ]);


                    $re_data['result_code'] = 'SUCCESS';
                    $re_data['result_msg'] = '退款成功';
                    $re_data['refundNo'] = $OutRefundNo;
                    $re_data['refundFee'] = $order->total_amount * 100;

                } else {
                    //退款失败
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $result->$responseNode->sub_msg;
                }
            }


            //微信官方扫码退款
            if (1999 < $type && $type < 2999) {

                $config = new WeixinConfigController();
                $options = $config->weixin_config($config_id);
                $weixin_store = $config->weixin_merchant($store_id, $store_pid);
                $wx_sub_merchant_id = $weixin_store->wx_sub_merchant_id;


                $config = [
                    'app_id' => $options['app_id'],
                    'mch_id' => $options['payment']['merchant_id'],
                    'key' => $options['payment']['key'],
                    'cert_path' => $options['payment']['cert_path'], // XXX: 绝对路径！！！！
                    'key_path' => $options['payment']['key_path'],     // XXX: 绝对路径！！！！
                    'sub_mch_id' => $wx_sub_merchant_id,
                    // 'device_info'     => '013467007045764',
                    // 'sub_app_id'      => '',
                    // ...
                ];

                $payment = Factory::payment($config);
                // 参数分别为：商户订单号、商户退款单号、订单金额、退款金额、其他参数
                $refund = $payment->refund->byOutTradeNumber($order->out_trade_no, $OutRefundNo, $order->total_amount * 100, $order->total_amount * 100);

                if ($refund['return_code'] == "SUCCESS") {
                    //退款成功
                    if ($refund['result_code'] == "SUCCESS") {
                        $order->pay_status_desc = '已退款';
                        $order->pay_status = 6;
                        $order->fee_amount = 0;//手续费
                        $order->status = 6;
                        $order->save();

                        RefundOrder::create([
                            'pay_status' => 6,
                            'status' => 6,
                            'type' => $type,
                            'ways_source' => $order->ways_source,
                            'status_desc' => '退款',
                            'refund_amount' => $order->total_amount,//退款金额
                            'refund_no' => $OutRefundNo,//退款单号
                            'store_id' => $data['merchantId'],
                            'merchant_id' => $store->merchant_id,
                            'out_trade_no' => $order->out_trade_no,
                            'trade_no' => $order->trade_no
                        ]);


                        $re_data['result_code'] = 'SUCCESS';
                        $re_data['result_msg'] = '退款成功';
                        $re_data['refundNo'] = $OutRefundNo;
                        $re_data['refundFee'] = $order->total_amount * 100;

                    } else {
                        //
                        $re_data['result_code'] = 'FALL';
                        $re_data['result_msg'] = $refund['result_msg'];
                    }


                } else {
                    //退款失败
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $refund['return_msg'];

                }
            }


            //京东收银支付退款
            if (5999 < $type && $type < 6999) {
                //读取配置
                $config = new JdConfigController();
                $jd_config = $config->jd_config($config_id);
                if (!$jd_config) {
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = '京东配置不存在请检查配置';
                    return $this->return_data($re_data);
                }

                $jd_merchant = $config->jd_merchant($store_id, $store_pid);
                if (!$jd_merchant) {
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = '京东商户号不存在';
                    return $this->return_data($re_data);
                }
                $obj = new \App\Api\Controllers\Jd\PayController();
                $data = [];
                $data['out_trade_no'] = $order->out_trade_no;
                $data['request_url'] = $obj->refund_url;//请求地址;
                $data['notifyUrl'] = url('/api/jd/refund_url');//通知地址;
                $data['merchant_no'] = $jd_merchant->merchant_no;
                $data['md_key'] = $jd_merchant->md_key;//
                $data['des_key'] = $jd_merchant->des_key;//
                $data['systemId'] = $jd_config->systemId;//
                $data['outRefundNo'] = $OutRefundNo;
                $data['amount'] = $order->total_amount;

                $return = $obj->refund($data);

                //退款请求成功
                if ($return["status"] == 1) {
                    $order->pay_status_desc = '已退款';
                    $order->pay_status = 6;
                    $order->status = 6;
                    $order->fee_amount = 0;//手续费
                    $order->save();


                    RefundOrder::create([
                        'pay_status' => 6,
                        'status' => 6,
                        'type' => $type,
                        'ways_source' => $order->ways_source,
                        'status_desc' => '退款',
                        'refund_amount' => $order->total_amount,//退款金额
                        'refund_no' => $OutRefundNo,//退款单号
                        'store_id' => $data['merchantId'],
                        'merchant_id' => $store->merchant_id,
                        'out_trade_no' => $order->out_trade_no,
                        'trade_no' => $order->trade_no
                    ]);


                    $re_data['result_code'] = 'SUCCESS';
                    $re_data['result_msg'] = '退款成功';
                    $re_data['refundNo'] = $OutRefundNo;
                    $re_data['refundFee'] = $order->total_amount * 100;

                } else {
                    //其他情况
                    $message = $return['message'];
                    //退款失败
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;
                }


            }


            //联拓富收银支付退款
            if (9999 < $type && $type < 19999) {
                //读取配置
                $config = new LtfConfigController();

                $ltf_merchant = $config->ltf_merchant($store_id, $store_pid);
                if (!$ltf_merchant) {
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = '商户号不存在';
                    return $this->return_data($re_data);
                }
                $obj = new \App\Api\Controllers\Ltf\PayController();
                $data = [];
                $data['out_trade_no'] = $order->out_trade_no;
                $data['request_url'] = $obj->refund_url;//请求地址;
                $data['notifyUrl'] = url('/api/jd/refund_url');//通知地址;
                $data['merchant_no'] = $ltf_merchant->merchantCode;
                $data['appId'] = $ltf_merchant->appId;//
                $data['key'] = $ltf_merchant->md_key;//
                $data['outRefundNo'] = $OutRefundNo;
                $data['amount'] = $order->total_amount;

                $return = $obj->refund($data);


                //退款请求成功
                if ($return["status"] == 1) {
                    $order->pay_status_desc = '已退款';
                    $order->pay_status = 6;
                    $order->status = 6;
                    $order->fee_amount = 0;//手续费
                    $order->save();


                    RefundOrder::create([
                        'pay_status' => 6,
                        'status' => 6,
                        'type' => $type,
                        'ways_source' => $order->ways_source,
                        'status_desc' => '退款',
                        'refund_amount' => $order->total_amount,//退款金额
                        'refund_no' => $OutRefundNo,//退款单号
                        'store_id' => $data['merchant_no'],
                        'merchant_id' => $store->merchant_id,
                        'out_trade_no' => $order->out_trade_no,
                        'trade_no' => $order->trade_no
                    ]);


                    $re_data['result_code'] = 'SUCCESS';
                    $re_data['result_msg'] = '退款成功';
                    $re_data['refundNo'] = $OutRefundNo;
                    $re_data['refundFee'] = $order->total_amount * 100;

                } else {
                    //其他情况
                    $message = $return['message'];
                    //退款失败
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;
                }


            }

            //网商收银支付退款
            if (2999 < $type && $type < 3999) {
                //读取配置
                $config = new MyBankConfigController();


                $mybank_merchant = $config->mybank_merchant($store_id, $store_pid);
                if (!$mybank_merchant) {
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = '网商商户号不存在';
                    return $this->return_data($re_data);
                }
                $wx_AppId=$mybank_merchant->wx_AppId;
                $MyBankConfig = $config->MyBankConfig($config_id,$wx_AppId);
                if (!$MyBankConfig) {
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = '网商配置不存在请检查配置';
                    return $this->return_data($re_data);
                }
                $obj = new TradePayController();
                $MerchantId = $mybank_merchant->MerchantId;
                $RefundAmount = $order->total_amount;
                $return = $obj->mybankrefund($MerchantId, $order->out_trade_no, $OutRefundNo, $RefundAmount, $config_id);

                //退款请求成功
                if ($return["status"] == 1) {
                    $order->pay_status_desc = '已退款';
                    $order->pay_status = 6;
                    $order->status = 6;
                    $order->fee_amount = 0;//手续费
                    $order->save();

                    RefundOrder::create([
                        'pay_status' => 3,
                        'status' => 3,
                        'type' => $type,
                        'ways_source' => $order->ways_source,
                        'status_desc' => '退款',
                        'refund_amount' => $order->total_amount,//退款金额
                        'refund_no' => $OutRefundNo,//退款单号
                        'store_id' => $store_id,
                        'merchant_id' => $store->merchant_id,
                        'out_trade_no' => $order->out_trade_no,
                        'trade_no' => $order->trade_no
                    ]);

                    $re_data['result_code'] = 'SUCCESS';
                    $re_data['result_msg'] = '退款成功';
                    $re_data['refundNo'] = $OutRefundNo;
                    $re_data['refundFee'] = $order->total_amount * 100;


                } else {
                    //其他情况
                    $message = $return['message'];
                    //退款失败
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;
                }
            }

            //和融通收银支付退款
            if (8999 < $type && $type < 9999) {
                //读取配置
                $config = new HConfigController();
                $h_config = $config->h_config($config_id);
                if (!$h_config) {

                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = '和融通配置不存在请检查配置';
                    return $this->return_data($re_data);
                }

                $h_merchant = $config->h_merchant($store_id, $store_pid);
                if (!$h_merchant) {
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = '和融通商户号不存在';
                    return $this->return_data($re_data);
                }
                $obj = new \App\Api\Controllers\Huiyuanbao\PayController();
                $data = [];
                $data['trade_no'] = $order->trade_no;
                $data['request_url'] = $obj->refund_url;//请求地址;
                $data['notifyUrl'] = url('/api/jd/refund_url');//通知地址;
                $data['mid'] = $h_merchant->h_mid;
                $data['md_key'] = $h_config->md_key;//
                $data['orgNo'] = $h_merchant->orgNo;//
                $data['outRefundNo'] = $OutRefundNo;
                $data['amount'] = $order->total_amount;

                $return = $obj->refund($data);

                //退款请求成功
                if ($return["status"] == 1) {
                    $order->pay_status_desc = '已退款';
                    $order->pay_status = 6;
                    $order->status = 6;
                    $order->fee_amount = 0;//手续费
                    $order->save();

                    RefundOrder::create([
                        'pay_status' => 3,
                        'status' => 3,
                        'type' => $type,
                        'ways_source' => $order->ways_source,
                        'status_desc' => '退款',
                        'refund_amount' => $order->total_amount,//退款金额
                        'refund_no' => $OutRefundNo,//退款单号
                        'store_id' => $store_id,
                        'merchant_id' => $store->merchant_id,
                        'out_trade_no' => $order->out_trade_no,
                        'trade_no' => $order->trade_no
                    ]);

                    $re_data['result_code'] = 'SUCCESS';
                    $re_data['result_msg'] = '退款成功';
                    $re_data['refundNo'] = $OutRefundNo;
                    $re_data['refundFee'] = $order->total_amount * 100;


                } else {
                    //其他情况
                    $message = $return['message'];
                    //退款失败
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;
                }
            }


        } catch (\Exception $exception) {
            $re_data = [
                'return_code' => 'FALL',
                'return_msg' => $exception->getMessage() . $exception->getLine(),
            ];
        }


        return $this->return_data($re_data);


    }


    //查询订单号状态
    public function refund_query(Request $request)
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


            //调用系统前参数

            $where = [];
            if (isset($data['tradeNo'])) {
                $where[] = ['out_trade_no', '=', $data['tradeNo']];
            }
            if (isset($data['out_transaction_id'])) {
                // $where[] = ['trade_no', '=', $data['out_transaction_id']];
                $where[] = ['out_trade_no', '=', $data['out_transaction_id']];

            }
            if (isset($data['outTradeNo'])) {
                $where[] = ['qwx_no', '=', $data['outTradeNo']];
            }


            //发起查询
            $order = Order::where('store_id', $data['merchantId'])
                ->where($where)
                ->select('ways_type', 'qwx_no', 'updated_at', 'out_trade_no')//2.0 ways_type 1.0 type
                ->first();

            //如果订单号为空或者不存在
            if (!$order) {
                $re_data['result_code'] = 'FALL';
                $re_data['result_msg'] = '订单号不存在';
                return $this->return_data($re_data);
            }

            $refundNo = $data['refundNo'];
            //上线后去掉开始
            $refund = RefundOrder::where('out_trade_no', $order->out_trade_no)
                ->select('refund_no')
                ->first();

            //如果订单号为空或者不存在
            if (!$refund) {
                $re_data['result_code'] = 'FALL';
                $re_data['result_msg'] = '订单号不存在';
                return $this->return_data($re_data);
            }
            $refundNo = $refund->refund_no;

            //上线后去掉结束

            $channel = '';
            if ($order->ways_type == 1001) {
                $channel = "ali_barcode_pay";
            }

            if ($order->ways_type == 2001) {
                $channel = "wx_barcode_pay";
            }

            $re_data = [
                'return_code' => 'SUCCESS',//SUCCESS/FALL 此字段是通信标识，非交易标识，交易是否成功需要查看result_code来判断
                'return_msg' => null,
                'result_code' => '',
                'result_msg' => '',
                'tradeNo' => $order->out_trade_no,
                'outTradeNo' => $order->qwx_no,//微收银唯一订单号
                'refundNo' => '',
                'refundFee' => '',
                'channel' => $channel,//$data['channel'],//支付类型 微信主扫：wx_barcode_pay 支付宝：ali_barcode_pay
                'totalFee' => '',
                'refundTime' => '',
                'refund_count' => '',
            ];


            $type = $order->ways_type;
            $store = Store::where('store_id', $data['merchantId'])
                ->select('config_id', 'merchant_id')
                ->first();
            $config_id = $store->config_id;
            $store_id = $data['merchantId'];
            $store_pid = $store->pid;
            //官方支付宝查询
            if (999 < $type && $type < 1999) {
                $config = AlipayIsvConfig::where('config_id', $config_id)
                    ->where('config_type', '01')
                    ->first();

                //获取token
                $storeInfo = AlipayAppOauthUsers::where('store_id', $data['merchantId'])
                    ->select('app_auth_token')
                    ->first();

                $aop = new AopClient();
                $aop->apiVersion = "2.0";
                $aop->appId = $config->app_id;
                $aop->rsaPrivateKey = $config->rsa_private_key;
                $aop->alipayrsaPublicKey = $config->alipay_rsa_public_key;
                $aop->signType = "RSA2";//升级算法
                $aop->gatewayUrl = $config->alipay_gateway;
                $aop->format = "json";
                $aop->charset = "GBK";
                $aop->version = "2.0";
                $aop->method = 'alipay.trade.fastpay.refund.query';
                $requests = new AlipayTradeFastpayRefundQueryRequest();
                $requests->setBizContent("{" .
                    "    \"out_trade_no\":\"" . $order->out_trade_no . "\"," .
                    "    \"out_request_no\":\"" . $refundNo . "\"" .
                    "  }");
                $refund = $aop->execute($requests, '', $storeInfo->app_auth_token);

                //支付成功
                if ($refund->alipay_trade_fastpay_refund_query_response->code == 10000) {
                    $re_data['result_code'] = 'SUCCESS';
                    $re_data['result_msg'] = '退款成功';

                    $re_data['refundNo'] = $refund->alipay_trade_fastpay_refund_query_response->out_request_no;
                    $re_data['refundFee'] = $refund->alipay_trade_fastpay_refund_query_response->refund_amount * 100;
                    $re_data['totalFee'] = $refund->alipay_trade_fastpay_refund_query_response->total_amount * 100;
                    $re_data['refundTime'] = '' . $order->updated_at . '';
                    $re_data['refund_count'] = '1';

                } else {
                    //其他情况
                    $message = $refund->alipay_trade_fastpay_refund_query_response->sub_msg;
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;
                }
            }

            //微信官方扫码
            if (1999 < $type && $type < 2999) {
                $config = new WeixinConfigController();
                $options = $config->weixin_config($config_id);
                $weixin_store = $config->weixin_merchant($store_id, $store_pid);
                $wx_sub_merchant_id = $weixin_store->wx_sub_merchant_id;
                $config = [
                    'app_id' => $options['app_id'],
                    'mch_id' => $options['payment']['merchant_id'],
                    'key' => $options['payment']['key'],
                    'cert_path' => $options['payment']['cert_path'], // XXX: 绝对路径！！！！
                    'key_path' => $options['payment']['key_path'],     // XXX: 绝对路径！！！！
                    'sub_mch_id' => $wx_sub_merchant_id,
                    // 'device_info'     => '013467007045764',
                    // 'sub_app_id'      => '',
                    // ...
                ];

                $payment = Factory::payment($config);
                $refund_query = $payment->refund->queryByOutTradeNumber($order->out_trade_no);


                if ($refund_query['return_code'] == "SUCCESS") {
                    //退款成功
                    if ($refund_query['result_code'] == "SUCCESS") {
                        $re_data['result_code'] = 'SUCCESS';
                        $re_data['result_msg'] = '退款成功';
                        $re_data['refundNo'] = $refund_query['refund_status_0'];
                        $re_data['refundFee'] = $refund_query['settlement_refund_fee_0'];
                        $re_data['totalFee'] = $refund_query['total_fee'];
                        $re_data['refundTime'] = $refund_query['refund_success_time_0'];
                        $re_data['refund_count'] = $refund_query['refund_count'];
                    } else {
                        //
                        $re_data['result_code'] = 'FALL';
                        $re_data['result_msg'] = $refund_query['result_msg'];
                    }


                } else {
                    //退款失败
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $refund_query['return_msg'];

                }


            }

            //京东收银支付退款查询
            if (5999 < $type && $type < 6999) {
                //读取配置
                $config = new JdConfigController();
                $jd_config = $config->jd_config($config_id);
                if (!$jd_config) {
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = '京东配置不存在请检查配置';
                    return $this->return_data($re_data);
                }

                $jd_merchant = $config->jd_merchant($store_id, $store_pid);
                if (!$jd_merchant) {
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = '京东商户号不存在';
                    return $this->return_data($re_data);
                }
                $obj = new \App\Api\Controllers\Jd\PayController();
                $data = [];
                $data['request_url'] = $obj->refund_query_url;//请求地址;
                $data['merchant_no'] = $jd_merchant->merchant_no;
                $data['md_key'] = $jd_merchant->md_key;//
                $data['des_key'] = $jd_merchant->des_key;//
                $data['systemId'] = $jd_config->systemId;//
                $data['outRefundNo'] = $refundNo;

                $return = $obj->refund_query($data);

                //退款成功
                if ($return["status"] == 1) {
                    $re_data['result_code'] = 'SUCCESS';
                    $re_data['result_msg'] = '退款成功';
                    $re_data['refundNo'] = $refundNo;
                    $re_data['refundFee'] = $return['data']['amount'];
                    $re_data['totalFee'] = $return['data']['amount'];
                    $re_data['refundTime'] = date('Y-m-d H:i:s', strtotime($return['data']['payFinishTime']));
                    $re_data['refund_count'] = '1';

                } //等待付款
                elseif ($return["status"] == 2) {
                    $re_data['result_code'] = 'USERPAYING';
                    $re_data['result_msg'] = '退款中';
                    $re_data['outTradeNo'] = $order->qwx_no;
                    return $this->return_data($re_data);

                } //订单失败关闭
                elseif ($return["status"] == 3) {
                    //其他情况
                    $message = '订单支付失败';
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;

                } else {
                    //其他情况
                    $message = $return['message'];
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;
                }

            }


            //联拓富收银支付退款查询
            if (9999 < $type && $type < 19999) {
                //读取配置
                $config = new LtfConfigController();

                $ltf_merchant = $config->ltf_merchant($store_id, $store_pid);
                if (!$ltf_merchant) {
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = '京东商户号不存在';
                    return $this->return_data($re_data);
                }
                $obj = new \App\Api\Controllers\Ltf\PayController();
                $data = [];
                $data['request_url'] = $obj->refund_query_url;//请求地址;
                $data['merchant_no'] = $ltf_merchant->merchantCode;
                $data['appId'] = $ltf_merchant->appId;//
                $data['key'] = $ltf_merchant->md_key;//
                $data['outRefundNo'] = $refundNo;

                $return = $obj->refund_query($data);

                //退款成功
                if ($return["status"] == 1) {
                    $re_data['result_code'] = 'SUCCESS';
                    $re_data['result_msg'] = '退款成功';
                    $re_data['refundNo'] = $refundNo;
                    $re_data['refundFee'] = $return['data']['refundAmount'];
                    $re_data['totalFee'] = $return['data']['refundAmount'];
                    $re_data['refundTime'] = date('Y-m-d H:i:s', strtotime($return['data']['time']));
                    $re_data['refund_count'] = '1';

                } //等待付款
                elseif ($return["status"] == 2) {
                    $re_data['result_code'] = 'USERPAYING';
                    $re_data['result_msg'] = '退款中';
                    $re_data['outTradeNo'] = $order->qwx_no;
                    return $this->return_data($re_data);

                } //订单失败关闭
                elseif ($return["status"] == 3) {
                    //其他情况
                    $message = '订单支付失败';
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;

                } else {
                    //其他情况
                    $message = $return['message'];
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;
                }

            }
            //网商银行
            if (2999 < $type && $type < 3999) {

                //读取配置
                $config = new MyBankConfigController();


                $mybank_merchant = $config->mybank_merchant($store_id, $store_pid);
                if (!$mybank_merchant) {
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = '网商商户号不存在';
                    return $this->return_data($re_data);
                }
                $wx_AppId=$mybank_merchant->wx_AppId;

                $MyBankConfig = $config->MyBankConfig($config_id,$wx_AppId);
                if (!$MyBankConfig) {
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = '网商配置不存在请检查配置';
                    return $this->return_data($re_data);
                }

                $aop = new \App\Api\Controllers\MyBank\BaseController();
                $ao = $aop->aop($config_id);
                $ao->url = env("MY_BANK_request2");
                $ao->Function = "ant.mybank.bkmerchanttrade.refundQuery";

                $data = [
                    'MerchantId' => $mybank_merchant->MerchantId,
                    'OutRefundNo' => $refundNo,
                ];
                $re = $ao->Request($data);
                if ($re['status'] == 0) {
                    //退款失败
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $re['msg'];
                    return $this->return_data($re_data);

                }

                $body = $re['data']['document']['response']['body'];
                $Result = $body['RespInfo'];
                //支付成功
                if ($Result['ResultStatus'] == "S") {
                    $GmtRefundment = $body['GmtRefundment'];

                    //退款成功
                    if ($body['TradeStatus'] == "succ") {
                        $re_data['result_code'] = 'SUCCESS';
                        $re_data['result_msg'] = '退款成功';
                        $re_data['refundNo'] = $body['OutRefundNo'];
                        $re_data['refundFee'] = '' . ($order->total_amount * 100) . '';
                        $re_data['totalFee'] = $body['RefundAmount'];
                        $re_data['refundTime'] = '' . $GmtRefundment . '';
                        $re_data['refund_count'] = '1';
                    }


                    //退款中
                    if ($body['TradeStatus'] == "refunding") {
                        $re_data['result_code'] = 'USERPAYING';
                        $re_data['result_msg'] = '退款中';
                        $re_data['refundNo'] = $refundNo;
                    }

                    //失败
                    if ($body['TradeStatus'] == "fail") {
                        $re_data['result_code'] = 'FALL';
                        $re_data['result_msg'] = '退款失败';
                        $re_data['refundNo'] = $refundNo;
                    }


                } else {
                    //其他情况
                    $message = $Result['ResultMsg'];
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;
                }
            }

            //和融通
            if (8999 < $type && $type < 9999) {

                //读取配置
                $config = new HConfigController();
                $h_config = $config->h_config($config_id);
                if (!$h_config) {

                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = '和融通配置不存在请检查配置';
                    return $this->return_data($re_data);
                }

                $h_merchant = $config->h_merchant($store_id, $store_pid);
                if (!$h_merchant) {
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = '和融通商户号不存在';
                    return $this->return_data($re_data);
                }
                $obj = new \App\Api\Controllers\Huiyuanbao\PayController();
                $data = [];
                $data['out_trade_no'] = $order->out_trade_no;
                $data['request_url'] = $obj->order_query_url;//暂时用查询代替
                $data['mid'] = $h_merchant->h_mid;
                $data['md_key'] = $h_config->md_key;//
                $data['orgNo'] = $h_merchant->orgNo;//

                $return = $obj->order_query($data);//暂时用查询代替

                //退款成功
                if ($return["status"] == 4) {
                    $re_data['result_code'] = 'SUCCESS';
                    $re_data['result_msg'] = '退款成功';
                    $re_data['refundNo'] = $refundNo;
                    $re_data['refundFee'] = $return['data']['amount'];
                    $re_data['totalFee'] = $return['data']['amount'];
                    $re_data['refundTime'] = date('Y-m-d H:i:s', strtotime($return['data']['payFinishTime']));
                    $re_data['refund_count'] = '1';

                } //等待付款
                elseif ($return["status"] == 5) {
                    $re_data['result_code'] = 'USERPAYING';
                    $re_data['result_msg'] = '退款中';
                    $re_data['outTradeNo'] = $order->qwx_no;
                    return $this->return_data($re_data);

                } //订单失败关闭
                elseif ($return["status"] == 3) {
                    //其他情况
                    $message = '订单支付失败';
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;

                } else {
                    //其他情况
                    $message = $return['message'];
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;
                }

            }


            return $this->return_data($re_data);


        } catch (\Exception $exception) {
            $re_data = [
                'return_code' => 'FALL',
                'return_msg' => $exception->getMessage() . $exception->getLine(),
            ];
        }


        return $this->return_data($re_data);


    }


}