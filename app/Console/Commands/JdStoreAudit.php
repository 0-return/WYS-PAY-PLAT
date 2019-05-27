<?php

namespace App\Console\Commands;

use Aliyun\AliSms;
use App\Api\Controllers\Config\JdConfigController;
use App\Api\Controllers\Jd\StoreController;
use App\Api\Controllers\MyBank\BaseController;
use App\Models\JdStore;
use App\Models\JdStoreItem;
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

class JdStoreAudit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jd-audit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'jd-audit';

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
        try {
            $JdStoreItem = JdStoreItem::all()->toArray();

            foreach ($JdStoreItem as $k => $v) {
                $config = new JdConfigController();
                $store_id = $v['store_id'];
                $jd_config = $config->jd_config($v['config_id']);
                $merchantNo = $v['merchant_no'];
                $store_status = [
                    'request_url' => 'https://psi.jd.com/merchant/status/queryApplySingle',
                    'agentNo' => $jd_config->agentNo,
                    'merchantNo' => $merchantNo,
                    'serialNo' => "" . time() . "",
                    'store_md_key' => $jd_config->store_md_key,
                    'store_des_key' => $jd_config->store_des_key,
                ];

                $OBJ = new StoreController();
                $re = $OBJ->store_status($store_status);
                if ($re['code'] == "0000") {
                    $status = $re['dataList'][0]['status'];
                    $statusMsg = $re['dataList'][0]['statusMsg'];

                    $data_up = [
                        'status' => 2,
                        'status_desc' => '审核中'
                    ];
                    //成功
                    if ($status == '4') {
                        $data_up = [
                            'status' => 1,
                            'status_desc' => $statusMsg
                        ];


                        //操作据库
                        if ($v['store_true'] == 1 && $v['pay_true'] == 1) {
                            //更新库
                            $MyBankStore = JdStore::where('store_id', $store_id)->first();
                            if ($MyBankStore) {
                                $MyBankStore->update($v);
                                $MyBankStore->save();
                            } else {
                                JdStore::create($v);
                            }


                            //更新密钥
                            $store_keys = [
                                'request_url' => 'https://psi.jd.com/merchant/status/queryMerchantKeys',
                                'agentNo' => $jd_config->agentNo,
                                'merchantNo' => $merchantNo,
                                'serialNo' => "" . time() . "",
                                'store_md_key' => $jd_config->store_md_key,
                                'store_des_key' => $jd_config->store_des_key,
                            ];

                            $OBJ = new StoreController();
                            $re = $OBJ->store_keys($store_keys);
                            if ($re['code'] == "0000") {
                                $desKey = $re['data']['desKey'];
                                $mdKey = $re['data']['mdKey'];

                                JdStore::where('store_id', $store_id)->update([
                                    'md_key' => $mdKey,
                                    'des_key' => $desKey,
                                ]);

                            } else {
                                continue;
                            }


                            //删除临时库
                            JdStoreItem::where('store_id', $v['store_id'])->delete();
                        }

                    }

                    //审核通过，待商户确认
                    if ($status == '3') {
                        $data_up = [
                            'status' => 1,
                            'status_desc' =>'开通成功' //$statusMsg
                        ];
                    }

                    //审核不通过
                    if ($status == '2') {
                        $data_up = [
                            'status' => 3,
                            'status_desc' => $statusMsg
                        ];

                    }


                    StorePayWay::where('store_id', $v['store_id'])
                        ->where('company', 'jdjr')
                        ->update($data_up);
                } else {
                    continue;
                }


            }

        } catch (\Exception $exception) {
            Log::info($exception);
        }
    }


    public function send($name, $status, $phone, $config_id = '1234')
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


    public function sendSms($phone, $app_key, $app_secret, $SignName, $TemplateCode, $data)
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
