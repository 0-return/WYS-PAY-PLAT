<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/9/7
 * Time: 下午3:27
 */

namespace App\Api\Controllers\OpenApi;


use Alipayopen\Sdk\AopClient;
use Alipayopen\Sdk\Request\AlipayTradeQueryRequest;
use App\Models\DeviceOem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SelectController extends BaseController
{

    //获得设备的接口地址
    public function GetRequestApiUrl(Request $request)
    {
        try {
            //获取请求参数
            $data = $request->getContent();
            Log::info($data);
            //$data_all = $request->all();
            $data = json_decode($data, true);

            //验证签名
            $check = $this->check_md5($data);
            if ($check['return_code'] == 'FALL') {
                return $this->return_data($check);
            }


            $device_id = $data['device_id'];
            $device_type = $data['device_type'];
            if (!$device_id) {
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '设备device_id不能为空',
                ];
                return $this->return_data($err);
            }
            if (!$device_type) {

                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '设备device_type不能为空',
                ];
                return $this->return_data($err);
            }


            $DeviceOem = DeviceOem::where('device_id', $device_id)
                ->where('device_type', $device_type)
                ->select('Request')
                ->first();

            if (!$DeviceOem) {
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '设备不存在',
                ];
                return $this->return_data($err);
            }


            $data = [
                'return_code' => "SUCCESS",
                'return_msg' => "数据返回成功",
                'Request' => $DeviceOem->Request,
                'ScanPay' => '/api/devicepay/scan_pay',
                'QrPay' => '/api/devicepay/qr_pay',
                'QrAuthPay' => '/api/devicepay/qr_auth_pay',
                'PayWays' => '/api/devicepay/store_pay_ways',
                'OrderQuery' => '/api/devicepay/order_query',
                'Order' => '/api/devicepay/order',
                'OrderList' => '/api/devicepay/order_list',
                'Refund' => '/api/devicepay/refund',
            ];

            return $this->return_data($data);


        } catch (\Exception $exception) {

            $err = [
                'return_code' => 'FALL',
                'return_msg' => $exception->getMessage() . $exception->getLine(),
            ];
            return $this->return_data($err);
        }

    }

}