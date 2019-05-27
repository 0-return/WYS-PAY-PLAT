<?php


namespace App\Api\Controllers\MyBank;


use Alipayopen\Sdk\AopClient;
use Alipayopen\Sdk\LtLogger;
use Alipayopen\Sdk\Request\AlipayOfflineMarketShopQuerydetailRequest;
use Alipayopen\Sdk\Request\AlipaySecurityRiskRainscoreQueryRequest;
use Alipayopen\Sdk\Request\AlipayTradeOrderSettleRequest;
use Alipayopen\Sdk\Request\AlipayTradeQueryRequest;
use Aliyun\AliSms;
use App\Api\Controllers\Config\AlipayIsvConfigController;
use App\Api\Controllers\Config\MyBankConfigController;
use App\Api\Controllers\Device\ZlbzController;
use App\Api\Controllers\Yilianyun\YLYOpenApiClient;
use App\Api\Controllers\Yilianyun\YLYTokenClient;
use App\Http\Controllers\AlipayOpen\AlipayOpenController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Push\JpushController;
use App\Jobs\MyBankRateJob;
use App\Models\AlipayAppOauthUsers;
use App\Models\MerchantStore;
use App\Models\MyBankConfig;
use App\Models\MyBankStore;
use App\Models\Order;
use App\Models\ProvinceCity;
use App\Models\SmsConfig;
use App\Models\Store;
use App\Models\StorePayWay;
use App\Models\UserAccount;
use App\Models\UserWallet;
use App\User;
use function EasyWeChat\Payment\get_client_ip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jdjr\Sdk\HttpUtils;
use Jdjr\Sdk\SignUtil;
use Jdjr\Sdk\TDESUtil;
use Jdjr\Sdk\XMLUtil;
use Maatwebsite\Excel\Facades\Excel;
use Monolog\Handler\IFTTTHandler;
use MyBank\Sdk;
use MyBank\Tools;
use OSS\Core\OssException;
use OSS\OssClient;
use Symfony\Component\VarDumper\Dumper\DataDumperInterface;
use Zmop\Request\ZhimaCreditAntifraudScoreGetRequest;
use Zmop\Request\ZhimaCreditAntifraudVerifyRequest;
use Zmop\ZmopClient;

class MyBankController extends BaseController
{


    //修改费率
    public function StoreRate($data)
    {
        try {
            $ta_rate_r = $data['ta_rate'];
            $tb_rate_r = $data['tb_rate'];
            $MerchantId = $data['MerchantId'];
            $ta_rate_r = number_format($ta_rate_r, 4);
            $tb_rate_r = number_format($tb_rate_r, 4);
            $FeeParamList = [
                //支付宝
                [
                    "channelType" => "01",
                    "feeType" => "01",//t0
                    "feeValue" => "" . ($ta_rate_r / 100) . ""
                ],
                //微信
                [
                    "channelType" => "02",
                    "feeType" => "01",
                    "feeValue" => "" . ($ta_rate_r / 100) . ""
                ],
                [
                    "channelType" => "01",
                    "feeType" => "02",//t1
                    "feeValue" => "" . ($tb_rate_r / 100.) . ""
                ],
                [
                    "channelType" => "02",
                    "feeType" => "02",
                    "feeValue" => "" . ($tb_rate_r / 100) . ""
                ]
            ];
            //修改网商银行费率
            $aop = new \App\Api\Controllers\MyBank\BaseController();
            $ao = $aop->aop();
            $ao->url = env("MY_BANK_request2");
            $ao->Function = "ant.mybank.merchantprod.merchant.updateMerchant";

            $data = [
                'MerchantId' => $MerchantId,
                'FeeParamList' => base64_encode(json_encode($FeeParamList)),
                'OutTradeNo' => date('YmdHis') . time() . rand(10000, 99999)
            ];
            $re = $ao->Request($data);
            if ($re['status'] == 2) {
                return json_encode($re);
            }
            $body = $re['data']['document']['response']['body'];

            if ($body['RespInfo']['ResultStatus'] == "S") {
                return ['status' => 1, 'message' => '修改成功'];

            } else {
                return [
                    'status' => 2,
                    'message' => $body['RespInfo']['ResultMsg'] . '-错误码' . $body['RespInfo']['ResultCode'],
                ];
            }

        } catch (\Exception $exception) {
            return [
                'status' => 2,
                'message' => $exception->getMessage(),
            ];
        }
    }


//查询订单状态
    public
    function mybankOrderQuery($MerchantId, $out_trade_no)
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

//订单撤销
    public
    function mybankOrderCancel($MerchantId, $out_trade_no)
    {
        $ao = $this->aop();
        $ao->url = env("MY_BANK_request2");
        $ao->Function = "ant.mybank.bkmerchanttrade.payCancel";

        $data = [
            'MerchantId' => $MerchantId,
            'OutTradeNo' => $out_trade_no,
        ];
        $re = $ao->Request($data);
        return $re;
    }

    //修改结算卡信息
    public function up_bank($data)
    {

        $ao = $this->aop($data['config_id']);
        $ao->url = env("MY_BANK_request2");
        $ao->Function = "ant.mybank.merchantprod.merchant.updateMerchant";
        $BankCardParam = [
            "BankCertName" => $data['BankCertName'],//名称
            "BankCardNo" => $data['BankCardNo'],//银行卡号
            "AccountType" =>  $data['AccountType'],//账户类型。可选值：01：对私账 02对公账户
            "BankCode" => $data['BankCode'],//开户行名称
            "BranchName" => $data['BranchName'],//开户支行名称
            "ContactLine" => $data['ContactLine'],//联航号
            "BranchProvince" =>  $data['BranchProvince'],//省编号
            "BranchCity" =>  $data['BranchCity'],//市编号
            "CertType" => '01',//持卡人证件类型。可选值： 01：身份证
            "CertNo" => $data['CertNo'],//持卡人证件号码
            "CardHolderAddress" => $data['CardHolderAddress'],//持卡人地址
        ];
        $Redata = [
            'MerchantId' => $data['MerchantId'],
            'BankCardParam' => base64_encode(json_encode($BankCardParam)),
            'OutTradeNo' => date('YmdHis') . time() . rand(10000, 99999),
        ];

        $re = $ao->Request($Redata);
        return $re;
    }
}