<?php

namespace App\Console\Commands;

use Aliyun\AliSms;
use App\Api\Controllers\MyBank\BaseController;
use App\Models\MerchantStore;
use App\Models\MyBankStore;
use App\Models\MyBankStoreTem;
use App\Models\SmsConfig;
use App\Models\Store;
use App\Models\StorePayWay;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MyBankStoreAudit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mybank-audit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'mybank-audit';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $MyBankStoreTem = MyBankStoreTem::all()->toArray();
        $aop = new BaseController();
        $ao = $aop->aop();
        $ao->url = env("MY_BANK_request2");
        $ao->Function = "ant.mybank.merchantprod.merchant.register.query";

        foreach ($MyBankStoreTem as $k => $v) {
            $OrderNo = $v['OrderNo'];
            if ($OrderNo == "") {
                continue;
            }
            try {
                $data = [
                    'OrderNo' => $OrderNo
                ];
                $re = $ao->Request($data);
                if ($re['status'] == 2) {
                    continue;
                }

                $body = $re['data']['document']['response']['body'];

                //店铺都是取缓存
                if (Cache::has($v['OutMerchantId'])) {
                    $store = Cache::get($v['OutMerchantId']);
                } else {
                    $store = Store::where('store_id', $v['OutMerchantId'])
                        ->select('config_id')
                        ->first();
                    Cache::put($v['OutMerchantId'], $store, 1000);
                }
                if (!$store) {
                    continue;
                }

                $config_id = $store->config_id;
                //审核通过
                if ($body['RegisterStatus'] == '1' && $body['MerchantId']) {
                    $v['status'] = 1;
                    $v['MerchantId'] = $body['MerchantId'];
                    $v['smid'] = $body['Smid'];

                    //微信子商户支付配置接口
                    try {
                        $ao->url = env("MY_BANK_request2");
                        $ao->Function = "ant.mybank.merchantprod.merchant.addMerchantConfig";
                        $data = [
                            'MerchantId' => $body['MerchantId'],
                            'Path' => env('APP_URL') . '/api/mybank/weixin/',
                            'OutTradeNo' => date('YmdHis') . time() . rand(10000, 99999),
                        ];
                        $re = $ao->Request($data);

                    } catch (\Exception $exception) {

                    }

                    if ($v['SettleMode'] == '02') {
                        //开通余利宝
                        try {
                            $ao->Function = "ant.mybank.yulibao.accountopen";
                            $data = [
                                'MerchantId' => $body['MerchantId'],
                                'FundCode' => '001529',
                                'OutTradeNo' => date('YmdHis') . time() . rand(10000, 99999),
                            ];
                            $re = $ao->Request($data);

                        } catch (\Exception $exception) {
                            Log::info($exception);
                            continue;
                        }
                    }
                    //开启事务
                    try {
                        DB::beginTransaction();
                        $MyBankStore = MyBankStore::where('OutMerchantId', $v['OutMerchantId'])->first();
                        if ($MyBankStore) {
                            $MyBankStore->update($v);
                            $MyBankStore->save();
                        } else {
                            MyBankStore::create($v);
                        }

                        //开启事务


                        //更新通道为成功
                        StorePayWay::where('store_id', $v['OutMerchantId'])
                            ->where('company', 'mybank')->update([
                                'status' => 1,
                                'status_desc' => '开通成功',
                            ]);


                        MyBankStoreTem::where('OrderNo', $OrderNo)->delete();
                        DB::commit();
                    } catch (\Exception $e) {
                        Log::info($e);

                        DB::rollBack();
                        continue;
                    }

                    $this->send('网商银行通道', '审核通过', $store->people_phone, $config_id);

                }

                //失败
                if ($body['RegisterStatus'] == '2') {
                    $this->send('网商银行通道', '未通过原因：' . $body['FailReason'], $store->people_phone, $config_id);
                    try {
                        DB::beginTransaction();
                        //更新通道
                        StorePayWay::where('store_id', $v['OutMerchantId'])
                            ->where('ways_type', 3001)->update([
                                'status' => 3,
                                'status_desc' => $body['FailReason'],
                            ]);

                        StorePayWay::where('store_id', $v['OutMerchantId'])
                            ->where('ways_type', 3002)->update([
                                'status' => 3,
                                'status_desc' => $body['FailReason'],
                            ]);
                        MyBankStoreTem::where('OrderNo', $OrderNo)->delete();
                        DB::commit();
                    } catch (\Exception $e) {
                        Log::info($e);

                        DB::rollBack();
                        continue;
                    }
                    continue;
                }

            } catch
            (\Exception $exception) {
                Log::info($exception);
                continue;
            }
        }

    }


    public
    function send($name, $status, $phone, $config_id = '1234')
    {
        try {

            $config = SmsConfig::where('type', '7')->where('config_id', $config_id)->first();
            if (!$config) {
                $config = SmsConfig::where('type', '7')->where('config_id', '1234')->first();
            }
            $data = ["name" => $name, 'status' => $status];
            $this->sendSms($phone, $config->app_key, $config->app_secret, $config->SignName, $config->TemplateCode, $data);
        } catch (\Exception $exception) {
        }
    }


    public
    function sendSms($phone, $app_key, $app_secret, $SignName, $TemplateCode, $data)
    {

        $demo = new AliSms($app_key, $app_secret);
        $response = $demo->sendSms(
            $SignName, // 短信签名
            $TemplateCode, // 短信模板编号
            $phone, // 短信接收者
            $data
        );
        return $response;

    }
}
