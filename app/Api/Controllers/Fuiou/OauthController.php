<?php

namespace App\Api\Controllers\Fuiou;

use App\Api\Controllers\Config\FuiouConfigController;
use App\Api\Controllers\Config\HConfigController;
use App\Api\Controllers\Config\JdConfigController;
use App\Api\Controllers\Config\MyBankConfigController;
use App\Api\Controllers\Config\NewLandConfigController;
use EasyWeChat\Factory;
use Illuminate\Http\Request;

class OauthController extends BaseController
{
    //授权
    public function oauth(Request $request)
    {
        $sub_info = $request->get('state');
        $sub_info_arr = json_decode(base64_decode((string)$sub_info), true);
        $config_id = $sub_info_arr['config_id'];
        $store_id = $sub_info_arr['store_id'];
        $store_pid = $sub_info_arr['store_pid'];
        //配置
        $config = new FuiouConfigController();
        $fuiou_config = $config->fuiou_config($config_id);
        if (!$fuiou_config) {
            return json_encode([
                'status' => 2,
                'message' => '富友配置不存在请检查配置'
            ]);
        }


        //去获取openID
        $fuiou__merchant = $config->fuiou_merchant($store_id, $store_pid);
        if (!$fuiou__merchant) {
            return json_encode([
                'status' => 2,
                'message' => '富友商户号不存在'
            ]);
        }

        $request = [
            'ins_cd' => $fuiou_config->ins_cd,//机构号
            'mchnt_cd' => $fuiou__merchant->mchnt_cd,//商户号
            'pem' => $fuiou_config->my_private_key,
        ];
        $obj = new \App\Api\Controllers\Fuiou\PayController();
        $return = $obj->get_openid($request, $sub_info);

        if ($return['status'] == 0) {
            return json_encode([
                'status' => 2,
                'message' => $return['message']
            ]);
        } else {
            return redirect($return['data']['url']);
        }

    }

    //显示页面
    public function pay_view(Request $request)
    {
        $data = json_decode(base64_decode((string)$request->get('data')), true);
        $data['open_id'] = $request->get('openid');
        return view('fuiou.weixin', compact('data'));
    }


}
