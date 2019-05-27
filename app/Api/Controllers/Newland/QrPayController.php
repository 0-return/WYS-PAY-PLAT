<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/10/20
 * Time: 11:01 AM
 */

namespace App\Api\Controllers\Newland;


use App\Common\PaySuccessAction;
use App\Models\MerchantWalletDetail;
use App\Models\Order;
use App\Models\RefundOrder;
use App\Models\Store;
use App\Models\StorePayWay;
use App\Models\UserWalletDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QrPayController extends \App\Api\Controllers\BaseController
{


    //支付后跳转回调地址
    public function pay_action(Request $request)
    {


        $return = $request->all();
        $trateno = $request->get('trateno');
        $total_amount = $request->get('amount');
        $message = $request->get('message', '支付成功');
        $ad_p_id = '';
        $store_id = '';
        $ways_source = "";
        $order = Order::where('out_trade_no', $trateno)
            ->select('store_id', 'ways_source')
            ->first();

        if ($order) {
            $store_id = $order->store_id;
            $ways_source = $order->ways_source;
        }

        if ($ways_source == "alipay") {
            //支付宝失败
            $ad_p_id = '3';
        } else {
            //微信失败
            $ad_p_id = '4';

        }
        //支付成功
        if ($return['returncode'] == '000000') {
            if ($ways_source == "alipay") {
                $ad_p_id = '1';
            } else {
                $ad_p_id = '2';

            }
            $url = "&store_id=" . $store_id . '&total_amount=' . $total_amount . "&ad_p_id=" . $ad_p_id;
            $message = '支付成功';
            $url = url('/page/pay_success?message=') . $message . $url;
        } //用户取消
        elseif ($return['returncode'] == '000098') {
            $url = "&store_id=" . $store_id . '&total_amount=' . $total_amount . "&ad_p_id=" . $ad_p_id;
            $message = '用户取消';
            $url = url('/page/pay_errors?message=') . $message . $url;

        } //失败
        else {
            $message = '支付失败';
            $url = "&store_id=" . $store_id . '&total_amount=' . $total_amount . "&ad_p_id=" . $ad_p_id;
            $url = url('/page/pay_errors?message=') . $message . $url;
        }

        return redirect($url);


    }


    //新大陆刷卡订单号入库
    public function PayInOrder(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $merchant_id = $merchant->merchant_id;
            $merchant_name = $merchant->merchant_name;
            $config_id = $merchant->config_id;
            $ways_type = $request->get('ways_type', '8005');
            $company = $request->get('company', 'newland');
            $device_id = $request->get('device_id', '');
            $pay_status = $request->get('pay_status');
            $total_amount = $request->get('total_amount');
            $translocaltime = $request->get('translocaltime');
            $translocaldate = $request->get('translocaldate');
            $out_trade_no = $request->get('out_trade_no', '');//外部订单号
            $trade_no = $request->get('trade_no', '');//新大陆订单号
            $status_desc = '支付失败';
            $store_id = $request->get('store_id');
            $remark = $request->get('remark', '');
            if ($pay_status == '1') {
                $status_desc = '支付成功';
            }

            if ($pay_status == '6') {
                $status_desc = '退款成功';
            }

            $store = Store::where('store_id', $store_id)
                ->select('user_id', 'store_name')
                ->first();
            if (!$store) {
                return json_encode(['status' => 2, 'message' => '门店ID不存在']);

            }

            $tg_user_id = $store->user_id;
            $store_name = $store->store_name;


            $order = Order::where('out_trade_no', $out_trade_no)->first();


            if ($order) {
                $insertW = [
                    'trade_no' => $trade_no,
                    "out_trade_no" => $out_trade_no,
                    'status' => $pay_status,
                    'pay_status_desc' => $status_desc,
                    'pay_status' => $pay_status,
                ];

                $order->update($insertW);
                $order->save();

            } else {
                $rate = '0.6';
                //插入数据库
                $StorePayWay = StorePayWay::where('ways_type', '8005')
                    ->where('store_id', $store_id)
                    ->select('rate_e')
                    ->first();

                if ($StorePayWay) {

                    $rate = $StorePayWay->rate_e;
                }
                $data_insert = [
                    'trade_no' => $trade_no,
                    'store_id' => $store_id,
                    'store_name' => $store_name,
                    'buyer_id' => '',
                    'ways_type' => $ways_type,
                    'ways_type_desc' => '银联刷卡',
                    'ways_source' => 'unionpay',
                    'ways_source_desc' => '银联刷卡',
                    'rate' => $rate,
                    'total_amount' => $total_amount,
                    'out_trade_no' => $out_trade_no,
                    'shop_price' => $total_amount,
                    'payment_method' => '',
                    'company' => $company,
                    'status' => $pay_status,
                    'pay_status' => $pay_status,
                    'pay_status_desc' => $status_desc,
                    'merchant_id' => $merchant_id,
                    'merchant_name' => $merchant_name,
                    'remark' => $remark,
                    'device_id' => $device_id,
                    'config_id' => $config_id,
                    'user_id' => $tg_user_id,
                ];

                if ($ways_type == "8005") {
                    $data_insert['created_at'] = $translocaldate . $translocaltime;
                    $data_insert['updated_at'] = $translocaldate . $translocaltime;
                }
                $order_id = Order::insertGetId($data_insert);

                //支付成功
                if ((int)$pay_status == 1) {
                    //支付成功后的动作
                    $data = [
                        'ways_type' => '8005',
                        'ways_type_desc' => '银联刷卡',
                        'source_type' => '8000',//返佣来源
                        'source_desc' => '银联刷卡',//返佣来源说明
                        'total_amount' => $total_amount,
                        'out_trade_no' => $out_trade_no,
                        'rate' => $data_insert['rate'],
                        'merchant_id' => $merchant_id,
                        'store_id' => $store_id,
                        'user_id' => $tg_user_id,
                        'config_id' => $config_id,
                        'store_name' => $store_name,
                        'ways_source' => 'unionpay',
                        'trade_no' => $trade_no,
                        'buyer_id' => '',
                        'ways_source_desc' => '银联刷卡',
                        'shop_price' => $total_amount,
                        'payment_method' => '',
                        'status' => $pay_status,
                        'pay_status' => $pay_status,
                        'pay_status_desc' => $status_desc,
                        'merchant_name' => $merchant_name,
                        'remark' => $remark,
                        'device_id' => $device_id,
                        'created_at' => $translocaldate . $translocaltime,
                        'updated_at' => $translocaldate . $translocaltime,
                        'no_print' => '1',//不打印
                        'no_push' => '1',//不推送
                    ];


                    PaySuccessAction::action($data);


                }

                Order::where('id', $order_id)->update($data_insert);

            }


            //

            if ($pay_status == '6') {
                $status_desc = '退款成功';
                $order->pay_status_desc = '已退款';
                $order->pay_status = 6;
                $order->fee_amount = 0;//手续费
                $order->refund_amount = $order->refund_amount + $total_amount;
                $order->status = 6;
                $order->save();

                RefundOrder::create([
                    'ways_source' => $order->ways_source,
                    'type' => '8005',
                    'refund_amount' => $order->total_amount,//退款金额
                    'refund_no' => $order->out_trade_no . rand(1000, 9999),//退款单号
                    'store_id' => $store_id,
                    'merchant_id' => $merchant_id,
                    'out_trade_no' => $order->out_trade_no,
                    'trade_no' => $order->trade_no
                ]);


                //返佣去掉
                UserWalletDetail::where('out_trade_no', $out_trade_no)->update([
                    'settlement' => '03',
                    'settlement_desc' => '退款订单',
                ]);
                MerchantWalletDetail::where('out_trade_no', $out_trade_no)->update([
                    'settlement' => '03',
                    'settlement_desc' => '退款订单',
                ]);


            }

            return json_encode([
                    'status' => 1,
                    'message' => '刷卡' . $status_desc,
                    'data' => [
                        "out_trade_no" => $out_trade_no,
                        "trade_no" => $trade_no,
                        'total_amount' => $request->get('total_amount'),
                    ]
                ]
            );
        } catch (\Exception $exception) {
            return json_encode(['status' => 2, 'message' => $exception->getMessage()]);
        }
    }


}