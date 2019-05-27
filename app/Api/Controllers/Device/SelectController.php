<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/8/9
 * Time: 下午2:37
 */

namespace App\Api\Controllers\Device;


use App\Models\DeviceOem;
use Illuminate\Http\Request;

class SelectController extends \App\Api\Controllers\BaseController
{
    //获得设备的接口地址
    public function device_oem_lists(Request $request)
    {
        try {
            $user_merchant = $this->parseToken();
            $device_id = $request->get('device_id', '');
            $user_id = $user_merchant->user_id;
            $where = [];

            if ($device_id) {
                $where[] = ['device_id', '=', $device_id];
            }

            if ($user_id) {
                $where[] = ['user_id', '=', $user_id];
            }

            $data = DeviceOem::where($where);
            $this->t = $data->count();
            $data = $this->page($data)->get();
            $this->status = 1;
            $this->message = "数据返回成功";
            return $this->format($data);


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }

    //删除
    public function device_oem_del(Request $request)
    {
        try {
            $user_merchant = $this->parseToken();
            $device_id = $request->get('device_id', '');
            $device_type = $request->get('device_type', '');
            $user_id = $user_merchant->user_id;
            $where = [];
            $check_data = [
                'device_id' => '设备ID',
                'device_type' => '设备类型',
            ];
            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }
            if ($device_id) {
                $where[] = ['device_id', '=', $device_id];
            }
            if ($device_type) {
                $where[] = ['device_type', '=', $device_type];
            }

            if ($device_id) {
                $where[] = ['user_id', '=', $user_id];
            }
            $data = DeviceOem::where($where)->delete();

            $this->status = 1;
            $this->message = "数据删除成功";
            return $this->format($request->except('token'));


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }

    //导入
    public function device_oem_import(Request $request)
    {
        try {
            $user_merchant = $this->parseToken();
            if (empty($_FILES)) {
                $this->status = 2;
                $this->message = '请上传xlsx表格！';
                return $this->format();
            }

            $file_arr = array_shift($_FILES);

            if ($file_arr['error'] !== 0) {

                $this->status = 2;
                $this->message = '请上传xlsx表格！';
                return $this->format();
            }
            // var_dump($file_arr);die;
            $file = $file_arr['tmp_name'];


            $excel_data = \App\Common\Excel\Excel::_readExcel($file);

            foreach ($excel_data as $k => $v) {

                if ($k == 0) {
                    continue;
                }

                if (trim($v[0]) == "") {
                    continue;
                }

                if (trim($v[1]) == "") {
                    continue;
                }


                $ym = trim($v[2]);

                $insert = [
                    'user_id' => $user_merchant->user_id,
                    'device_id' => trim($v[0]),
                    'device_type' => trim($v[1]),
                    'Request' => $ym,
                    'ScanPay' => '/api/devicepay/scan_pay',
                    'QrPay' => '/api/devicepay/qr_pay',
                    'QrAuthPay' => '/api/devicepay/qr_auth_pay',
                    'PayWays' => '/api/devicepay/store_pay_ways',
                    'OrderQuery' => '/api/devicepay/order_query',
                    'Order' => '/api/devicepay/order',
                    'OrderList' => '/api/devicepay/order_list',
                    'Refund' => '/api/devicepay/refund',
                ];

                $newland_merchant = DeviceOem::where('device_id', trim($v[0]))
                    ->where('device_type', trim($v[1]))
                    ->first();

                if ($newland_merchant) {

                    $this->status = 2;
                    $this->message = '已经有重复的设备ID请删除在导入';
                    return $this->format();

                } else {

                    DeviceOem::create($insert);

                }
            }

            $this->status = 1;
            $this->message = '导入成功';
            return $this->format();


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }


}