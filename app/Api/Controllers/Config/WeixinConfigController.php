<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2017/10/20
 * Time: 下午3:41
 */

namespace App\Api\Controllers\Config;


use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Models\WeixinConfig;
use App\Models\WeixinNotifyTemplate;
use App\Models\WeixinStore;
use Illuminate\Support\Facades\Log;

class WeixinConfigController extends Controller
{

    public function weixin_config($config_id)
    {

        $config = WeixinConfig::where('config_id', $config_id)->first();

        if (!$config) {
            $config = WeixinConfig::where('config_id', '1234')->first();
        }
        $options = [
            'app_id' => $config->app_id,
            'app_secret' => $config->app_secret,
            'payment' => [
                'merchant_id' => $config->wx_merchant_id,
                'key' => $config->key,
                'cert_path' => public_path() . $config->cert_path, // XXX: 绝对路径！！！！
                'key_path' => public_path() . $config->key_path,      // XXX: 绝对路径！！！！
                'notify_url' => $config->notify_url,       // 你也可以在下单时单独设置来想覆盖它
            ],
        ];

        return $options;

    }

    public function weixin_config_obj($config_id)
    {

        $config = WeixinConfig::where('config_id', $config_id)->first();
        if (!$config) {
            $config = WeixinConfig::where('config_id', '1234')->first();
        }
        return $config;

    }

    public function weixin_merchant($store_id, $store_pid = "")
    {
        if ($store_pid) {
            //分店配置
            $WeixinStore = WeixinStore::where('store_id', $store_id)->first();
            if (!$WeixinStore) {
                $store_pid_id = "";//上级id
                $store_p = Store::where('id', $store_pid)
                    ->select('store_id')
                    ->first();
                if ($store_p) {
                    $store_pid_id = $store_p->store_id;
                }

                $WeixinStore = WeixinStore::where('store_id', $store_pid_id)->first();
            }

        } else {
            $WeixinStore = WeixinStore::where('store_id', $store_id)->first();
        }

        return $WeixinStore;
    }

    public function weixin_template($config_id, $type)
    {

        $WeixinNotifyTemplate = WeixinNotifyTemplate::where('config_id', $config_id)
            ->where('type', $type)
            ->first();
        if (!$WeixinNotifyTemplate) {
            $WeixinNotifyTemplate = WeixinNotifyTemplate::where('config_id', '1234')
                ->where('type', $type)
                ->first();
        }

        return $WeixinNotifyTemplate;

    }


}