<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/10/23
 * Time: 1:54 PM
 */

namespace App\Api\Controllers\Config;


use App\Http\Controllers\Controller;
use App\Models\FuiouConfig;
use App\Models\FuiouStore;
use App\Models\HConfig;
use App\Models\HStore;
use App\Models\JdConfig;
use App\Models\JdStore;
use App\Models\Store;

class FuiouConfigController extends Controller
{

    public function fuiou_config($config_id)
    {
        //配置取缓存
        $config = FuiouConfig::where('config_id', $config_id)
            ->first();
        if (!$config) {
            $config = FuiouConfig::where('config_id', '1234')
                ->first();
        }

        return $config;
    }


    public function fuiou_merchant($store_id, $store_pid)
    {

        if ($store_pid) {
            //分店配置
            $H_merchant = FuiouStore::where('store_id', $store_id)->first();
            if (!$H_merchant) {
                $store_pid_id = '';
                $store_p = Store::where('id', $store_pid)
                    ->select('store_id')
                    ->first();

                if ($store_p) {
                    $store_pid_id = $store_p->store_id;
                }

                $H_merchant = FuiouStore::where('store_id', $store_pid_id)->first();
            }


        } else {
            $H_merchant = FuiouStore::where('store_id', $store_id)->first();
        }

        return $H_merchant;
    }

}