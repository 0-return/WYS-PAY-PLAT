<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/12/3
 * Time: 11:39 AM
 */

namespace App\Api\Controllers\Errors;


use App\Api\Controllers\ApiController;
use App\Api\Controllers\Self\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SelfErrorsController extends BaseController
{

    //自助设备报错
    public function self_errors(Request $request)
    {

        $data = $request->all();
        Log::info($data);
        $device_id = $request->get('device_id');
        $device_type = $request->get('device_type');

        //验证签名
        $check = $this->check_md5($data);
        if ($check['return_code'] == 'FALL') {
            return $this->return_data($check);
        }

        $re_data = [
            'return_code' => 'SUCCESS',//SUCCESS/FALL 此字段是通信标识，非交易标识，交易是否成功需要查看result_code来判断
            'return_msg' => null,
            'result_code' => 'SUCCESS',
            'result_msg' => '数据上报成功',
            'device_id' => $device_id,
            'device_type' => $device_type,
        ];

        return $this->return_data($re_data);
    }

}