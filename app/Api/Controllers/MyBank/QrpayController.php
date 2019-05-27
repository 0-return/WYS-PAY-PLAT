<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2017/9/25
 * Time: 下午6:46
 */

namespace App\Api\Controllers\MyBank;


use App\Api\Controllers\Config\MyBankConfigController;
use App\Models\AlipayHbOrder;
use App\Models\AppOem;
use App\Models\Merchant;
use App\Models\MerchantStore;
use App\Models\MyBankConfig;
use App\Models\MyBankStore;
use App\Models\Order;
use App\Models\Store;
use function EasyWeChat\Payment\get_client_ip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class QrpayController extends BaseController
{
    //动态二维码-公共
    public function gdqr($data, $config_id)
    {
        try {
            $aop = new BaseController();
            $ao = $aop->aop($config_id);
            $ao->url = env("MY_BANK_request2");
            $ao->Function = "ant.mybank.bkmerchanttrade.dynamicOrder";
            $re = $ao->Request($data);
            if ($re['status'] == 2) {
                return $re;
            }

            $body = $re['data']['document']['response']['body'];
            //这个地方代表需要排查
            if ($body['RespInfo']['ResultStatus'] != 'S') {
                return [
                    'status' => 2,
                    'message' => $body['RespInfo']['ResultMsg']
                ];
            }
            return [
                'status' => 1,
                'code_url' => $body['QrCodeUrl']
            ];


        } catch (\Exception $exception) {
            return [
                'status' => 2,
                'message' => $exception->getLine()
            ];
        }

    }


    //静态码-公共
    public function auth_qr($data)
    {
        try {
            //0.接受参数
            $total_amount = $data['total_amount'];
            $merchant_id = $data['merchant_id'];
            $store_id = $data['store_id'];
            $open_id = $data['open_id'];
            $remark = $data['remark'];
            $out_trade_no = $data['out_trade_no'];
            $mybank_merchant_id = $data['MerchantId'];
            $SettleType = $data['SettleType'];
            $PayLimit = $data['PayLimit'];
            $Body = $data['Body'];
            $ChannelType = $data['pay_type'];
            $DeviceCreateIp = $data['DeviceCreateIp'];
            $Attach = $data['Attach'];
            $NotifyUrl = $data['NotifyUrl'];
            $ali_pid = $data['ali_pid'];
            $SubAppId = $data['SubAppId'];

            $ao = $this->aop();
            $ao->url = env("MY_BANK_request2");
            $ao->Function = "ant.mybank.bkmerchanttrade.prePay";


            $data = [
                'MerchantId' => $mybank_merchant_id,
                'OutTradeNo' => $out_trade_no,
                'Body' => $Body,
                'TotalAmount' => $total_amount * 100,//金额
                'Currency' => 'CNY',
                'ChannelType' => $ChannelType,
                'OperatorId' => $merchant_id,//操作员id
                'StoreId' => $store_id,
                'DeviceCreateIp' => $DeviceCreateIp,
                'SettleType' => $SettleType,//清算方式
                'Attach' => $Attach,//附加信息，原样返回。
                'OpenId' => $open_id,
                'NotifyUrl' => $NotifyUrl,
                'PayLimit' => $PayLimit,//禁用方式
            ];


            if ($ChannelType == "ALI") {
                $data['SysServiceProviderId'] = $ali_pid;
            }

            if ($ChannelType == "WX") {
                $data['SubAppId'] = $SubAppId;
            }


            $re = $ao->Request($data);
            if ($re['status'] == 0) {
                return $re;
            }
            $body = $re['data']['document']['response']['body'];
            //这个地方代表需要排查
            if ($body['RespInfo']['ResultStatus'] != 'S') {
                return [
                    'status' => 2,
                    'message' => $body['RespInfo']['ResultMsg']
                ];
            }

            $data = [
                'status' => 1,
                'data' => $body,
                "message" => "OK",
            ];

        } catch (\Exception $exception) {
            $data = [
                'status' => 2,
                'message' => $exception->getMessage()
            ];
        }
        return $data;

    }

    public function weixin(Request $request)
    {
        $store_id = $request->get('store_id');//子商户号
        $m_id = $request->get('m_id');
        //店铺都是取缓存
        if (Cache::has($store_id)) {
            $store = Cache::get($store_id);
        } else {
            $store = Store::where('store_id', $store_id)->first();
            Cache::put($store_id, $store, 1);
        }

        //取缓存
//        if (Cache::has('AppOem=' . $store_id)) {
//            $AppOem = Cache::get('AppOem=' . $store_id);
//        } else {
//            $AppOem = AppOem::where('config_id', $store->config_id)->first();
//            if (!$AppOem) {
//                //取有梦想的数据缓存
//                $AppOem = AppOem::where('config_id', '123')->first();
//            }
//            Cache::put('AppOem=' . $store_id, $AppOem, 1);
//        }
        $data = [
            // 'oem_name' => $AppOem->name,
            'store_id' => $store_id,
            'store_name' => $store->store_name,
            'store_address' => $store->store_address,
            'merchant_id' => $m_id,
        ];
        return view('mybank.weixin', compact('AppOem', 'data', 'store_id', 'm_id'));
    }

    public function mbweixin(Request $request)
    {
        //Log::info($request->all());
        try {
            //0.接受参数
            $total_amount = $request->get('total_amount');
            $merchant_id = $request->get('merchant_id', '');
            $store_id = $request->get('store_id');
            $remark = $request->get("remark");
            $shop_price = $request->get('shop_price', $total_amount);
            $ao = $this->aop();
            $ao->url = env("MY_BANK_request2");
            $ao->Function = "ant.mybank.bkmerchanttrade.prePay";
            $out_trade_no = 'mb' . date('Ymdhis', time()) . time();
            $mbstore = MyBankStore::where('OutMerchantId', $store_id)
                ->select('MerchantId','wx_AppId')
                ->first();
            if (!$mbstore) {
                return json_encode([
                    'status' => 0,
                    'msg' => '通道资料不存在请联系服务商'
                ]);
            }

            $mybank_merchant_id = $mbstore->MerchantId;
            $wx_AppId=$mbstore->wx_AppId;

            if (!$total_amount) {
                return json_encode([
                    'status' => 0,
                    'msg' => '金额未定义'
                ]);
            }

            if (!$store_id) {
                return json_encode([
                    'status' => 0,
                    'msg' => '门店id未定义'
                ]);
            }


            if (Cache::has('store_id=' . $store_id)) {
                $store = Cache::get('store_id=' . $store_id);
            } else {
                $store = Store::where('store_id', $store_id)->first();
                Cache::put('store_id=' . $store_id, $store, 1);
            }
            //代表是二维码收款
            if ((int)$merchant_id == 0) {
                $merchant_id = $store->merchant_id;
            }
            //网商配置
            $mbconfig = new MyBankConfigController();
            $MyBankConfig = $mbconfig->MyBankConfig($store->config_id,$wx_AppId);
            $wx_AppId = $MyBankConfig->wx_AppId;
            $wx_user_data = $request->session()->get('wx_user_data');


            $SettleType = 'T0';
            //企业
            if ($store->FeeType == '02') {
                $SettleType = 'T1';
            }


            $PayLimit = '';
            if (Cache::has('credit_type=' . $store_id)) {
                $PayLimit = Cache::get('credit_type=' . $store_id);
            }


            //前3天及笔数小的限制信用卡收款额度
            if ((int)$total_amount > 500 && $store->blacklist == 1) {
                $PayLimit = $this->PayLimit($total_amount, $store_id, $store->created_at);
            }

            $data = [
                'MerchantId' => $mybank_merchant_id,
                'OutTradeNo' => $out_trade_no,
                'Body' => '门店收款-' . $store->category_name,
                'TotalAmount' => $total_amount * 100,//金额
                'Currency' => 'CNY',
                'ChannelType' => "WX",
                'OperatorId' => $merchant_id,//操作员id
                'StoreId' => $store_id,
                'DeviceCreateIp' => get_client_ip(),
                'SettleType' => $SettleType,//清算方式
                'Attach' => 'mybank_qr',//附加信息，原样返回。
                'OpenId' => $wx_user_data[0]['id'],
                'NotifyUrl' => url('/mybank/notify'),
                'SubAppId' => $wx_AppId,
                'PayLimit' => $PayLimit,//禁用方式
            ];
            $type = 3002;
            $Indata = [
                'out_trade_no' => $out_trade_no,
                'store_id' => $store_id,
                'merchant_id' => $merchant_id,
                'type' => $type,
                'type_desc' => '网商微信支付',
                'type_source' => 'weixin',
                'total_amount' => $total_amount,
                'shop_price' => $total_amount,
                'status' => 2,
                'status_desc' => '等待支付',
                'pay_status' => 2,
                'buyer_id' => '',
                "remark" => $remark,
                "user_id" => $store->user_id
            ];

            $insert = Order::create($Indata);
            if (!$insert) {
                return json_encode([
                    'status' => 0,
                    'msg' => '订单未入库'
                ]);
            }
            $re = $ao->Request($data);

            if ($re['status'] == 0) {
                return json_encode($re);
            }
            $body = $re['data']['document']['response']['body'];
            //这个地方代表需要排查
            if ($body['RespInfo']['ResultStatus'] != 'S') {
                return json_encode([
                    'status' => 0,
                    'msg' => $body['RespInfo']['ResultMsg']
                ]);
            }

            return json_encode([
                'status' => 1,
                'data' => $body['PayInfo']
            ]);
            //return $body['PayInfo'];
        } catch (\Exception $exception) {
            return json_encode([
                'status' => 0,
                'msg' => $exception->getMessage()
            ]);
        }

    }

}