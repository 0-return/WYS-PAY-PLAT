<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/9/6
 * Time: 下午7:17
 *///自助收银设备

namespace App\Api\Controllers\Self;


use Alipayopen\Sdk\AopClient;
use Alipayopen\Sdk\Request\AlipayTradeQueryRequest;
use App\Api\Controllers\Config\WeixinConfigController;
use App\Common\MerchantFuwu;
use App\Common\PaySuccessAction;
use App\Common\StoreDayMonthOrder;
use App\Common\UserGetMoney;
use App\Models\AlipayAppOauthUsers;
use App\Models\AlipayIsvConfig;
use App\Models\Order;
use App\Models\Store;
use EasyWeChat\Factory;
use function EasyWeChat\Kernel\Support\get_client_ip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MyBank\Tools;

class IndexController extends BaseController
{


    //自助收银设备扫一扫收款
    public function scan_pay(Request $request)
    {
        try {
            //获取请求参数
            //  $data = $request->getContent();
            //  $data = json_decode($data, true);

            $data = $request->all();
            Log::info('支付接口');
            Log::info($data);

            //验证签名
            $check = $this->check_md5($data);
            if ($check['return_code'] == 'FALL') {
                return $this->return_data($check);
            }


//            $check_data = [
//                'store_id'=>'门店ID',
//                'total_amount' => '订单金额',
//                'pay_amount'=>'应付金额',
//                'code' => '付款码编号',
//            ];
//
//            $check = $this->check_required($data, $check_data);
//            if ($check) {
//                $err = [
//                    'return_code' => 'FALL',
//                    'return_msg' =>$check,
//                ];
//                return $this->return_data($err);
//
//            }

            //校验支付是否符合规则
            if (!isset($data['auth_code'])) {
                //
                $re_data['return_code'] = 'SUCCESS';//SUCCESS/FALL 此字段是通信标识，非交易标识，交易是否成功需要查看result_code来判断
                $re_data['return_msg'] = null;
                $re_data['result_code'] = 'SUCCESS';
                $re_data['result_msg'] = '校验成功';
                return $this->return_data($re_data);

            }


            //调用系统前参数
            $ro_data = [
                'store_id' => $data['store_id'],
                'code' => $data['auth_code'],
                'total_amount' => $data['total_amount'] / 100,
                'pay_amount' => $data['pay_amount'] / 100,//需要付款金额
                'shop_price' => $data['total_amount'] / 100,
                'remark' => '',
                'device_id' => $data['device_id'],
                'shop_name' => '商品-扫一扫',
                'shop_desc' => '商品-扫一扫',
            ];

            //发起交易
            $order = new TradepayTwoController();
            $tra_data = $order->scan_pay($ro_data);
            $tra_data_arr = json_decode($tra_data, true);


            $re_data = [
                'device_id' => $data['device_id'],
                'device_type' => $data['device_type'],
                'return_code' => 'SUCCESS',//SUCCESS/FALL 此字段是通信标识，非交易标识，交易是否成功需要查看result_code来判断
                'return_msg' => null,
                'result_code' => '',
                'result_msg' => '',
                'out_trade_no' => '',
                'trade_no' => '',
                'total_amount' => $data['total_amount'],
                'pay_amount' => $data['pay_amount'],
                'pay_time' => '',

                'number' => '',//会员卡
                'name' => '',//会员卡名称
                'integral' => '100',//积分
                'new_integral' => '10',//新增积分
                'mdiscount_amount'=>'',//
                'discount_amount'=>'',//
            ];


            $num = rand(100, 999);//取餐编号
            $re_data['tm_number'] = $num;


            //用户支付成功
            if ($tra_data_arr['status'] == 1) {
                $re_data['result_code'] = 'SUCCESS';
                $re_data['result_msg'] = '支付成功';
                $re_data['buyer_logon_id'] = $tra_data_arr['data']['buyer_logon_id'];
                $re_data['out_trade_no'] = $tra_data_arr['data']['out_trade_no'];
                $re_data['trade_no'] = $tra_data_arr['data']['trade_no'];
                $re_data['pay_time'] = $tra_data_arr['data']['pay_time'];
                $re_data['buyer_pay_amount'] = "" . $tra_data_arr['data']['buyer_pay_amount'] . "";
                $re_data['mdiscount_amount'] = "" . $tra_data_arr['data']['mdiscount_amount'] . "";
                $re_data['discount_amount'] = "" . $tra_data_arr['data']['discount_amount'] . "";
                $re_data['ways_type'] = $tra_data_arr['data']['ways_type'];
                $re_data['ways_type_desc'] = $tra_data_arr['data']['ways_type_desc'];


            } elseif ($tra_data_arr['status'] == 9) {
                //正在支付
                $re_data['result_code'] = 'USERPAYING';
                $re_data['result_msg'] = '用户正在支付';
                $re_data['out_trade_no'] = $tra_data_arr['data']['out_trade_no'];
            } else {
                //其他错误
                $re_data['result_code'] = 'FALL';
                $re_data['result_msg'] = $tra_data_arr['message'];
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


    //自助收银支付宝设备刷脸支付收款
    public function face_pay(Request $request)
    {
        try {
            $data = $request->all();
            //验证签名
            $check = $this->check_md5($data);
            if ($check['return_code'] == 'FALL') {
                return $this->return_data($check);
            }

            //调用系统前参数
            $ro_data = [
                'face_type' => 'alipay',
                'store_id' => $data['store_id'],
                'ftoken' => $data['ftoken'],
                'total_amount' => $data['total_amount'] / 100,//分-元
                'pay_amount' => $data['pay_amount'] / 100,//需要付款金额
                'shop_price' => $data['total_amount'] / 100,
                'remark' => '',
                'device_id' => $data['device_id'],
                'shop_name' => '刷脸支付-有梦想科技',
                'shop_desc' => '刷脸支付-有梦想科技',
            ];

            //发起交易
            $order = new TradepayTwoController();
            $tra_data = $order->face_pay($ro_data);
            $tra_data_arr = json_decode($tra_data, true);


            $re_data = [
                'device_id' => $data['device_id'],
                'device_type' => $data['device_type'],
                'return_code' => 'SUCCESS',//SUCCESS/FALL 此字段是通信标识，非交易标识，交易是否成功需要查看result_code来判断
                'return_msg' => null,
                'result_code' => '',
                'result_msg' => '',
                'out_trade_no' => '',
                'trade_no' => '',
                'total_amount' => $data['total_amount'],
                'pay_amount' => $data['pay_amount'],
                'pay_time' => '',
                'number' => '',//会员卡
                'name' => '',//会员卡名称
                'integral' => '100',//积分
                'new_integral' => '10',//新增积分
                'mdiscount_amount'=>'',//
                'discount_amount'=>'',//
            ];


            $num = rand(100, 999);//取餐编号
            $re_data['tm_number'] = $num;

            //用户支付成功
            if ($tra_data_arr['status'] == 1) {
                $re_data['result_code'] = 'SUCCESS';
                $re_data['result_msg'] = '支付成功';
                $re_data['buyer_logon_id'] = $tra_data_arr['data']['buyer_logon_id'];
                $re_data['out_trade_no'] = $tra_data_arr['data']['out_trade_no'];
                $re_data['trade_no'] = $tra_data_arr['data']['trade_no'];
                $re_data['pay_time'] = $tra_data_arr['data']['pay_time'];
                $re_data['buyer_pay_amount'] = "" . $tra_data_arr['data']['buyer_pay_amount'] . "";
                $re_data['mdiscount_amount'] = "" . $tra_data_arr['data']['mdiscount_amount'] . "";
                $re_data['discount_amount'] = "" . $tra_data_arr['data']['discount_amount'] . "";
                $re_data['ways_type'] = $tra_data_arr['data']['ways_type'];
                $re_data['ways_type_desc'] = $tra_data_arr['data']['ways_type_desc'];

            } elseif ($tra_data_arr['status'] == 9) {
                //正在支付
                $re_data['result_code'] = 'USERPAYING';
                $re_data['result_msg'] = '用户正在支付';
                $re_data['out_trade_no'] = $tra_data_arr['data']['out_trade_no'];
            } else {
                //其他错误
                $re_data['result_code'] = 'FALL';
                $re_data['result_msg'] = $tra_data_arr['message'];
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


    //自助收银微信设备刷脸支付收款
    public function wxface_pay(Request $request)
    {
        try {
            $data = $request->all();
            //验证签名
            $check = $this->check_md5($data);
            if ($check['return_code'] == 'FALL') {
                return $this->return_data($check);
            }

            $store_id = $data['store_id'];
            $store = Store::where('store_id', $store_id)->first();
            $config_id = $store->config_id;
            $merchant_id = $store->merchant_id;
            $merchant_name = '';
            $store_name = $store->store_name;
            $store_pid = $store->pid;
            $tg_user_id = $store->user_id;
            $remark = "";
            $out_trade_no = $data['out_trade_no'];
            $total_amount = $data['pay_amount'];
            $pay_amount = $total_amount;//单位 分
            $device_id = $data['device_id'];


            $config = new WeixinConfigController();
            $options = $config->weixin_config($config_id);
            $weixin_store = $config->weixin_merchant($store_id, $store_pid);
            if (!$weixin_store) {
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '微信商户号不存在',
                ];
                return $this->return_data($err);
            }
            $wx_sub_merchant_id = $weixin_store->wx_sub_merchant_id;
            //公共配置
            $config = [
                'appid' => 'wx94b87b679e8677aa',//'wx2b029c08a6232582',//$options['app_id'],
                "mch_id" => '1494729062',//$options['payment']['merchant_id'],
                "version" => "1",
                "sign_type" => 'MD5',
                "nonce_str" => '' . time() . '',
                "body" => '刷脸购物-' . $store_name,
                "out_trade_no" => $data['out_trade_no'],
                "total_fee" => $data['pay_amount'],
                "spbill_create_ip" => get_client_ip(),
                "openid" => $data['open_id'],
                "face_code" => $data['face_code'],
            ];
            $useCert = false;
            //子商户
            if ($wx_sub_merchant_id) {
                $config['sub_mch_id'] = '1518150321';
            }

            $obj = new WxBaseController();
            $key ='sahadiiPPKh2209373757hhffrrrfhjk'; //$options['payment']['key'];



            $config['sign'] = $obj->MakeSign($config, $key);
            $xml = $obj->ToXml($config);
            $url = $obj->facepay_url;
            Log::info('请求');
            Log::info($xml);
            $return_data = $obj::postXmlCurl($config, $xml, $url, $useCert, $second = 30);
            Log::info($return_data);
            $return_data = $obj::xml_to_array($return_data);

            Log::info($config);

            Log::info($return_data);

            $re_data = [
                'device_id' => $data['device_id'],
                'device_type' => $data['device_type'],
                'return_code' => 'SUCCESS',//SUCCESS/FALL 此字段是通信标识，非交易标识，交易是否成功需要查看result_code来判断
                'return_msg' => null,
                'result_code' => '',
                'result_msg' => '',
                'out_trade_no' => '',
                'trade_no' => '',
                'total_amount' => $data['total_amount'],
                'pay_amount' => $data['pay_amount'],
                'pay_time' => '',

                'number' => '',//会员卡
                'name' => '',//会员卡名称
                'integral' => '100',//积分
                'new_integral' => '10',//新增积分
                'mdiscount_amount'=>'0',//
                'discount_amount'=>'0',//
            ];

            //插入数据库
            $data_insert = [
                'out_trade_no' => $out_trade_no,
                'trade_no' => '',
                'user_id' => $tg_user_id,
                'store_id' => $store_id,
                'store_name' => $store_name,
                'buyer_id' => '',
                'total_amount' => $total_amount,
                'pay_amount' => $pay_amount,
                'shop_price' => $pay_amount,
                'payment_method' => '',
                'status' => '',
                'pay_status' => 2,
                'pay_status_desc' => '等待支付',
                'merchant_id' => $merchant_id,
                'merchant_name' => $merchant_name,
                'remark' => $remark,
                'device_id' => $device_id,
                'config_id' => $config_id,
                'ways_type' => 2008,
                'ways_type_desc' => '微信刷脸支付',
                'ways_source' => 'weixin',
                'ways_source_desc' => '微信支付',
                'rate'=>'0.00'//费率

            ];
            //入库
            $insert_re = Order::create($data_insert);

            if (!$insert_re) {
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '订单未入库',
                ];
                return $this->return_data($err);
            }

            //请求状态
            if ($return_data['return_code'] == 'SUCCESS') {
                //支付成功
                if ($return_data['result_code'] == 'SUCCESS') {
                    Order::where('out_trade_no', $out_trade_no)->update(
                        [
                            'trade_no' => $return_data['transaction_id'],
                            'buyer_id' => $return_data['openid'],
                            'buyer_logon_id' => $return_data['openid'],
                            'status' => 1,
                            'pay_status_desc' => '支付成功',
                            'pay_status' => 1,
                            'payment_method' => $return_data['bank_type'],
                            'buyer_pay_amount' => $total_amount / 100,
                        ]);

                    //支付成功后的动作
                    $data = [
                        'ways_type' => $data_insert['ways_type'],
                        'ways_type_desc' => $data_insert['ways_type_desc'],
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
                        'ways_source'=>$data_insert['ways_source'],

                    ];


                    PaySuccessAction::action($data);



                    $re_data['result_code'] = 'SUCCESS';
                    $re_data['result_msg'] = '支付成功';
                    $re_data['buyer_logon_id'] = $return_data['openid'];
                    $re_data['out_trade_no'] = $out_trade_no;
                    $re_data['trade_no'] = $return_data['transaction_id'];
                    $re_data['pay_time'] = $return_data['time_end'];
                    $re_data['buyer_pay_amount'] = "" . $total_amount . "";
                    $re_data['mdiscount_amount'] = "0";
                    $re_data['discount_amount'] = "0";
                    $re_data['ways_type'] = '2008';
                    $re_data['ways_type_desc'] = '微信支付';

                } else {
                    if ($return_data['err_code'] == "USERPAYING") {
                        //正在支付
                        $re_data['result_code'] = 'USERPAYING';
                        $re_data['result_msg'] = '用户正在支付';
                        $re_data['out_trade_no'] = $out_trade_no;

                    } else {
                        $msg = $return_data['err_code_des'];//错误信息
                        //其他错误
                        $re_data['result_code'] = 'FALL';
                        $re_data['result_msg'] = $msg;
                    }
                }

            } else {
                //其他错误
                $re_data['result_code'] = 'FALL';
                $re_data['result_msg'] = $return_data['return_msg'];
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


    //自助收银设备查询订单号状态
    public function order_query(Request $request)
    {

        try {
            //获取请求参数
            //  $data = $request->getContent();
            //  $data = json_decode($data, true);


            $data = $request->all();


            //验证签名
            $check = $this->check_md5($data);
            if ($check['return_code'] == 'FALL') {
                return $this->return_data($check);
            }


            //调用系统前参数

            $where = [];
            if (isset($data['out_trade_no'])) {
                $where[] = ['out_trade_no', '=', $data['out_trade_no']];
            }
            if (isset($data['trade_no'])) {
                $where[] = ['trade_no', '=', $data['trade_no']];
            }


            //发起查询
            $order = Order::where('store_id', $data['store_id'])
                ->where($where)
                // ->select('out_trade_no','','', 'config_id', 'trade_no', 'total_amount', 'ways_type')//2.0 ways_type 1.0 type
                ->first();

            //如果订单号为空或者不存在
            if (!$order) {
                $re_data['result_code'] = 'FALL';
                $re_data['result_msg'] = '订单号不存在';
                return $this->return_data($re_data);
            }


            $re_data = [
                'return_code' => 'SUCCESS',//SUCCESS/FALL 此字段是通信标识，非交易标识，交易是否成功需要查看result_code来判断
                'return_msg' => null,
                'device_id' => $data['device_id'],
                'device_type' => $data['device_type'],
                'result_code' => '',
                'result_msg' => '',
                'out_trade_no' => '',
                'trade_no' => '',
                'pay_time' => '',
                'pay_amount' => "" . ($order->pay_amount * 100) . "",

                'number' => '',//会员卡
                'name' => '',//会员卡名称
                'integral' => '100',//积分
                'new_integral' => '10',//新增积分
                'mdiscount_amount'=>'0',//
                'discount_amount'=>'0',//
            ];


            $num = rand(100, 999);//取餐编号
            $re_data['tm_number'] = $num;


            $ways_type = $order->ways_type;
            $store = Store::where('store_id', $data['store_id'])
                ->select('config_id', 'merchant_id', 'pid')
                ->first();
            $config_id = $store->config_id;
            $store_id = $data['store_id'];
            $store_pid = $store->pid;

            //官方支付宝查询-扫一扫-刷脸支付
            if ((int)$ways_type == 1001 || (int)$ways_type == 1008) {
                $config = AlipayIsvConfig::where('config_id', $config_id)
                    ->where('config_type', '01')
                    ->first();

                //获取token
                $storeInfo = AlipayAppOauthUsers::where('store_id', $data['store_id'])
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
                    $re_data['total_amount'] = "" . ($order->total_amount * 100) . "";
                    $re_data['trade_no'] = $status->alipay_trade_query_response->trade_no;
                    $re_data['pay_time'] = date('Ymdhis', strtotime($status->alipay_trade_query_response->send_pay_date));
                    $re_data['buyer_pay_amount'] = "" . ($status->alipay_trade_query_response->buyer_pay_amount * 100) . "";
                    $re_data['buyer_logon_id'] = $status->alipay_trade_query_response->buyer_logon_id;
                    $re_data['ways_type'] = $order->ways_type;
                    $re_data['ways_type_desc'] = $order->ways_type_desc;


                    //优惠信息
                    $mdiscount_amount = 0;
                    $discount_amount = 0;

                    if (isset($status->alipay_trade_query_response->mdiscount_amount)) {
                        $mdiscount_amount = $status->alipay_trade_query_response->mdiscount_amount * 100;

                    }

                    if (isset($status->alipay_trade_query_response->discount_amount)) {
                        $discount_amount = $status->alipay_trade_query_response->discount_amount * 100;
                    }
                    $re_data['mdiscount_amount'] = "" . $mdiscount_amount . "";
                    $re_data['discount_amount'] = "" . $discount_amount . "";

                    if ($order->pay_status != 1) {
                        //改变数据库状态
                        $order->status = 'TRADE_SUCCESS';
                        $order->pay_status = 1;
                        $order->pay_status_desc = '支付成功';
                        $order->buyer_logon_id = $status->alipay_trade_query_response->buyer_user_id;
                        $order->trade_no = $status->alipay_trade_query_response->trade_no;
                        $order->pay_time = $status->alipay_trade_query_response->send_pay_date;
                        $order->buyer_pay_amount = $status->alipay_trade_query_response->buyer_pay_amount;

                        $order->save();
                    }

                    return $this->return_data($re_data);

                } //等待付款
                elseif ($status->alipay_trade_query_response->trade_status == "WAIT_BUYER_PAY") {
                    $re_data['result_code'] = 'USERPAYING';
                    $re_data['result_msg'] = '等待用户付款';
                    $re_data['out_trade_no'] = $order->out_trade_no;
                    return $this->return_data($re_data);

                } //订单关闭
                elseif ($status->alipay_trade_query_response->trade_status == 'TRADE_CLOSED') {
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = '订单关闭';
                    $re_data['total_amount'] = "" . ($order->total_amount * 100) . "";
                    $re_data['out_trade_no'] = $order->out_trade_no;
                    return $this->return_data($re_data);

                } else {
                    //其他情况
                    $message = $status->alipay_trade_query_response->sub_msg;
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;
                    $re_data['out_trade_no'] = $order->out_trade_no;

                    return $this->return_data($re_data);
                }


            }

            //微信查询-扫一扫-刷脸支付
            if ((int)$ways_type == 2001 || (int)$ways_type == 2008) {

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
                    $re_data['total_amount'] = "" . ($order->total_amount * 100) . "";
                    $re_data['trade_no'] = $query['transaction_id'];
                    $re_data['pay_time'] = date('Ymdhis', strtotime($query['time_end']));
                    $re_data['buyer_pay_amount'] ="" . ($order->total_amount * 100) . "";
                    $re_data['buyer_logon_id'] = $query['openid'];
                    $re_data['ways_type'] = $order->ways_type;
                    $re_data['ways_type_desc'] = $order->ways_type_desc;


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
                            'source_desc' =>'微信支付',//返佣来源说明
                            'total_amount' => $order->total_amount,
                            'out_trade_no' => $order->out_trade_no,
                            'rate' => $order->rate,
                            'merchant_id' => $order->merchant_id,
                            'store_id' => $order->store_id,
                            'user_id' => $order->user_id,
                            'config_id' => $config_id,
                            'store_name' => $order->store_name,
                            'ways_source'=> $order->ways_source,
                            'pay_time'=>date('Y-m-d H:i:s', strtotime($query['time_end'])),

                        ];


                        PaySuccessAction::action($data);

                    }

                } elseif ($query['trade_state'] == "USERPAYING") {
                    $re_data['result_code'] = 'USERPAYING';
                    $re_data['result_msg'] = '等待用户付款';
                    $re_data['out_trade_no'] = $order->out_trade_no;
                    return $this->return_data($re_data);


                } else {
                    //其他情况
                    $message = $query['trade_state_desc'];
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $message;
                    $re_data['out_trade_no'] = $order->out_trade_no;
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


    public
    function initialization(Request $request)
    {


        $data = [
            'merchantId' => '2018061205492993161',
            'refundNo' => '73597',
            'totalFee' => '1',
            'authCode' => '285472617037741582',
            'channel' => 'wx_barcode_pay',
            'device_id' => '1627',
            'out_transaction_id' => 'ali_scan20180921105212002527504',
            //"tradeNo" => "ali_scan20180920102048475409808",
            "outTradeNo" => "dm52222344",
        ];


        $string = $this->getSignContent($data) . '&key=88888888';
//dd($string);

        $data['sign'] = md5($string);
        $data = json_encode($data);
        // return $data;
        $re = Tools::curl($data, 'http://pay.umxnt.com/api/qwx/order_query');
        $data = json_decode($re, true);
        dd($data);

        //获取请求参数
        $data = $request->getContent();
        $data = json_decode($data, true);


        //验证签名
        $check = $this->check_md5($data);
        if ($check['return_code'] == 'FALL') {
            return $this->return_data($check);
        }


        //处理业务参数

        $re_data = [
            'return_code' => 'SUCCESS',
            'return_msg' => '',
            'merchantId' => '20180988212121',
            'merchantName' => '门店名称',
            'scanPay' => 'http://openapidev.umxnt.com/api/qwx/scan_pay',
            'orderQuery' => 'http://openapidev.umxnt.com/api/qwx/order_query',
            'refund' => 'http://openapidev.umxnt.com/api/qwx/refund',
            'refundQuery' => 'http://openapidev.umxnt.com/api/qwx/refund_query',
        ];


        return $this->return_data($re_data);

    }


    //设备初始化
    public function start(Request $request)
    {

        try {
            //获取请求参数
            //  $data = $request->getContent();
            //  $data = json_decode($data, true);

            $data = $request->all();
            //验证签名
            $check = $this->check_md5($data);
            if ($check['return_code'] == 'FALL') {
                return $this->return_data($check);
            }


            //调用系统前参数

            $re_data = [
                'return_code' => 'SUCCESS',//SUCCESS/FALL 此字段是通信标识，非交易标识，交易是否成功需要查看result_code来判断
                'return_msg' => null,
                'result_code' => 'SUCCESS',
                'result_msg' => '返回成功',
                'device_id' => $data['device_id'],
                'device_type' => $data['device_type'],
                'store_id' => '2018061205492993161',
                'pid' => '0',
                'user_id' => '1',//推广者ID
                'store_name' => '有梦想科技',
                'config_id' => '1234',
                'isv_name' => '有梦想科技',
                'isv_logo' => url('/self/images/logo-fuwushang.png'),
                'alipay_store_info' => json_encode([
                    'app_id' => '2018060360256678',
                    'merchantId' => '2088031790260468',//签约商户的pid。来自蚂蚁开放平台，示例："2088011211821038"
                    'partnerId' => '2088031790260468',//ISV的pid。对于自用型商户，填写签约商户的pid，和merchantId保持一致
                    'storeCode' => '2018061205492993161',//可选，商户门店编号，和当面付请求的store_id保持一致。
                    'alipayStoreCode' => '',//可选，口碑内部门店编号，如2017100900077000000045877777，和当面付请求中的alipay_store_id保持一致。
                    'brandCode' => '',
                ]),
                'wxpay_store_info' => json_encode([
                    'appid' => 'wx94b87b679e8677aa',
                    "mch_id" => '1494729062',
                    "sub_appid" => "",
                    "sub_mch_id" => '1518150321',
                    "telephone" => '18851186776',
                    "ask_face_permit" => '1',
                ]),
                'erp_info' => json_encode([
                    'erp_type' => 'pospal',
                    'appID' => '3C1A7D6DC783490F60668216CA819A67',
                    'appKey' => '839357223459871896',
                ]),
                'is_cd' => '1',//是否支持会员价
                'is_coupon' => '1',//是否开启使用优惠
                'start_coupon' => '10000',//使用优惠开始金额
                "discount" => json_encode([
                    [
                        's' => '2000',
                        'e' => '10000',
                        'r' => '500',
                    ], [
                        's' => '10000',
                        'e' => '20000000',
                        'r' => '1000',
                    ],
                ]),

            ];


            return $this->return_data($re_data);


        } catch (\Exception $exception) {
            $err = [
                'return_code' => 'FALL',
                'return_msg' => $exception->getMessage() . $exception->getLine(),
            ];
            return $this->return_data($err);
        }

    }


}