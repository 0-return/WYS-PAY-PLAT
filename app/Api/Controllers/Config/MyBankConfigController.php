<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2017/10/20
 * Time: 下午4:08
 */

namespace App\Api\Controllers\Config;


use App\Http\Controllers\Controller;
use App\Models\MyBankConfig;
use App\Models\MyBankStore;
use App\Models\Store;
use Illuminate\Support\Facades\Cache;

class MyBankConfigController extends Controller
{


    public function MyBankConfig($config_id, $wx_AppId = "")
    {

        //代表有新渠道
        if ($wx_AppId) {
            $config = MyBankConfig::where('config_id', '1234-1')->first();

            if (!$config) {
                //新渠道没有配置走旧渠道
                $config = MyBankConfig::where('config_id', '1234')->first();
            }
        } else {
            $config = MyBankConfig::where('config_id', '1234')->first();

        }


        return $config;


        //公共的配置

        //服务商的配置取缓存
        if (Cache::has('MyBankConfig=' . $config_id)) {
            $config = Cache::get('MyBankConfig=' . $config_id);
        } else {
            $config = MyBankConfig::where('config_id', $config_id)->first();
            Cache::put('MyBankConfig=' . $config_id, $config, 1);
        }
        //有梦想的配置
        if (Cache::has('MyBankConfig=123')) {
            $config123 = Cache::get('MyBankConfig=123');
        } else {
            $config123 = MyBankConfig::where('config_id', '123')->first();
            Cache::put('MyBankConfig=123', $config123, 1);
        }

        //为空读取有梦想的配置
        if (!$config) {
            $config = $config123;
        }
        if ($config->wx_AppId == "null") {
            $config->wx_AppId = $config123->wx_AppId;
        }
        if ($config->wx_Secret == "null") {
            $config->wx_Secret = $config123->wx_Secret;
        }
        if ($config->SubscribeAppId == "null") {
            $config->SubscribeAppId = $config123->SubscribeAppId;
        }

        if (!$config->wx_AppId) {
            $config->wx_AppId = $config123->wx_AppId;
            $config->wx_Secret = $config123->wx_Secret;
        }

        if (!$config->SubscribeAppId) {
            $config->SubscribeAppId = $config123->SubscribeAppId;
        }
        if (!$config->ali_pid) {
            $config->ali_pid = $config123->ali_pid;
        }

        return $config;

    }


    public function mybank_merchant($store_id, $store_pid)
    {

        if ($store_pid) {
            //分店配置
            $MyBankStore = MyBankStore::where('OutMerchantId', $store_id)
                ->inRandomOrder()
                ->first();
            if (!$MyBankStore) {
                $store_pid_id = '';
                $store_p = Store::where('id', $store_pid)
                    ->select('store_id')
                    ->first();

                if ($store_p) {
                    $store_pid_id = $store_p->store_id;
                }

                $MyBankStore = MyBankStore::where('OutMerchantId', $store_pid_id)
                    ->inRandomOrder()
                    ->first();
            }


        } else {
            $MyBankStore = MyBankStore::where('OutMerchantId', $store_id)
                ->inRandomOrder()
                ->first();
        }

        return $MyBankStore;
    }


}