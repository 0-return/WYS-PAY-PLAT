<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2016/12/17
 * Time: 16:09
 */

namespace App\Api\Controllers\AlipayOpen;


use Alipayopen\Sdk\AopClient;
use App\Api\Controllers\Config\AlipayIsvConfigController;
use App\Api\Controllers\Push\JpushController;
use App\Common\MerchantFuwu;
use App\Common\PaySuccessAction;
use App\Common\StoreDayMonthOrder;
use App\Common\UserGetMoney;
use App\Http\Controllers\Controller;
use App\Models\AlipayAppOauthUsers;
use App\Models\AlipayHbOrder;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotifyController extends Controller
{
    //支付宝异步处理
    public function qr_pay_notify(Request $request)
    {
        try {
            //支付异步通知
            $data = $request->all();
            $Order = Order::where('out_trade_no', $data['out_trade_no'])
                ->first();

            $merchant_id = $Order->merchant_id;
            $store_id = $Order->store_id;

            $store = Store::where('store_id', $Order->store_id)
                ->select('pid', 'config_id')
                ->first();
            $store_pid = $store->pid;
            //配置
            $isvconfig = new AlipayIsvConfigController();


            $storeInfo = $isvconfig->alipay_auth_info($store_id, $store_pid);//通道信息
            $config_type = '01';


            if ($storeInfo->config_type == '02') {
                $config_type = '02';
            }

            $config = $isvconfig->AlipayIsvConfig($store->config_id, $config_type);

            if ($config) {
                //1.接入参数初始化
                $aop = new AopClient();
                $aop->apiVersion = "2.0";
                $aop->appId = $config->app_id;
                $aop->rsaPrivateKey = $config->rsa_private_key;
                $aop->alipayrsaPublicKey = $config->alipay_rsa_public_key;
                $aop->signType = "RSA2";//升级算法
                $aop->gatewayUrl = $config->alipay_gateway;
                $aop->format = "json";
                $aop->charset = "UTF-8";


                $check = $aop->rsaCheckUmxnt($request->all(), $config->alipay_rsa_public_key);
                if ($check) {
                    //如果状态不相同修改数据库状态
                    if ($Order->status != $data['trade_status']) {

                        if ($data['trade_status'] == 'TRADE_SUCCESS') {
                            if ($Order->pay_status != 1) {
                                $payment_method = json_decode($data['fund_bill_list'], true)[0]['fundChannel'];
                                if (substr($data['out_trade_no'], 0, 2) == "fq") {
                                    $type = 1007;
                                    $updatedata = [
                                        'buyer_id' => $data['buyer_id'],
                                        'buyer_logon_id' => $data['buyer_logon_id'],
                                        'trade_no' => $data['trade_no'],
                                        'total_amount' => $data['total_amount'],
                                        'receipt_amount' => $data['receipt_amount'],
                                        'pay_status' => 1,
                                        'pay_status_desc' => '支付成功',
                                    ];
                                    AlipayHbOrder::where('out_trade_no', $data['out_trade_no'])->update($updatedata);

                                } else {
                                    $type = 1003;
                                    $updatedata = [
                                        'status' => $data['trade_status'],
                                        'payment_method' => $payment_method,
                                        'buyer_id' => $data['buyer_id'],
                                        'buyer_logon_id' => $data['buyer_logon_id'],
                                        'trade_no' => $data['trade_no'],
                                        'total_amount' => $data['total_amount'],
                                        'receipt_amount' => $data['receipt_amount'],
                                        'pay_status' => 1,
                                        'pay_status_desc' => '支付成功',
                                    ];
                                    Order::where('out_trade_no', $data['out_trade_no'])->update($updatedata);

                                }


                                //支付成功后的动作
                                $data = [
                                    'ways_type' => '1000',
                                    'ways_type_desc' => '支付宝',
                                    'source_type' => 'alipay',//返佣来源
                                    'source_desc' => '支付宝',//返佣来源说明
                                    'total_amount' => $Order->total_amount,
                                    'out_trade_no' => $Order->out_trade_no,
                                    'rate' => $Order->rate,
                                    'merchant_id' => $Order->merchant_id,
                                    'store_id' => $Order->store_id,
                                    'user_id' => $Order->user_id,
                                    'config_id' => $store->config_id,
                                    'store_name' => $Order->store_name,
                                    'remark' => $Order->remark,
                                    'ways_source'=>$Order->ways_source,

                                ];


                                PaySuccessAction::action($data);

                            }
                        }
                    }
                }
            }


            echo 'success';
        } catch (\Exception $exception) {
            Log::info($exception->getMessage());
        }
    }

}