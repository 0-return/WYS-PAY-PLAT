<?php

namespace App\Api\Controllers\Weixin;

use App\Api\Controllers\Weixin\BaseController;
use App\Common\MerchantFuwu;
use App\Common\StoreDayMonthOrder;
use App\Models\Order;
use App\Models\WeixinStore;
use EasyWeChat\Factory;
use EasyWeChat\Payment\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PayController extends BaseController
{
    /** 公众号支付/二维码支付
     * @param $data
     * @return mixed
     */
    public function qr_pay($data, $trade_type = "JSAPI", $notify_url = "")
    {
        $out_trade_no = $data['out_trade_no'];
        $config_id = $data['config_id'];
        $store_id = $data['store_id'];
        $merchant_id = $data['merchant_id'];
        $total_amount = $data['total_amount'];
        $shop_price = $data['shop_price'];
        $remark = $data['remark'];
        $device_id = $data['device_id'];
        $shop_name = $data['shop_name'];
        $shop_desc = $data['shop_desc'];
        $goods_detail = $data['goods_detail'];
        $store_name = $data['store_name'];
        $open_id = $data['open_id'];
        $attach = $data['attach'];//原样返回
        $options = $data['options'];
        $wx_sub_merchant_id = $data['wx_sub_merchant_id'];
        //异步地址
        if ($notify_url == "") {
            $notify_url = url('api/weixin/qr_pay_notify');
        }

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
        $jssdk = $payment->jssdk;

        $attributes = [
            'trade_type' => $trade_type, // JSAPI，NATIVE，APP...
            'body' => $shop_name,
            'detail' => $shop_desc,
            'out_trade_no' => $out_trade_no,
            'total_fee' => $total_amount * 100,
            'notify_url' => $notify_url, // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'openid' => $open_id,
            'attach' => $attach,//原样返回
            'device_info' => $device_id,
            'goods_detail' => $goods_detail,//交易详细数据
        ];
        $result = $payment->order->unify($attributes);
        Log::info($result);

        if ($result['return_code'] == 'SUCCESS') {
            $json = [];
            //公众号支付
            if ($trade_type == "JSAPI") {
                $prepayId = $result['prepay_id'];
                $json = $jssdk->bridgeConfig($prepayId);
            }

            if ($trade_type == 'NATIVE') {
                $json = $result;
            }

            $data = [
                'status' => 1,
                'data' => $json
            ];

        } else {
            $data = [
                'status' => 2,
                "message" => $result['return_msg'],
            ];
        }

        return json_encode($data);
    }

    /**扫码枪
     * @param $data
     * @return string
     */
    public function scan_pay($data)
    {
        $out_trade_no = $data['out_trade_no'];
        $config_id = $data['config_id'];
        $store_id = $data['store_id'];
        $merchant_id = $data['merchant_id'];
        $total_amount = $data['total_amount'];
        $shop_price = $data['shop_price'];
        $remark = $data['remark'];
        $device_id = $data['device_id'];
        $shop_name = $data['shop_name'];
        $shop_desc = $data['shop_desc'];
        $goods_detail = $data['goods_detail'];
        $store_name = $data['store_name'];
        $auth_code = $data['auth_code'];
        $attach = $data['attach'];//原样返回
        $wx_sub_merchant_id = $data['wx_sub_merchant_id'];
        $options = $data['options'];

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

        $attributes = [
            'body' => $shop_name,
            'detail' => $shop_desc,
            'out_trade_no' => $out_trade_no,
            'total_fee' => $total_amount * 100,
            'auth_code' => $auth_code,
            'attach' => $attach,//原样返回
            'device_info' => $device_id,
            'goods_detail' => $goods_detail,//交易详细数据
        ];

        $result = $payment->pay($attributes);


        return $result;

    }

}
