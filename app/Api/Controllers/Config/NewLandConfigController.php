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
use App\Models\NewLandConfig;
use App\Models\NewLandStore;
use App\Models\Store;

class NewLandConfigController extends Controller
{

    public function new_land_config($config_id)
    {
        //配置取缓存
        $config = NewLandConfig::where('config_id', $config_id)
            ->first();
        if (!$config) {
            $config = NewLandConfig::where('config_id', '1234')->first();
        }

        return $config;
    }


    public function new_land_merchant($store_id, $store_pid)
    {

        if ($store_pid) {
            //分店配置
            $NewLandStore = NewLandStore::where('store_id', $store_id)
                ->inRandomOrder()
                ->first();
            if (!$NewLandStore) {
                $store_pid_id = '';
                $store_p =Store::where('id', $store_pid)
                    ->select('store_id')
                    ->inRandomOrder()
                    ->first();

                if ($store_p) {
                    $store_pid_id = $store_p->store_id;
                }

                $NewLandStore = NewLandStore::where('store_id', $store_pid_id)
                    ->inRandomOrder()
                    ->first();
            }


        } else {
            $NewLandStore = NewLandStore::where('store_id', $store_id)
                ->inRandomOrder()
                ->first();
        }

        return $NewLandStore;
    }

}