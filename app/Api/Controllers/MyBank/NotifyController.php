<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2017/9/25
 * Time: 下午6:43
 */

namespace App\Api\Controllers\MyBank;


use App\Common\PaySuccessAction;
use App\Models\MyBankStore;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MyBank\Tools;

class NotifyController extends BaseController
{
    //异步通知
    public function notify(Request $request)
    {

        $data = Tools::xml_to_array($request->getContent());
        $MerchantId = $data['document']['request']['body']['MerchantId'];
        $MerchantStore = MyBankStore::where('MerchantId', $MerchantId)
            ->select('config_id')
            ->first();
        $config_id = '1234';
        if ($MerchantStore) {
            $config_id = $MerchantStore->config_id;
        }
        $ao = new BaseController();
        $aop = $ao->aop($config_id);


        $data = [
            "ResultStatus" => "F",
            "ResultCode" => "",
            "ResultMsg" => '',
        ];
        try {
            Log::info('异步收到的');
            Log::info($request->getContent());
            $check = $aop->check($request->getContent());
            $head = $check['data']['document']['request']['head'];
            $aop->ReqMsgId=$head['ReqMsgId'];
            if ($check['status']) {
                $body = $check['data']['document']['request']['body'];
                $bodyquery = $this->mybankOrderQuery($body['MerchantId'], $body['OutTradeNo']);
                $bodyquerydata = $bodyquery['data']['document']['response']['body'];
                //成功
                $order = Order::where('out_trade_no', $body['OutTradeNo'])->first();
                //状态不相同
                if ($order->status != $bodyquerydata['TradeStatus']) {
                    //成功
                    if ($bodyquerydata['TradeStatus'] == 'succ' && $order->pay_status == 2) {
                        $datain = [
                            'trade_no' => $body['OrderNo'],
                            'buyer_id' => $body['BuyerUserId'],
                            'pay_status' => 1,
                            'pay_status_desc' => '支付成功',
                            'payment_method' => strtolower($body['Credit']),
                            'status' => $bodyquerydata['TradeStatus'],
                        ];
                        //微信付款的id
                        $pay_type_desc = '';
                        if ($order->ways_type == 3002) {
                            $pay_type_desc = '微信支付';
                            $Indata['buyer_id'] = $body['SubOpenId'];
                        }
                        if ($order->ways_type == 3001) {
                            $pay_type_desc = '支付宝';
                            $Indata['buyer_id'] = $body['BuyerUserId'];
                        }

                        $order->update($datain);
                        $order->save();


                        //支付成功后的动作
                        $data = [
                            'ways_type' => $order->ways_type,
                            'ways_type_desc' => $order->ways_type_desc,
                            'source_type' => '3000',//返佣来源
                            'source_desc' => '网商银行',//返佣来源说明
                            'total_amount' => $order->total_amount,
                            'out_trade_no' => $order->out_trade_no,
                            'rate' => $order->rate,
                            'merchant_id' => $order->merchant_id,
                            'store_id' => $order->store_id,
                            'user_id' => $order->user_id,
                            'config_id' => $config_id,
                            'store_name' => $order->store_name,
                            'ways_source' => $order->ways_source,

                        ];


                        PaySuccessAction::action($data);


                        $data = [
                            "ResultStatus" => "S",
                            "ResultCode" => "0000",
                            "ResultMsg" => "处理成功",
                        ];

                    } else {
                        $data = [
                            "ResultStatus" => "F",
                            "ResultCode" => "",
                            "ResultMsg" => '',
                        ];

                    }
                }
            } else {
                $data = [
                    "ResultStatus" => "F",
                    "ResultCode" => "",
                    "ResultMsg" => "其他错误",
                ];
            }

        } catch (\Exception $exception) {
            $data = [
                "ResultStatus" => "F",
                "ResultCode" => "",
                "ResultMsg" => $exception->getMessage(),
            ];
        }

        $aop->Function = 'ant.mybank.bkmerchanttrade.prePayNotice';
        $response = $aop->response($data);
        Log::info('异步返回的');
        Log::info($response);
        return $response;
    }

//打款失败通知
    public function notifyPayResult(Request $request)
    {
        $ao = new BaseController();
        $aop = $ao->aop();
        $data = [
            "ResultStatus" => "F",
            "ResultCode" => "",
            "ResultMsg" => '',
        ];
        try {
            $check = $aop->check($request->getContent());
            Log::info('打款失败收到的');
            Log::info($request->getContent());
            $body = $check['data']['document']['request']['body'];
            $head = $check['data']['document']['request']['head'];

            $aop->url = env("MY_BANK_request2");
            $aop->Function = "ant.mybank.bkmerchantsettle.notifyPayResult";
            $aop->ReqMsgId=$head['ReqMsgId'];
            //逻辑代码


            $data = [
                "ResponseCode" => 'OK',
                "IsvOrgId" => $body['IsvOrgId'],
            ];

        } catch (\Exception $exception) {
            $data = [
                "ResultStatus" => "F",
                "ResultCode" => "",
                "ResultMsg" => $exception->getMessage(),
            ];
        }

        $response = $aop->response_a($data);
        Log::info('打款失败返回的');
        Log::info($response);
        return $response;
    }


    //查询订单状态
    public function mybankOrderQuery($MerchantId, $out_trade_no)
    {
        $ao = $this->aop();
        $ao->url = env("MY_BANK_request2");
        $ao->Function = "ant.mybank.bkmerchanttrade.payQuery";

        $data = [
            'MerchantId' => $MerchantId,
            'OutTradeNo' => $out_trade_no,
        ];
        $re = $ao->Request($data);
        return $re;
    }


    public function riskgo_notify(Request $request)
    {

        //  $data = $request->getContent();
        $data = $request->all();


    }


    public function activity_notify(Request $request)
    {

        //  $data = $request->getContent();
        $data = $request->all();

    }

}