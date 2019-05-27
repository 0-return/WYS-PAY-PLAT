<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2019/2/19
 * Time: 11:53 AM
 */

namespace App\Api\Controllers\MyBank;


use App\Models\RefundOrder;
use Illuminate\Http\Request;

class TestController extends BaseController
{

    //商户开启接口
    public function index111123()
    {
        $ao = $this->aop();
        $ao->url = env("MY_BANK_request2");
        $ao->Function = "ant.mybank.merchantprod.merchant.unfreeze";

        $data = [
            'MerchantId' => '226801000000025119458',
            'UnfreezeReason' => '测试',
            'OutTradeNo' => date('YmdHis') . time() . rand(10000, 99999)
        ];
        // dd($data);
        $re = $ao->Request($data);
        dd($re);
    }

    //商户关闭接口
    public function index1211()
    {
        $ao = $this->aop();
        $ao->url = env("MY_BANK_request2");
        $ao->Function = "ant.mybank.merchantprod.merchant.freeze";

        $data = [
            'MerchantId' => '226801000000025119458',
            'FreezeReason' => '测试',
            'OutTradeNo' => date('YmdHis') . time() . rand(10000, 99999)
        ];
        // dd($data);
        $re = $ao->Request($data);
        dd($re);
    }

    //测试接口
    public function test(Request $request)
    {
        try {

            $MerchantId = $request->get('MerchantId');
            $out_trade_no = $request->get('out_trade_no');


            //商户信息查询接口
            $aop = new \App\Api\Controllers\MyBank\BaseController();
            $ao = $aop->aop();
            $ao->url = env("MY_BANK_request2");
            $ao->Function = "ant.mybank.merchantprod.merchant.query";

            $data = [
                'MerchantId' => $MerchantId,
            ];

            $re = $ao->Request($data);
            var_dump(base64_decode($re['data']['document']['response']['body']['WechatChannelList']));

            dd($re);

            //订单撤销
            $aop = new \App\Api\Controllers\MyBank\BaseController();
            $ao = $aop->aop();
            $ao->url = env("MY_BANK_request2");
            $ao->Function = "ant.mybank.bkmerchanttrade.payCancel";

            $data = [
                'MerchantId' => $MerchantId,
                'OutTradeNo' => $out_trade_no,
            ];
            $re = $ao->Request($data);
            dd($re);


            //5.2.6	商户关闭接口
            $aop = new \App\Api\Controllers\MyBank\BaseController();
            $ao = $aop->aop();
            $ao->url = env("MY_BANK_request2");
            $ao->Function = "ant.mybank.merchantprod.merchant.freeze";

            $data = [
                'MerchantId' => $MerchantId,
                'FreezeReason' => '测试',
                'OutTradeNo' => date('YmdHis') . time() . rand(10000, 99999)
            ];
            $re = $ao->Request($data);

            echo "商户关闭接口";
            echo "<br>";
            echo "时间:" . date('Y-m-d H:i:s');

            var_dump($re);

            //5.2.7	商户开启接口
            $aop = new \App\Api\Controllers\MyBank\BaseController();
            $ao = $aop->aop();
            $ao->url = env("MY_BANK_request2");
            $ao->Function = "ant.mybank.merchantprod.merchant.unfreeze";

            $data = [
                'MerchantId' => $MerchantId,
                'UnfreezeReason' => '测试',
                'OutTradeNo' => date('YmdHis') . time() . rand(10000, 99999)
            ];
            echo "商户开启接口";
            echo "<br>";
            echo "时间:" . date('Y-m-d H:i:s');
            $re = $ao->Request($data);
            var_dump($re);


            //网商银行短信
            $aop = new \App\Api\Controllers\MyBank\BaseController();
            $ao = $aop->aop();
            $ao->url = env("MY_BANK_request2");
            $ao->Function = "ant.mybank.merchantprod.sendsmscode";

            $data = [
                'BizType' => '02',
                'MerchantId' => '226801000008254711070',
                'OutTradeNo' => date('YmdHis') . time() . rand(10000, 99999),
            ];
            $re = $ao->Request($data);
            dd($re);


            //订单关闭  ant.mybank.bkmerchanttrade.payClose
            $aop = new \App\Api\Controllers\MyBank\BaseController();
            $ao = $aop->aop();
            $ao->url = env("MY_BANK_request2");
            $ao->Function = "ant.mybank.bkmerchanttrade.payClose";
            $data = [
                'MerchantId' => $MerchantId,
                'OutTradeNo' => 'omqr20190124145219800122853',
            ];
            $re = $ao->Request($data);
            dd($re);

            //5.2.19    他行卡结算查询接口
            $ao = $this->aop();
            $ao->url = env("MY_BANK_request2");
            $ao->Function = "ant.mybank.bkmerchantsettle.payResultQuery";

            $data = [
                'MerchantId' => $MerchantId,
                'PayDate' => '2019-01-16',
                "QueryMode" => "T1",
            ];

            $re = $ao->Request($data);
            var_dump(base64_decode($re['data']['document']['response']['body']['ResultList']));
            dd($re);

            //5.2.20    结算单打款查询接口
            //ant.mybank.bkmerchantsettle.stmtPayResultQuery

            $ao = $this->aop();
            $ao->url = env("MY_BANK_request2");
            $ao->Function = "ant.mybank.bkmerchantsettle.stmtPayResultQuery";
            $data = [
                'TradeDate' => '2019-01-16',
                'MerchantId' => $MerchantId,
                "QueryMode" => "T1",
            ];
            $re = $ao->Request($data);
            var_dump(base64_decode($re['data']['document']['response']['body']['ResultList']));

            dd($re);

            //退款查询  ant.mybank. bkmerchanttrade.refundQuery
            $aop = new \App\Api\Controllers\MyBank\BaseController();
            $ao = $aop->aop();
            $ao->url = env("MY_BANK_request2");
            $ao->Function = "ant.mybank.bkmerchanttrade.refundQuery";
            $OutRefundNo = RefundOrder::where('out_trade_no', $out_trade_no)->first()->refund_no;
            $data = [
                'MerchantId' => $MerchantId,
                'OutRefundNo' => $OutRefundNo,
            ];

            $re = $ao->Request($data);
            dd($re);

//5.2.3	微信子商户支付配置接口< ant.mybank.merchantprod.merchant.addMerchantConfig >
            $aop = new \App\Api\Controllers\MyBank\BaseController();
            $ao = $aop->aop();
            $ao->url = env("MY_BANK_request2");
            $ao->Function = "ant.mybank.merchantprod.merchant.addMerchantConfig";
            $data = [
                'MerchantId' => $MerchantId,
                'Path' => env('APP_URL') . '/api/mybank/weixin/',
                'OutTradeNo' => date('YmdHis') . time() . rand(10000, 99999),
            ];
            $re = $ao->Request($data);
            dd($re);
        } catch (\Exception $e) {
            return ['status' => 3, 'message' => '系统异常：' . $e->getMessage() . $e->getFile() . $e->getLine()];
        }
    }


}