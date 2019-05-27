<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/10/24
 * Time: 1:25 PM
 */

namespace App\Api\Controllers\Jd;


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

    public function notify_url(Request $request)
    {
        try {

            $data = $request->all();

            if ($data['success']) {
                $jd_merchant = JdStore::where('merchant_no', $data['merchantNo'])
                    ->select('config_id', 'md_key', 'des_key')
                    ->first();
                if (!$jd_merchant) {
                    return 'error';
                }


                //对数据解密
                $obj = new BaseController();
                $obj->des_key = $jd_merchant->des_key;
                $obj->md_key = $jd_merchant->md_key;

                $cipherJson = $obj->decrypt($data['cipherJson'], $jd_merchant->des_key);

                if (!$cipherJson) {
                    return 'error';
                }


                //验证sign
                $re_data = json_decode($cipherJson, true);
                $string = $obj->getSignContent($re_data) . $jd_merchant->md_key;
                $sign = md5($string);
                if ($sign == $data['sign']) {
                    $local_pay_status = 2;
                    $piType = $re_data['piType'];//支付方式;
                    $productType = $re_data['productType'];//QR(二维码)，BAR(条形码)，CASH(收银台)
                    $pay_status = $re_data['payStatus'];//支付状态;
                    if ($pay_status == "FINISH") {
                        $local_pay_status = 1;//成功
                    }

                    if ($pay_status == "CLOSE") {
                        $local_pay_status = 4;//关闭
                    }

                    $order = Order::where('out_trade_no', $re_data['outTradeNo'])->first();
                    if (!$order) {
                        return '';
                    }


                    //状态不一样
                    if ($local_pay_status != $order->pay_status) {
                        //成功
                        if ($local_pay_status == 1 && $order->pay_status != 1) {
                            $trade_no = $re_data['tradeNo'];
                            $pay_time = date('Y-m-d H:i:s', strtotime($re_data['payFinishTime']));
                            $buyer_pay_amount = $re_data['piAmount'] / 100;
                            $buyer_pay_amount = number_format($buyer_pay_amount, 2, '.', '');
                            $in_data = [
                                'status' => '1',
                                'pay_status' => 1,
                                'pay_status_desc' => '支付成功',
                                'trade_no' => $trade_no,
                                'pay_time' => $pay_time,
                                'buyer_pay_amount' => $buyer_pay_amount,
                            ];
                            $pay_type = '';
                            //支付宝
                            if ($piType == "ALIPAY") {
                                $pay_type = "支付宝";
                                $in_data['ways_source_desc'] = '支付宝';
                                $in_data['ways_type_desc'] = '支付宝';
                                $in_data['ways_type'] = '6001';
                                $in_data['ways_source'] = 'alipay';
                            }

                            //微信支付
                            if ($piType == "WX") {
                                $pay_type = "微信支付";
                                $in_data['ways_source_desc'] = '微信支付';
                                $in_data['ways_type_desc'] = '微信支付';
                                $in_data['ways_type'] = '6002';
                                $in_data['ways_source'] = 'weixin';
                            }

                            //京东银联
                            if ($piType == "JDPAY") {
                                //京东
                                if ($order->ways_type == 6003) {
                                    $in_data['ways_source_desc'] = '京东支付';
                                    $in_data['ways_type_desc'] = '京东支付';
                                    $in_data['ways_type'] = '6003';
                                    $in_data['ways_source'] = 'jd';
                                    $pay_type = "京东支付";

                                }

                                //京东
                                if ($order->ways_type == 6004) {
                                    $in_data['ways_source_desc'] = '银联支付';
                                    $in_data['ways_type_desc'] = '银联支付';
                                    $in_data['ways_type'] = '6004';
                                    $in_data['ways_source'] = 'unionpay';
                                    $pay_type = "银联支付";

                                }

                            }
                            $order->update($in_data);
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
                                'config_id' => $jd_merchant->config_id,
                                'store_name' => $order->store_name,
                                'ways_source' => $order->ways_source,
                                'pay_time' => $pay_time,

                            ];


                            PaySuccessAction::action($data);


                        }
                    }


                } else {
                    return 'error';
                }

            } else {
                return 'error';
            }

            echo 'success';

        } catch (\Exception $exception) {
            Log::info($exception);
            return 'error';
        }


    }


    public function refund_url(Request $request)
    {

    }
}