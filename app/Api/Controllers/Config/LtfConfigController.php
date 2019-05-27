<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/10/23
 * Time: 1:54 PM
 */

namespace App\Api\Controllers\Config;


use App\Http\Controllers\Controller;
use App\Models\JdConfig;
use App\Models\JdStore;
use App\Models\LtfConfig;
use App\Models\LtfStore;
use App\Models\Store;

class LtfConfigController extends Controller
{

    public function ltf_config($config_id)
    {
        //配置取缓存
        $config = LtfConfig::where('config_id', $config_id)
            ->first();
        if (!$config) {
            $config = LtfConfig::where('config_id', '1234')
                ->first();
        }

        return $config;
    }


    public function ltf_merchant($store_id, $store_pid)
    {

        if ($store_pid) {
            //分店配置
            $LtfStore = LtfStore::where('store_id', $store_id)->first();
            if (!$LtfStore) {
                $store_pid_id = '';
                $store_p = Store::where('id', $store_pid)
                    ->select('store_id')
                    ->first();

                if ($store_p) {
                    $store_pid_id = $store_p->store_id;
                }

                $LtfStore = LtfStore::where('store_id', $store_pid_id)->first();
            }


        } else {
            $LtfStore = LtfStore::where('store_id', $store_id)->first();
        }

        return $LtfStore;
    }

}