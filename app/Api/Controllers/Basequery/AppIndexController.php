<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2017/11/22
 * Time: 上午11:21
 */

namespace App\Api\Controllers\Basequery;


use App\Api\Controllers\BaseController;
use App\Models\AppOem;
use App\Models\User;
use Illuminate\Http\Request;

class AppIndexController extends BaseController
{


    //客服电话
    public function contact(Request $request)
    {
        try {
            $user = $this->parseToken();
            //服务商
            if ($user->type == 'user') {
                if ($user['level'] == 1) {
                    $user_id = $user->user_id;
                } else {
                    $user_id = $user->pid;
                }
                $users = User::where('id', $user_id)->first();
                $pid_phone = '';
                $pid_name = '';
                if ($users) {
                    $pid_phone = $users->phone;
                    $pid_name = $users->name;

                }
            } //商户收银员
            else {
                $users = User::where('id', $user->user_id)->first();
                $pid_phone = '';
                $pid_name = '';
                if ($users) {
                    $pid_phone = $users->phone;
                    $pid_name = $users->name;
                }
            }

            $oem = AppOem::where('config_id', $user->config_id)->first();
            $sys_phone = '4008500508';
            if ($oem && $oem->phone) {
                $sys_phone = $oem->phone;
                $sys_name = $oem->name;
            } else {
                $oem = AppOem::where('config_id', '1234')->first();
                $sys_phone = $oem->phone;
                $sys_name = $oem->name;
            }
            $data = [
                'status' => 1,
                'data' => [
                    'sys_phone' => $sys_phone,
                    'sys_name' => $sys_name,
                    'pid_phone' => $sys_phone,
                    'pid_name' => $sys_name,
                ]
            ];

            return json_encode($data);

        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }
    }

}