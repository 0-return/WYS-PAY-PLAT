<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/12/13
 * Time: 2:27 PM
 */

namespace App\Api\Controllers\Huodong;


use App\Api\Controllers\BaseController;
use Illuminate\Http\Request;

class JdbtController extends BaseController
{


    public function jdbt(Request $request)
    {
        try {
            $public = $this->parseToken();
            $phone = $request->get('phone', '');
            $money = $request->get('money', '');
            $number = $request->get('number', '');
            $time_start = $request->get('time_start');
            $time_end = $request->get('time_end');
            $pay_password = $request->get('pay_password');


            $check_data = [
                'time_start' => '开始时间',
                'time_end' => '结束时间',
                'pay_password' => '支付密码',
                'phone' => '手机号',
                'money' => '推广金额',
                'number' => '推广数',
            ];


            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }

            return json_encode([
                'status' => 1,
                'message' => '数据录入成功'
            ]);

        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }


    }

}