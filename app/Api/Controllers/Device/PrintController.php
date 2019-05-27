<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/12/26
 * Time: 1:22 PM
 */

namespace App\Api\Controllers\Device;


use App\Api\Controllers\BaseController;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PrintController extends BaseController
{


    //打印模版
    public function print_tpl(Request $request)
    {

        try {
            $user = $this->parseToken();
            $out_trade_no = $request->get('out_trade_no', '');
            $store_name = $request->get('store_name', '门店名称');
            $merchant_name = $request->get('merchant_name', '收银员名称');
            $order = Order::where('out_trade_no', $out_trade_no)->first();
            $total_amount = "";
            $store_name = "";
            $ways_source_desc = "";
            $pay_status_desc = "";
            $pay_time = "";
            $remark = "";
            if ($order) {
                $store_name = $order->store_name;
                $merchant_name = $order->merchant_name;
                $total_amount = $order->total_amount;
                $ways_source_desc = $order->ways_source_desc;
                $pay_status_desc = $order->pay_status_desc;
                $pay_time = $order->pay_time;
                $remark = $order->remark;
            }
            $data = "商户名称：" . $store_name .
                "\r\n收银员：" . $merchant_name .
                "\r\n订单号：" . $out_trade_no .
                "\r\n订单金额：" . $total_amount .
                "\r\n支付方式：" . $ways_source_desc .
                "\r\n支付状态：" . $pay_status_desc . "" .
                "\r\n支付时间：" . $pay_time . "" .
                "\r\n\r\n用户备注：" . $remark . "" .
                "\r\n-----------------------------\r\n";


            return json_encode([
                'status' => 1,
                'data' => $data,
            ]);


        } catch (\Exception $exception) {
            Log::info($exception);
            return json_encode([
                'status' => -1,
                'msg' => $exception->getMessage() . $exception->getLine(),
            ]);
        }

    }


    //商米T1-打印模版
    public function order_tpl(Request $request)
    {

        try {
            $user = $this->parseToken();
            $out_trade_no = $request->get('out_trade_no', '');
            $store_name = $request->get('store_name', '门店名称');
            $merchant_name = $request->get('merchant_name', '收银员名称');
            $order = Order::where('out_trade_no', $out_trade_no)->first();
            $total_amount = "";
            $store_name = "";
            $ways_source_desc = "";
            $pay_status_desc = "";
            $pay_time = "";
            $remark = "";
            if ($order) {
                $store_name = $order->store_name;
                $merchant_name = $order->merchant_name;
                $total_amount = $order->total_amount;
                $ways_source_desc = $order->ways_source_desc;
                $pay_status_desc = $order->pay_status_desc;
                $pay_time = $order->pay_time;
                $remark = $order->remark;
            }
            $data = "商户名称：" . $store_name .
                "\r\n收银员：" . $merchant_name .
                "\r\n订单号：" . $out_trade_no .
                "\r\n订单金额：" . $total_amount .
                "\r\n支付方式：" . $ways_source_desc .
                "\r\n支付状态：" . $pay_status_desc . "" .
                "\r\n支付时间：" . $pay_time . "" .
                "\r\n\r\n用户备注：" . $remark . "" .
                "\r\n-----------------------------\r\n";


            return json_encode([
                'status' => 1,
                'data' => $data,
            ]);


        } catch (\Exception $exception) {
            Log::info($exception);
            return json_encode([
                'status' => -1,
                'msg' => $exception->getMessage() . $exception->getLine(),
            ]);
        }

    }

}