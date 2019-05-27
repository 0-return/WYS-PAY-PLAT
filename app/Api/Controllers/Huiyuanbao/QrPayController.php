<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/10/20
 * Time: 11:01 AM
 */

namespace App\Api\Controllers\Huiyuanbao;


use App\Api\Controllers\Basequery\AdSelectController;
use App\Models\Order;
use App\Models\Store;
use function EasyWeChat\Kernel\Support\get_client_ip;
use function EasyWeChat\Kernel\Support\get_server_ip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QrPayController extends BaseController
{


    //支付后跳转回调地址
    public function pay_action(Request $request)
    {


        $return = $request->getContent();
        Log::info($return);

        return '';
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


}