<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/12/25
 * Time: 7:28 AM
 */

namespace App\Api\Controllers\Fuiou;


use App\Common\PaySuccessAction;
use App\Models\HStore;
use App\Models\Order;
use App\Models\StorePayWay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MyBank\Tools;

class NotifyController extends BaseController
{

    public function pay_notify(Request $request)
    {

        try {
            $data = $request->all();
            $resultXml = URLdecode($data['req']);
            $ob = simplexml_load_string($resultXml);
            $xmljson = json_encode($ob);//将对象转换个JSON
            $xmlarray = json_decode($xmljson, true);//将json转换成数组


            if (isset($xmlarray['result_code'])) {
                $out_trade_no = $xmlarray['mchnt_order_no'];
                $order = Order::where('out_trade_no', $out_trade_no)->first();
                //订单存在
                if ($order) {
                    //三方订单状态成功
                    if ($xmlarray['result_code'] == '000000') {
                        //系统订单未成功
                        if ($order->pay_status != 1) {
                            $trade_no = $xmlarray['transaction_id'];
                            $pay_time = date('Y-m-d H:i:s', time());
                            $buyer_pay_amount = $xmlarray['order_amt'];
                            $buyer_pay_amount = number_format($buyer_pay_amount / 100, 2, '.', '');
                            $buyer_id = $xmlarray['user_id'];
                            $in_data = [
                                'status' => '1',
                                'pay_status' => 1,
                                'pay_status_desc' => '支付成功',
                                'trade_no' => $trade_no,
                                'pay_time' => $pay_time,
                                'buyer_id' => $buyer_id,
                                'buyer_logon_id' => $buyer_id,
                                'buyer_pay_amount' => $buyer_pay_amount,
                            ];

                            $order->update($in_data);
                            $order->save();


                            if (strpos($out_trade_no, 'scan')) {


                            } else {
                                //支付成功后的动作
                                $data = [
                                    'ways_type' => $order->ways_type,
                                    'ways_type_desc' => $order->ways_type_desc,
                                    'source_type' => '11000',//返佣来源
                                    'source_desc' => '富友',//返佣来源说明
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


                        } else {
                            //系统订单已经成功了
                        }
                    }


                } else {


                }

            }


            return '1';

        } catch (\Exception $exception) {
            Log::info($exception);
        }
    }


    public
    function store_notify(Request $request)
    {

        try {
            $json = $request->getContent();
            $arr = json_decode($json, true);

            if (isset($arr['status'])) {

                $mid = $arr['mid'];
                $HStore = HStore::where('h_mid', $mid)->first();
                if (!$HStore) {

                    $url = url('');
                    if (strpos($url, 'umxnt') !== false) {
                        $data = [
                            'https://www.cmcczf.com/api/huiyuanbao/store_notify',
                            'https://ss.tonlot.com/api/huiyuanbao/store_notify'
                        ];
                        try {
                            foreach ($data as $k => $v) {
                                $url = $v;
                                $return = $this->curlPost_java($json, $url);
                                return $return;
                            }

                        } catch (\Exception $exception) {
                            Log::info('和融通异步给其他服务商');
                            Log::info($exception);
                        }

                    } else {
                        return '';
                    }
                }
                $store_id = $HStore->store_id;
                //审核通过
                if ($HStore && $arr['status'] == '0') {
                    $data_up = [
                        'status' => 1,
                        'status_desc' => '审核成功',
                    ];

                    StorePayWay::where('store_id', $store_id)
                        ->where('company', 'herongtong')
                        ->update($data_up);
                }

                //审核不通过
                if ($HStore && $arr['status'] == '1') {
                    $data_up = [
                        'status' => 3,
                        'status_desc' => $arr['msg'],
                    ];

                    StorePayWay::where('store_id', $store_id)
                        ->where('company', 'herongtong')
                        ->update($data_up);

                }


            }

        } catch (\Exception $exception) {
            Log::info($exception);
        }
    }


    public
    function curlPost_java($data, $Url)
    {
        $ch = curl_init($Url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//$data JSON类型字符串
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($data)));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

}