<?php

namespace App\Console\Commands;

use Aliyun\AliSms;
use App\Api\Controllers\Config\JdConfigController;
use App\Api\Controllers\Config\NewLandConfigController;
use App\Api\Controllers\Jd\StoreController;
use App\Api\Controllers\MyBank\BaseController;
use App\Models\JdStore;
use App\Models\JdStoreItem;
use App\Models\MerchantStore;
use App\Models\MyBankStore;
use App\Models\MyBankStoreTem;
use App\Models\NewLandConfig;
use App\Models\NewLandStore;
use App\Models\NewLandStoreItem;
use App\Models\SmsConfig;
use App\Models\Store;
use App\Models\StorePayWay;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NewLandStoreAudit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newland-audit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'newland-audit';

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
            $JdStoreItem = NewLandStoreItem::all()->toArray();

            foreach ($JdStoreItem as $k => $v) {

                $config_obj = new NewLandConfigController();
                $store_id = $v['store_id'];
                $config = $config_obj->new_land_config($v['config_id']);
                $aop = new \App\Common\XingPOS\Aop();
                $aop->key = $config->nl_key;
                $aop->version = 'V1.0.1';
                $aop->org_no = $config->org_no;

                $sign_data = [
                    'mercId' => $v['nl_mercId'],
                ];

                $request_obj = new  \App\Common\XingPOS\Request\XingStoreShangHuChaXun();
                $request_obj->setBizContent($sign_data);
                $return = $aop->executeStore($request_obj);

                //报错系统
                if ($return['msg_cd'] != '000000') {
                    Log::info($return);
                    continue;
                }

                //

                if (isset($return['check_flag'])) {
                    $check_flag = $return['check_flag'];
                } else {
                    $check_flag = '2';
                }


                //审核中
                if ($check_flag == '3') {
                    continue;
                }
                //成功
                if ($check_flag == '1') {

                    $data_up = [
                        'status' => 1,
                        'status_desc' => '审核成功',
                    ];


                    $key = $return['key'];
                    $trmNo = $return['REC'][0]['trmNo'];

                    //
                    $v['nl_key'] = $key;
                    $v['trmNo'] = $trmNo;
                    $v['check_flag'] = $check_flag;

                    //更新库
                    $MyBankStore = NewLandStore::where('store_id', $store_id)->first();
                    if ($MyBankStore) {
                        $MyBankStore->update($v);
                        $MyBankStore->save();
                    } else {
                        NewLandStore::create($v);
                    }

                    //删除临时库
                    NewLandStoreItem::where('store_id', $v['store_id'])->delete();

                    StorePayWay::where('store_id', $v['store_id'])
                        ->where('company', 'newland')
                        ->update($data_up);
                }

                //审核失败
                if ($check_flag == '2') {
                    $data_up = [
                        'status' => 3,
                        'status_desc' => '审核失败',
                    ];
                    //删除临时库
                    NewLandStoreItem::where('store_id', $v['store_id'])->delete();

                    StorePayWay::where('store_id', $v['store_id'])
                        ->where('company', 'newland')
                        ->update($data_up);

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
