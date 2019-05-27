<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2019/4/8
 * Time: 7:05 PM
 */

namespace App\Api\Controllers\DevicePay;


use App\Api\Controllers\Config\AlipayIsvConfigController;
use App\Api\Controllers\Config\WeixinConfigController;
use App\Api\Controllers\Deposit\AliDepositController;
use App\Api\Controllers\Deposit\WxDepositController;
use App\Api\Controllers\Merchant\OrderController;
use App\Api\Controllers\Merchant\PayBaseController;
use App\Common\PaySuccessAction;
use App\Models\DepositOrder;
use App\Models\Device;
use App\Models\DeviceOem;
use App\Models\Order;
use App\Models\Store;
use function EasyWeChat\Kernel\Support\get_client_ip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DeviceFaceController extends BaseController
{

    /**
     * 刷脸设备支付接口
     */


    //设备初始化接口

    public function face_device_start(Request $request)
    {

        try {
            //获取请求参数
            $data = $request->all();
            //验证签名
            $check = $this->check_md5($data);
            if ($check['return_code'] == 'FALL') {
                return $this->return_data($check);
            }


            $device_id = $data['device_id'];
            $device_type = $data['device_type'];

            if (!$device_id) {
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '设备device_id不能为空',
                ];
                return $this->return_data($err);
            }
            if (!$device_type) {

                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '设备device_type不能为空',
                ];
                return $this->return_data($err);
            }


            $DeviceOem = DeviceOem::where('device_id', $device_id)
                ->where('device_type', $device_type)
                ->select('Request')
                ->first();

            if (!$DeviceOem) {
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '设备不存在',
                ];
                return $this->return_data($err);
            }
            //调用系统前参数
            $data = [
                'return_code' => "SUCCESS",
                'return_msg' => "数据返回成功",
                'Request' => $DeviceOem->Request,
                'PayInit' => '/api/devicepay/face_pay_start',
                'WXInit' => '/api/devicepay/wxfacepay_initialize',
                'ScanPay' => '/api/devicepay/all_pay',
                'OrderQuery' => '/api/devicepay/all_pay_query',

            ];


            return $this->return_data($data);


        } catch (\Exception $exception) {

            $err = [
                'return_code' => 'FALL',
                'return_msg' => $exception->getMessage() . $exception->getLine(),
            ];
            return $this->return_data($err);
        }

    }


    /**
     * 刷脸交易初始化接口
     */

    public function face_pay_start(Request $request)
    {

        try {
            //获取请求参数
            $data = $request->all();
            //验证签名
            $check = $this->check_md5($data);
            if ($check['return_code'] == 'FALL') {
                return $this->return_data($check);
            }


            $device_id = $data['device_id'];
            $device_type = $data['device_type'];

            if (!$device_id) {
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '设备device_id不能为空',
                ];
                return $this->return_data($err);
            }
            if (!$device_type) {

                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '设备device_type不能为空',
                ];
                return $this->return_data($err);
            }

            //找到设备ID
            $Device = Device::where('device_no', $device_id)
                ->where('device_type', $device_type)->first();

            if (!$Device) {
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '设备不存在',
                ];
                return $this->return_data($err);
            }

            $store_id = $Device->store_id;
            $store_name = $Device->store_name;
            $config_id = $Device->config_id;


            $store = Store::where('store_id', $store_id)
                ->select('is_admin_close', 'is_delete', 'pid', 'is_close')
                ->first();
            if (!$store) {

                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '门店未认证',
                ];
                return $this->return_data($err);

            }

            //关闭的商户禁止交易
            if ($store->is_close || $store->is_admin_close || $store->is_delete) {

                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '商户已经被服务商关闭',
                ];
                return $this->return_data($err);

            }


            $store_pid = $store->pid;


            //支付宝刷脸交易信息
            $alipay_obj = new AlipayIsvConfigController();
            $storeInfo = $alipay_obj->alipay_auth_info($store_id, $store_pid);
            if (!$storeInfo) {

                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '门店信息不存在',
                ];
                return $this->return_data($err);

            }
            $alipay_config = $alipay_obj->AlipayIsvConfig($config_id, '01');

            if (!$alipay_config) {

                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '配置信息不存在',
                ];
                return $this->return_data($err);

            }
            $alipay_store_id = $storeInfo->alipay_store_id;
            $out_store_id = $storeInfo->out_store_id;
            $app_auth_token = $storeInfo->app_auth_token;
            $alipay_user_id = $storeInfo->alipay_user_id;
            $partnerId = $alipay_config->alipay_pid;

            //微信刷脸交易信息
            $weixin_obj = new WeixinConfigController();
            $weixin_config = $weixin_obj->weixin_config_obj($config_id);
            $weixin_store = $weixin_obj->weixin_merchant($store_id, $store_pid);
            if (!$weixin_store) {

                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '微信支付商户号不存在',
                ];
                return $this->return_data($err);
            }
            if (!$weixin_config) {

                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '微信支付配置不存在',
                ];
                return $this->return_data($err);
            }
            $wx_sub_merchant_id = $weixin_store->wx_sub_merchant_id;

            //调用系统前参数
            $data = [
                'return_code' => "SUCCESS",
                'return_msg' => "数据返回成功",
                'store_id' => $store_id,
                'store_name' => $store_name,
                'config_id' => $config_id,
                'face_type'=>'weixin',
                'pay_action' => 'deposit',//pay -普通支付  deposit-押金支付
                'pay_action_desc' => '微信刷脸支付',//pay -普通支付  deposit-押金支付
                'pay_voice' => '微信刷脸支付',
                'ali_app_id' => $alipay_config->app_id,
                'ali_user_id' => $alipay_user_id,//签约商户的pid。来自蚂蚁开放平台，示例："2088011211821038"
                'ali_pid' => $partnerId,//ISV的pid。对于自用型商户，填写签约商户的pid，和merchantId保持一致
                'ali_store_id' => $store_id,//可选，商户门店编号，和当面付请求的store_id保持一致。
                'ali_store_code' => $alipay_store_id,//可选，口碑内部门店编号，如2017100900077000000045877777，和当面付请求中的alipay_store_id保持一致。
                'al_brand_code' => '',
                'wx_app_id' => 'wx94b87b679e8677aa', //$weixin_config->app_id,
                "wx_mch_id" => '1494729062', //$weixin_config->wx_merchant_id,
                "wx_sub_app_id" => "",
                "wx_sub_mch_id" => '1518150321',//$wx_sub_merchant_id,
                "wx_telephone" => '18851186776',
                "wx_ask_face_permit" => '1',

                "ad_action_time" => '5',//切换时间 5s
                "ad_url_a" => url('/ad1.png'),
                "ad_url_b" => url('/ad1.png'),
                "ad_url_c" => url('/ad1.png'),

                'isv_name' => '有梦想科技',
                'isv_logo' => url('/f_logo.png'),
                'isv_phone' => '4008500508',
            ];


            return $this->return_data($data);


        } catch (\Exception $exception) {

            $err = [
                'return_code' => 'FALL',
                'return_msg' => $exception->getMessage() . $exception->getLine(),
            ];
            return $this->return_data($err);
        }

    }


    //微信通过该接口获取支付宝刷脸服务的初始化信息。
    public function wxfacepay_initialize(Request $request)
    {

        try {
            $data = $request->all();
            //验证签名
            $check = $this->check_md5($data);
            if ($check['return_code'] == 'FALL') {
                return $this->return_data($check);
            }


            $device_id = $data['device_id'];
            $device_type = $data['device_type'];
            $store_id = $data['store_id'];
            $rawdata = $data['rawdata'];

            if (!$device_id) {
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '设备device_id不能为空',
                ];
                return $this->return_data($err);
            }
            if (!$device_type) {

                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '设备device_type不能为空',
                ];
                return $this->return_data($err);
            }

            //找到设备ID
            $Device = Device::where('device_no', $device_id)
                ->where('store_id', $store_id)
                ->where('device_type', $device_type)
                ->first();

            if (!$Device) {
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '设备不存在',
                ];
                return $this->return_data($err);
            }


            $store = Store::where('store_id', $store_id)
                ->select('pid', 'config_id', 'store_name')
                ->first();
            $store_pid = $store->pid;
            $store_name = $store->store_name;

            $config_id = $store->config_id;

            //微信刷脸交易信息
            $weixin_obj = new WeixinConfigController();
            $weixin_config = $weixin_obj->weixin_config_obj($config_id);
            $weixin_store = $weixin_obj->weixin_merchant($store_id, $store_pid);

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
                'appid' => $weixin_config->app_id,//$options['app_id'],
                "mch_id" => $weixin_config->wx_merchant_id,//$options['payment']['merchant_id'],
                "version" => "1",
                "sign_type" => 'MD5',
                "now" => '' . time() . '',
                "nonce_str" => '' . time() . '',
                "store_id" => $store_id,
                "store_name" => $store_name,
                "device_id" => $device_id,
                'rawdata' => $rawdata,
            ];

            //子商户
            if ($wx_sub_merchant_id) {
                $config['sub_mch_id'] = $wx_sub_merchant_id;
            }
            $obj = new WxBaseController();
            $url = $obj->get_wxpayface_authinfo_url;
            $key = $weixin_config->key;


            //公共配置
            $config = [
                'appid' => 'wx94b87b679e8677aa',//$options['app_id'],
                "mch_id" => '1494729062',//$options['payment']['merchant_id'],
                "version" => "1",
                "sign_type" => 'MD5',
                "now" => '' . time() . '',
                "nonce_str" => '' . time() . '',
                "store_id" => $store_id,
                "store_name" => $store_name,
                "device_id" => $device_id,
                'rawdata' => $rawdata,
            ];
            //子商户
            if ($wx_sub_merchant_id) {
                $config['sub_mch_id'] = '1518150321';
            }
            $obj = new WxBaseController();
            $url = $obj->get_wxpayface_authinfo_url;
            $key = '6488674rdfghjkgtfvgvuyfrtyfvuygb'; //$options['payment']['key'];


            $config['sign'] = $obj->MakeSign($config, $key);
            Log::info($config);
            $xml = $obj->ToXml($config);
            Log::info('刷脸初始化');
            Log::info($config);
            $re_data = $obj::postXmlCurl($config, $xml, $url, $useCert = false, $second = 30);
            $re_data = $obj::xml_to_array($re_data);
Log::info($re_data);
            if ($re_data['return_code'] == 'SUCCESS') {
                $re_data = [
                    'return_code' => 'SUCCESS',//SUCCESS/FALL 此字段是通信标识，非交易标识，交易是否成功需要查看result_code来判断
                    'return_msg' => null,
                    'result_code' => 'SUCCESS',
                    'result_msg' => '数据返回成功',
                    'device_id' => $data['device_id'],
                    'device_type' => $data['device_type'],
                    'store_id' => $data['store_id'],
                    'authinfo' => $re_data['authinfo'],
                    'expires_in'=>$re_data['expires_in'],
                ];
            } else {
                $re_data = [
                    'return_code' => 'FALL',//SUCCESS/FALL 此字段是通信标识，非交易标识，交易是否成功需要查看result_code来判断
                    'return_msg' => $re_data['return_msg'],
                    'device_id' => $data['device_id'],
                    'device_type' => $data['device_type'],
                    'store_id' => $data['store_id'],
                ];
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


    //统一支付
    public function all_pay(Request $request)
    {
        try {
            $request_data = $request->all();

            //验证签名
            $check = $this->check_md5($request_data);
            if ($check['return_code'] == 'FALL') {
                return $this->return_data($check);
            }

            $check_data = [
                'total_amount' => '付款金额',
                'device_id' => '设备编号',
                'device_type' => '设备类型',
                'store_id' => '门店ID',
                'config_id' => '服务商ID',
            ];
            $check = $this->check_required($request_data, $check_data);
            if ($check) {
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => $check,
                ];
                return $this->return_data($err);
            }
            $device_id = $request_data['device_id'];
            $store_id = $request_data['store_id'];
            $device_type = $request_data['device_type'];
            $other_no = isset($request_data['other_no']) ? $request_data['other_no'] : "";
            $out_trade_no = isset($request_data['out_trade_no']) ? $request_data['out_trade_no'] : "";
            $auth_code = isset($request_data['auth_code']) ? $request_data['auth_code'] : "";
            $total_amount = isset($request_data['total_amount']) ? $request_data['total_amount'] : "";
            $remark = isset($request_data['remark']) ? $request_data['remark'] : "";
            $shop_name = isset($request_data['shop_name']) ? $request_data['shop_name'] : "交易";
            $shop_desc = isset($request_data['shop_desc']) ? $request_data['shop_desc'] : "交易";
            $pay_action = isset($request_data['pay_action']) ? $request_data['pay_action'] : "";
            $ftoken = isset($request_data['ftoken']) ? $request_data['ftoken'] : "";
            $face_code = isset($request_data['face_code']) ? $request_data['face_code'] : "";
            $pay_action = "pay";

            //找到设备ID
            $Device = Device::where('device_no', $device_id)
                ->where('store_id', $store_id)
                ->where('device_type', $device_type)
                ->select()
                ->first();

            if (!$Device) {
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '设备不存在',
                ];
                return $this->return_data($err);
            }

            $merchant_id = $Device->merchant_id;
            $merchant_name = $Device->merchant_name;
            $store_id = $Device->store_id;
            $config_id = $Device->config_id;
            //公共返回参数
            $re_data = [
                'return_code' => 'SUCCESS',
                'return_msg' => '返回成功',
                'result_code' => '',
                'result_msg' => '',
                'other_no' => $other_no,
                'out_trade_no' => '',
                'pay_time' => '',
                'total_amount' => '',
                'pay_amount' => '',
                'store_id' => $store_id,
                'payer_user_id' => '',
                'payer_logon_id' => '',
                'ways_source' => '',
                'ways_source_desc' => '',

            ];

            //请求参数
            $data = [
                'config_id' => $config_id,
                'merchant_id' => $merchant_id,
                'merchant_name' => $merchant_name,
                'code' => $auth_code,
                'total_amount' => $total_amount,
                'shop_price' => $total_amount,
                'remark' => $remark,
                'device_id' => $device_id,
                'shop_name' => $shop_name,
                'shop_desc' => $shop_desc,
                'store_id' => $store_id,
                'other_no' => $other_no,
            ];


            /**
             * 1.纯独立收银支付开始
             */


            //纯独立聚合收银支付开始-扫码
            if ($pay_action == "pay") {


                //统一扫码支付扫码支付
                if ($auth_code) {
                    $pay_obj = new PayBaseController();
                    $scan_pay_public = $pay_obj->scan_pay_public($data);
                    $tra_data_arr = json_decode($scan_pay_public, true);
                    if ($tra_data_arr['status'] != 1) {
                        $err = [
                            'return_code' => 'FALL',
                            'return_msg' => $tra_data_arr['message'],
                        ];
                        return $this->return_data($err);
                    }

                    //用户支付成功
                    if ($tra_data_arr['pay_status'] == '1') {
                        //微信，支付宝支付凭证
                        $re_data['result_code'] = 'SUCCESS';
                        $re_data['result_msg'] = '支付成功';
                        $re_data['out_trade_no'] = $tra_data_arr['data']['out_trade_no'];
                        $re_data['pay_time'] = date('YmdHis', strtotime(isset($tra_data_arr['data']['pay_time']) ? $tra_data_arr['data']['pay_time'] : time()));
                        $re_data['ways_source'] = $tra_data_arr['data']['ways_source'];

                        $re_data['total_amount'] = isset($tra_data_arr['data']['total_amount']) ? $tra_data_arr['data']['total_amount'] : "";
                        $re_data['pay_amount'] = isset($tra_data_arr['data']['total_amount']) ? $tra_data_arr['data']['total_amount'] : "";

                        $pay_voice = '';
                        if ($re_data['ways_source'] == 'alipay') {
                            $re_data['ways_source_desc'] = "支付宝";
                            $pay_voice = '支付宝支付' . $re_data['pay_amount'] . '元';
                        }
                        if ($re_data['ways_source'] == 'weixin') {
                            $re_data['ways_source_desc'] = "微信支付";
                            $pay_voice = '微信支付' . $re_data['pay_amount'] . '元';

                        }
                        if ($re_data['ways_source'] == 'jd') {
                            $re_data['ways_source_desc'] = "京东支付";
                            $pay_voice = '京东支付' . $re_data['pay_amount'] . '元';

                        }
                        if ($re_data['ways_source'] == 'unionpay') {
                            $re_data['ways_source_desc'] = "银联支付";
                            $pay_voice = '银联支付' . $re_data['pay_amount'] . '元';

                        }
                        $re_data['pay_voice'] = $pay_voice;


                    } elseif ($tra_data_arr['pay_status'] == '2') {
                        //正在支付
                        $re_data['result_code'] = 'USERPAYING';
                        $re_data['result_msg'] = '用户正在支付';
                        $re_data['out_trade_no'] = $tra_data_arr['data']['out_trade_no'];
                    } else {
                        //其他错误
                        $re_data['result_code'] = 'FALL';
                        $re_data['result_msg'] = $tra_data_arr['message'];
                    }
                }

                //微信刷脸支付
                if ($face_code) {
                    $store = Store::where('store_id', $store_id)
                        ->select('user_id', 'config_id', 'merchant_id', 'store_name', 'user_id')
                        ->first();
                    $config_id = $store->config_id;
                    $merchant_id = $store->merchant_id;
                    $merchant_name = '';
                    $store_name = $store->store_name;
                    $store_pid = $store->pid;
                    $tg_user_id = $store->user_id;
                    $remark = "";
                    $total_amount = $data['total_amount'];
                    $pay_amount = $total_amount;//单位 分
                    $device_id = $data['device_id'];


                    $config = new WeixinConfigController();
                    $weixin_config = $config->weixin_config_obj($config_id);
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
                        "body" => '刷脸支付-' . $shop_name,
                        "out_trade_no" => $out_trade_no,
                        "total_fee" => $request_data['total_amount'] * 100,
                        "spbill_create_ip" => get_client_ip(),
                        "openid" => $request_data['open_id'],
                        "face_code" => $request_data['face_code'],
                    ];
                    $useCert = false;
                    //子商户
                    if ($wx_sub_merchant_id) {

                        $config['sub_mch_id'] = '1518150321';
                    }

                    $obj = new WxBaseController();
                    $key = '6488674rdfghjkgtfvgvuyfrtyfvuygb'; //$options['payment']['key'];


                    $config['sign'] = $obj->MakeSign($config, $key);
                    $xml = $obj->ToXml($config);
                    $url = $obj->facepay_url;
                    $return_data = $obj::postXmlCurl($config, $xml, $url, $useCert, $second = 30);
                    $return_data = $obj::xml_to_array($return_data);

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
                        'rate' => '0.00'//费率

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
                                'ways_source' => $data_insert['ways_source'],

                            ];


                            PaySuccessAction::action($data);


                            $re_data['result_code'] = 'SUCCESS';
                            $re_data['result_msg'] = '支付成功';
                            $re_data['out_trade_no'] = $out_trade_no;
                            $re_data['pay_time'] = date('YmdHis', strtotime(isset($return_data['time_end'])));
                            $re_data['ways_source'] = $data_insert['ways_source'];
                            $re_data['ways_source_desc'] = $data_insert['ways_source_desc'];
                            $re_data['pay_voice'] = '微信刷脸支付' . $total_amount . '元';
                            $re_data['total_amount'] = $total_amount;
                            $re_data['pay_amount'] = $total_amount;


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

                }

                //支付宝刷脸支付
                if ($ftoken) {

                }
            }

            /**
             * 2.酒店押金支付开始
             */


            //酒店押金支付始
            if ($pay_action == "deposit") {


                //支付宝刷脸支付押金
                if ($ftoken) {

                } //微信刷脸支付押金
                elseif ($face_code) {


                } //扫码押金
                else {

                }
            }
            /**
             * 公共返回
             */


            return $this->return_data($re_data);


        } catch (\Exception $exception) {
            $err = [
                'return_code' => 'FALL',
                'return_msg' => $exception->getMessage() . $exception->getLine(),
            ];
            return $this->return_data($err);
        }
    }


    //查询订单号状态
    public function all_pay_query(Request $request)
    {

        try {
            //获取请求参数
            $data = $request->all();

            //验证签名
            $check = $this->check_md5($data);
            if ($check['return_code'] == 'FALL') {
                return $this->return_data($check);
            }

            $other_no = isset($data['other_no']) ? $data['other_no'] : '';
            $out_trade_no = isset($data['out_trade_no']) ? $data['out_trade_no'] : "";
            $device_id = $data['device_id'];
            $device_type = $data['device_type'];
            $pay_action = isset($data['pay_action']) ? $data['pay_action'] : "";
            $store_id = isset($data['store_id']) ? $data['store_id'] : "";
            $config_id = isset($data['config_id']) ? $data['config_id'] : "";

            $pay_action = "pay";

            $check_data = [
                'device_id' => '设备编号',
                'device_type' => '设备类型',
                'store_id' => '门店ID',
                'config_id' => '服务商ID',
            ];
            $check = $this->check_required($data, $check_data);
            if ($check) {
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => $check,
                ];
                return $this->return_data($err);
            }


            //公共返回参数
            $re_data = [
                'return_code' => 'SUCCESS',
                'return_msg' => '返回成功',
                'result_code' => '',
                'result_msg' => '',
                'other_no' => $other_no,
                'out_trade_no' => '',
                'pay_time' => '',
                'store_id' => $store_id,
                'total_amount' => '',
                'pay_amount' => '',
                'payer_user_id' => '',
                'payer_logon_id' => '',
                'ways_source' => '',
                'ways_source_desc' => '',
            ];


            $data = [
                'out_trade_no' => $out_trade_no,
                'other_no' => $other_no,
                'store_id' => $store_id,
                'ways_type' => '',
                'config_id' => $config_id,
            ];


            /**
             * 押金支付查询
             */

            if ($pay_action == "deposit") {

                $where = [];
                if (isset($out_order_no)) {
                    $where[] = ['out_order_no', '=', $out_order_no];
                }
                if (isset($out_trade_no)) {
                    $where[] = ['out_trade_no', '=', $out_trade_no];
                }

                $DepositOrder = DepositOrder::where($where)
                    ->select(
                        'out_order_no', 'out_trade_no', 'pay_amount', 'amount', 'ways_source', 'ways_source_desc', 'ways_company', 'operation_id', 'auth_no', 'out_request_no',
                        'deposit_status'
                    )
                    ->first();
                if (!$DepositOrder) {
                    $err = [
                        'return_code' => 'FALL',
                        'return_msg' => '订单号不存在',
                    ];
                    return $this->return_data($err);
                }

                $store = Store::where('store_id', $store_id)
                    ->select('id', 'config_id', 'user_id', 'pid')
                    ->first();


                if (!$store) {

                    $err = [
                        'return_code' => 'FALL',
                        'return_msg' => '门店不存在',
                    ];
                    return $this->return_data($err);
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

                    ];
                    $re = $obj->base_fund_order_query($data);

                    //支付宝扫码预授权查询  0 系统错 1成功 2 等待用户确认 3失败

                    //0 系统错  3失败
                    if ($re['status'] == 0 || $re['status'] == 3) {
                        $err = [
                            'return_code' => 'FALL',
                            'return_msg' => $re['message'],
                        ];
                        return $this->return_data($err);
                    }

                    $re['data']['store_id'] = $store_id;
                    $re['data']['ways_source'] = $DepositOrder->ways_source;
                    $re['data']['ways_source_desc'] = $DepositOrder->ways_source_desc;
                    $re['data']['out_trade_no'] = $DepositOrder->out_trade_no;

                    //冻结成功
                    if ($re['status'] == 1) {
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

                        //返回参数
                        $re_data = [
                            'return_code' => 'SUCCESS',
                            'return_msg' => '返回成功',
                            'result_code' => 'SUCCESS',
                            'result_msg' => '支付成功',
                            'total_amount' => $DepositOrder->amount,
                            'pay_amount' => $DepositOrder->pay_amount,
                            'other_no' => $other_no,
                            'out_trade_no' => $DepositOrder->out_trade_no,
                            'pay_time' => $re['data']['gmt_trans'],
                            'store_id' => $store_id,
                            'payer_user_id' => $re['data']['payer_user_id'],
                            'payer_logon_id' => $re['data']['payer_logon_id'],
                            'ways_source' => $re['data']['ways_source'],
                            'ways_source_desc' => $re['data']['ways_source_desc'],
                        ];
                        return $this->return_data($re_data);


                    } elseif ($re['status'] == 2) {
                        //返回参数
                        $re_data = [
                            'return_code' => 'SUCCESS',
                            'return_msg' => '返回成功',
                            'result_code' => 'USERPAYING',
                            'result_msg' => '等待成功',
                            'total_amount' => $DepositOrder->amount,
                            'pay_amount' => $DepositOrder->pay_amount,
                            'other_no' => $other_no,
                            'out_trade_no' => $DepositOrder->out_trade_no,
                            'pay_time' => '',
                            'store_id' => $store_id,
                            'payer_user_id' => '',
                            'payer_logon_id' => '',
                            'ways_source' => $re['data']['ways_source'],
                            'ways_source_desc' => $re['data']['ways_source_desc'],
                        ];
                        return $this->return_data($re_data);

                    } else {
                        //其他错误
                        $re_data['result_code'] = 'FALL';
                        $re_data['result_msg'] = $re['message'];

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


                        //返回参数
                        $re_data = [
                            'return_code' => 'SUCCESS',
                            'return_msg' => '返回成功',
                            'result_code' => 'SUCCESS',
                            'result_msg' => '支付成功',
                            'other_no' => $other_no,
                            'total_amount' => $DepositOrder->amount,
                            'pay_amount' => $DepositOrder->pay_amount,
                            'out_trade_no' => $DepositOrder->out_trade_no,
                            'pay_time' => $re['data']['gmt_trans'],
                            'store_id' => $store_id,
                            'payer_user_id' => $re['data']['openid'],
                            'payer_logon_id' => $re['data']['sub_openid'],
                            'ways_source' => $DepositOrder->ways_source,
                            'ways_source_desc' => $DepositOrder->ways_source_desc,
                        ];
                        return $this->return_data($re_data);


                    } elseif ($re['status'] == 2) {

                        //返回参数
                        $re_data = [
                            'return_code' => 'SUCCESS',
                            'return_msg' => '返回成功',
                            'result_code' => 'USERPAYING',
                            'result_msg' => '等待成功',
                            'other_no' => $other_no,
                            'total_amount' => $DepositOrder->amount,
                            'pay_amount' => $DepositOrder->pay_amount,
                            'out_trade_no' => $DepositOrder->out_trade_no,
                            'pay_time' => '',
                            'store_id' => $store_id,
                            'payer_user_id' => '',
                            'payer_logon_id' => '',
                            'ways_source' => $DepositOrder->ways_source,
                            'ways_source_desc' => $DepositOrder->ways_source_desc,
                        ];
                        return $this->return_data($re_data);

                    } else {
                        //其他错误
                        $re_data['result_code'] = 'FALL';
                        $re_data['result_msg'] = $re['message'];
                    }

                }

            }


            /**
             * 聚合支付查询
             */

            if ($pay_action == "pay") {
                $order_obj = new OrderController();
                $return = $order_obj->order_foreach_public($data);
                $tra_data_arr = json_decode($return, true);
                if ($tra_data_arr['status'] != 1) {
                    $err = [
                        'return_code' => 'FALL',
                        'return_msg' => $tra_data_arr['message'],
                    ];
                    return $this->return_data($err);
                }

                //用户支付成功
                if ($tra_data_arr['pay_status'] == '1') {
                    //微信，支付宝支付凭证
                    $re_data['result_code'] = 'SUCCESS';
                    $re_data['result_msg'] = '支付成功';
                    $re_data['out_trade_no'] = $tra_data_arr['data']['out_trade_no'];
                    $re_data['pay_time'] = date('YmdHis', strtotime($tra_data_arr['data']['pay_time']));
                    $re_data['ways_source'] = $tra_data_arr['data']['ways_source'];
                    $re_data['total_amount'] = isset($tra_data_arr['data']['total_amount']) ? $tra_data_arr['data']['total_amount'] : "";
                    $re_data['pay_amount'] = isset($tra_data_arr['data']['pay_amount']) ? $tra_data_arr['data']['pay_amount'] : "";

                    if ($re_data['ways_source'] == 'alipay') {
                        $re_data['ways_source_desc'] = "支付宝";
                    }
                    if ($re_data['ways_source'] == 'weixin') {
                        $re_data['ways_source_desc'] = "微信支付";
                    }
                    if ($re_data['ways_source'] == 'jd') {
                        $re_data['ways_source_desc'] = "京东支付";
                    }
                    if ($re_data['ways_source'] == 'unionpay') {
                        $re_data['ways_source_desc'] = "银联";
                    }

                } elseif ($tra_data_arr['pay_status'] == '2') {
                    //正在支付
                    $re_data['result_code'] = 'USERPAYING';
                    $re_data['result_msg'] = '用户正在支付';
                    $re_data['out_trade_no'] = $tra_data_arr['data']['out_trade_no'];
                } else {
                    //其他错误
                    $re_data['result_code'] = 'FALL';
                    $re_data['result_msg'] = $tra_data_arr['message'];
                }

            }


            return $this->return_data($re_data);


        } catch (\Exception $exception) {

            $err = [
                'return_code' => 'FALL',
                'return_msg' => $exception->getMessage() . $exception->getLine(),];
            return $this->return_data($err);
        }


    }


}