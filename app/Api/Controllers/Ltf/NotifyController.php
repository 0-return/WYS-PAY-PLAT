<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/10/24
 * Time: 1:25 PM
 */

namespace App\Api\Controllers\Ltf;


use App\Api\Controllers\Push\JpushController;
use App\Common\MerchantFuwu;
use App\Common\PaySuccessAction;
use App\Common\StoreDayMonthOrder;
use App\Common\UserGetMoney;
use App\Models\JdConfig;
use App\Models\JdStore;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotifyController extends BaseController
{

    public function pay_notify(Request $request)
    {
        try {
            $data = $request->all();
            if ($data['code'] == "SUCCESS") {
                $order = Order::where('out_trade_no', $data['outTradeNo'])->first();
                if (!$order) {
                    return '';
                }
                //状态不一样
                if ($data['orderStatus'] != $order->status) {
                    //不成功
                    if ($order->pay_status != 1) {
                        $trade_no = $data['transactionId'];
                        $pay_time = date('Y-m-d H:i:s', strtotime($data['payTime']));
                        $buyer_pay_amount = $data['receiptAmount'];
                        $buyer_pay_amount = number_format($buyer_pay_amount, 2, '.', '');
                        $in_data = [
                            'status' => '1',
                            'pay_status' => 1,
                            'pay_status_desc' => '支付成功',
                            'trade_no' => $trade_no,
                            'pay_time' => $pay_time,
                            'buyer_pay_amount' => $buyer_pay_amount,
                        ];
                        $order->update($in_data);
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
                            'config_id' => $order->config_id,
                            'store_name' => $order->store_name,
                            'ways_source' => $order->ways_source,
                            'pay_time' => $pay_time,

                        ];


                        PaySuccessAction::action($data);


                    }
                }


            }


            echo 'success';

        } catch (\Exception $exception) {
            Log::info($exception);
            return 'error';
        }

    }

}