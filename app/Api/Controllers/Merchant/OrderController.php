<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/6/20
 * Time: 下午3:18
 */

namespace App\Api\Controllers\Merchant;


use Alipayopen\Sdk\AopClient;
use Alipayopen\Sdk\Request\AlipayTradeQueryRequest;
use Alipayopen\Sdk\Request\AlipayTradeRefundRequest;
use App\Api\Controllers\BaseController;
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
use App\Models\AlipayHbOrder;
use App\Models\AlipayIsvConfig;
use App\Models\MerchantFuwu;
use App\Models\MerchantStore;
use App\Models\MerchantStoreDayOrder;
use App\Models\MerchantStoreMonthOrder;
use App\Models\MerchantWalletDetail;
use App\Models\MyBankConfig;
use App\Models\MyBankStore;
use App\Models\Order;
use App\Models\RefundOrder;
use App\Models\Store;
use App\Models\StoreDayOrder;
use App\Models\StoreMonthOrder;
use App\Models\UserWalletDetail;
use EasyWeChat\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends BaseController
{

    public function order(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $merchant_id = $request->get('merchant_id', '');
            $pay_status = $request->get('pay_status', '');
            $ways_source = $request->get('ways_source', '');
            $ways_type = $request->get('ways_type', '');
            $time_start = $request->get('time_start', '');
            $time_end = $request->get('time_end', '');
            $out_trade_no = $request->get('out_trade_no', '');
            $trade_no = $request->get('trade_no', '');
            $return_type = $request->get('return_type', '1');

            $sort = $request->get('sort', '');
            $obj = DB::table('orders');
            $where = [];
            $store_ids = [];
            if ($out_trade_no) {
                $where[] = ['out_trade_no', 'like', '%' . $out_trade_no . '%'];
            }

            if ($trade_no) {
                $where[] = ['trade_no', 'like', '%' . $trade_no . '%'];
            }
            //收银员
            if ($merchant->merchant_type == 2) {
                $where[] = ['merchant_id', '=', $merchant->merchant_id];
            }

            //是否传收银员ID
            if ($merchant_id && $merchant_id != "NULL") {
                $where[] = ['merchant_id', '=', $merchant_id];
            }
            if (1) {
                $where[] = ['ways_type', '!=', '2005'];
            }
            if ($pay_status) {
                $where[] = ['pay_status', '=', $pay_status];
            } else {
                $where[] = ['pay_status', '!=', 2];
            }


            if ($store_id && $store_id != "NULL") {
                $store_ids = [
                    [
                        'store_id' => $store_id,
                    ]
                ];
            } else {
                $MerchantStore = MerchantStore::where('merchant_id', $merchant->merchant_id)
                    ->select('store_id')
                    ->get();

                if (!$MerchantStore->isEmpty()) {
                    $store_ids = $MerchantStore->toArray();
                }
            }
            if ($ways_source) {
                $where[] = ['ways_source', '=', $ways_source];
            }
            if ($ways_type) {
                $where[] = ['ways_type', '=', $ways_type];
            }
            if ($time_start) {
                $time_start = date('Y-m-d H:i:s', strtotime($time_start));
                $where[] = ['updated_at', '>=', $time_start];
            }
            if ($time_end) {
                $time_end = date('Y-m-d H:i:s', strtotime($time_end));
                $where[] = ['updated_at', '<=', $time_end];
            }


            if ($sort) {
                //返回基础数据
                if ($return_type == '1') {
                    $obj = $obj->where($where)
                        ->whereIn('store_id', $store_ids)
                        ->orderBy('total_amount', $sort)
                        ->select('out_trade_no',
                            'trade_no',
                            'qwx_no',
                            'other_no',
                            'store_id',
                            'out_store_id',
                            'merchant_id',
                            'store_name',
                            'merchant_name',
                            'payment_method',//支付方式
                            'ways_type',
                            'ways_type_desc',
                            'ways_source',
                            'ways_source_desc',
                            'company',//通道方
                            'total_amount',
                            'shop_price',
                            'receipt_amount',//商家实际收到的款项
                            'pay_amount',//用户需要支付总金额
                            'buyer_pay_amount',//用户实际付款支付金额
                            'status',
                            'pay_status',//系统状态
                            'pay_status_desc',
                            'rate',//商户交易时的费率
                            'fee_amount',
                            'cost_rate',
                            'buyer_id',
                            'buyer_logon_id',
                            "remark",
                            'coupon_type',//优惠类型
                            'coupon_amount',//优惠金额
                            'device_id',
                            'pay_time',//支付时间
                            'mdiscount_amount',//商家优惠金额
                            'created_at',
                            'updated_at',
                            'discount_amount'//第三方支付公司平台优惠金额
                        );

                } else {
                    $obj = $obj->where($where)
                        ->whereIn('store_id', $store_ids)
                        ->orderBy('total_amount', $sort);
                }

            } else {
                if ($return_type == '1') {
                    //返回基础数据
                    $obj = $obj->where($where)
                        ->whereIn('store_id', $store_ids)
                        ->orderBy('updated_at', 'desc')
                        ->select('out_trade_no',
                            'trade_no',
                            'qwx_no',
                            'other_no',
                            'store_id',
                            'out_store_id',
                            'merchant_id',
                            'store_name',
                            'merchant_name',
                            'payment_method',//支付方式
                            'ways_type',
                            'ways_type_desc',
                            'ways_source',
                            'ways_source_desc',
                            'company',//通道方
                            'total_amount',
                            'shop_price',
                            'receipt_amount',//商家实际收到的款项
                            'pay_amount',//用户需要支付总金额
                            'buyer_pay_amount',//用户实际付款支付金额
                            'status',
                            'pay_status',//系统状态
                            'pay_status_desc',
                            'rate',//商户交易时的费率
                            'fee_amount',
                            'cost_rate',
                            'buyer_id',
                            'buyer_logon_id',
                            "remark",
                            'coupon_type',//优惠类型
                            'coupon_amount',//优惠金额
                            'device_id',
                            'pay_time',//支付时间
                            'mdiscount_amount',//商家优惠金额
                            'created_at',
                            'updated_at',
                            'discount_amount'//第三方支付公司平台优惠金额
                        );
                } else {
                    $obj = $obj->where($where)
                        ->whereIn('store_id', $store_ids)
                        ->orderBy('updated_at', 'desc');
                }
            }

            $this->t = $obj->count();
            $data = $this->page($obj)->get();
            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);
        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage() . $exception->getLine();
            return $this->format();
        }
    }

    public function order_info(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $out_trade_no = $request->get('out_trade_no', '');
            $data = Order::orWhere('out_trade_no', $out_trade_no)
                ->orWhere('trade_no', $out_trade_no)
                ->first();
            if (!$data) {
                return json_encode([
                    'status' => 2,
                    'message' => '订单号不存在'
                ]);
            }

            //保证订单号是此门店的
            $MerchantStore = MerchantStore::where('store_id', $data->store_id)
                ->where('merchant_id', $merchant->merchant_id)
                ->select('id')
                ->first();

            if (!$MerchantStore) {
                return json_encode([
                    'status' => 2,
                    'message' => '订单号不在你的查询范围'
                ]);
            }

            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);
        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }
    }

    //app退款
    public function refund(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $merchant_id = $merchant->merchant_id;
            $out_trade_no = $request->get('out_trade_no', '');
            $refund_amount = $request->get('refund_amount', '');

            //收银员
            if ($merchant->merchant_type == 2) {
                return json_encode([
                    'status' => 2,
                    'message' => '收银员没有退款权限'
                ]);
            }
            $data = [
                'merchant_id' => $merchant_id,
                'out_trade_no' => $out_trade_no,
                'refund_amount' => $refund_amount,
            ];
            return $this->refund_public($data);

        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage() . $exception->getLine()
            ]);
        }
    }

    //app退款-公共
    public function refund_public($data)
    {
        try {
            $merchant_id = $data['merchant_id'];
            $out_trade_no = $data['out_trade_no'];
            $refund_amount = $data['refund_amount'];;

            $order = Order::orWhere('out_trade_no', $out_trade_no)
                ->orWhere('trade_no', $out_trade_no)
                ->first();


            if (!$order) {
                return json_encode([
                    'status' => 2,
                    'message' => '订单号不存在'
                ]);
            }

            //判断退款是否达到
            if ($order->refund_amount >= $order->total_amount) {
                return json_encode([
                    'status' => 2,
                    'message' => '订单号已经全部退款无需退款'
                ]);
            }

            $out_trade_no = $order->out_trade_no;
            $OutRefundNo = $out_trade_no . '123';
            $refund_amount = isset($refund_amount) && $refund_amount ? $refund_amount : $order->total_amount;

            //保证订单号是此门店的
            $MerchantStore = MerchantStore::where('store_id', $order->store_id)
                ->where('merchant_id', $merchant_id)
                ->select('id')
                ->first();

            if (!$MerchantStore) {
                return json_encode([
                    'status' => 2,
                    'message' => '订单号不在你的查询范围'
                ]);
            }


            $store_id = $order->store_id;
            $ways_type = $order->ways_type;
            $store = Store::where('store_id', $store_id)
                ->select('config_id', 'merchant_id', 'pid')
                ->first();
            $config_id = $store->config_id;
            $store_id = $order->store_id;
            $store_pid = $store->pid;
            $other_no = $order->other_no;


            //支付宝官方
            if (999 < $ways_type && $ways_type < 1999) {
                //配置
                $isvconfig = new AlipayIsvConfigController();
                $config_type = '01';
                $config = $isvconfig->AlipayIsvConfig($config_id, $config_type);


                //获取token
                $storeInfo = AlipayAppOauthUsers::where('store_id', $store_id)
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
                    $order->fee_amount = 0;//手续费
                    $order->refund_amount = $order->refund_amount + $refund_amount;
                    $order->status = 6;
                    $order->save();


                    RefundOrder::create([
                        'ways_source' => $order->ways_source,
                        'type' => $ways_type,
                        'refund_amount' => $order->total_amount,//退款金额
                        'refund_no' => $OutRefundNo,//退款单号
                        'store_id' => $store_id,
                        'merchant_id' => $merchant_id,
                        'out_trade_no' => $order->out_trade_no,
                        'trade_no' => $order->trade_no
                    ]);


                    $data = [
                        'refund_amount' => $refund_amount,
                        'out_trade_no' => $out_trade_no,
                        'other_no' => $other_no
                    ];


                    //返佣去掉
                    UserWalletDetail::where('out_trade_no', $out_trade_no)->update([
                        'settlement' => '03',
                        'settlement_desc' => '退款订单',
                    ]);
                    MerchantWalletDetail::where('out_trade_no', $out_trade_no)->update([
                        'settlement' => '03',
                        'settlement_desc' => '退款订单',
                    ]);


                    return json_encode([
                        'status' => 1,
                        'message' => '退款成功',
                        'data' => $data,
                    ]);


                } else {

                    //退款失败
                    $data = [
                        'refund_amount' => $refund_amount,
                        'out_trade_no' => $out_trade_no,
                    ];


                    return json_encode([
                        'status' => 2,
                        'message' => $result->$responseNode->sub_msg,
                        'data' => $data,
                    ]);
                }
            }


            //微信官方扫码退款
            if (1999 < $ways_type && $ways_type < 2999) {

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
                        $order->refund_amount = $order->refund_amount + $refund_amount;
                        $order->status = 6;
                        $order->save();

                        RefundOrder::create([
                            'ways_source' => $order->ways_source,
                            'type' => $ways_type,
                            'refund_amount' => $order->total_amount,//退款金额
                            'refund_no' => $OutRefundNo,//退款单号
                            'store_id' => $store_id,
                            'merchant_id' => $merchant_id,
                            'out_trade_no' => $order->out_trade_no,
                            'trade_no' => $order->trade_no
                        ]);

                        $data = [
                            'refund_amount' => $refund_amount,
                            'out_trade_no' => $out_trade_no,
                            'other_no' => $other_no

                        ];


                        //返佣去掉
                        UserWalletDetail::where('out_trade_no', $out_trade_no)->update([
                            'settlement' => '03',
                            'settlement_desc' => '退款订单',
                        ]);
                        MerchantWalletDetail::where('out_trade_no', $out_trade_no)->update([
                            'settlement' => '03',
                            'settlement_desc' => '退款订单',
                        ]);


                        return json_encode([
                            'status' => 1,
                            'message' => '退款成功',
                            'data' => $data,
                        ]);

                    } else {
                        $data = [
                            'refund_amount' => $refund_amount,
                            'out_trade_no' => $out_trade_no,
                        ];

                        return json_encode([
                            'status' => 2,
                            'message' => $refund['err_code_des'],
                            'data' => $data,
                        ]);

                    }


                } else {
                    //退款失败
                    $data = [
                        'refund_amount' => $refund_amount,
                        'out_trade_no' => $out_trade_no,
                    ];

                    return json_encode([
                        'status' => 2,
                        'message' => $refund['return_msg'],
                        'data' => $data,
                    ]);


                }
            }

            //京东收银通道
            if (5999 < $ways_type && $ways_type < 6999) {
                //读取配置
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
                $obj = new \App\Api\Controllers\Jd\PayController();
                $data = [];
                $data['out_trade_no'] = $out_trade_no;
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
                    $order->fee_amount = 0;//手续费
                    $order->refund_amount = $order->refund_amount + $refund_amount;
                    $order->status = 6;
                    $order->save();

                    RefundOrder::create([
                        'ways_source' => $order->ways_source,
                        'type' => $ways_type,
                        'refund_amount' => $order->total_amount,//退款金额
                        'refund_no' => $OutRefundNo,//退款单号
                        'store_id' => $store_id,
                        'merchant_id' => $merchant_id,
                        'out_trade_no' => $order->out_trade_no,
                        'trade_no' => $order->trade_no
                    ]);

                    $data = [
                        'refund_amount' => $refund_amount,
                        'out_trade_no' => $out_trade_no,
                        'other_no' => $other_no

                    ];


                    //返佣去掉
                    UserWalletDetail::where('out_trade_no', $out_trade_no)->update([
                        'settlement' => '03',
                        'settlement_desc' => '退款订单',
                    ]);
                    MerchantWalletDetail::where('out_trade_no', $out_trade_no)->update([
                        'settlement' => '03',
                        'settlement_desc' => '退款订单',
                    ]);


                    return json_encode([
                        'status' => 1,
                        'message' => '退款成功',
                        'data' => $data,
                    ]);

                } else {
                    //其他情况
                    $message = $return['message'];
                    return json_encode([
                        'status' => 2,
                        'message' => $message
                    ]);
                }


            }


            //网商银行通道
            if (2999 < $ways_type && $ways_type < 3999) {
                //读取配置
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

                $obj = new TradePayController();
                $MerchantId = $mybank_merchant->MerchantId;
                $RefundAmount = $order->total_amount;
                $return = $obj->mybankrefund($MerchantId, $out_trade_no, $OutRefundNo, $RefundAmount, $config_id);

                //退款请求成功
                if ($return["status"] == 1) {
                    $order->pay_status_desc = '已退款';
                    $order->pay_status = 6;
                    $order->fee_amount = 0;//手续费
                    $order->refund_amount = $order->refund_amount + $refund_amount;
                    $order->status = 6;
                    $order->save();

                    RefundOrder::create([
                        'ways_source' => $order->ways_source,
                        'type' => $ways_type,
                        'refund_amount' => $order->total_amount,//退款金额
                        'refund_no' => $OutRefundNo,//退款单号
                        'store_id' => $store_id,
                        'merchant_id' => $merchant_id,
                        'out_trade_no' => $order->out_trade_no,
                        'trade_no' => $order->trade_no
                    ]);

                    $data = [
                        'refund_amount' => $refund_amount,
                        'out_trade_no' => $out_trade_no,
                        'other_no' => $other_no

                    ];


                    //返佣去掉
                    UserWalletDetail::where('out_trade_no', $out_trade_no)->update([
                        'settlement' => '03',
                        'settlement_desc' => '退款订单',
                    ]);
                    MerchantWalletDetail::where('out_trade_no', $out_trade_no)->update([
                        'settlement' => '03',
                        'settlement_desc' => '退款订单',
                    ]);

                    return json_encode([
                        'status' => 1,
                        'message' => '退款成功',
                        'data' => $data,
                    ]);


                } else {
                    //其他情况
                    $message = $return['message'];
                    return json_encode([
                        'status' => 2,
                        'message' => $message
                    ]);
                }


            }

            //新大陆
            if (7999 < $ways_type && $ways_type < 8999) {
                //读取配置
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
                    'trade_no' => $order->trade_no,
                    'key' => $new_land_merchant->nl_key,
                    'org_no' => $new_land_config->org_no,
                    'merc_id' => $new_land_merchant->nl_mercId,
                    'trm_no' => $new_land_merchant->trmNo,
                    'op_sys' => '3',
                    'opr_id' => $store->merchant_id,
                    'trm_typ' => 'T',
                ];
                $obj = new PayController();
                $return = $obj->refund($request_data);

                //退款请求成功
                if ($return["status"] == 1) {
                    $order->pay_status_desc = '已退款';
                    $order->pay_status = 6;
                    $order->fee_amount = 0;//手续费
                    $order->refund_amount = $order->refund_amount + $refund_amount;
                    $order->status = 6;
                    $order->save();

                    RefundOrder::create([
                        'ways_source' => $order->ways_source,
                        'type' => $ways_type,
                        'refund_amount' => $order->total_amount,//退款金额
                        'refund_no' => $OutRefundNo,//退款单号
                        'store_id' => $store_id,
                        'merchant_id' => $merchant_id,
                        'out_trade_no' => $order->out_trade_no,
                        'trade_no' => $order->trade_no
                    ]);

                    $data = [
                        'refund_amount' => $refund_amount,
                        'out_trade_no' => $out_trade_no,
                        'other_no' => $other_no

                    ];


                    //返佣去掉
                    UserWalletDetail::where('out_trade_no', $out_trade_no)->update([
                        'settlement' => '03',
                        'settlement_desc' => '退款订单',
                    ]);
                    MerchantWalletDetail::where('out_trade_no', $out_trade_no)->update([
                        'settlement' => '03',
                        'settlement_desc' => '退款订单',
                    ]);


                    return json_encode([
                        'status' => 1,
                        'message' => '退款成功',
                        'data' => $data,
                    ]);


                } else {
                    //其他情况
                    $message = $return['message'];
                    return json_encode([
                        'status' => 2,
                        'message' => $message
                    ]);
                }


            }
            //和融通
            if (8999 < $ways_type && $ways_type < 9999) {
                //读取配置
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
                    $order->fee_amount = 0;//手续费
                    $order->refund_amount = $order->refund_amount + $refund_amount;
                    $order->status = 6;
                    $order->save();

                    RefundOrder::create([
                        'ways_source' => $order->ways_source,
                        'type' => $ways_type,
                        'refund_amount' => $order->total_amount,//退款金额
                        'refund_no' => $return['data']['refundOrderNo'],//退款单号
                        'store_id' => $store_id,
                        'merchant_id' => $merchant_id,
                        'out_trade_no' => $order->out_trade_no,
                        'trade_no' => $order->trade_no
                    ]);

                    $data = [
                        'refund_amount' => $refund_amount,
                        'out_trade_no' => $out_trade_no,
                        'other_no' => $other_no

                    ];


                    //返佣去掉
                    UserWalletDetail::where('out_trade_no', $out_trade_no)->update([
                        'settlement' => '03',
                        'settlement_desc' => '退款订单',
                    ]);
                    MerchantWalletDetail::where('out_trade_no', $out_trade_no)->update([
                        'settlement' => '03',
                        'settlement_desc' => '退款订单',
                    ]);


                    return json_encode([
                        'status' => 1,
                        'message' => '退款成功',
                        'data' => $data,
                    ]);


                } else {
                    //其他情况
                    $message = $return['message'];
                    return json_encode([
                        'status' => 2,
                        'message' => $message
                    ]);
                }


            }

            //lft收银通道
            if (9999 < $ways_type && $ways_type < 19999) {
                //读取配置
                $config = new LtfConfigController();
                $ltf_merchant = $config->ltf_merchant($store_id, $store_pid);
                if (!$ltf_merchant) {
                    return json_encode([
                        'status' => 2,
                        'message' => '商户号不存在'
                    ]);
                }
                $obj = new \App\Api\Controllers\Ltf\PayController();
                $data = [];
                $data['out_trade_no'] = $out_trade_no;
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
                    $order->fee_amount = 0;//手续费
                    $order->refund_amount = $order->refund_amount + $refund_amount;
                    $order->status = 6;
                    $order->save();

                    RefundOrder::create([
                        'ways_source' => $order->ways_source,
                        'type' => $ways_type,
                        'refund_amount' => $order->total_amount,//退款金额
                        'refund_no' => $OutRefundNo,//退款单号
                        'store_id' => $store_id,
                        'merchant_id' => $merchant_id,
                        'out_trade_no' => $order->out_trade_no,
                        'trade_no' => $order->trade_no
                    ]);

                    $data = [
                        'refund_amount' => $refund_amount,
                        'out_trade_no' => $out_trade_no,
                        'other_no' => $other_no

                    ];


                    //返佣去掉
                    UserWalletDetail::where('out_trade_no', $out_trade_no)->update([
                        'settlement' => '03',
                        'settlement_desc' => '退款订单',
                    ]);
                    MerchantWalletDetail::where('out_trade_no', $out_trade_no)->update([
                        'settlement' => '03',
                        'settlement_desc' => '退款订单',
                    ]);


                    return json_encode([
                        'status' => 1,
                        'message' => '退款成功',
                        'data' => $data,
                    ]);

                } else {
                    //其他情况
                    $message = $return['message'];
                    return json_encode([
                        'status' => 2,
                        'message' => $message
                    ]);
                }


            }


        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage() . $exception->getLine()
            ]);
        }
    }

    //对账统计-比较全-用这个
    public function order_count(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $merchant_id = $request->get('merchant_id', '');
            $time_start = $request->get('time_start', '');
            $time_end = $request->get('time_end', '');
            $time_type = $request->get('time_type', '2');
            $return_type = $request->get('return_type', '01');
            $check_data = [
                'time_start' => '开始时间',
                'time_end' => '结束时间',
            ];
            $where = [];
            $whereIn = [];
            $store_ids = [];
            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }
            //条件查询
            if ($store_id) {
                $where[] = ['store_id', '=', $store_id];
                $store_ids = [
                    [
                        'store_id' => $store_id,
                    ]
                ];

            } else {
                $MerchantStore = MerchantStore::where('merchant_id', $merchant->merchant_id)
                    ->select('store_id')
                    ->get();

                if (!$MerchantStore->isEmpty()) {
                    $store_ids = $MerchantStore->toArray();
                }

            }
            //收银员
            if ($merchant->merchant_type == 2) {
                $where[] = ['merchant_id', '=', $merchant->merchant_id];
            }

            //是否传收银员ID
            if ($request->get('merchant_id', '')) {
                $where[] = ['merchant_id', '=', $request->get('merchant_id', '')];
            }

            if ($time_type == "1") {
                $y = date("Y", time());
                $time_start = $y . $time_start;
                $time_end = $y . $time_end;
            }

            if ($time_start) {
                if ($time_type == "1") {

                    $time_start = date('Y-m-d 00:00:00', strtotime($time_start));
                } else {
                    $time_start = date('Y-m-d H:i:s', strtotime($time_start));

                }

                $where[] = ['created_at', '>=', $time_start];
            }
            if ($time_end) {
                if ($time_type == "1") {
                    $time_end = date('Y-m-d 23:59:59', strtotime($time_end));
                } else {
                    $time_end = date('Y-m-d H:i:s', strtotime($time_end));
                }

                $where[] = ['created_at', '<=', $time_end];
            }


            //区间
            $e_order = '0.00';

            if ($merchant_id) {

                $order_data = Order::where($where)
                    ->whereIn('pay_status', [1, 6, 3])//成功+退款
                    ->where('merchant_id', $merchant_id)
                    ->select('total_amount', 'refund_amount', 'receipt_amount', 'fee_amount', 'mdiscount_amount');

                $refund_obj = RefundOrder::where('merchant_id', $merchant_id)
                    ->where($where)
                    ->select('refund_amount');

                //支付宝
                $alipay_order_data = Order::where($where)
                    ->whereIn('pay_status', [1, 6, 3])//成功+退款
                    ->where('ways_source', 'alipay')
                    ->where('merchant_id', $merchant_id)
                    ->select('total_amount', 'refund_amount', 'receipt_amount', 'fee_amount', 'mdiscount_amount');

                $alipay_refund_obj = RefundOrder::where('merchant_id', $merchant_id)
                    ->where('ways_source', 'alipay')
                    ->where($where)
                    ->select('refund_amount');

                //微信
                $weixin_order_data = Order::where($where)
                    ->whereIn('pay_status', [1, 6, 3])//成功+退款
                    ->where('ways_source', 'weixin')
                    ->where('merchant_id', $merchant_id)
                    ->select('total_amount', 'refund_amount', 'receipt_amount', 'fee_amount', 'mdiscount_amount');

                $weixin_refund_obj = RefundOrder::where('merchant_id', $merchant_id)
                    ->where('ways_source', 'weixin')
                    ->where($where)
                    ->select('refund_amount');


                //京东
                $jd_order_data = Order::where($where)
                    ->whereIn('pay_status', [1, 6, 3])//成功+退款
                    ->where('ways_source', 'jd')
                    ->where('merchant_id', $merchant_id)
                    ->select('total_amount', 'refund_amount', 'receipt_amount', 'fee_amount', 'mdiscount_amount');

                $jd_refund_obj = RefundOrder::where('merchant_id', $merchant_id)
                    ->where('ways_source', 'jd')
                    ->where($where)
                    ->select('refund_amount');


                //银联刷卡
                $un_order_data = Order::where($where)
                    ->whereIn('pay_status', [1, 6, 3])//成功+退款
                    ->whereIn('ways_type', [6005, 8005])//新大陆+京东刷卡
                    ->where('ways_source', 'unionpay')
                    ->where('merchant_id', $merchant_id)
                    ->select('total_amount', 'refund_amount', 'receipt_amount', 'fee_amount', 'mdiscount_amount');

                $un_refund_obj = RefundOrder::where('merchant_id', $merchant_id)
                    ->whereIn('type', [6005, 8005])//新大陆+京东刷卡
                    ->where('ways_source', 'unionpay')
                    ->where($where)
                    ->select('refund_amount');


                //银联扫码
                $unqr_order_data = Order::where($where)
                    ->whereIn('pay_status', [1, 6, 3])//成功+退款
                    ->whereNotIn('ways_type', [6005, 8005])//新大陆+京东刷卡
                    ->where('ways_source', 'unionpay')
                    ->where('merchant_id', $merchant_id)
                    ->select('total_amount', 'refund_amount', 'receipt_amount', 'fee_amount', 'mdiscount_amount');

                $unqr_refund_obj = RefundOrder::where('merchant_id', $merchant_id)
                    ->whereNotIn('type', [6005, 8005])//新大陆+京东刷卡
                    ->where('ways_source', 'unionpay')
                    ->where($where)
                    ->select('refund_amount');


            } else {
                $order_data = Order::whereIn('store_id', $store_ids)
                    ->where($where)
                    ->whereIn('pay_status', [1, 6, 3])//成功+退款
                    ->select('total_amount', 'refund_amount', 'receipt_amount', 'fee_amount', 'mdiscount_amount');


                $refund_obj = RefundOrder::whereIn('store_id', $store_ids)
                    ->where($where)
                    ->select('refund_amount');

                //支付宝
                $alipay_order_data = Order::whereIn('store_id', $store_ids)
                    ->where($where)
                    ->whereIn('pay_status', [1, 6, 3])//成功+退款
                    ->where('ways_source', 'alipay')
                    ->select('total_amount', 'refund_amount', 'receipt_amount', 'fee_amount', 'mdiscount_amount');


                $alipay_refund_obj = RefundOrder::whereIn('store_id', $store_ids)
                    ->where($where)
                    ->where('ways_source', 'alipay')
                    ->select('refund_amount');


                //微信
                $weixin_order_data = Order::whereIn('store_id', $store_ids)
                    ->where($where)
                    ->whereIn('pay_status', [1, 6, 3])//成功+退款
                    ->where('ways_source', 'weixin')
                    ->select('total_amount', 'refund_amount', 'receipt_amount', 'fee_amount', 'mdiscount_amount');


                $weixin_refund_obj = RefundOrder::whereIn('store_id', $store_ids)
                    ->where($where)
                    ->where('ways_source', 'weixin')
                    ->select('refund_amount');


                //京东
                $jd_order_data = Order::whereIn('store_id', $store_ids)
                    ->where($where)
                    ->whereIn('pay_status', [1, 6, 3])//成功+退款
                    ->where('ways_source', 'jd')
                    ->select('total_amount', 'refund_amount', 'receipt_amount', 'fee_amount', 'mdiscount_amount');


                $jd_refund_obj = RefundOrder::whereIn('store_id', $store_ids)
                    ->where($where)
                    ->where('ways_source', 'jd')
                    ->select('refund_amount');


                //银联刷卡
                $un_order_data = Order::whereIn('store_id', $store_ids)
                    ->where($where)
                    ->whereIn('pay_status', [1, 6, 3])//成功+退款
                    ->whereIn('ways_type', [6005, 8005])//新大陆+京东刷卡
                    ->where('ways_source', 'unionpay')
                    ->select('total_amount', 'refund_amount', 'receipt_amount', 'fee_amount', 'mdiscount_amount');


                $un_refund_obj = RefundOrder::whereIn('store_id', $store_ids)
                    ->where($where)
                    ->where('ways_source', 'unionpay')
                    ->whereIn('type', [6005, 8005])//新大陆+京东刷卡
                    ->select('refund_amount');


                //银联二维码
                $unqr_order_data = Order::whereIn('store_id', $store_ids)
                    ->where($where)
                    ->whereIn('pay_status', [1, 6, 3])//成功+退款
                    ->whereNotIn('ways_type', [6005, 8005])//去除新大陆+京东刷卡
                    ->where('ways_source', 'unionpay')
                    ->select('total_amount', 'refund_amount', 'receipt_amount', 'fee_amount', 'mdiscount_amount');


                $unqr_refund_obj = RefundOrder::whereIn('store_id', $store_ids)
                    ->where($where)
                    ->where('ways_source', 'unionpay')
                    ->whereNotIn('type', [6005, 8005])//去除新大陆+京东刷卡
                    ->select('refund_amount');


            }

            //总的
            $total_amount = $order_data->sum('total_amount');//交易金额
            $refund_amount = $refund_obj->sum('refund_amount');//退款金额
            $fee_amount = $order_data->sum('fee_amount');//结算服务费/手续费
            $mdiscount_amount = $order_data->sum('mdiscount_amount');//商家优惠金额
            $get_amount = $total_amount - $refund_amount - $mdiscount_amount;//商家实收，交易金额-退款金额
            $receipt_amount = $get_amount - $fee_amount;//实际净额，实收-手续费
            $e_order = '' . $e_order . '';
            $total_count = '' . count($order_data->get()) . '';
            $refund_count = count($refund_obj->get());

            //支付宝
            $alipay_total_amount = $alipay_order_data->sum('total_amount');//交易金额
            $alipay_refund_amount = $alipay_refund_obj->sum('refund_amount');//退款金额
            $alipay_fee_amount = $alipay_order_data->sum('fee_amount');//结算服务费/手续费
            $alipay_mdiscount_amount = $alipay_order_data->sum('mdiscount_amount');//商家优惠金额
            $alipay_get_amount = $alipay_total_amount - $alipay_refund_amount - $alipay_mdiscount_amount;//商家实收，交易金额-退款金额
            $alipay_receipt_amount = $alipay_get_amount - $alipay_fee_amount;//实际净额，实收-手续费
            $alipay_total_count = '' . count($alipay_order_data->get()) . '';
            $alipay_refund_count = count($alipay_refund_obj->get());


            //微信
            $weixin_total_amount = $weixin_order_data->sum('total_amount');//交易金额
            $weixin_refund_amount = $weixin_refund_obj->sum('refund_amount');//退款金额
            $weixin_fee_amount = $weixin_order_data->sum('fee_amount');//结算服务费/手续费
            $weixin_mdiscount_amount = $weixin_order_data->sum('mdiscount_amount');//商家优惠金额
            $weixin_get_amount = $weixin_total_amount - $weixin_refund_amount - $weixin_mdiscount_amount;//商家实收，交易金额-退款金额
            $weixin_receipt_amount = $weixin_get_amount - $weixin_fee_amount;//实际净额，实收-手续费
            $weixin_total_count = '' . count($weixin_order_data->get()) . '';
            $weixin_refund_count = count($weixin_refund_obj->get());


            //京东
            $jd_total_amount = $jd_order_data->sum('total_amount');//交易金额
            $jd_refund_amount = $jd_refund_obj->sum('refund_amount');//退款金额
            $jd_fee_amount = $jd_order_data->sum('fee_amount');//结算服务费/手续费
            $jd_mdiscount_amount = $jd_order_data->sum('mdiscount_amount');//商家优惠金额
            $jd_get_amount = $jd_total_amount - $jd_refund_amount - $jd_mdiscount_amount;//商家实收，交易金额-退款金额
            $jd_receipt_amount = $jd_get_amount - $jd_fee_amount;//实际净额，实收-手续费
            $jd_total_count = '' . count($jd_order_data->get()) . '';
            $jd_refund_count = count($jd_refund_obj->get());

            //银联刷卡
            $un_total_amount = $un_order_data->sum('total_amount');//交易金额
            $un_refund_amount = $un_refund_obj->sum('refund_amount');//退款金额
            $un_fee_amount = $un_order_data->sum('fee_amount');//结算服务费/手续费
            $un_mdiscount_amount = $un_order_data->sum('mdiscount_amount');//商家优惠金额
            $un_get_amount = $un_total_amount - $un_refund_amount - $un_mdiscount_amount;//商家实收，交易金额-退款金额
            $un_receipt_amount = $un_get_amount - $un_fee_amount;//实际净额，实收-手续费
            $un_total_count = '' . count($un_order_data->get()) . '';
            $un_refund_count = count($un_refund_obj->get());

            //银联扫码
            $unqr_total_amount = $unqr_order_data->sum('total_amount');//交易金额
            $unqr_refund_amount = $unqr_refund_obj->sum('refund_amount');//退款金额
            $unqr_fee_amount = $unqr_order_data->sum('fee_amount');//结算服务费/手续费
            $unqr_mdiscount_amount = $unqr_order_data->sum('mdiscount_amount');//商家优惠金额
            $unqr_get_amount = $unqr_total_amount - $unqr_refund_amount - $unqr_mdiscount_amount;//商家实收，交易金额-退款金额
            $unqr_receipt_amount = $unqr_get_amount - $unqr_fee_amount;//实际净额，实收-手续费
            $unqr_total_count = '' . count($unqr_order_data->get()) . '';
            $unqr_refund_count = count($unqr_refund_obj->get());


            $data = [
                'total_amount' => number_format($total_amount, 2, '.', ''),//交易金额
                'total_count' => '' . $total_count . '',//交易笔数
                'refund_count' => '' . $refund_count . '',//退款金额
                'get_amount' => number_format($get_amount, 2, '.', ''),//商家实收，交易金额-退款金额
                'refund_amount' => number_format($refund_amount, 2, '.', ''),//退款金额
                'receipt_amount' => number_format($receipt_amount, 2, '.', ''),//实际净额，实收-手续费
                'fee_amount' => number_format($fee_amount, 2, '.', ''),//结算服务费/手续费
                'mdiscount_amount' => number_format($mdiscount_amount, 2, '.', ''),

                'alipay_total_amount' => number_format($alipay_total_amount, 2, '.', ''),//交易金额
                'alipay_total_count' => '' . $alipay_total_count . '',//交易笔数
                'alipay_refund_count' => '' . $alipay_refund_count . '',//退款金额
                'alipay_get_amount' => number_format($alipay_get_amount, 2, '.', ''),//商家实收，交易金额-退款金额
                'alipay_refund_amount' => number_format($alipay_refund_amount, 2, '.', ''),//退款金额
                'alipay_receipt_amount' => number_format($alipay_receipt_amount, 2, '.', ''),//实际净额，实收-手续费
                'alipay_fee_amount' => number_format($alipay_fee_amount, 2, '.', ''),//结算服务费/手续费
                'alipay_mdiscount_amount' => number_format($alipay_mdiscount_amount, 2, '.', ''),

                'weixin_total_amount' => number_format($weixin_total_amount, 2, '.', ''),//交易金额
                'weixin_total_count' => '' . $weixin_total_count . '',//交易笔数
                'weixin_refund_count' => '' . $weixin_refund_count . '',//退款金额
                'weixin_get_amount' => number_format($weixin_get_amount, 2, '.', ''),//商家实收，交易金额-退款金额
                'weixin_refund_amount' => number_format($weixin_refund_amount, 2, '.', ''),//退款金额
                'weixin_receipt_amount' => number_format($weixin_receipt_amount, 2, '.', ''),//实际净额，实收-手续费
                'weixin_fee_amount' => number_format($weixin_fee_amount, 2, '.', ''),//结算服务费/手续费
                'weixin_mdiscount_amount' => number_format($weixin_mdiscount_amount, 2, '.', ''),

                'jd_total_amount' => number_format($jd_total_amount, 2, '.', ''),//交易金额
                'jd_total_count' => '' . $jd_total_count . '',//交易笔数
                'jd_refund_count' => '' . $jd_refund_count . '',//退款金额
                'jd_get_amount' => number_format($jd_get_amount, 2, '.', ''),//商家实收，交易金额-退款金额
                'jd_refund_amount' => number_format($jd_refund_amount, 2, '.', ''),//退款金额
                'jd_receipt_amount' => number_format($jd_receipt_amount, 2, '.', ''),//实际净额，实收-手续费
                'jd_fee_amount' => number_format($jd_fee_amount, 2, '.', ''),//结算服务费/手续费
                'jd_mdiscount_amount' => number_format($jd_mdiscount_amount, 2, '.', ''),

                'un_total_amount' => number_format($un_total_amount, 2, '.', ''),//交易金额
                'un_total_count' => '' . $un_total_count . '',//交易笔数
                'un_refund_count' => '' . $un_refund_count . '',//退款金额
                'un_get_amount' => number_format($un_get_amount, 2, '.', ''),//商家实收，交易金额-退款金额
                'un_refund_amount' => number_format($un_refund_amount, 2, '.', ''),//退款金额
                'un_receipt_amount' => number_format($un_receipt_amount, 2, '.', ''),//实际净额，实收-手续费
                'un_fee_amount' => number_format($un_fee_amount, 2, '.', ''),//结算服务费/手续费
                'un_mdiscount_amount' => number_format($un_mdiscount_amount, 2, '.', ''),

                'unqr_total_amount' => number_format($unqr_total_amount, 2, '.', ''),//交易金额
                'unqr_total_count' => '' . $unqr_total_count . '',//交易笔数
                'unqr_refund_count' => '' . $unqr_refund_count . '',//退款金额
                'unqr_get_amount' => number_format($unqr_get_amount, 2, '.', ''),//商家实收，交易金额-退款金额
                'unqr_refund_amount' => number_format($unqr_refund_amount, 2, '.', ''),//退款金额
                'unqr_receipt_amount' => number_format($unqr_receipt_amount, 2, '.', ''),//实际净额，实收-手续费
                'unqr_fee_amount' => number_format($unqr_fee_amount, 2, '.', ''),//结算服务费/手续费
                'unqr_mdiscount_amount' => number_format($unqr_mdiscount_amount, 2, '.', ''),

                'hbfq_total_amount' => number_format(0, 2, '.', ''),//交易金额
                'hbfq_total_count' => '' . 0 . '',//交易笔数
                'hbfq_refund_count' => '' . 0 . '',//退款金额
                'hbfq_get_amount' => number_format(0, 2, '.', ''),//商家实收，交易金额-退款金额
                'hbfq_refund_amount' => number_format(0, 2, '.', ''),//退款金额
                'hbfq_receipt_amount' => number_format(0, 2, '.', ''),//实际净额，实收-手续费
                'hbfq_fee_amount' => number_format(0, 2, '.', ''),//结算服务费/手续费
                'hbfq_mdiscount_amount' => number_format(0, 2, '.', ''),

            ];


            //打印ID
            $print_data = $data;
            $print_data['store_id'] = $store_id;
            $print_data['merchant_id'] = $merchant_id;
            $print_data['time_start'] = $time_start;
            $print_data['time_end'] = $time_end;

            $data['print_id'] = $store_id . $merchant->merchant_id;
            Cache::put($data['print_id'], json_encode($print_data), 1);


            //附加流水详情
            if ($return_type == "02") {
                $obj = DB::table('orders');
                $obj = $obj->where($where)
                    ->whereIn('store_id', $store_ids)
                    ->orderBy('updated_at', 'desc');
                $this->t = $obj->count();
                $data['order_list'] = $this->page($obj)->get();
            }


            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);
        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }
    }

    //经营数据统计-即将舍弃
    public function data_count(Request $request)
    {
        try {
            // dd($merchant->store['store_id']);
            $merchant = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $merchant_id = $request->get('merchant_id', '');
            $time_start = $request->get('time_start', '');
            $time_end = $request->get('time_end', '');
            $time_type = $request->get('time_type', '1');//时间格式
            $store_ids = [];
            $check_data = [
                'time_start' => '开始时间',
                'time_end' => '结束时间',
            ];
            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }
            //没有传入收银员id 角色是收银员 只返回自己
            if ($merchant->merchant_type == 2) {
                $merchant_id = $merchant->merchant_id;
            }

            //如果门店为空 传登录者绑定的门店ID
            if ($store_id) {
                $where[] = ['store_id', '=', $store_id];
                $store_ids = [
                    [
                        'store_id' => $store_id,
                    ]
                ];

            } else {
                $MerchantStore = MerchantStore::where('merchant_id', $merchant->merchant_id)
                    ->select('store_id')
                    ->get();

                if (!$MerchantStore->isEmpty()) {
                    $store_ids = $MerchantStore->toArray();
                }

            }


            if ($time_type == '1') {
                $y = date("Y", time());
                $time_start = $y . $time_start;
                $time_end = $y . $time_end;
            } else {
                $time_start = date("Ymd", strtotime($time_start));
                $time_end = date("Ymd", strtotime($time_end));
            }

            $time_start1 = date('Y-m-d H:i:s', strtotime($time_start));
            $time_end1 = date('Y-m-d 23:59:59', strtotime($time_end));


            if ($merchant->merchant_type == 2) {
                $data_obj = MerchantStoreDayOrder::whereBetween('day', [$time_start, $time_end])
                    ->where('merchant_id', $merchant_id)
                    ->whereIn('store_id', $store_ids)
                    ->select('total_amount', 'order_sum');

                $hb_obj = MerchantStoreDayOrder::whereBetween('day', [$time_start, $time_end])
                    ->where('merchant_id', $merchant_id)
                    ->whereIn('store_id', $store_ids)
                    ->select('total_amount', 'order_sum')
                    ->whereIn('type', [1006, 1007]);


                $refund_obj = RefundOrder::where('merchant_id', $merchant_id)
                    ->where('updated_at', '<=', $time_end1)
                    ->where('updated_at', '>=', $time_start1)
                    ->whereIn('store_id', $store_ids)
                    ->select('refund_amount');

                $alipay_obj = MerchantStoreDayOrder::whereBetween('day', [$time_start, $time_end])
                    ->where('merchant_id', $merchant_id)
                    ->whereIn('store_id', $store_ids)
                    ->select('total_amount', 'order_sum')
                    ->where('source_type', 'alipay');

                $weixin_obj = MerchantStoreDayOrder::whereBetween('day', [$time_start, $time_end])
                    ->where('merchant_id', $merchant_id)
                    ->whereIn('store_id', $store_ids)
                    ->select('total_amount', 'order_sum')
                    ->where('source_type', 'weixin');

                $jd_obj = MerchantStoreDayOrder::whereBetween('day', [$time_start, $time_end])
                    ->where('merchant_id', $merchant_id)
                    ->whereIn('store_id', $store_ids)
                    ->select('total_amount', 'order_sum')
                    ->where('source_type', 'jdjr');


            } else {


                $data_obj = StoreDayOrder::whereBetween('day', [$time_start, $time_end])
                    ->whereIn('store_id', $store_ids)
                    ->select('total_amount', 'order_sum');


                $hb_obj = StoreDayOrder::whereBetween('day', [$time_start, $time_end])
                    ->whereIn('store_id', $store_ids)
                    ->select('total_amount', 'order_sum')
                    ->whereIn('type', [1006, 1007]);

                $refund_obj = RefundOrder::where('updated_at', '<=', $time_end1)
                    ->where('updated_at', '>=', $time_start1)
                    ->whereIn('store_id', $store_ids)
                    ->select('refund_amount');


                $alipay_obj = StoreDayOrder::whereBetween('day', [$time_start, $time_end])
                    ->whereIn('store_id', $store_ids)
                    ->select('total_amount', 'order_sum')
                    ->where('source_type', 'alipay');

                $weixin_obj = StoreDayOrder::whereBetween('day', [$time_start, $time_end])
                    ->whereIn('store_id', $store_ids)
                    ->select('total_amount', 'order_sum')
                    ->where('source_type', 'weixin');

                $jd_obj = StoreDayOrder::whereBetween('day', [$time_start, $time_end])
                    ->whereIn('store_id', $store_ids)
                    ->select('total_amount', 'order_sum')
                    ->where('source_type', 'jdjr');


            }

            //总金额

            $total_amount = $data_obj->sum('total_amount');
            $order_sum = $data_obj->sum('order_sum');

            $hb_fq_amount = $hb_obj->sum('total_amount');
            $hb_fq_count = $hb_obj->sum('order_sum');

            $alipay_amount = $alipay_obj->sum('total_amount');
            $alipay_count = $alipay_obj->sum('order_sum');

            $weixin_amount = $weixin_obj->sum('total_amount');
            $weixin_count = $weixin_obj->sum('order_sum');

            $jd_amount = $jd_obj->sum('total_amount');
            $jd_count = $jd_obj->sum('order_sum');


            $refund_amount = $refund_obj->sum('refund_amount');
            $refund_count = count($refund_obj->get());


            $data = [
                'total_amount' => number_format($total_amount, 2, '.', ''),
                'total_count' => "" . $order_sum . "",

                'refund_amount' => number_format($refund_amount, 2, '.', ''),
                'refund_count' => "" . $refund_count . "",

                'alipay_amount' => number_format($alipay_amount, 2, '.', ''),
                'alipay_count' => "" . $alipay_count . "",

                'weixin_amount' => number_format($weixin_amount, 2, '.', ''),
                'weixin_count' => "" . $weixin_count . "",

                'jd_amount' => number_format($jd_amount, 2, '.', ''),
                'jd_count' => '' . $jd_count . '',

                'hb_fq_amount' => number_format($hb_fq_amount, 2, '.', ''),
                'hb_fq_count' => "" . $hb_fq_count . "",

            ];
            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);
        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage() . $exception->getLine();
            return $this->format();
        }
    }

    //数据走势
    public function order_data(Request $request)
    {
        try {
            // dd($merchant->store['store_id']);
            $merchant = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $merchant_id = $request->get('merchant_id', '');

            //如果门店为空 传登录者绑定的门店ID
            if ($store_id == "") {
                $MyBankStore = MerchantStore::where('merchant_id', $merchant->merchant_id)
                    ->orderBy('created_at', 'asc')
                    ->first();
                $store_id = $MyBankStore->store_id;
            }
            $store_ids = [$store_id];

            //没有传入收银员id 角色是收银员 只返回自己
            if ($merchant->merchant_type == 2) {
                $merchant_id = $merchant->merchant_id;
            } else {
                $where[] = ['store_id', '=', $store_id];
                $store = Store::where('store_id', $store_id)
                    ->select('id', 'pid')
                    ->first();

                if ($store) {
                    $store_ids = $this->getStore_id($store_id, $store->id);
                }
            }


            //今日
            $day = date('Ymd', time());
            $beginToday = date("Y-m-d 00:00:00", time());
            $endToday = date("Y-m-d H:i:s", time());


            //收银员
            if ($merchant->merchant_type == 2) {
                $day_order_data = Order::whereIn('store_id', $store_ids)
                    ->where('created_at', '>=', $beginToday)
                    ->where('merchant_id', $merchant_id)
                    ->where('created_at', '<=', $endToday)
                    ->whereIn('pay_status', [1, 3, 6])
                    ->select('total_amount');

                $refund_order_data = RefundOrder::whereBetween('created_at', [$beginToday, $endToday])
                    ->where('merchant_id', $merchant_id)
                    ->where('store_id', $store_id)
                    ->select('refund_amount');


            } else {

                $day_order_data = Order::whereIn('store_id', $store_ids)
                    ->where('created_at', '>=', $beginToday)
                    ->where('created_at', '<=', $endToday)
                    ->whereIn('pay_status', [1, 3, 6])
                    ->select('total_amount');


                $refund_order_data = RefundOrder::whereBetween('created_at', [$beginToday, $endToday])
                    ->whereIn('store_id', $store_ids)
                    ->select('refund_amount');

            }


            $day_order = $day_order_data->sum('total_amount');
            $day_order = '' . $day_order . '';
            $day_order_count = '' . count($day_order_data->get()) . '';

            $refund_day_order = $refund_order_data->sum('refund_amount');
            $refund_day_order = '' . $refund_day_order . '';
            $refund_day_order_count = '' . count($refund_order_data->get()) . '';


            //上个月
            //得到系统的年月
            $tmp_date = date("Ym", time());
            //切割出年份
            $tmp_year = substr($tmp_date, 0, 4);
            //切割出月份
            $tmp_mon = substr($tmp_date, 4, 2);
            $tmp_forwardmonth = mktime(0, 0, 0, $tmp_mon - 1, 1, $tmp_year);
            $fm_forward_month = date("Ym", $tmp_forwardmonth);

            $old_begin_time = date("Y-m-d 00:00:00", $tmp_forwardmonth);
            $old_end_time = date("Y-m-d 23:59:59", strtotime(-date('d') . 'day'));

            if ($merchant->merchant_type == 2) {
                $old_month_order_data = Order::whereIn('store_id', $store_ids)
                    ->where('created_at', '>=', $old_begin_time)
                    ->where('merchant_id', $merchant_id)
                    ->where('created_at', '<=', $old_end_time)
                    ->whereIn('pay_status', [1, 3, 6])
                    ->select('total_amount');

                $refund_old_month_order_data = RefundOrder::whereBetween('created_at', [$old_begin_time, $old_end_time])
                    ->where('merchant_id', $merchant_id)
                    ->where('store_id', $store_id)
                    ->select('refund_amount');


            } else {
                $old_month_order_data = Order::whereIn('store_id', $store_ids)
                    ->where('created_at', '>=', $old_begin_time)
                    ->where('created_at', '<=', $old_end_time)
                    ->whereIn('pay_status', [1, 3, 6])
                    ->select('total_amount');


                $refund_old_month_order_data = RefundOrder::whereBetween('created_at', [$old_begin_time, $old_end_time])
                    ->whereIn('store_id', $store_ids)
                    ->select('refund_amount');
            }


            $old_month_order = $old_month_order_data->sum('total_amount');
            $old_month_amount = '' . $old_month_order . '';
            $old_month_count = '' . count($old_month_order_data->get()) . '';

            $refund_old_month_amount = $refund_old_month_order_data->sum('refund_amount');
            $refund_old_month_amount = '' . $refund_old_month_amount . '';
            $refund_old_month_count = '' . count($refund_old_month_order_data->get()) . '';


            //本月
            $tmonth = date('Ym', time());
            $month_begin_time = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), 1, date("Y")));
            $month_end_time = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), date("t"), date("Y")));


            if ($merchant->merchant_type == 2) {
                $month_order_data = Order::whereIn('store_id', $store_ids)
                    ->where('created_at', '>=', $month_begin_time)
                    ->where('merchant_id', $merchant_id)
                    ->where('created_at', '<=', $month_end_time)
                    ->whereIn('pay_status', [1, 3, 6])
                    ->select('total_amount');;


                $refund_month_order_data = RefundOrder::whereBetween('created_at', [$month_begin_time, $month_end_time])
                    ->where('merchant_id', $merchant_id)
                    ->where('store_id', $store_id)
                    ->select('refund_amount');


            } else {
                $month_order_data = Order::whereIn('store_id', $store_ids)
                    ->where('created_at', '>=', $month_begin_time)
                    ->where('created_at', '<=', $month_end_time)
                    ->whereIn('pay_status', [1, 3, 6])
                    ->select('total_amount');


                $refund_month_order_data = RefundOrder::whereBetween('created_at', [$month_begin_time, $month_end_time])
                    ->whereIn('store_id', $store_ids)
                    ->select('refund_amount');
            }

            $month_order = $month_order_data->sum('total_amount');
            $month_amount = '' . $month_order . '';
            $month_count = '' . count($month_order_data->get()). '';

            $refund_month_amount = $refund_month_order_data->sum('refund_amount');
            $refund_month_amount = '' . $refund_month_amount . '';
            $refund_month_count = '' . count($refund_month_order_data->get()) . '';

            //7天
            $data_day = [];
            $data = [1, 2, 3, 4, 5, 6, 7];
            foreach ($data as $k => $v) {
                $day = date("Ymd", time() - 24 * $v * 60 * 60);
                $data_day[$k]['date'] = date("m/d", time() - 24 * $v * 60 * 60);


                if ($merchant_id) {
                    $day_order_data = MerchantStoreDayOrder::where('day', $day)
                        ->where('merchant_id', $merchant_id)
                        ->select('total_amount', 'order_sum');

                } else {
                    $day_order_data = StoreDayOrder::where('day', $day)
                        ->whereIn('store_id', $store_ids);
                }


                $day_order_day = $day_order_data->sum('total_amount');
                $day_order_day = '' . $day_order_day . '';
                $day_order_count_day = '' . $day_order_data->sum('order_sum') . '';
                $data_day[$k]['day_amount'] = number_format($day_order_day, 2, '.', '');
                $data_day[$k]['day_count'] = $day_order_count_day;

            }


            $data = [
                'day_amount' => number_format($day_order, 2, '.', ''),
                'day_count' => $day_order_count,

                'refund_day_amount' => number_format($refund_day_order, 2, '.', ''),
                'refund_day_count' => $refund_day_order_count,


                'month_amount' => number_format($month_amount, 2, '.', ''),
                'month_count' => $month_count,

                'refund_month_amount' => number_format($refund_month_amount, 2, '.', ''),
                'refund_month_count' => $refund_month_count,


                'old_month_amount' => number_format($old_month_amount, 2, '.', ''),
                'old_month_count' => $old_month_count,

                'refund_old_month_amount' => number_format($refund_old_month_amount, 2, '.', ''),
                'refund_old_month_count' => $refund_old_month_count,

                'data_day' => $data_day

            ];

            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);
        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage() . $exception->getLine();
            return $this->format();
        }
    }

    //APP订单轮询接口
    public function order_foreach(Request $request)
    {
        try {

            $store_id = $request->get('store_id', '');
            $out_trade_no = $request->get('out_trade_no', '');
            $ways_type = $request->get('ways_type', '');
            $config_id = $request->get('config_id', '');

            $data = [
                'out_trade_no' => $out_trade_no,
                'store_id' => $store_id,
                'ways_type' => $ways_type,
                'config_id' => $config_id,

            ];
            return $this->order_foreach_public($data);


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage() . $exception->getLine();
            return $this->format();
        }

    }

    //APP订单轮询-公共
    public function order_foreach_public($data)
    {
        try {

            $store_id = $data['store_id'];
            $out_trade_no = isset($data['out_trade_no']) ? $data['out_trade_no'] : "";
            $other_no = isset($data['other_no']) ? $data['other_no'] : "";

            $ways_type = $data['ways_type'];
            $config_id = $data['config_id'];

            if ($out_trade_no) {
                $order = Order::where('out_trade_no', $out_trade_no)->first();
            } else {
                $order = Order::where('other_no', $other_no)->first();
            }

            if (!$order) {
                return json_encode([
                    'status' => 2,
                    'message' => '订单号不存在'
                ]);
            }
            $ways_type = $order->ways_type;
            $out_trade_no = $order->out_trade_no;

            $store = Store::where('store_id', $store_id)
                ->select('config_id', 'merchant_id', 'pid')
                ->first();

            if (!$store) {
                return json_encode([
                    'status' => 2,
                    'message' => '门店ID不存在'
                ]);
            }
            $config_id = $store->config_id;
            $store_pid = $store->pid;


            //支付宝官方订单
            if (999 < $ways_type && $ways_type < 1999) {
                //配置
                $isvconfig = new AlipayIsvConfigController();
                $storeInfo = $isvconfig->alipay_auth_info($store_id, $store_pid);
                $config = $isvconfig->AlipayIsvConfig($config_id);

                $app_auth_token = $storeInfo->app_auth_token;

                $notify_url = url('/api/alipayopen/qr_pay_notify');
                $aop = new AopClient();
                $aop->apiVersion = "2.0";
                $aop->appId = $config->app_id;
                $aop->rsaPrivateKey = $config->rsa_private_key;
                $aop->alipayrsaPublicKey = $config->alipay_rsa_public_key;
                $aop->notify_url = $notify_url;
                $aop->signType = "RSA2";//升级算法
                $aop->gatewayUrl = $config->alipay_gateway;
                $aop->format = "json";
                $aop->charset = "GBK";
                $aop->version = "2.0";
                $aop->method = 'alipay.trade.query';
                $requests = new AlipayTradeQueryRequest();
                $requests->setBizContent("{" .
                    "    \"out_trade_no\":\"" . $out_trade_no . "\"" .
                    "  }");
                $status = $aop->execute($requests, '', $app_auth_token);
                if ($status->alipay_trade_query_response->code == 40004) {
                    return json_encode([
                        'status' => 2,
                        'pay_status' => '3',
                        'message' => $status->alipay_trade_query_response->sub_msg
                    ]);
                }
                //支付成功
                if ($status->alipay_trade_query_response->trade_status == "TRADE_SUCCESS") {
                    //改变数据库状态
                    if ($order->pay_status != 1) {
                        $order->update([
                            'status' => 'TRADE_SUCCESS',
                            'pay_status' => 1,
                            'pay_status_desc' => '支付成功',
                            'buyer_logon_id' => $status->alipay_trade_query_response->buyer_user_id,
                            'trade_no' => $status->alipay_trade_query_response->trade_no,
                            'pay_time' => $status->alipay_trade_query_response->send_pay_date,
                            'buyer_pay_amount' => $status->alipay_trade_query_response->buyer_pay_amount,
                        ]);
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
                            'device_id'=>isset($order->device_id)?$order->device_id:"",

                        ];


                        PaySuccessAction::action($data);


                    }

                    return json_encode([
                        'status' => 1,
                        'pay_status' => '1',
                        'message' => '支付成功',
                        'data' => [
                            'ways_source' => $order->ways_source,
                            'out_trade_no' => $order->out_trade_no,
                            'pay_time' => $status->alipay_trade_query_response->send_pay_date,
                            'pay_amount' => $order->pay_amount,
                            'total_amount' => $order->total_amount,
                        ]
                    ]);

                } //等待付款
                elseif ($status->alipay_trade_query_response->trade_status == "WAIT_BUYER_PAY") {

                    return json_encode([
                        'status' => 1,
                        'pay_status' => '2',
                        'message' => '等待支付',
                        'data' => [
                            'out_trade_no' => $order->out_trade_no,
                            'pay_amount' => $order->pay_amount,
                            'total_amount' => $order->total_amount,
                        ]
                    ]);

                } //订单关闭
                elseif ($status->alipay_trade_query_response->trade_status == 'TRADE_CLOSED') {
                    $order->update([
                        'status' => '4',
                        'pay_status' => 4,
                        'pay_status_desc' => '订单关闭',
                    ]);
                    $order->save();

                    return json_encode([
                        'status' => 1,
                        'pay_status' => '3',
                        'message' => '订单关闭'
                    ]);

                } else {
                    //其他情况
                    $message = $status->alipay_trade_query_response->sub_msg;
                    return json_encode([
                        'status' => 1,
                        'pay_status' => '3',
                        'message' => $message
                    ]);
                }


            }
            //官方微信支付订单
            if (1999 < $ways_type && $ways_type < 2999) {
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
                            'device_id'=>isset($order->device_id)?$order->device_id:"",

                        ];


                        PaySuccessAction::action($data);
                    }

                    return json_encode([
                        'status' => 1,
                        'pay_status' => '1',
                        'message' => '支付成功',
                        'data' => [
                            'ways_source' => $order->ways_source,
                            'pay_time' => date('Y-m-d H:i:s', strtotime($query['time_end'])),
                            'out_trade_no' => $order->out_trade_no,
                            'pay_amount' => $order->pay_amount,
                            'total_amount' => $order->total_amount,

                        ]
                    ]);

                } elseif ($query['trade_state'] == "USERPAYING" || $query['trade_state'] == "NOTPAY") {

                    return json_encode([
                        'status' => 1,
                        'pay_status' => '2',
                        'message' => '等待支付',
                        'data' => [
                            'out_trade_no' => $order->out_trade_no,
                            'pay_amount' => $order->pay_amount,
                            'total_amount' => $order->total_amount,
                        ]
                    ]);


                } elseif ($query['trade_state'] == "CLOSED") {

                    $order->update([
                        'status' => '4',
                        'pay_status' => '4',
                        'pay_status_desc' => '订单关闭',
                    ]);
                    $order->save();


                    return json_encode([
                        'status' => 1,
                        'pay_status' => '3',
                        'message' => '订单关闭',
                        'data' => [
                            'out_trade_no' => $order->out_trade_no,
                            'pay_amount' => $order->pay_amount,
                            'total_amount' => $order->total_amount,
                        ]
                    ]);


                } elseif ($query['trade_state'] == "REVOKED") {

                    $order->update([
                        'status' => '3',
                        'pay_status' => '3',
                        'pay_status_desc' => '订单已撤销',
                    ]);
                    $order->save();


                    return json_encode([
                        'status' => 1,
                        'pay_status' => '3',
                        'message' => '订单关闭',
                        'data' => [
                            'out_trade_no' => $order->out_trade_no,
                            'pay_amount' => $order->pay_amount,
                            'total_amount' => $order->total_amount,
                        ]
                    ]);


                } else {

                    //其他情况
                    $message = $query['trade_state_desc'];
                    return json_encode([
                        'status' => 1,
                        'pay_status' => '3',
                        'message' => $message
                    ]);
                }

            }
            //京东收银支付
            if (5999 < $ways_type && $ways_type < 6999) {
                //读取配置
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
                $obj = new \App\Api\Controllers\Jd\PayController();
                $data = [];
                $data['out_trade_no'] = $out_trade_no;
                $data['request_url'] = $obj->order_query_url;//请求地址;
                $data['merchant_no'] = $jd_merchant->merchant_no;
                $data['md_key'] = $jd_merchant->md_key;//
                $data['des_key'] = $jd_merchant->des_key;//
                $data['systemId'] = $jd_config->systemId;//
                $return = $obj->order_query($data);
                //支付成功
                if ($return["status"] == 1) {
                    $pay_time = date('Y-m-d H:i:s', strtotime($return['data']['payFinishTime']));
                    //改变数据库状态
                    if ($order->pay_status != 1) {
                        $trade_no = $return['data']['tradeNo'];
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
                            'device_id'=>isset($order->device_id)?$order->device_id:"",

                        ];


                        PaySuccessAction::action($data);

                    }

                    return json_encode([
                        'status' => 1,
                        'pay_status' => '1',
                        'message' => '支付成功',
                        'data' => [
                            'ways_source' => $order->ways_source,
                            'pay_time' => $pay_time,
                            'out_trade_no' => $order->out_trade_no,
                            'pay_amount' => $order->pay_amount,
                            'total_amount' => $order->total_amount,
                        ]
                    ]);

                } //等待付款
                elseif ($return["status"] == 2) {

                    return json_encode([
                        'status' => 1,
                        'pay_status' => '2',
                        'message' => '等待支付',
                        'data' => [
                            'out_trade_no' => $order->out_trade_no,
                            'pay_amount' => $order->pay_amount,
                            'total_amount' => $order->total_amount,
                        ]
                    ]);

                } //订单失败关闭
                elseif ($return["status"] == 3) {
                    return json_encode([
                        'status' => 1,
                        'pay_status' => '3',
                        'message' => '订单支付失败'
                    ]);

                }//订单退款
                elseif ($return["status"] == 4) {
                    return json_encode([
                        'status' => 1,
                        'pay_status' => '3',
                        'message' => '订单已经退款'
                    ]);

                } else {
                    //其他情况
                    $message = $return['message'];
                    return json_encode([
                        'status' => 1,
                        'pay_status' => '3',
                        'message' => $message
                    ]);
                }


            }

            //网商银行
            if (2999 < $ways_type && $ways_type < 3999) {
                //读取配置
                $MyBankobj = new MyBankConfigController();
                $MyBankConfig = $MyBankobj->MyBankConfig($config_id);
                if (!$MyBankConfig) {
                    return json_encode([
                        'status' => 2,
                        'message' => '网商配置不存在请检查配置'
                    ]);
                }

                $mybank_merchant = $MyBankobj->mybank_merchant($store_id, $store_pid);
                if (!$mybank_merchant) {
                    return json_encode([
                        'status' => 2,
                        'message' => '网商商户号不存在'
                    ]);
                }
                $MerchantId = $mybank_merchant->MerchantId;
                $obj = new TradePayController();
                $return = $obj->mybankOrderQuery($MerchantId, $config_id, $out_trade_no);
                if ($return['status'] == 0) {
                    return json_encode([
                        'status' => 2,
                        'message' => $return['message']
                    ]);
                }
                $body = $return['data']['document']['response']['body'];
                $TradeStatus = $body['TradeStatus'];

                //成功
                if ($TradeStatus == 'succ') {
                    $OrderNo = $body['MerchantOrderNo'];
                    $GmtPayment = $body['GmtPayment'];
                    $buyer_id = '';
                    if ($ways_type == 3004) {
                        $buyer_id = $body['SubOpenId'];
                    }
                    if ($ways_type == 3003) {
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
                            'device_id'=>isset($order->device_id)?$order->device_id:"",

                        ];


                        PaySuccessAction::action($data);
                    }

                    return json_encode([
                        'status' => 1,
                        'pay_status' => '1',
                        'message' => '支付成功',
                        'data' => [
                            'ways_source' => $order->ways_source,
                            'pay_time' => $pay_time,
                            'out_trade_no' => $order->out_trade_no,
                            'pay_amount' => $order->pay_amount,
                            'total_amount' => $order->total_amount,

                        ]
                    ]);

                } elseif ($TradeStatus == 'paying') {

                    return json_encode([
                        'status' => 1,
                        'pay_status' => '2',
                        'message' => '等待支付',
                        'data' => [
                            'out_trade_no' => $order->out_trade_no,
                            'pay_amount' => $order->pay_amount,
                            'total_amount' => $order->total_amount,
                        ]
                    ]);


                } else {
                    //其他情况
                    $message = '请重新扫码';
                    return json_encode([
                        'status' => 1,
                        'pay_status' => '3',
                        'message' => $message
                    ]);
                }


            }

            //新大陆
            if (7999 < $ways_type && $ways_type < 8999) {
                //读取配置
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
                    'key' => $mybank_merchant->nl_key,
                    'org_no' => $new_land_config->org_no,
                    'merc_id' => $mybank_merchant->nl_mercId,
                    'trm_no' => $mybank_merchant->trmNo,
                    'op_sys' => '3',
                    'opr_id' => $store->merchant_id,
                    'trm_typ' => 'T',
                ];
                $obj = new PayController();
                $return = $obj->order_query($request_data);
                //支付成功
                if ($return["status"] == 1) {
                    $pay_time = date('Y-m-d H:i:s', strtotime($return['data']['sysTime']));
                    //改变数据库状态
                    if ($order->pay_status != 1) {
                        $trade_no = $return['data']['orderNo'];
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
                            'device_id'=>isset($order->device_id)?$order->device_id:"",

                        ];


                        PaySuccessAction::action($data);

                    }

                    return json_encode([
                        'status' => 1,
                        'pay_status' => '1',
                        'message' => '支付成功',
                        'data' => [
                            'ways_source' => $order->ways_source,
                            'pay_time' => $pay_time,
                            'out_trade_no' => $order->out_trade_no,
                            'pay_amount' => $order->pay_amount,
                            'total_amount' => $order->total_amount,
                        ]
                    ]);

                } //等待付款
                elseif ($return["status"] == 2) {

                    return json_encode([
                        'status' => 1,
                        'pay_status' => '2',
                        'message' => '等待支付',
                        'data' => [
                            'out_trade_no' => $order->out_trade_no,
                            'pay_amount' => $order->pay_amount,
                            'total_amount' => $order->total_amount,
                        ]
                    ]);

                } //订单失败关闭
                elseif ($return["status"] == 3) {
                    return json_encode([
                        'status' => 1,
                        'pay_status' => '3',
                        'message' => '订单支付失败'
                    ]);

                }//订单退款
                elseif ($return["status"] == 4) {
                    return json_encode([
                        'status' => 1,
                        'pay_status' => '3',
                        'message' => '订单已经退款'
                    ]);

                } else {
                    //其他情况
                    $message = $return['message'];
                    return json_encode([
                        'status' => 1,
                        'pay_status' => '3',
                        'message' => $message
                    ]);
                }


            }

            //和融通收银支付
            if (8999 < $ways_type && $ways_type < 9999) {
                //读取配置
                $config = new HConfigController();
                $h_config = $config->h_config($config_id);
                if (!$h_config) {
                    return json_encode([
                        'status' => 2,
                        'message' => '和融通不存在请检查配置'
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
                $data = [];
                $data['out_trade_no'] = $out_trade_no;
                $data['request_url'] = $obj->order_query_url;//请求地址;
                $data['md_key'] = $h_config->md_key;//
                $data['mid'] = $h_merchant->h_mid;//
                $data['orgNo'] = $h_config->orgNo;//

                $return = $obj->order_query($data);

                //支付成功
                if ($return["status"] == 1) {
                    $pay_time = date('Y-m-d H:i:s', strtotime($return['data']['timeEnd']));

                    //改变数据库状态
                    if ($order->pay_status != 1) {
                        $trade_no = '112121' . $return['data']['transactionId'];
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
                            'device_id'=>isset($order->device_id)?$order->device_id:"",

                        ];


                        PaySuccessAction::action($data);

                    }

                    return json_encode([
                        'status' => 1,
                        'pay_status' => '1',
                        'message' => '支付成功',
                        'data' => [
                            'out_trade_no' => $order->out_trade_no,
                            'ways_source' => $order->ways_source,
                            'pay_time' => $pay_time,
                            'pay_amount' => $order->pay_amount,
                            'total_amount' => $order->total_amount,
                        ]
                    ]);

                } //等待付款
                elseif ($return["status"] == 2) {

                    return json_encode([
                        'status' => 1,
                        'pay_status' => '2',
                        'message' => '等待支付',
                        'data' => [
                            'out_trade_no' => $order->out_trade_no,
                            'pay_amount' => $order->pay_amount,
                            'total_amount' => $order->total_amount,
                        ]
                    ]);

                } //订单失败关闭
                elseif ($return["status"] == 3) {
                    return json_encode([
                        'status' => 1,
                        'pay_status' => '3',
                        'message' => '订单支付失败'
                    ]);

                }//订单退款
                elseif ($return["status"] == 4) {
                    return json_encode([
                        'status' => 1,
                        'pay_status' => '3',
                        'message' => '订单已经退款'
                    ]);

                } else {
                    //其他情况
                    $message = $return['message'];
                    return json_encode([
                        'status' => 1,
                        'pay_status' => '3',
                        'message' => $message
                    ]);
                }


            }


            //ltf收银支付
            if (9999 < $ways_type && $ways_type < 19999) {
                //读取配置
                $config = new LtfConfigController();
                $ltf_merchant = $config->ltf_merchant($store_id, $store_pid);
                if (!$ltf_merchant) {
                    return json_encode([
                        'status' => 2,
                        'message' => '商户号不存在'
                    ]);
                }
                $obj = new \App\Api\Controllers\Ltf\PayController();
                $data = [];
                $data['out_trade_no'] = $out_trade_no;
                $data['request_url'] = $obj->order_query_url;//请求地址;
                $data['merchant_no'] = $ltf_merchant->merchantCode;
                $data['appId'] = $ltf_merchant->appId;//
                $data['key'] = $ltf_merchant->md_key;//

                $return = $obj->order_query($data);
                //支付成功
                if ($return["status"] == 1) {
                    $pay_time = date('Y-m-d H:i:s', strtotime($return['data']['payTime']));
                    //改变数据库状态
                    if ($order->pay_status != 1) {
                        $trade_no = $return['data']['outTransactionId'];
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
                            'source_desc' => '联拓富',//返佣来源说明
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
                            'device_id'=>isset($order->device_id)?$order->device_id:"",

                        ];


                        PaySuccessAction::action($data);

                    }

                    return json_encode([
                        'status' => 1,
                        'pay_status' => '1',
                        'message' => '支付成功',
                        'data' => [
                            'ways_source' => $order->ways_source,
                            'pay_time' => $pay_time,
                            'out_trade_no' => $order->out_trade_no,
                            'pay_amount' => $order->pay_amount,
                            'total_amount' => $order->total_amount,
                        ]
                    ]);

                } //等待付款
                elseif ($return["status"] == 2) {

                    return json_encode([
                        'status' => 1,
                        'pay_status' => '2',
                        'message' => '等待支付',
                        'data' => [
                            'out_trade_no' => $order->out_trade_no,
                        ]
                    ]);

                } //订单失败关闭
                elseif ($return["status"] == 3) {
                    return json_encode([
                        'status' => 1,
                        'pay_status' => '3',
                        'message' => '订单支付失败'
                    ]);

                }//订单退款
                elseif ($return["status"] == 4) {
                    return json_encode([
                        'status' => 1,
                        'pay_status' => '3',
                        'message' => '订单已经退款'
                    ]);

                } else {
                    //其他情况
                    $message = $return['message'];
                    return json_encode([
                        'status' => 1,
                        'pay_status' => '3',
                        'message' => $message
                    ]);
                }


            }


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage() . $exception->getLine();
            return $this->format();
        }

    }

    //花呗订单轮询
    public function hb_order_foreach(Request $request)
    {
        try {
            $store_id = $request->get('store_id', '');
            $out_trade_no = $request->get('out_trade_no', '');
            $ways_type = $request->get('ways_type', '');
            $config_id = $request->get('config_id', '');

            if (999 < $ways_type && $ways_type < 1999) {
                $config = AlipayIsvConfig::where('config_id', $config_id)
                    ->where('config_type', '01')
                    ->first();

                $storeInfo = AlipayAppOauthUsers::where('store_id', $store_id)
                    ->select('app_auth_token')
                    ->first();
                $app_auth_token = $storeInfo->app_auth_token;

                $notify_url = url('/api/alipayopen/qr_pay_notify');
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
                $aop->method = 'alipay.trade.query';
                $requests = new AlipayTradeQueryRequest();
                $requests->setBizContent("{" .
                    "    \"out_trade_no\":\"" . $out_trade_no . "\"" .
                    "  }");
                $status = $aop->execute($requests, '', $app_auth_token);

                //支付成功
                if ($status->alipay_trade_query_response->trade_status == "TRADE_SUCCESS") {
                    //改变数据库状态
                    AlipayHbOrder::where('out_trade_no', $out_trade_no)->update(
                        [
                            'trade_no' => $status->alipay_trade_query_response->trade_no,
                            'buyer_logon_id' => $status->alipay_trade_query_response->buyer_user_id,
                            'pay_status_desc' => '支付成功',
                            'pay_status' => 1,
                        ]);


                    return json_encode([
                        'status' => 1,
                        'pay_status' => '1',
                        'message' => '支付成功'
                    ]);

                } //等待付款
                elseif ($status->alipay_trade_query_response->trade_status == "WAIT_BUYER_PAY") {

                    return json_encode([
                        'status' => 1,
                        'pay_status' => '2',
                        'message' => '等待支付'
                    ]);

                } //订单关闭
                elseif ($status->alipay_trade_query_response->trade_status == 'TRADE_CLOSED') {
                    return json_encode([
                        'status' => 1,
                        'pay_status' => '3',
                        'message' => '订单关闭'
                    ]);

                } else {
                    //其他情况
                    $message = $status->alipay_trade_query_response->sub_msg;
                    return json_encode([
                        'status' => 1,
                        'pay_status' => '3',
                        'message' => $message
                    ]);
                }


            }


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage() . $exception->getLine();
            return $this->format();
        }

    }


    //小程序首页的数据
    public function weixinapp_index_count(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $merchant_id = $merchant->merchant_id;
            $store_ids = [];
            $where = [];
            //收银员
            if ($merchant->merchant_type == 2) {
                $where[] = ['merchant_id', '=', $merchant_id];
            }
            $MerchantStore = MerchantStore::where('merchant_id', $merchant_id)
                ->select('store_id')
                ->get();

            if (!$MerchantStore->isEmpty()) {
                $store_ids = $MerchantStore->toArray();
            }

            //今日
//            $day = date('Ymd', time());
//
//            //收银员
//            if ($merchant->merchant_type == 2) {
//                $day_order_data = MerchantStoreDayOrder::where('day', $day)
//                    ->whereIn('store_id', $store_ids)
//                    ->where($where)
//                    ->select('total_amount', 'order_sum');
//
//            } else {
//                $day_order_data = StoreDayOrder::where('day', $day)
//                    ->whereIn('store_id', $store_ids)
//                    ->select('total_amount', 'order_sum');
//            }
//
//
//            $day_order = $day_order_data->sum('total_amount');
//            $day_order = '' . $day_order . '';
//            $day_order_count = '' . $day_order_data->sum('order_sum') . '';


            //下面需要优化 读取动态的


            $time_start = date('Y-m-d 00:00:00', time());

            $time_end = date('Y-m-d H:i:s', time());

            $order_data = Order::whereIn('store_id', $store_ids)
                ->where('created_at', '>=', $time_start)
                ->where('created_at', '<=', $time_end)
                ->whereIn('pay_status', [1, 6])
                ->select('total_amount', 'id');

            $day_order = $order_data->sum('total_amount');//交易金额
            $day_order_count = $order_data->count('id');

            //列表

            $order = Order::where($where)
                ->whereIn('store_id', $store_ids)
                ->whereIn('pay_status', [1])
                ->orderBy('updated_at', 'desc')
                ->select('out_trade_no', 'ways_source', 'ways_source_desc', 'updated_at', 'total_amount')
                ->first();

            if (!$order) {
                $order = '';
            }
            $data = [
                'day_order' => $day_order,
                'day_order_count' => $day_order_count,
                'order_list' => $order
            ];
            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);

        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage() . $exception->getLine();
            return $this->format();
        }
    }


}