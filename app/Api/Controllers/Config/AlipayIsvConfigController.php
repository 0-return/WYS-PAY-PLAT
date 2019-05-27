<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2017/10/20
 * Time: 下午3:41
 */

namespace App\Api\Controllers\Config;


use App\Http\Controllers\Controller;
use App\Models\AlipayAppOauthUsers;
use App\Models\AlipayIsvConfig;
use App\Models\Store;

class AlipayIsvConfigController extends Controller
{

    public function AlipayIsvConfig($config_id, $config_type = '01')
    {

        //公共的配置

        //配置取缓存
        $config = AlipayIsvConfig::where('config_id', $config_id)
            ->where('config_type', $config_type)
            ->first();

        //为空读取01
        if (!$config) {
            $config = AlipayIsvConfig::where('config_id', '1234')
                ->where('config_type', '01')
                ->first();
        }

        return $config;

    }

    //支付宝商户授权查询
    public function alipay_auth_info($store_id, $store_pid)
    {
        //分店
        if ($store_pid) {
            //查总店信息
            $store_pid_id = "";//上级id
            $store_p = Store::where('id', $store_pid)
                ->select('store_id')
                ->first();
            if ($store_p) {
                $store_pid_id = $store_p->store_id;
            }

            //分店自己的
            $storeInfo = AlipayAppOauthUsers::where('store_id', $store_id)->first();

            //分店存在
            if ($storeInfo) {
                //判断商户是否存在令牌没有就走总店的
                if ($storeInfo->app_auth_token == "") {
                    $app_auth_token = '';
                    $storeInfo_pid = AlipayAppOauthUsers::where('store_id', $store_pid_id)
                        ->select('app_auth_token')
                        ->first();
                    if ($storeInfo_pid) {
                        $app_auth_token = $storeInfo_pid->app_auth_token;
                    }

                    $storeInfo->app_auth_token = $app_auth_token;
                }

                return $storeInfo;


            } else {
                //总店
                $storeInfo_pid = AlipayAppOauthUsers::where('store_id', $store_pid_id)->first();

                return $storeInfo_pid;
            }

            //总店
        } else {
            $storeInfo = AlipayAppOauthUsers::where('store_id', $store_id)->first();
            return $storeInfo;
        }

    }

}