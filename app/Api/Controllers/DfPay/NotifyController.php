<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/12/25
 * Time: 7:28 AM
 */

namespace App\Api\Controllers\DfPay;


use App\Models\DfOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotifyController extends \App\Api\Controllers\BaseController
{

    public function pay_notify(Request $request)
    {

        try {

            $all = $request->all();
            Log::info($all);
            $str = $all['dstbdata'];
            parse_str($str, $arr);
            //成功
            if ($arr['returncode'] == "00") {
                $in_data = [
                    'order_id' => $arr['orderid'],
                    'in_order_id' => '',
                    'deal_time' => date('Y-m-d H:i:s', strtotime($arr['transdate'] . $arr['transtime'])),
                    'pay_status' => 1,
                    'pay_status_desc' => '代付成功',
                ];
            } else {
                //失败
                $in_data = [
                    'order_id' => $arr['orderid'],
                    'in_order_id' => '',
                    'deal_time' => date('Y-m-d H:i:s', strtotime($arr['transdate'] . $arr['transtime'])),
                    'pay_status' => 3,
                    'pay_status_desc' => $arr['errtext'],
                ];
            }

            DfOrder::where('order_number', $arr['dsorderid'])
                ->update($in_data);


            return '00';


        } catch (\Exception $exception) {
            Log::info($exception);
        }
    }


}