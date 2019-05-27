<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/6/21
 * Time: 下午5:54
 */

namespace App\Api\Controllers\Weixin;


use App\Api\Controllers\Push\JpushController;
use App\Common\MerchantFuwu;
use App\Common\PaySuccessAction;
use App\Common\StoreDayMonthOrder;
use App\Common\UserGetMoney;
use App\Models\Order;
use App\Models\Store;
use EasyWeChat\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotifyController extends BaseController
{

    //支付
    public function qr_pay_notify(Request $request)
    {
        try {
            $data = $request->getContent();
            $array_data = json_decode(json_encode(simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
            $attach = $array_data['attach'];
            $attach_array = explode(",", $attach);
            $store_id = $attach_array[0];
            $config_id = $attach_array[1];
            $options = $this->Options($config_id);
            $config = [
                'app_id' => $options['app_id'],
                'mch_id' => $options['payment']['merchant_id'],
                'key' => $options['payment']['key'],
                'cert_path' => $options['payment']['cert_path'], // XXX: 绝对路径！！！！
                'key_path' => $options['payment']['key_path'],     // XXX: 绝对路径！！！！
            ];
            $app = Factory::payment($config);
            $response = $app->handlePaidNotify(function ($message, $fail) {
                $out_trade_no = $message['out_trade_no'];
                $order = Order::where('out_trade_no', $out_trade_no)->first();
                $merchant_id = $order->merchant_id;
                $store_id = $order->store_id;
                $config_id = $order->config_id;
                //订单和库里的状态不一致
                if ($message['return_code'] != $order->status) {
                    ///////////// <- 建议在这里调用微信的【订单查询】接口查一下该笔订单的情况，确认是已经支付 /////////////
                    if ($message['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
                        // 用户是否支付成功
                        if (array_get($message, 'result_code') === 'SUCCESS') {

                            $data_in = [
                                'receipt_amount' => 0,//商家实际收到的款项
                                'status' => $message['result_code'],
                                'pay_status' => 1,//系统状态
                                'pay_status_desc' => '支付成功',
                                'payment_method' => $message['bank_type'],
                                'buyer_id' => $message['openid'],
                                'trade_no' => $message['transaction_id'],
                            ];
                            $order->update($data_in);
                            $order->save();
                            $type = 2002;//静态码公众号支付
                            //动太码
                            if ($message['trade_type'] == "NATIVE") {
                                $type = 2003;
                            }

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
                                'remark' => $order->remark,
                                'ways_source'=>$order->ways_source,

                            ];


                            PaySuccessAction::action($data);


                            // 用户支付失败
                        } elseif (array_get($message, 'result_code') === 'FAIL') {


                        }

                    } else {
                        return $fail('通信失败，请稍后再通知我');
                    }
                }

                return true; // 返回处理完成
            });

            return $response;

        } catch (\Exception $exception) {
            Log::info('微信支付异步');
            Log::info($exception);
        }
    }

    //学校支付
    public function school_pay_notify(Request $request)
    {
        try {
            $data = $request->getContent();
            $array_data = json_decode(json_encode(simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
            $attach = $array_data['attach'];
            $attach_array = explode(",", $attach);
            $store_id = $attach_array[0];
            $config_id = $attach_array[1];
            $item_id = $attach_array[2];
            $item_array = explode("-", $item_id);
            $item_array = array_filter($item_array);//去除空值
            $item_array = array_values($item_array);//去除键值


            $options = $this->Options($config_id);
            $config = [
                'app_id' => $options['app_id'],
                'mch_id' => $options['payment']['merchant_id'],
                'key' => $options['payment']['key'],
                'cert_path' => $options['payment']['cert_path'], // XXX: 绝对路径！！！！
                'key_path' => $options['payment']['key_path'],     // XXX: 绝对路径！！！！
            ];
            $app = Factory::payment($config);
            $response = $app->handlePaidNotify(function ($message, $fail) {
                $out_trade_no = $message['out_trade_no'];
                $order = Order::where('out_trade_no', $out_trade_no)->first();
                $merchant_id = $order->merchant_id;
                //订单和库里的状态不一致
                if ($message['return_code'] != $order->status) {
                    ///////////// <- 建议在这里调用微信的【订单查询】接口查一下该笔订单的情况，确认是已经支付 /////////////
                    if ($message['return_code'] === 'SUCCESS') { // return_code 表示通信状态，不代表支付状态
                        // 用户是否支付成功
                        if (array_get($message, 'result_code') === 'SUCCESS') {
                            $data_in = [
                                'receipt_amount' => $order->total_amount,//商家实际收到的款项
                                'status' => $message['result_code'],
                                'pay_status' => 1,//系统状态
                                'pay_status_desc' => '支付成功',
                                'payment_method' => $message['bank_type'],
                                'buyer_id' => $message['openid'],
                                'trade_no' => $message['transaction_id'],
                                'pay_time' => $message['time_end'],
                            ];
                            $order->update($data_in);
                            $order->save();
                            $type = 2005;//学校缴费

                            //写入学校状态

                            $attach = $message['attach'];
                            $attach_array = explode(",", $attach);
                            $store_id = $attach_array[0];
                            $config_id = $attach_array[1];
                            $item_id = $attach_array[2];
                            $item_array = explode("-", $item_id);
                            $item_array = array_filter($item_array);//去除空值
                            $item_array = array_values($item_array);//去除键值

                            $out_trade_no = $order->other_no;
                            $mul_item = $item_array;
                            $cin = [
                                'pay_amount' => $order->total_amount,//支付金额
                                'receipt_amount' => $order->total_amount,//商家在交易中实际收到的款项，单位为元
                                'buyer_id' => $message['openid'],//买家支付宝账号对应的支付宝唯一用户号。以2088开头的纯16位数字,或者微信的openid
                                'buyer_logon_id' => $message['openid'],//买家支付宝账号，或者微信昵称
                                'pay_type' => '2005',//支付类型，1000-官方支付宝扫码，1005-支付宝行业缴费，2000-微信缴费，2005-微信支付缴费
                                'pay_type_desc' => '微信公众号缴费',//支付宝扫码，支付宝缴费，微信支付缴费  微信支付扫码
                                'pay_type_source' => 'weixin',//支付来源 如 alipay-支付宝，weixin-微信支付
                                'pay_type_source_desc' => '微信支付',//官方支付宝
                                'out_trade_no' => $out_trade_no,
                                'trade_no' => $message['transaction_id'],
                            ];
                            $re = \App\Logic\PrimarySchool\SyncOrder::paySuccess($out_trade_no, $mul_item, $cin);

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

                            ];


                          //  PaySuccessAction::action($data);


                            // 用户支付失败
                        } elseif (array_get($message, 'result_code') === 'FAIL') {


                        }

                    } else {
                        return $fail('通信失败，请稍后再通知我');
                    }
                }

                return true; // 返回处理完成
            });

            return $response;

        } catch (\Exception $exception) {
            Log::info('微信教育缴费异步');
            Log::info($exception);
        }
    }


}