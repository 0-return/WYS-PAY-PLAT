<?php

namespace App\Common;

use Alipayopen\Sdk\AopClient;
use Alipayopen\Sdk\LtLogger;
use Alipayopen\Sdk\Request\AlipayFundTransToaccountTransferRequest;
use App\Api\Controllers\Config\AlipayIsvConfigController;
use App\Models\AlipayAppOauthUsers;
use App\Models\AlipayIsvConfig;
use App\Models\Merchant;
use App\Models\MerchantWithdrawalsRecords;
use App\Models\User;
use App\Models\UserWithdrawalsRecords;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AlipayTransfer
{

    protected $type;
    protected $alipay_account;
    protected $out_trade_no;
    protected $account_name;
    protected $total_amount;
    protected $config_id;
    protected $sxf_amount;
    protected $tx_remark;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($out_trade_no, $type, $total_amount, $alipay_account, $account_name, $config_id, $sxf_amount = 0, $tx_remark)
    {
        //
        $this->type = $type;
        $this->alipay_account = $alipay_account;
        $this->out_trade_no = $out_trade_no;
        $this->account_name = $account_name;
        $this->total_amount = $total_amount;
        $this->config_id = $config_id;
        $this->sxf_amount = $sxf_amount;
        $this->tx_remark = $tx_remark;

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function insert()
    {
        try {
            //配置
            //分润由想用发放
            $config = AlipayIsvConfig::where('config_id', '1234')
                ->where('config_type', '01')
                ->select('app_id', 'rsa_private_key', 'alipay_gateway')
                ->first();
            $aop = new AopClient();
            $aop->appId = $config->app_id;
            $aop->rsaPrivateKey = $config->rsa_private_key;
            $aop->signType = "RSA2";//升级算法
            $aop->gatewayUrl = $config->alipay_gateway;
            $aop->format = "json";
            $aop->charset = "GBK";
            $aop->version = "2.0";
            $aop->method = 'alipay.fund.trans.toaccount.transfer';
            $requests = new AlipayFundTransToaccountTransferRequest();


            $requests->setBizContent("{" .
                "\"out_biz_no\":\"" . $this->out_trade_no . "\"," .
                "\"payee_type\":\"ALIPAY_LOGONID\"," .
                "\"payee_account\":\"" . $this->alipay_account . "\"," .
                "\"amount\":\"" . ($this->total_amount - $this->sxf_amount) . "\"," .
              //  "\"payer_show_name\":\"" . $this->account_name . "\"," .
              //  "\"payee_real_name\":\"" . $this->account_name . "\"," .
                "\"remark\":\"" .$this->tx_remark . '(总提现'.$this->total_amount.',手续费'.$this->sxf_amount. ")\"" .
                "  }");

            //读取平台门店里面的账户
            $AlipayAccount = AlipayAppOauthUsers::where('store_id', '1234')
                ->select('app_auth_token')
                ->first();//用账号提现


            $result = $aop->execute($requests, null, $AlipayAccount->app_auth_token);

            $responseNode = str_replace(".", "_", $requests->getApiMethodName()) . "_response";
            //更改状态
            if ($this->type == 'user') {
                if ($result->$responseNode->code == 10000) {
                    $UserWithdrawalsRecords = UserWithdrawalsRecords::where('out_trade_no', $this->out_trade_no)->first();
                    $UserWithdrawalsRecords->status = 1;
                    $UserWithdrawalsRecords->status_desc = '成功';
                    $UserWithdrawalsRecords->save();
                } else {
                    //开启事务
                    try {
                        DB::beginTransaction();
                        $UserWithdrawalsRecords = UserWithdrawalsRecords::where('out_trade_no', $this->out_trade_no)->first();
                        $user = User::where('id', $UserWithdrawalsRecords->user_id)->first();
                        $user->money = $user->money + $this->total_amount;
                        $user->save();

                        $UserWithdrawalsRecords->status = 3;
                        $UserWithdrawalsRecords->status_desc = '提现失败-' . $result->$responseNode->sub_msg;
                        $UserWithdrawalsRecords->remark = $result->$responseNode->sub_msg;
                        $UserWithdrawalsRecords->save();

                        DB::commit();
                    } catch (\Exception $e) {
                        Log::info($e);
                        DB::rollBack();
                    }
                }

            }

            if ($this->type == 'merchant') {
                if ($result->$responseNode->code == 10000) {
                    $MerchantWithdrawalsRecords = MerchantWithdrawalsRecords::where('out_trade_no', $this->out_trade_no)->first();
                    $MerchantWithdrawalsRecords->status = 1;
                    $MerchantWithdrawalsRecords->status_desc = '成功';
                    $MerchantWithdrawalsRecords->save();
                } else {
                    //开启事务
                    try {
                        DB::beginTransaction();

                        $MerchantWithdrawalsRecords = MerchantWithdrawalsRecords::where('out_trade_no', $this->out_trade_no)->first();
                        $merchant = Merchant::where('id', $MerchantWithdrawalsRecords->merchant_id)->first();
                        $merchant->money = $merchant->money + $this->total_amount;
                        $merchant->save();
                        $MerchantWithdrawalsRecords->status = 3;
                        $MerchantWithdrawalsRecords->status_desc = '提现失败';
                        $MerchantWithdrawalsRecords->remark = $result->$responseNode->sub_msg;
                        $MerchantWithdrawalsRecords->save();

                        DB::commit();
                    } catch (\Exception $e) {
                        Log::info($e);
                        DB::rollBack();
                    }

                }
            }

        } catch (\Exception $e) {
            $this->writelog(storage_path() . '/logs/AlipayTransfer.log', $e);
        }
    }

    public function writelog($log_file, $exception)
    {
        try {
            $logger = new LtLogger();
            $logger->conf["log_file"] = $log_file;
            $logger->conf["separator"] = "---------------";
            $logData = array(
                date("Y-m-d H:i:s"),
                str_replace("\n", "", $exception),
            );
            $logger->log($logData);
        } catch (\Exception $exception) {
            Log::info($exception);
        }
    }

}
