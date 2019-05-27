<?php


namespace App\Api\Controllers\MyBank;


use Alipayopen\Sdk\AopClient;
use App\Api\Controllers\Config\MyBankConfigController;
use App\Api\Controllers\Tools\TransactionControl;
use App\Common\MyBankRate;
use App\Common\StoreDayMonthOrder;
use App\Http\Controllers\Controller;
use App\Jobs\HbfqUserRateJob;
use App\Jobs\MerchantStoreDayMonthOrderJob;
use App\Jobs\MerchantStoreDayOrderJob;
use App\Jobs\MerchantStoreMonthOrderJob;
use App\Jobs\MyBankRateJob;
use App\Jobs\StoreDayOrderJob;
use App\Jobs\StoreMoneyOutAlipay;
use App\Jobs\StoreMonthOrderJob;
use App\Models\AlipayHbOrder;
use App\Models\MyBankConfig;
use App\Models\Order;
use App\Models\Store;
use function EasyWeChat\Payment\get_client_ip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\IFTTTHandler;
use MyBank\Sdk;
use MyBank\Tools;
use OSS\Core\OssException;
use OSS\OssClient;

class TradePayController extends BaseController
{
    //被扫反扫接口
    public function TradePay($data)
    {
        try {
            $mybank_merchant_id = $data['mybank_merchant_id'];
            $merchant_id = $data['merchant_id'];
            $code = $data['code'];
            $TotalAmount = $data['TotalAmount'];
            $is_fq = $data['is_fq'];//0
            $fq_num = $data['fq_num'];//3
            $hb_fq_seller_percent = $data['hb_fq_seller_percent'];//0
            $buydata = $data['buydata'];//数组
            $SettleType = $data['SettleType'];//T1
            $type_source = $data['type_source'];//alipay,weixin
            $remark = $data['remark'];
            $out_trade_no = $data['out_trade_no'];
            $Body = $data['body'];
            $store_id = $data['store_id'];
            $Attach = $data['attach'];//附加信息，原样返回。
            $PayLimit = $data['PayLimit'];//禁用方式
            $config_id = $data['config_id'];
            $ao = $this->aop($config_id);

            $ali_pid = $ao->ali_pid;

            $ao->url = env("MY_BANK_request2");
            $ao->Function = "ant.mybank.bkmerchanttrade.pay";
            $str = substr($code, 0, 2);
            $ChannelType = '';
            if ($type_source == 'alipay') {
                $ChannelType = 'ALI';
            }
            if ($type_source == 'weixin') {
                $ChannelType = 'WX';
            }
            $data = [
                'MerchantId' => $mybank_merchant_id,
                'AuthCode' => $code,
                'OutTradeNo' => $out_trade_no,
                'Body' => $Body,
                'TotalAmount' => $TotalAmount,//金额
                'Currency' => 'CNY',
                'ChannelType' => $ChannelType,
                'OperatorId' => $merchant_id,//操作员id
                'StoreId' => $store_id,
                'DeviceCreateIp' => \EasyWeChat\Kernel\Support\get_client_ip(),
                'SettleType' => $SettleType,//清算方式
                'Attach' => $Attach,//附加信息，原样返回。
                'PayLimit' => $PayLimit,//禁用方式
            ];

            //支付宝pid
            if ($type_source == 'alipay') {
                $data['SysServiceProviderId'] = $ali_pid;
            }


            if ($str == '28' && $is_fq) {
                $data['CheckLaterNm'] = $fq_num;
                $pay_type = "HBPAY";

            }
            //请求网商银行
            $re = $ao->Request($data);
            if ($re['status'] == 0) {
                return $re;
            }
            $body = $re['data']['document']['response']['body'];

            //支付成功
            if ($body['RespInfo']['ResultStatus'] == 'S') {
                return [
                    'status' => 1,
                    'message' => '交易成功',
                    'data' => $body,
                ];
            }

            //用户输入密码
            if ($body['RespInfo']['ResultStatus'] == 'U') {
                return [
                    'status' => 2,
                    'message' => '请用户输入密码',
                    'data' => $re['data'],
                ];
            }


            if ($body['RespInfo']['ResultStatus'] == 'F') {
                return [
                    'status' => 0,
                    'message' => $body['RespInfo']['ResultMsg']
                ];
            }
            //其他情况
            if ($body['TradeStatus'] == 'fail') {
                return [
                    'status' => 0,
                    'message' => '付款失败'
                ];
            }

            //其他 F 报错
            return [
                'status' => 0,
                'message' => $body['RespInfo']['ResultMsg']
            ];

        } catch (\Exception $exception) {
            return [
                'status' => 0,
                'message' => $exception->getLine()
            ];
        }

    }

    //查询订单状态
    public function mybankOrderQuery($MerchantId, $config_id, $out_trade_no)
    {
        $ao = $this->aop($config_id);
        $ao->url = env("MY_BANK_request2");
        $ao->Function = "ant.mybank.bkmerchanttrade.payQuery";

        $data = [
            'MerchantId' => $MerchantId,
            'OutTradeNo' => $out_trade_no,
        ];
        $re = $ao->Request($data);
        return $re;
    }

    //订单撤销
    public function mybankOrderCancel($MerchantId, $config_id, $out_trade_no)
    {
        $ao = $this->aop($config_id);
        $ao->url = env("MY_BANK_request2");
        $ao->Function = "ant.mybank.bkmerchanttrade.payCancel";

        $data = [
            'MerchantId' => $MerchantId,
            'OutTradeNo' => $out_trade_no,
        ];
        $re = $ao->Request($data);
        return $re;
    }

    //订单关闭
    public function mybankOrderClose($MerchantId, $config_id, $out_trade_no)
    {
        $ao = $this->aop($config_id);
        $ao->url = env("MY_BANK_request2");
        $ao->Function = "ant.mybank.bkmerchanttrade.payClose";

        $data = [
            'MerchantId' => $MerchantId,
            'OutTradeNo' => $out_trade_no,
        ];
        $re = $ao->Request($data);
        return $re;
    }

    //退款接口
    public function mybankrefund($MerchantId, $OutTradeNo, $OutRefundNo, $RefundAmount, $config_id)
    {

        $aop = new \App\Api\Controllers\MyBank\BaseController();
        $ao = $aop->aop($config_id);
        $ao->url = env("MY_BANK_request2");
        $ao->Function = "ant.mybank.bkmerchanttrade.refund";

        $data = [
            'MerchantId' => $MerchantId,
            'OutTradeNo' => $OutTradeNo,
            'OutRefundNo' => $OutRefundNo,
            'RefundAmount' => "" . (($RefundAmount) * 100) . "",
            'RefundReason' => '退款操作',
            'DeviceCreateIp' => \EasyWeChat\Kernel\Support\get_client_ip()
        ];
        $re = $ao->Request($data);
        if ($re['status'] == 0) {
            return $re;
        }

        $Result = $re['data']['document']['response']['body']['RespInfo'];
        if ($Result['ResultStatus'] == 'S') {

            return [
                'status' => 1,
                'message' => '退款申请成功',
                'data' => $re['data']['document']['response']['body'],
            ];

        } else {
            return [
                'status' => 0,
                'message' => $Result['ResultMsg']
            ];
        }
    }


    //退款查询接口
    public function mybankrefund_query($MerchantId, $OutRefundNo)
    {



    }

}