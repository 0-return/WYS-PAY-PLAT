<?php

namespace App\Api\Controllers\Deposit;

use App\Api\Controllers\BaseController;
use App\Api\Controllers\Config\AlipayIsvConfigController;
use App\Api\Controllers\Config\PayWaysController;
use App\Api\Controllers\Config\WeixinConfigController;
use App\Common\DSuccessAction;
use App\Models\DepositOrder;
use App\Models\DepositRefundOrder;
use App\Models\MerchantStore;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2019/3/30
 * Time: 3:45 PM
 */
class DepositController extends BaseController
{


    //预授权 扫码入口
    public function micropay(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $store_id = $request->get('store_id');
            $merchant_id = $request->get('merchant_id', $merchant->merchant_id);
            $total_amount = $request->get('total_amount');
            $out_trade_no = '11' . time() . substr(microtime(), 2, 4);
            $out_order_no = '22' . time() . substr(microtime(), 2, 4);
            $out_request_no = '33' . time() . substr(microtime(), 2, 4);
            $code = $request->get('code');
            $device_id = $request->get('device_id', 'POS');
            $remark = $request->get('remark', '备注');
            $shop_name = $request->get('shop_name', '');
            $shop_desc = $request->get('shop_desc', '');

            $check_data = [
                'store_id' => '门店ID',
                'total_amount' => '押金金额',
                'code' => '付款码',

            ];
            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'deposit_status' => '3',
                    'message' => $check
                ]);
            }

            $str = substr($code, 0, 2);
            $store = Store::where('store_id', $store_id)
                ->select('id', 'store_short_name', 'config_id', 'user_id', 'pid')
                ->first();


            if (!$store) {
                return json_encode([
                    'status' => 2,
                    'deposit_status' => '3',
                    'message' => '门店不存在'
                ]);
            }

            $config_id = $store->config_id;
            $order_title = $shop_name ? $shop_name . $shop_desc : $store->store_short_name . '押金';
            $user_id = $store->user_id;
            $store_pid = $store->pid;
            $store_name = $store->store_short_name;
            $merchant_name = $merchant->merchant_name;

            //插入数据库
            $data_insert = [
                'store_id' => $store_id,
                'store_name' => $store_name,
                'merchant_name' => $merchant_name,
                'config_id' => $config_id,
                'user_id' => $user_id,
                'seller_id' => '',
                'merchant_id' => $merchant_id,
                'out_trade_no' => $out_trade_no,//平台交易系统单号
                'trade_no' => '',//支付宝或者微信交易单号
                'out_order_no' => $out_order_no,//商户的授权资金订单号
                'out_request_no' => $out_request_no,//商户本次资金操作的请求流水号
                'operation_id' => '',//支付宝或者微信资金操作流水号
                'auth_no' => '',
                'amount' => $total_amount,
                'pay_amount' => '0.00',//用户需要支付总金额
                'refund_amount' => 0.00,//退款金额
                'pay_status' => '2',//系统状态
                'pay_status_desc' => '等待消费',
                'deposit_status' => '2',//系统状态
                'deposit_status_desc' => '等待押金冻结',
                'deposit_time' => '',//冻结成功时间
                'refund_time' => '',//预授权支付完成时间
                'payer_user_id' => '',
                'payer_logon_id' => '',
                'rate' => '',//商户交易时的费率
                'fee_amount' => "",
                'device_id' => $device_id,
                "remark" => $remark,
                'ways_type' => '',
                'ways_source' => '',
                'ways_source_desc' => '',
                'ways_company' => '',//通道方
            ];


            //  支付宝
            if (in_array($str, ['28'])) {
                $ways_type = '1000';

                //读取优先为高级的通道
                $obj_ways = new PayWaysController();
                $ways = $obj_ways->ways_type($ways_type, $store_id, $store_pid);

                if (!$ways) {
                    return json_encode([
                        'status' => 2,
                        'deposit_status' => '3',
                        'message' => '没有开通此类型通道'
                    ]);
                }

                //费率入库
                $data_insert['rate'] = $ways->rate;
                $data_insert['fee_amount'] = $ways->rate * $total_amount / 100;


                //官方支付宝预授权
                if ($ways_type == "1000") {

                    $obj = new AliDepositController();
                    $isvconfig = new AlipayIsvConfigController();
                    $config = $isvconfig->AlipayIsvConfig($config_id);
                    if (!$config) {
                        return json_encode([
                            'status' => 2,
                            'deposit_status' => '3',
                            'message' => '服务商未配置支付宝应用'
                        ]);
                    }
                    $merchanr_info = $isvconfig->alipay_auth_info($store_id, $store_pid);
                    if (!$merchanr_info) {
                        return json_encode([
                            'status' => 2,
                            'deposit_status' => '3',
                            'message' => '商户未使用支付宝授权'
                        ]);
                    }


                    //入库参数新增
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['ways_company'] = $ways->company;
                    $data_insert['ways_type_desc'] = '支付宝';
                    $data_insert['ways_source'] = 'alipay';
                    $data_insert['ways_source_desc'] = '支付宝';
                    $data_insert['seller_id'] = $merchanr_info->alipay_user_id;


                    //预授权
                    $data = [
                        'app_id' => $config->app_id,
                        'rsa_private_key' => $config->rsa_private_key,
                        'alipay_rsa_public_key' => $config->alipay_rsa_public_key,
                        'alipay_gateway' => $config->alipay_gateway,
                        'notify_url' => '',
                        'app_auth_token' => $merchanr_info->app_auth_token,
                        'out_order_no' => $out_order_no,
                        'out_request_no' => $out_request_no,
                        'auth_code' => $code,
                        'order_title' => $order_title,
                        'amount' => $total_amount,
                        'pay_timeout' => '5m',
                        'payee_user_id' => $merchanr_info->alipay_user_id,
                        'sys_service_provider_id' => $config->alipay_pid,
                    ];
                    $re = $obj->base_fund_freeze($data);


                    //  0 系统错 1成功 2 等待用户确认 3失败


                    //0 系统错  3失败
                    if ($re['status'] == 0 || $re['status'] == 3) {
                        return json_encode([
                            'status' => 2,
                            'deposit_status' => '3',
                            'message' => $re['message']
                        ]);
                    }


                    //新增请求成功入库参数
                    $data_insert['auth_no'] = $re['data']['auth_no'];
                    $data_insert['operation_id'] = $re['data']['operation_id'];


                    $data_insert = DepositOrder::create($data_insert);

                    if (!$data_insert) {
                        return json_encode([
                            'status' => 2,
                            'deposit_status' => '3',
                            'message' => '数据入库失败'
                        ]);
                    }


                    $re['data']['store_id'] = $store_id;
                    $re['data']['ways_source'] = $data_insert['ways_source'];
                    $re['data']['ways_source_desc'] = $data_insert['ways_source_desc'];
                    $re['data']['out_trade_no'] = $data_insert['out_trade_no'];

                    //授权冻结成功
                    if ($re['status'] == 1) {
                        $pay_time = date('Y-m-d H:i:s', strtotime($re['data']['gmt_trans']));
                        DepositOrder::where('out_trade_no', $out_trade_no)->update(
                            [
                                'deposit_time' => $re['data']['gmt_trans'],
                                'deposit_status_desc' => '押金冻结成功',
                                'deposit_status' => 1,
                                'payer_user_id' => $re['data']['payer_user_id'],
                                'payer_logon_id' => $re['data']['payer_logon_id'],
                            ]);


                        //支付成功后的动作
                        $data = [
                            'ways_type' => 'alipay',
                            'ways_type_desc' => '支付宝',
                            'source_type' => '1000',//返佣来源
                            'source_desc' => '支付宝',//返佣来源说明
                            'total_amount' => $data_insert['amount'],
                            'out_trade_no' => $data_insert['out_trade_no'],
                            'rate' => $data_insert['rate'],
                            'merchant_id' => $data_insert['merchant_id'],
                            'store_id' => $data_insert['store_id'],
                            'user_id' => $data_insert['user_id'],
                            'config_id' => $data_insert['config_id'],
                            'store_name' => $data_insert['store_name'],
                            'ways_source' => $data_insert['ways_source'],
                            'pay_time' => $pay_time,
                            'print_type' => '1',//1 押金预授权 2 押金消费，3押金退款

                        ];


                        DSuccessAction::action($data);


                        return json_encode([
                            'status' => 1,
                            'deposit_status' => '1',
                            'message' => $re['message'],
                            'data' => $re['data']
                        ]);
                    }


                    //冻结确认输入密码中
                    if ($re['status'] == 2) {
                        return json_encode([
                            'status' => 1,
                            'deposit_status' => '2',
                            'message' => $re['message'],
                            'data' => $re['data']
                        ]);
                    }


                }
            }


            //  微信
            if (in_array($str, ['13', '14'])) {
                $ways_type = '2000';
                //读取优先为高级的通道
                $obj_ways = new PayWaysController();
                $ways = $obj_ways->ways_type($ways_type, $store_id, $store_pid);

                if (!$ways) {
                    return json_encode([
                        'status' => 2,
                        'deposit_status' => '3',
                        'message' => '没有开通此类型通道'
                    ]);
                }

                //费率入库
                $data_insert['rate'] = $ways->rate;
                $data_insert['fee_amount'] = $ways->rate * $total_amount / 100;


                //官方微信预授权
                if ($ways_type == "2000") {

                    $config = new WeixinConfigController();
                    $options = $config->weixin_config($config_id);
                    $weixin_store = $config->weixin_merchant($store_id, $store_pid);
                    if (!$weixin_store) {
                        return json_encode([
                            'status' => 2,
                            'message' => '微信商户号不存在'
                        ]);
                    }

                    $sub_merchant_id = $weixin_store->wx_sub_merchant_id;
                    $wx_sub_app_id = $weixin_store->wx_sub_app_id;

                    //入库参数新增
                    $data_insert['out_trade_no'] = $out_trade_no;
                    $data_insert['ways_type'] = $ways->ways_type;
                    $data_insert['ways_company'] = $ways->company;
                    $data_insert['ways_type_desc'] = '微信支付';
                    $data_insert['ways_source'] = 'weixin';
                    $data_insert['ways_source_desc'] = '微信支付';
                    $data_insert['seller_id'] = $sub_merchant_id;


                    //预授权
                    $data = [
                        'app_id' => $options['app_id'],
                        'key' => $options['payment']['key'],
                        'sub_app_id' => $wx_sub_app_id,
                        'mch_id' => $options['payment']['merchant_id'],
                        'sub_mch_id' => $sub_merchant_id,
                        'device_info' => $device_id,
                        'out_order_no' => $out_order_no,
                        'body' => $order_title,
                        'auth_code' => $code,
                        'attach' => $remark,
                        'total_fee' => $total_amount,
                    ];


                    $obj = new WxDepositController();
                    $re = $obj->base_fund_freeze($data);


                    //  0 系统错 1成功 2 等待用户确认 3失败

                    //0 系统错  3失败
                    if ($re['status'] == 0 || $re['status'] == 3) {
                        return json_encode([
                            'status' => 2,
                            'deposit_status' => '3',
                            'message' => $re['message']
                        ]);
                    }


                    //新增请求成功入库参数
                    //  $data_insert['auth_no'] = '';
                    //  $data_insert['operation_id'] = '';


                    $data_insert = DepositOrder::create($data_insert);

                    if (!$data_insert) {
                        return json_encode([
                            'status' => 2,
                            'deposit_status' => '3',
                            'message' => '数据入库失败'
                        ]);
                    }


                    //授权冻结成功
                    if ($re['status'] == 1) {
                        $pay_time = date('Y-m-d H:i:s', strtotime($re['data']['gmt_trans']));

                        DepositOrder::where('out_trade_no', $out_trade_no)->update(
                            [
                                'deposit_time' => $re['data']['gmt_trans'],
                                'deposit_status_desc' => '押金冻结成功',
                                'deposit_status' => 1,
                                'payer_user_id' => $re['data']['openid'],
                                'payer_logon_id' => $re['data']['sub_openid'],
                                'trade_no' => $re['data']['transaction_id'],
                            ]);


                        //支付成功后的动作
                        $data = [
                            'ways_type' => 'weixin',
                            'ways_type_desc' => '微信支付',
                            'source_type' => '2000',//返佣来源
                            'source_desc' => '微信支付',//返佣来源说明
                            'total_amount' => $data_insert['amount'],
                            'out_trade_no' => $data_insert['out_trade_no'],
                            'rate' => $data_insert['rate'],
                            'merchant_id' => $data_insert['merchant_id'],
                            'store_id' => $data_insert['store_id'],
                            'user_id' => $data_insert['user_id'],
                            'config_id' => $data_insert['config_id'],
                            'store_name' => $data_insert['store_name'],
                            'ways_source' => $data_insert['ways_source'],
                            'pay_time' => $pay_time,
                            'print_type' => '1',//1 押金预授权 2 押金消费，3押金退款

                        ];


                        DSuccessAction::action($data);


                        return json_encode([
                            'status' => 1,
                            'deposit_status' => '1',
                            'message' => $re['message'],
                            'data' => [
                                'store_id' => $store_id,
                                'ways_source' => $data_insert['ways_source'],
                                'ways_source_desc' => $data_insert['ways_source_desc'],
                                "amount" => $total_amount,
                                "auth_no" => "",
                                "operation_id" => "",
                                "out_order_no" => $out_order_no,
                                "out_trade_no" => $out_trade_no,
                                "out_request_no" => "",
                                "gmt_trans" => $re['data']['gmt_trans'],
                                "payer_user_id" => $re['data']['openid'],
                                "payer_logon_id" => $re['data']['sub_openid'],
                            ]
                        ]);
                    }


                    //冻结确认输入密码中
                    if ($re['status'] == 2) {
                        return json_encode([
                            'status' => 1,
                            'deposit_status' => '2',
                            'message' => $re['message'],
                            'data' => [
                                'store_id' => $store_id,
                                'ways_source' => $data_insert['ways_source'],
                                'ways_source_desc' => $data_insert['ways_source_desc'],
                                "amount" => $total_amount,
                                "auth_no" => "",
                                "operation_id" => "",
                                "out_order_no" => $out_order_no,
                                "out_trade_no" => $out_trade_no,
                                "out_request_no" => "",
                            ]
                        ]);
                    }


                }
            }


            return json_encode([
                'status' => 2,
                'deposit_status' => '3',
                'message' => '暂时不支持此付款码授权'
            ]);

        } catch (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'deposit_status' => '3',
                'message' => $exception->getMessage(),
            ]);
        }

    }


    //预授权查询
    public function fund_order_query(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $out_order_no = $request->get('out_order_no');
            $out_trade_no = $request->get('out_trade_no');


            $where = [];
            if (isset($out_order_no)) {
                $where[] = ['out_order_no', '=', $out_order_no];
            }
            if (isset($out_trade_no)) {
                $where[] = ['out_trade_no', '=', $out_trade_no];
            }

            $DepositOrder = DepositOrder::where($where)
                ->select(
                    'out_order_no', 'rate', 'merchant_id', 'user_id', 'out_trade_no', 'amount', 'ways_source', 'ways_source_desc', 'ways_company', 'operation_id', 'auth_no', 'out_request_no',
                    'deposit_status'
                )
                ->first();
            if (!$DepositOrder) {
                return json_encode([
                    'status' => 2,
                    'deposit_status' => '3',
                    'message' => '订单号不存在'
                ]);
            }

            $store = Store::where('store_id', $store_id)
                ->select('id', 'store_short_name', 'config_id', 'user_id', 'pid')
                ->first();


            if (!$store) {
                return json_encode([
                    'status' => 2,
                    'deposit_status' => '3',
                    'message' => '门店不存在'
                ]);
            }

            $store_pid = $store->pid;
            $config_id = $store->config_id;
            $store_name = $store->store_short_name;
            //官方支付宝
            if ($DepositOrder->ways_company == "alipay") {
                //支付宝预授权
                $obj = new AliDepositController();

                $isvconfig = new AlipayIsvConfigController();
                $config = $isvconfig->AlipayIsvConfig($config_id);
                $merchanr_info = $isvconfig->alipay_auth_info($store_id, $store_pid);


                //查询
                $data = [
                    'app_id' => $config->app_id,
                    'rsa_private_key' => $config->rsa_private_key,
                    'alipay_rsa_public_key' => $config->alipay_rsa_public_key,
                    'alipay_gateway' => $config->alipay_gateway,
                    'notify_url' => '',
                    'app_auth_token' => $merchanr_info->app_auth_token,
                    'out_order_no' => $DepositOrder->out_order_no,
                    'operation_id' => $DepositOrder->operation_id,
                    'auth_no' => $DepositOrder->auth_no,
                    'out_request_no' => $DepositOrder->out_request_no,

                ];
                $re = $obj->base_fund_order_query($data);

                //支付宝扫码预授权查询  0 系统错 1成功 2 等待用户确认 3失败


                //0 系统错  3失败
                if ($re['status'] == 0 || $re['status'] == 3) {
                    return json_encode([
                        'status' => 2,
                        'deposit_status' => '3',
                        'message' => $re['message']
                    ]);
                }

                $re['data']['store_id'] = $store_id;
                $re['data']['ways_source'] = $DepositOrder->ways_source;
                $re['data']['ways_source_desc'] = $DepositOrder->ways_source_desc;
                $re['data']['out_trade_no'] = $DepositOrder->out_trade_no;
                $re['data']['amount'] = $DepositOrder->amount;

                //冻结成功
                if ($re['status'] == 1) {
                    $pay_time = date('Y-m-d H:i:s', strtotime($re['data']['gmt_trans']));

                    //如果数据库是冻结中改变状态
                    if ($DepositOrder->deposit_status == 2) {
                        DepositOrder::where('out_order_no', $DepositOrder->out_order_no)->update(
                            [
                                'deposit_time' => $re['data']['gmt_trans'],
                                'deposit_status_desc' => '押金冻结成功',
                                'deposit_status' => 1,
                                'payer_user_id' => $re['data']['payer_user_id'],
                                'payer_logon_id' => $re['data']['payer_logon_id'],
                            ]);
                    }


                    //支付成功后的动作
                    $data = [
                        'ways_type' => 'alipay',
                        'ways_type_desc' => '支付宝',
                        'source_type' => '1000',//返佣来源
                        'source_desc' => '支付宝',//返佣来源说明
                        'total_amount' => $DepositOrder->amount,
                        'out_trade_no' => $DepositOrder->out_trade_no,
                        'rate' => $DepositOrder->rate,
                        'merchant_id' => $DepositOrder->merchant_id,
                        'store_id' => $store_id,
                        'user_id' => $DepositOrder->user_id,
                        'config_id' => $config_id,
                        'store_name' => $store_name,
                        'ways_source' => $DepositOrder->ways_source,
                        'pay_time' => $pay_time,
                        'print_type' => '1',//1 押金预授权 2 押金消费，3押金退款

                    ];


                    DSuccessAction::action($data);


                    return json_encode([
                        'status' => 1,
                        'deposit_status' => '1',
                        'message' => $re['message'],
                        'data' => $re['data']
                    ]);

                } elseif ($re['status'] == 2) {
                    //等待用户输入密码
                    return json_encode([
                        'status' => 1,
                        'deposit_status' => '2',
                        'message' => $re['message'],
                        'data' => $re['data']
                    ]);
                } else {
                    return json_encode([
                        'status' => 1,
                        'deposit_status' => '3',
                        'message' => $re['message'],
                    ]);

                }

            }


            //官方微信

            if ($DepositOrder->ways_company == "weixin") {
                //支付宝预授权
                $obj = new WxDepositController();
                $config = new WeixinConfigController();
                $options = $config->weixin_config($config_id);
                $weixin_store = $config->weixin_merchant($store_id, $store_pid);
                $sub_merchant_id = $weixin_store->wx_sub_merchant_id;
                $wx_sub_app_id = $weixin_store->wx_sub_app_id;
                //查询
                $data = [
                    'app_id' => $options['app_id'],
                    'key' => $options['payment']['key'],
                    'sub_app_id' => $wx_sub_app_id,
                    'mch_id' => $options['payment']['merchant_id'],
                    'sub_mch_id' => $sub_merchant_id,
                    'out_order_no' => $DepositOrder->out_order_no,
                ];

                $re = $obj->base_fund_order_query($data);
                //支付宝扫码预授权查询  0 系统错 1成功 2 等待用户确认 3失败


                //0 系统错  3失败
                if ($re['status'] == 0 || $re['status'] == 3) {
                    return json_encode([
                        'status' => 2,
                        'deposit_status' => '3',
                        'message' => $re['message']
                    ]);
                }

                $re['data']['store_id'] = $store_id;
                $re['data']['ways_source'] = $DepositOrder->ways_source;
                $re['data']['ways_source_desc'] = $DepositOrder->ways_source_desc;
                $re['data']['out_trade_no'] = $DepositOrder->out_trade_no;

                //冻结成功
                if ($re['status'] == 1) {
                    $pay_time = date('Y-m-d H:i:s', strtotime($re['data']['gmt_trans']));

                    //如果数据库是冻结中改变状态
                    if ($DepositOrder->deposit_status == 2) {

                        $insert_data = [
                            'deposit_time' => '' . "" . $re['data']['gmt_trans'] . "" . '',
                            'deposit_status_desc' => '押金冻结成功',
                            'deposit_status' => 1,
                            'payer_user_id' => $re['data']['openid'],
                            'payer_logon_id' => $re['data']['sub_openid'],
                            'trade_no' => $re['data']['transaction_id'],
                        ];

                        DepositOrder::where('out_order_no', $DepositOrder->out_order_no)->update($insert_data);
                    }

                    //支付成功后的动作
                    $data = [
                        'ways_type' => 'weixin',
                        'ways_type_desc' => '微信支付',
                        'source_type' => '2000',//返佣来源
                        'source_desc' => '微信支付',//返佣来源说明
                        'total_amount' => $DepositOrder->amount,
                        'out_trade_no' => $DepositOrder->out_trade_no,
                        'rate' => $DepositOrder->rate,
                        'merchant_id' => $DepositOrder->merchant_id,
                        'store_id' => $store_id,
                        'user_id' => $DepositOrder->user_id,
                        'config_id' => $config_id,
                        'store_name' => $store_name,
                        'ways_source' => $DepositOrder->ways_source,
                        'pay_time' => $pay_time,
                        'print_type' => '1',//1 押金预授权 2 押金消费，3押金退款

                    ];


                    DSuccessAction::action($data);

                    return json_encode([
                        'status' => 1,
                        'deposit_status' => '1',
                        'message' => $re['message'],
                        'data' => [
                            'store_id' => $store_id,
                            'ways_source' => $DepositOrder->ways_source,
                            'ways_source_desc' => $DepositOrder->ways_source_desc,
                            "amount" => $DepositOrder->amount,
                            "auth_no" => "",
                            "operation_id" => "",
                            "out_order_no" => $DepositOrder->out_order_no,
                            "out_trade_no" => $DepositOrder->out_trade_no,
                            "out_request_no" => "",
                            "gmt_trans" => $re['data']['gmt_trans'],
                            "payer_user_id" => $re['data']['openid'],
                            "payer_logon_id" => $re['data']['sub_openid'],
                        ]
                    ]);

                } elseif ($re['status'] == 2) {
                    //等待用户输入密码
                    return json_encode([
                        'status' => 1,
                        'deposit_status' => '2',
                        'message' => $re['message'],
                        'data' => [
                            'store_id' => $store_id,
                            'ways_source' => $DepositOrder->ways_source,
                            'ways_source_desc' => $DepositOrder->ways_source_desc,
                            "amount" => $DepositOrder->amount,
                            "auth_no" => "",
                            "operation_id" => "",
                            "out_order_no" => $DepositOrder->out_order_no,
                            "out_trade_no" => $DepositOrder->out_trade_no,
                            "out_request_no" => "",
                        ]
                    ]);
                } else {
                    return json_encode([
                        'status' => 1,
                        'deposit_status' => '3',
                        'message' => $re['message'],
                    ]);

                }

            }


        } catch (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'deposit_status' => '3',
                'message' => $exception->getMessage()
            ]);
        }

    }


    //预授权撤销
    public function fund_cancel(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $out_order_no = $request->get('out_order_no');
            $out_trade_no = $request->get('out_trade_no');


            $check_data = [
                'store_id' => '门店ID',
            ];
            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'cancel_status' => '2',
                    'message' => $check
                ]);
            }


            $where = [];
            if (isset($out_order_no)) {
                $where[] = ['out_order_no', '=', $out_order_no];
            }
            if (isset($out_trade_no)) {
                $where[] = ['out_trade_no', '=', $out_trade_no];
            }

            $DepositOrder = DepositOrder::where($where)
                ->select(
                    'out_order_no', 'out_trade_no', 'ways_company', 'out_trade_no', 'operation_id', 'auth_no', 'out_request_no',
                    'deposit_status'
                )
                ->first();
            if (!$DepositOrder) {
                return json_encode([
                    'status' => 2,
                    'cancel_status' => '2',
                    'message' => '订单号不存在'
                ]);
            }

            $store = Store::where('store_id', $store_id)
                ->select('id', 'config_id', 'user_id', 'pid')
                ->first();


            if (!$store) {
                return json_encode([
                    'status' => 2,
                    'cancel_status' => '2',
                    'message' => '门店不存在'
                ]);
            }

            $store_pid = $store->pid;
            $config_id = $store->config_id;
            //官方支付宝
            if ($DepositOrder->ways_company == "alipay") {

                //支付宝预授权
                $obj = new AliDepositController();

                $isvconfig = new AlipayIsvConfigController();
                $config = $isvconfig->AlipayIsvConfig($config_id);
                $merchanr_info = $isvconfig->alipay_auth_info($store_id, $store_pid);


                //查询
                $data = [
                    'app_id' => $config->app_id,
                    'rsa_private_key' => $config->rsa_private_key,
                    'alipay_rsa_public_key' => $config->alipay_rsa_public_key,
                    'alipay_gateway' => $config->alipay_gateway,
                    'notify_url' => '',
                    'app_auth_token' => $merchanr_info->app_auth_token,
                    'out_order_no' => $DepositOrder->out_order_no,
                    'operation_id' => $DepositOrder->operation_id,
                    'auth_no' => $DepositOrder->auth_no,
                    'out_request_no' => $DepositOrder->out_request_no,
                    'remark' => '预授权撤销'


                ];
                $re = $obj->base_fund_cancel($data);


                // 0 系统异常 1 成功 3 失败


                //0 系统错  3失败
                if ($re['status'] == 0 || $re['status'] == 3) {
                    return json_encode([
                        'status' => 2,
                        'cancel_status' => '2',
                        'message' => $re['message']
                    ]);
                }

                $re['data']['out_trade_no'] = $DepositOrder->out_trade_no;
                $re['data']['out_order_no'] = $DepositOrder->out_order_no;
                $re['data']['store_id'] = $store_id;

                //订单已撤销成功
                if ($re['status'] == 1) {
                    $re['data']['store_id'] = $store_id;
                    DepositOrder::where('out_order_no', $out_order_no)->update(
                        [
                            'deposit_status_desc' => '订单已撤销成功',
                            'deposit_status' => 4,
                        ]);


                    return json_encode([
                        'status' => 1,
                        'cancel_status' => '1',
                        'message' => $re['message'],
                        'data' => $re['data']
                    ]);

                } else {
                    return json_encode([
                        'status' => 2,
                        'cancel_status' => '2',
                        'message' => $re['message'],
                    ]);

                }

            }


            //官方微信支付
            if ($DepositOrder->ways_company == "weixin") {
                //官方微信支付预授权撤销
                $obj = new WxDepositController();
                $config = new WeixinConfigController();
                $options = $config->weixin_config($config_id);
                $weixin_store = $config->weixin_merchant($store_id, $store_pid);

                $sub_merchant_id = $weixin_store->wx_sub_merchant_id;
                $wx_sub_app_id = $weixin_store->wx_sub_app_id;

                //查询
                $data = [
                    'app_id' => $options['app_id'],
                    'key' => $options['payment']['key'],
                    'sub_app_id' => $wx_sub_app_id,
                    'mch_id' => $options['payment']['merchant_id'],
                    'sub_mch_id' => $sub_merchant_id,
                    'out_order_no' => $out_order_no,
                    'cert_path' => $options['payment']['cert_path'],
                    'key_path' => $options['payment']['key_path'],
                ];

                $re = $obj->base_fund_cancel($data);


                // 0 系统异常 1 成功 3 失败


                //0 系统错  3失败
                if ($re['status'] == 0 || $re['status'] == 3) {
                    return json_encode([
                        'status' => 2,
                        'cancel_status' => '2',
                        'message' => $re['message']
                    ]);
                }

                //订单已撤销成功
                if ($re['status'] == 1) {

                    DepositOrder::where('out_order_no', $out_order_no)->update(
                        [
                            'deposit_status_desc' => '订单已撤销成功',
                            'deposit_status' => 4,
                        ]);


                    return json_encode([
                        'status' => 1,
                        'cancel_status' => '1',
                        'message' => $re['message'],
                        'data' => [
                            "store_id" => $store_id,
                            "auth_no" => "",
                            "operation_id" => "",
                            "out_order_no" => $DepositOrder->out_order_no,
                            "out_trade_no" => $DepositOrder->out_trade_no,
                            "out_request_no" => "",
                            "remark" => "押金支付撤销",
                            "action" => "unfreeze"
                        ]
                    ]);

                } else {
                    return json_encode([
                        'status' => 2,
                        'cancel_status' => '2',
                        'message' => $re['message'],
                    ]);

                }

            }


        } catch (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'cancel_status' => '2',
                'message' => $exception->getMessage(),
            ]);
        }

    }

    //解冻转支付
    public function fund_pay(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $out_order_no = $request->get('out_order_no');
            $pay_amount = $request->get('pay_amount', '');
            $shop_name = $request->get('shop_name', '押金转消费');

            $out_trade_no = $request->get('out_trade_no');


            $where = [];
            if (isset($out_order_no)) {
                $where[] = ['out_order_no', '=', $out_order_no];
            }
            if (isset($out_trade_no)) {
                $where[] = ['out_trade_no', '=', $out_trade_no];
            }

            $DepositOrder = DepositOrder::where($where)
                ->select(
                    'out_order_no', 'merchant_id', 'user_id', 'rate', 'trade_no', 'seller_id', 'ways_source', 'ways_source_desc', 'payer_user_id', 'out_trade_no', 'ways_company', 'operation_id', 'auth_no', 'out_request_no',
                    'deposit_status', 'amount'
                )
                ->first();
            if (!$DepositOrder) {
                return json_encode([
                    'status' => 2,
                    'pay_status' => '3',
                    'message' => '订单号不存在'
                ]);
            }

            $store = Store::where('store_id', $store_id)
                ->select('id', 'store_short_name', 'config_id', 'user_id', 'pid')
                ->first();


            if (!$store) {
                return json_encode([
                    'status' => 2,
                    'pay_status' => '3',
                    'message' => '门店不存在'
                ]);
            }

            $store_pid = $store->pid;
            $store_name = $store->store_short_name;
            $config_id = $store->config_id;
            //官方支付宝
            if ($DepositOrder->ways_company == "alipay") {

                //支付宝预授权
                $obj = new AliDepositController();

                $isvconfig = new AlipayIsvConfigController();
                $config = $isvconfig->AlipayIsvConfig($config_id);
                $merchanr_info = $isvconfig->alipay_auth_info($store_id, $store_pid);


                //押金转支付

                $data = [
                    'app_id' => $config->app_id,
                    'rsa_private_key' => $config->rsa_private_key,
                    'alipay_rsa_public_key' => $config->alipay_rsa_public_key,
                    'alipay_gateway' => $config->alipay_gateway,
                    'notify_url' => '',
                    'app_auth_token' => $merchanr_info->app_auth_token,
                    'out_trade_no' => $DepositOrder->out_trade_no,
                    'trade_no' => $DepositOrder->trade_no,
                    'total_amount' => $DepositOrder->amount,
                    'pay_amount' => $pay_amount,
                    'auth_no' => $DepositOrder->auth_no,
                    'buyer_id' => $DepositOrder->payer_user_id,
                    'seller_id' => $DepositOrder->seller_id,
                    'subject' => $shop_name
                ];

                $re = $obj->base_fund_pay($data);


                if ($re['status'] == 0 || $re['status'] == 3) {
                    return json_encode([
                        'status' => 2,
                        'pay_status' => '3',
                        'message' => $re['message']
                    ]);
                }
                $re['data']['ways_source'] = $DepositOrder->ways_source;
                $re['data']['ways_source_desc'] = $DepositOrder->ways_source_desc;
                $re['data']['store_id'] = $store_id;
                $re['data']['out_trade_no'] = $DepositOrder->out_trade_no;
                $re['data']['out_order_no'] = $DepositOrder->out_order_no;

                //解冻支付成功
                if ($re['status'] == 1) {
                    $pay_time = date('Y-m-d H:i:s', strtotime($re['data']['pay_time']));

                    DepositOrder::where('out_order_no', $out_order_no)->update(
                        [
                            'pay_amount' => $pay_amount,
                            'pay_time' => $re['data']['pay_time'],
                            'pay_status_desc' => '押金消费成功',
                            'trade_no' => $re['data']['trade_no'],
                            'pay_status' => 1,
                        ]);

                    //支付成功后的动作
                    $data = [
                        'ways_type' => 'alipay',
                        'ways_type_desc' => '支付宝',
                        'source_type' => '1000',//返佣来源
                        'source_desc' => '支付宝',//返佣来源说明
                        'total_amount' => $DepositOrder->amount,
                        'out_trade_no' => $DepositOrder->out_trade_no,
                        'rate' => $DepositOrder->rate,
                        'merchant_id' => $DepositOrder->merchant_id,
                        'pay_amount' => $pay_amount,
                        'store_id' => $store_id,
                        'user_id' => $DepositOrder->user_id,
                        'config_id' => $config_id,
                        'store_name' => $store_name,
                        'ways_source' => $DepositOrder->ways_source,
                        'pay_time' => $pay_time,
                        'print_type' => '2',//1 押金预授权 2 押金消费，3押金退款

                    ];


                    DSuccessAction::action($data);

                    return json_encode([
                        'status' => 1,
                        'pay_status' => '1',
                        'message' => $re['message'],
                        'data' => $re['data']
                    ]);

                }


                //冻结确认输入密码中
                if ($re['status'] == 2) {

                    return json_encode([
                        'status' => 1,
                        'pay_status' => '2',
                        'message' => $re['message'],
                        'data' => $re['data']
                    ]);
                }


            }


            //官方微信支付
            if ($DepositOrder->ways_company == "weixin") {

                //支付宝预授权
                $config = new WeixinConfigController();
                $options = $config->weixin_config($config_id);
                $weixin_store = $config->weixin_merchant($store_id, $store_pid);


                $sub_merchant_id = $weixin_store->wx_sub_merchant_id;
                $wx_sub_app_id = $weixin_store->wx_sub_app_id;


                //预授权转支付
                $data = [
                    'app_id' => $options['app_id'],
                    'key' => $options['payment']['key'],
                    'sub_app_id' => $wx_sub_app_id,
                    'mch_id' => $options['payment']['merchant_id'],
                    'sub_mch_id' => $sub_merchant_id,
                    'out_order_no' => $out_order_no,
                    'trade_no' => $DepositOrder->trade_no,
                    'total_fee' => $DepositOrder->amount,
                    'consume_fee' => $pay_amount,
                    'cert_path' => $options['payment']['cert_path'],
                    'key_path' => $options['payment']['key_path'],
                ];

                $obj = new WxDepositController();
                $re = $obj->base_fund_pay($data);


                if ($re['status'] == 0 || $re['status'] == 3) {
                    return json_encode([
                        'status' => 2,
                        'pay_status' => '3',
                        'message' => $re['message']
                    ]);
                }

                //解冻支付成功
                if ($re['status'] == 1) {
                    $pay_time = date('Y-m-d H:i:s', time());

                    DepositOrder::where('out_order_no', $out_order_no)->update(
                        [
                            'pay_amount' => $pay_amount,
                            'pay_time' => "" . date('Y-m-d H:i:s', time()) . "",
                            'pay_status_desc' => '押金消费成功',
                            'trade_no' => $re['data']['transaction_id'],
                            'pay_status' => 1,
                        ]);


                    //支付成功后的动作
                    $data = [
                        'ways_type' => 'weixin',
                        'ways_type_desc' => '微信支付',
                        'source_type' => '2000',//返佣来源
                        'source_desc' => '微信支付',//返佣来源说明
                        'total_amount' => $DepositOrder->amount,
                        'pay_amount' => $pay_amount,
                        'out_trade_no' => $DepositOrder->out_trade_no,
                        'rate' => $DepositOrder->rate,
                        'merchant_id' => $DepositOrder->merchant_id,
                        'store_id' => $store_id,
                        'user_id' => $DepositOrder->user_id,
                        'config_id' => $config_id,
                        'store_name' => $store_name,
                        'ways_source' => $DepositOrder->ways_source,
                        'pay_time' => $pay_time,
                        'print_type' => '2',//1 押金预授权 2 押金消费，3押金退款
                    ];


                    DSuccessAction::action($data);


                    $return_data = [
                        'status' => 1,
                        'pay_status' => '1',
                        'message' => $re['message'],
                        'data' => [
                            "out_trade_no" => $DepositOrder->out_trade_no,
                            "out_order_no" => $DepositOrder->out_order_no,
                            "trade_no" => $re['data']['transaction_id'],
                            "total_amount" => $pay_amount,
                            "invoice_amount" => $pay_amount,
                            "pay_time" => "" . date('Y-m-d H:i:s', time()) . "",
                            "buyer_user_id" => "",
                            "buyer_logon_id" => "",
                            "payment_method" => "",
                            "ways_source" => $DepositOrder->ways_source,
                            "ways_source_desc" => $DepositOrder->ways_source_desc,
                            "store_id" => $store_id,
                        ]
                    ];


                    return json_encode($return_data);

                }


                //冻结确认输入密码中
                if ($re['status'] == 2) {

                    return json_encode([
                        'status' => 1,
                        'pay_status' => '2',
                        'message' => $re['message'],
                        'data' => [
                            "store_id" => $store_id,
                            "out_trade_no" => $DepositOrder->out_trade_no,
                            "out_order_no" => $DepositOrder->out_order_no,
                            "total_amount" => $pay_amount,
                        ]
                    ]);
                }


            }


        } catch (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'pay_status' => '3',
                'message' => $exception->getMessage(),
            ]);
        }

    }


    //支付查询
    public function pay_order_query(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $out_trade_no = $request->get('out_trade_no');
            $out_order_no = $request->get('out_order_no');


            $where = [];
            if (isset($out_order_no)) {
                $where[] = ['out_order_no', '=', $out_order_no];
            }
            if (isset($out_trade_no)) {
                $where[] = ['out_trade_no', '=', $out_trade_no];
            }

            $DepositOrder = DepositOrder::where($where)
                ->select('ways_company', 'rate', 'user_id', 'merchant_id', 'out_trade_no', 'ways_source', 'ways_source_desc', 'out_order_no', 'operation_id', 'auth_no', 'out_request_no',
                    'deposit_status'
                )
                ->first();
            if (!$DepositOrder) {
                return json_encode([
                    'status' => 2,
                    'pay_status' => '3',
                    'message' => '订单号不存在'
                ]);
            }

            $store = Store::where('store_id', $store_id)
                ->select('id', 'store_short_name', 'config_id', 'user_id', 'pid')
                ->first();


            if (!$store) {
                return json_encode([
                    'status' => 2,
                    'pay_status' => '3',
                    'message' => '门店不存在'
                ]);
            }

            $store_pid = $store->pid;
            $config_id = $store->config_id;
            $store_name = $store->store_short_name;

            //官方支付宝
            if ($DepositOrder->ways_company == "alipay") {

                //支付宝预授权
                $obj = new AliDepositController();

                $isvconfig = new AlipayIsvConfigController();
                $config = $isvconfig->AlipayIsvConfig($config_id);
                $merchanr_info = $isvconfig->alipay_auth_info($store_id, $store_pid);


                //支付查询
                $data = [
                    'app_id' => $config->app_id,
                    'rsa_private_key' => $config->rsa_private_key,
                    'alipay_rsa_public_key' => $config->alipay_rsa_public_key,
                    'alipay_gateway' => $config->alipay_gateway,
                    'notify_url' => '',
                    'app_auth_token' => $merchanr_info->app_auth_token,
                    'out_trade_no' => $DepositOrder->out_trade_no,

                ];

                $re = $obj->base_fund_pay_query($data);


                // 0 系统错 1成功 2 等待用户确认 3失败


                //0 系统错  3失败
                if ($re['status'] == 0 || $re['status'] == 3) {
                    return json_encode([
                        'status' => 2,
                        'pay_status' => '3',
                        'message' => $re['message']
                    ]);
                }

                $re['data']['ways_source'] = $DepositOrder->ways_source;
                $re['data']['ways_source_desc'] = $DepositOrder->ways_source_desc;

                //支付成功
                if ($re['status'] == 1) {
                    $pay_time = date('Y-m-d H:i:s', strtotime($re['data']['pay_time']));

                    //如果数据库是冻结中改变状态
                    if ($DepositOrder->pay_status == 2) {
                        DepositOrder::where('out_trade_no', $out_trade_no)->update(
                            [
                                'pay_time' => "" . $re['data']['pay_time'] . "",
                                'pay_status_desc' => '支付成功',
                                'pay_status' => 1,
                                'trade_no' => $re['data']['trade_no'],
                            ]);
                    }

                    //支付成功后的动作
                    $data = [
                        'ways_type' => 'alipay',
                        'ways_type_desc' => '支付宝',
                        'source_type' => '1000',//返佣来源
                        'source_desc' => '支付宝',//返佣来源说明
                        'total_amount' => $DepositOrder->amount,
                        'out_trade_no' => $DepositOrder->out_trade_no,
                        'rate' => $DepositOrder->rate,
                        'merchant_id' => $DepositOrder->merchant_id,
                        'pay_amount' => $DepositOrder->pay_amount,
                        'store_id' => $store_id,
                        'user_id' => $DepositOrder->user_id,
                        'config_id' => $config_id,
                        'store_name' => $store_name,
                        'ways_source' => $DepositOrder->ways_source,
                        'pay_time' => $pay_time,
                        'print_type' => '2',//1 押金预授权 2 押金消费，3押金退款
                    ];


                    DSuccessAction::action($data);

                    return json_encode([
                        'status' => 1,
                        'pay_status' => '1',
                        'message' => $re['message'],
                        'data' => $re['data']
                    ]);

                } elseif ($re['status'] == 2) {
                    //等待用户输入密码
                    return json_encode([
                        'status' => 1,
                        'pay_status' => '2',
                        'message' => $re['message'],
                        'data' => $re['data']
                    ]);
                } else {
                    return json_encode([
                        'status' => 1,
                        'pay_status' => '3',
                        'message' => $re['message'],
                    ]);
                }


            }

            //官方微信
            if ($DepositOrder->ways_company == "weixin") {
                //支付宝预授权
                $obj = new WxDepositController();
                $config = new WeixinConfigController();
                $options = $config->weixin_config($config_id);
                $weixin_store = $config->weixin_merchant($store_id, $store_pid);
                $sub_merchant_id = $weixin_store->wx_sub_merchant_id;
                $wx_sub_app_id = $weixin_store->wx_sub_app_id;
                //查询
                $data = [
                    'app_id' => $options['app_id'],
                    'key' => $options['payment']['key'],
                    'sub_app_id' => $wx_sub_app_id,
                    'mch_id' => $options['payment']['merchant_id'],
                    'sub_mch_id' => $sub_merchant_id,
                    'out_order_no' => $out_order_no,
                ];


                $re = $obj->base_fund_pay_query($data);

                // 0 系统错 1成功 2 等待用户确认 3失败


                //0 系统错  3失败
                if ($re['status'] == 0 || $re['status'] == 3) {
                    return json_encode([
                        'status' => 2,
                        'pay_status' => '3',
                        'message' => $re['message']
                    ]);
                }

                $re['data']['ways_source'] = $DepositOrder->ways_source;
                $re['data']['ways_source_desc'] = $DepositOrder->ways_source_desc;

                //支付成功
                if ($re['status'] == 1) {
                    $pay_time = date('Y-m-d H:i:s', strtotime($re['data']['pay_time']));

                    //如果数据库是冻结中改变状态
                    if ($DepositOrder->pay_status == 2) {
                        DepositOrder::where('out_trade_no', $out_trade_no)->update(
                            [
                                'pay_time' => "" . $re['data']['pay_time'] . "",
                                'pay_status_desc' => '支付成功',
                                'trade_no' => $re['data']['transaction_id'],
                                'pay_status' => 1,
                            ]);
                    }


                    //支付成功后的动作
                    $data = [
                        'ways_type' => 'weixin',
                        'ways_type_desc' => '微信支付',
                        'source_type' => '2000',//返佣来源
                        'source_desc' => '微信支付',//返佣来源说明
                        'total_amount' => $DepositOrder->amount,
                        'pay_amount' => $DepositOrder->pay_amount,
                        'out_trade_no' => $DepositOrder->out_trade_no,
                        'rate' => $DepositOrder->rate,
                        'merchant_id' => $DepositOrder->merchant_id,
                        'store_id' => $store_id,
                        'user_id' => $DepositOrder->user_id,
                        'config_id' => $config_id,
                        'store_name' => $store_name,
                        'ways_source' => $DepositOrder->ways_source,
                        'pay_time' => $pay_time,
                        'print_type' => '2',//1 押金预授权 2 押金消费，3押金退款
                    ];


                    DSuccessAction::action($data);


                    return json_encode([
                        'status' => 1,
                        'pay_status' => '1',
                        'message' => $re['message'],
                        'data' => [
                            'store_id' => $store_id,
                            'ways_source' => $DepositOrder->ways_source,
                            'ways_source_desc' => $DepositOrder->ways_source_desc,
                            'trade_no' => $re['data']['transaction_id'],
                            "amount" => $DepositOrder->amount,
                            "auth_no" => "",
                            "operation_id" => "",
                            "out_order_no" => $out_order_no,
                            "out_trade_no" => $out_trade_no,
                            "out_request_no" => "",
                            "gmt_trans" => $re['data']['gmt_trans'],
                            "payer_user_id" => $re['data']['openid'],
                            "payer_logon_id" => $re['data']['sub_openid'],
                        ]
                    ]);

                } elseif ($re['status'] == 2) {
                    //等待用户输入密码
                    return json_encode([
                        'status' => 1,
                        'pay_status' => '2',
                        'message' => $re['message'],
                        'data' => $re['data']
                    ]);
                } else {
                    return json_encode([
                        'status' => 1,
                        'pay_status' => '3',
                        'message' => $re['message'],
                    ]);
                }


            }

        } catch
        (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'deposit_status' => '3',
                'message' => $exception->getMessage()
            ]);
        }

    }


    //订单列表查询
    public function pay_order_list(Request $request)
    {

        try {
            $merchant = $this->parseToken();
            $out_trade_no = $request->get('out_trade_no');
            $merchant_id = $request->get('merchant_id', '');
            $order_status = $request->get('order_status', '');
            $ways_source = $request->get('ways_source', '');
            $ways_type = $request->get('ways_type', '');
            $time_start = $request->get('time_start', '');
            $time_end = $request->get('time_end', '');
            $trade_no = $request->get('trade_no');
            $user_id = $request->get('user_id', '');

            $store_id = $request->get('store_id', '');


            $obj = DB::table('deposit_orders');
            $where = [];
            $store_ids = [];
            $user_ids = [];

            $status = explode('-', $order_status);
            $deposit_status = isset($status[0]) ? $status[0] : '';
            $pay_status = isset($status[1]) ? $status[1] : '';

            if ($out_trade_no) {
                $where[] = ['out_trade_no', 'like', '%' . $out_trade_no . '%'];
            }

            if ($trade_no) {
                $where[] = ['trade_no', 'like', '%' . $trade_no . '%'];
            }

            //收银员
            if ($merchant->type == "merchant") {
                if (isset($merchant->merchant_type) && $merchant->merchant_type == 2) {
                    $where[] = ['merchant_id', '=', $merchant->merchant_id];
                }
            } else {
                if ($user_id) {
                    $user_ids = [$user_id];
                } else {
                    $user_ids = $this->getSubIds($merchant->user_id);
                }

            }

            //是否传收银员ID
            if ($merchant_id && $merchant_id != "NULL") {
                $where[] = ['merchant_id', '=', $merchant_id];
            }
            if ($ways_source) {
                $where[] = ['ways_source', '=', $ways_source];
            }
            if ($deposit_status) {
                $where[] = ['deposit_status', '=', $deposit_status];
            }
            if ($pay_status) {
                $where[] = ['pay_status', '=', $pay_status];
            }
            if ($ways_type) {
                $where[] = ['ways_type', '=', $ways_type];
            }
            if ($time_start) {
                $time_start = date('Y-m-d H:i:s', strtotime($time_start));
                $where[] = ['created_at', '>=', $time_start];
            }
            if ($time_end) {
                $time_end = date('Y-m-d H:i:s', strtotime($time_end));
                $where[] = ['created_at', '<=', $time_end];
            }

            //是否传代理商ID
            if ($user_id && $user_id != "NULL") {
                $where[] = ['user_id', '=', $user_id];
            }

            if ($store_id) {
                $where[] = ['store_id', '=', $store_id];
            }

            if ($merchant->type == "merchant") {

                //返回基础数据
                if ($store_id) {
                    $obj = $obj->where($where)
                        ->orderBy('updated_at', 'desc');

                } else {
                    $MerchantStore = MerchantStore::where('merchant_id', $merchant->merchant_id)
                        ->select('store_id')
                        ->get();
                    if (!$MerchantStore->isEmpty()) {
                        $store_ids = $MerchantStore->toArray();
                        $obj = $obj->where($where)
                            ->whereIn('store_id', $store_ids)
                            ->orderBy('updated_at', 'desc');

                    }
                }

            } else {
                //返回基础数据
                $obj = $obj->where($where)
                    ->whereIn('user_id', $user_ids)
                    ->orderBy('updated_at', 'desc');
            }


            $this->t = $obj->count();
            $data = $this->page($obj)->get();
            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);
        } catch
        (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'message' => $exception->getMessage()
            ]);
        }
    }


    //单个订单详细
    public function pay_order_info(Request $request)
    {

        try {
            $merchant = $this->parseToken();
            $out_trade_no = $request->get('out_trade_no');
            $out_order_no = $request->get('out_order_no');


            $where = [];
            if (isset($out_order_no)) {
                $where[] = ['out_order_no', '=', $out_order_no];
            }
            if (isset($out_trade_no)) {
                $where[] = ['out_trade_no', '=', $out_trade_no];
            }


            $obj = DepositOrder::where($where)->first();

            return json_encode([
                'status' => 1,
                'message' => '数据返回成功',
                'data' => $obj
            ]);

        } catch
        (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'message' => $exception->getMessage()
            ]);
        }
    }


    //获取订单类型
    public function ways_source(Request $request)
    {
        $data = [
            [
                'ways_source' => 'alipay',
                'ways_source_desc' => '支付宝',
            ], [
                'ways_source' => 'weixin',
                'ways_source_desc' => '微信支付',
            ],
        ];
        return json_encode($data);
    }


    //退款
    public function refund(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $refund_amount = $request->get('refund_amount', '');
            $out_trade_no = $request->get('out_trade_no', '');
            $out_order_no = $request->get('out_order_no', '');


            $where = [];
            if ($out_order_no) {
                $where[] = ['out_order_no', '=', $out_order_no];
            }
            if ($out_trade_no) {
                $where[] = ['out_trade_no', '=', $out_trade_no];
            }

            $check_data = [
                'store_id' => '门店ID',
                'refund_amount' => '退款金额',
            ];
            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'refund_status' => '3',
                    'message' => $check
                ]);
            }

            $DepositOrder = DepositOrder::where($where)
                ->select('id', 'trade_no', 'ways_company', 'pay_amount', 'amount', 'out_order_no', 'out_trade_no', 'pay_status')
                ->first();

            if (!$DepositOrder) {
                return json_encode([
                    'status' => 2,
                    'refund_status' => '3',
                    'message' => '订单号不存在'
                ]);
            }

            if ($DepositOrder->pay_status != 1) {
                return json_encode([
                    'status' => 2,
                    'refund_status' => '3',
                    'message' => '此订单状态不支持退款'
                ]);
            }

            $store = Store::where('store_id', $store_id)
                ->select('id', 'store_short_name', 'config_id', 'user_id', 'pid')
                ->first();


            if (!$store) {
                return json_encode([
                    'status' => 2,
                    'refund_status' => '3',
                    'message' => '门店不存在'
                ]);
            }
            $store_name = $store->store_short_name;
            $store_pid = $store->pid;
            $config_id = $store->config_id;
            $refund_no = $DepositOrder->out_trade_no . rand(1000, 9999);
            $order_amount = $DepositOrder->amount;

            $refund_data = [
                'out_trade_no' => $DepositOrder->out_trade_no,
                'store_id' => $store_id,
                'merchant_id' => $merchant->merchant_id,
                'refund_amount' => $refund_amount,//退款金额
                'refund_no' => $refund_no,//退款单号
                'order_amount' => $order_amount,//退款单号

            ];


            //官方支付宝
            if ($DepositOrder->ways_company == "alipay") {

                //支付宝预授权
                $obj = new AliDepositController();

                $isvconfig = new AlipayIsvConfigController();
                $config = $isvconfig->AlipayIsvConfig($config_id);
                $merchanr_info = $isvconfig->alipay_auth_info($store_id, $store_pid);


                //查询
                $data = [
                    'app_id' => $config->app_id,
                    'rsa_private_key' => $config->rsa_private_key,
                    'alipay_rsa_public_key' => $config->alipay_rsa_public_key,
                    'alipay_gateway' => $config->alipay_gateway,
                    'notify_url' => '',
                    'app_auth_token' => $merchanr_info->app_auth_token,
                    'out_trade_no' => $DepositOrder->out_trade_no,
                    'refund_amount' => $refund_amount,
                ];
                $re = $obj->refund($data);


                // 0 系统异常 1 成功 3 失败


                //0 系统错  3失败
                if ($re['status'] == 0 || $re['status'] == 3) {
                    return json_encode([
                        'status' => 2,
                        'refund_status' => '3',
                        'message' => $re['message']
                    ]);
                }


                //订单退款成功
                if ($re['status'] == 1) {

                    DepositOrder::where('out_trade_no', $DepositOrder->out_trade_no)->update(
                        [
                            'pay_status_desc' => '订单已退款',
                            'pay_status' => 4,
                            'refund_amount' => $refund_amount,
                            'refund_time' => date('Y-m-d H:i:s', time())
                        ]);

                    //退款入库
                    $refund_data['refund_status'] = 1;
                    DepositRefundOrder::create($refund_data);


                    //退款成功后的动作
                    $data = [
                        'ways_type' => 'alipay',
                        'ways_type_desc' => '支付宝',
                        'source_type' => '1000',//返佣来源
                        'source_desc' => '支付宝',//返佣来源说明
                        'total_amount' => $DepositOrder->amount,
                        'out_trade_no' => $DepositOrder->out_trade_no,
                        'rate' => $DepositOrder->rate,
                        'merchant_id' => $DepositOrder->merchant_id,
                        'pay_amount' => $DepositOrder->pay_amount,
                        'refund_amount' => $refund_amount,
                        'store_id' => $store_id,
                        'user_id' => $DepositOrder->user_id,
                        'config_id' => $config_id,
                        'store_name' => $store_name,
                        'ways_source' => $DepositOrder->ways_source,
                        'pay_time' => date('Y-m-d H:i:s', time()),
                        'print_type' => '3',//1 押金预授权 2 押金消费，3押金退款
                    ];
                    DSuccessAction::action($data);


                    return json_encode([
                        'status' => 1,
                        'refund_status' => '1',
                        'message' => $re['message'],
                        'data' => $re['data']
                    ]);

                } else {
                    return json_encode([
                        'status' => 2,
                        'refund_status' => '3',
                        'message' => $re['message'],
                    ]);

                }

            }

            //官方微信支付
            if ($DepositOrder->ways_company == "weixin") {

                //支付宝预授权
                $config = new WeixinConfigController();
                $options = $config->weixin_config($config_id);
                $weixin_store = $config->weixin_merchant($store_id, $store_pid);


                $sub_merchant_id = $weixin_store->wx_sub_merchant_id;
                $wx_sub_app_id = $weixin_store->wx_sub_app_id;


                //退款
                $data = [
                    'app_id' => $options['app_id'],
                    'key' => $options['payment']['key'],
                    'mch_id' => $options['payment']['merchant_id'],
                    'sub_mch_id' => $sub_merchant_id,
                    'trade_no' => $DepositOrder->trade_no,
                    'total_fee' => $DepositOrder->pay_amount,
                    'refund_fee' => $refund_amount,
                    'out_refund_no' => $refund_no,
                    'cert_path' => $options['payment']['cert_path'],
                    'key_path' => $options['payment']['key_path'],
                ];

                $obj = new WxDepositController();

                $re = $obj->refund($data);


                // 0 系统异常 1 成功 3 失败


                //0 系统错  3失败
                if ($re['status'] == 0 || $re['status'] == 3) {
                    return json_encode([
                        'status' => 2,
                        'refund_status' => '3',
                        'message' => $re['message']
                    ]);
                }


                //订单退款成功
                if ($re['status'] == 1) {

                    DepositOrder::where('out_trade_no', $DepositOrder->out_trade_no)->update(
                        [
                            'pay_status_desc' => '订单已退款',
                            'pay_status' => 4,
                            'refund_amount' => $refund_amount,
                            'refund_time' => date('Y-m-d H:i:s', time())
                        ]);

                    //退款入库
                    $refund_data['refund_status'] = 1;
                    DepositRefundOrder::create($refund_data);


                    //退款成功后的动作
                    $data = [
                        'ways_type' => 'weixin',
                        'ways_type_desc' => '微信支付',
                        'source_type' => '2000',//返佣来源
                        'source_desc' => '微信支付',//返佣来源说明
                        'total_amount' => $DepositOrder->amount,
                        'out_trade_no' => $DepositOrder->out_trade_no,
                        'rate' => $DepositOrder->rate,
                        'merchant_id' => $DepositOrder->merchant_id,
                        'pay_amount' => $DepositOrder->pay_amount,
                        'refund_amount' => $refund_amount,
                        'store_id' => $store_id,
                        'user_id' => $DepositOrder->user_id,
                        'config_id' => $config_id,
                        'store_name' => $store_name,
                        'ways_source' => $DepositOrder->ways_source,
                        'pay_time' => date('Y-m-d H:i:s', time()),
                        'print_type' => '3',//1 押金预授权 2 押金消费，3押金退款
                    ];
                    DSuccessAction::action($data);


                    return json_encode([
                        'status' => 1,
                        'refund_status' => '1',
                        'message' => $re['message'],
                        'data' => [
                            "out_trade_no" => $DepositOrder->out_trade_no,
                            "refund_amount" => $refund_amount,
                            "out_order_no" => $DepositOrder->out_order_no
                        ]
                    ]);

                } else {
                    return json_encode([
                        'status' => 2,
                        'refund_status' => '3',
                        'message' => $re['message'],
                    ]);

                }

            }


        } catch (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'refund_status' => '3',
                'message' => $exception->getMessage(),
            ]);
        }

    }


    public function print_tpl(Request $request)
    {

        try {
            $merchant = $this->parseToken();
            $out_trade_no = $request->get('out_trade_no', '');
            $store_id = $request->get('store_id', '');
            $order = DepositOrder::where('out_trade_no', $out_trade_no)
                ->where('store_id', $store_id)
                ->first();
            $merchant_name = "";
            $store_name = "";
            $ways_source_desc = "";
            $pay_status_desc = "";
            $pay_time = "";
            $remark = "";
            $amount = '';
            $pay_amount = '';
            $refund_amount = '';
            $deposit_time = '';

            if ($order) {
                $store_name = $order->store_name;
                $merchant_name = $order->merchant_name;
                $amount = $order->amount;
                $pay_amount = $order->pay_amount;
                $refund_amount = $order->refund_amount;
                $ways_source_desc = $order->ways_source_desc;
                $pay_status_desc = $order->deposit_status_desc . '-' . $order->pay_status_desc;
                $pay_time = $order->pay_time;
                $deposit_time = $order->deposit_time;

                $remark = $order->remark;
            }
            $data = "商户名称：" . $store_name .
                "\r\n收银员：" . $merchant_name .
                "\r\n订单号：" . $out_trade_no .
                "\r\n押金金额：" . $amount .
                "\r\n支付金额：" . $pay_amount .
                "\r\n退款金额：" . $refund_amount .
                "\r\n支付方式：" . $ways_source_desc .
                "\r\n订单状态：" . $pay_status_desc . "" .
                "\r\n押金时间：" . $deposit_time . "" .
                "\r\n支付时间：" . $pay_time . "" .
                "\r\n\r\n用户备注：" . $remark . "" .
                "\r\n-----------------------------\r\n";


            return json_encode([
                'status' => 1,
                'data' => $data,
            ]);


        } catch (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'msg' => $exception->getMessage() . $exception->getLine(),
            ]);
        }

    }


    //押金统计查询
    public function pay_order_count(Request $request)
    {

        try {
            $merchant = $this->parseToken();
            $out_trade_no = $request->get('out_trade_no');
            $merchant_id = $request->get('merchant_id', '');
            $order_status = $request->get('order_status', '');
            $ways_source = $request->get('ways_source', '');
            $ways_type = $request->get('ways_type', '');
            $time_start = $request->get('time_start', '');
            $time_end = $request->get('time_end', '');
            $trade_no = $request->get('trade_no');
            $user_id = $request->get('user_id', '');
            $store_id = $request->get('store_id', '');

            $obj = DB::table('deposit_orders');
            $where = [];
            $store_ids = [];


            $status = explode('-', $order_status);
            $deposit_status = isset($status[0]) ? $status[0] : '';
            $pay_status = isset($status[1]) ? $status[1] : '';

            if ($out_trade_no) {
                $where[] = ['out_trade_no', 'like', '%' . $out_trade_no . '%'];
            }

            if ($trade_no) {
                $where[] = ['trade_no', 'like', '%' . $trade_no . '%'];
            }

            //收银员
            if ($merchant->type == "merchant") {
                if (isset($merchant->merchant_type) && $merchant->merchant_type == 2) {
                    $where[] = ['merchant_id', '=', $merchant->merchant_id];
                }
            } else {
                if ($user_id) {
                    $user_ids = [$user_id];
                } else {
                    $user_ids = $this->getSubIds($merchant->user_id);
                }

            }

            //是否传收银员ID
            if ($merchant_id && $merchant_id != "NULL") {
                $where[] = ['merchant_id', '=', $merchant_id];
            }
            if ($ways_source) {
                $where[] = ['ways_source', '=', $ways_source];
            }
            if ($deposit_status) {
                $where[] = ['deposit_status', '=', $deposit_status];
            }
            if ($pay_status) {
                $where[] = ['pay_status', '=', $pay_status];
            }
            if ($ways_type) {
                $where[] = ['ways_type', '=', $ways_type];
            }
            if ($time_start) {
                $time_start = date('Y-m-d H:i:s', strtotime($time_start));
                $where[] = ['created_at', '>=', $time_start];
            }
            if ($time_end) {
                $time_end = date('Y-m-d H:i:s', strtotime($time_end));
                $where[] = ['created_at', '<=', $time_end];
            }

            //是否传代理商ID
            if ($user_id && $user_id != "NULL") {
                $where[] = ['user_id', '=', $user_id];
            }

            if ($store_id && $store_id != "NULL") {
                $where[] = ['store_id', '=', $store_id];
            }

            if ($merchant->type == "merchant") {

                //返回基础数据
                if ($store_id) {
                    $order_data = DepositOrder::where($where)
                        ->whereIn('pay_status', [1, 4])//成功+
                        ->where('deposit_status', 1)
                        ->select(
                            'amount',
                            'pay_amount',
                            'refund_amount'
                        );

                } else {
                    $MerchantStore = MerchantStore::where('merchant_id', $merchant->merchant_id)
                        ->select('store_id')
                        ->get();
                    if (!$MerchantStore->isEmpty()) {
                        $store_ids = $MerchantStore->toArray();
                        $order_data = DepositOrder::where($where)
                            ->whereIn('pay_status', [1, 4])//成功+
                            ->whereIn('store_id', $store_ids)//成功+
                            ->where('deposit_status', 1)
                            ->select(
                                'amount',
                                'pay_amount',
                                'refund_amount'
                            );
                    }
                }

            } else {
                //返回基础数据
                $order_data = DepositOrder::where($where)
                    ->whereIn('pay_status', [1, 4])//成功+
                    ->whereIn('user_id', $user_ids)
                    ->where('deposit_status', 1)
                    ->select(
                        'amount',
                        'pay_amount',
                        'refund_amount'
                    );
            }


            $amount = $order_data->sum('amount');//交易金额
            $pay_amount = $order_data->sum('pay_amount');//交易金额
            $refund_amount = $order_data->sum('refund_amount');//交易金额


            $data = [
                'deposit_all_amount' => $amount,
                'deposit_ing_amount' => '0.00',
                'deposit_pay_amount' => $pay_amount,
                'deposit_refund_amount' => $refund_amount,
            ];

            return json_encode([
                'status' => 1,
                'message' => '数据返回成功',
                'data' => $data,
            ]);

        } catch
        (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'message' => $exception->getMessage()
            ]);
        }
    }


}