<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/9/7
 * Time: 下午2:28
 */

namespace App\Api\Controllers\Self;


use Illuminate\Http\Request;
use App\Api\Controllers\BaseController;


class SelectController extends BaseController
{


    //会员
    public function member(Request $request)
    {

        try {
            $device_id = $request->get('device_id', '');
            $device_type = $request->get('device_type', '');
            $number = $request->get('number', '');


            if ($number == "18155855951") {
                $data = [
                    'number' => $number,
                    'name' => '阮浩文',
                    'integral' => '100',
                    'vip' => '1',
                    'end_time' => '2028-10-10 01:02:00',
                    'created_at' => '2018-10-10 01:02:00',
                    'updated_at' => '2018-10-10 01:02:00',
                ];

                return json_encode([
                    'status' => 1,
                    'data' => $data,
                ]);
            } else {
                return json_encode([
                    'status' => 2,
                    'message' => '会员卡不存在',
                ]);
            }


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }

    //校验优惠卷
    public function check_coupon(Request $request)
    {

        try {
            $device_id = $request->get('device_id', '');
            $device_type = $request->get('device_type', '');
            $number = $request->get('number', '');
            $total_amount = $request->get('total_amount', '');
            $pay_amount = $request->get('pay_amount', '');

            //小于100元 不符合条件
            if ((int)$total_amount < 10000) {
                return json_encode([
                    'status' => 2,
                    'message' => '暂不符合使用条件',
                ]);
            }

            if ($number == "123") {
                $data = [
                    'number' => $number,
                    'coupon' => '1200',
                    's_time' => '2018-01-01 00:00:00',
                    'e_time' => '2018-11-01 00:00:00',
                ];

                return json_encode([
                    'status' => 1,
                    'data' => $data,
                ]);
            }


            if ($number == "234") {
                $data = [
                    'number' => $number,
                    'coupon' => '1000',
                    's_time' => '2018-01-01 00:00:00',
                    'e_time' => '2018-11-01 00:00:00',
                ];

                return json_encode([
                    'status' => 1,
                    'data' => $data,
                ]);
            }


            return json_encode([
                'status' => 2,
                'message' => '优惠券不存在',
            ]);


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }


}