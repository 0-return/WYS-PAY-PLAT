<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/9/21
 * Time: 下午5:44
 */

namespace App\Api\Controllers\Qwx;


use App\Models\Merchant;
use App\Models\MerchantStore;
use App\Models\QwxStore;
use App\Models\Store;
use Illuminate\Http\Request;

class StoreController extends \App\Api\Controllers\BaseController
{


    public function add_code(Request $request)
    {
        try {
            //获取请求参数
            $data = $request->all();

            $check_data = [
                'merchant_id' => '收银员',
                'store_id' => '门店',
            ];
            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }

            $store = Store::where('store_id', $data['store_id'])
                ->select('store_name')
                ->first();

            if (!$store) {
                return json_encode([
                    'status' => 2,
                    'message' => '门店不存在'
                ]);
            }

            $MerchantStore = MerchantStore::where('store_id', $data['store_id'])
                ->where('merchant_id', $data['merchant_id'])
                ->select('id')
                ->first();

            if (!$MerchantStore) {
                return json_encode([
                    'status' => 2,
                    'message' => '收银员不存在'
                ]);
            }

            $merchant_name = Merchant::where('id', $data['merchant_id'])
                ->select('name')
                ->first()->name;

            $obj = new BaseController();

            $args = [
                'dm_code' => [
                    'o2o_dm_code_wxpayyihoupay_wxpay' => [
                        'merchant_no' => $data['store_id'],
                        'merchant_key' => '88888888',
                        'host' => url(''),
                        'proc_tpl' => [
                            'pay' => 'RT00046',
                            'refund' => 'RT00047',
                        ]
                    ],
                    'o2o_dm_code_wxpayyihoupay_alipay' => [
                        'merchant_no' => $data['store_id'],
                        'merchant_key' => '88888888',
                        'host' => url(''),
                        'proc_tpl' => [
                            'pay' => 'RT00044',
                            'refund' => 'RT00045',
                        ]
                    ],
                  //  'shift_tpl' => 'RT00005',
                ]
            ];


            $data_request = [
                'client_id' => '0',
                'appid' => '26',
                'name' => $merchant_name . '-' . $data['merchant_id'],//设备名称
                'client_name' => $store->store_name,//商户名称
                'args' => json_encode($args),
            ];
            $string = '/0/device/add?' . $obj->getSignContent($data_request) . '&20180910shanghushouyingtai';

            $url = "http://dct.semoor.cn/o2o_api/client.php/0/device/add?" . $obj->getSignContent($data_request) . '&sign=' . sha1($string);
            //  dd($url);
            $re = $obj->curl_get($url);

            $re_data = json_decode($re, true);
            if (isset($re_data['error'])) {
                return json_encode([
                    'status' => 2,
                    'message' => $re_data['error']
                ]);
            }
            $client_id = $re_data['client_id'];
            $device_id = $re_data['id'];
            $insert = [
                'store_id' => $data['store_id'],
                'store_name' => $store->store_name,
                'merchant_id' => $data['merchant_id'],
                'merchant_name' => $merchant_name,
                'device_id' => $device_id,
                'secret_key' => $re_data['secretkey'],
                'client_id' => $client_id
            ];

            QwxStore::create($insert);


            //开启
            $data_request = [
                'client_id' => '0',
                'appid' => '26',
            ];
            //{client_id}/device/{device_id}/enabled/{enabled}
            $string = '/' . $client_id . '/device/' . $device_id . '/enabled/1?' . $obj->getSignContent($data_request) . '&20180910shanghushouyingtai';
            $url = "http://dct.semoor.cn/o2o_api/client.php/" . $client_id . "/device/" . $device_id . "/enabled/1?" . $obj->getSignContent($data_request) . '&sign=' . sha1($string);
            //  dd($url);
            $re = $obj->curl_get($url);
            $re_data = json_decode($re, true);
            if (isset($re_data['error'])) {
                return json_encode([
                    'status' => 2,
                    'message' => $re_data['error']
                ]);
            }

            return json_encode([
                'status' => 1,
                'message' => '添加成功',
                'data' => $insert
            ]);


        } catch (\Exception $exception) {

            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage()
            ]);

        }

    }


    public function code_list(Request $request)
    {
        try {
            //获取请求参数
            $data = $request->all();

            $check_data = [
                'store_id' => '门店',
            ];
            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }

            $store = Store::where('store_id', $data['store_id'])
                ->select('store_name')
                ->first();

            if (!$store) {
                return json_encode([
                    'status' => 2,
                    'message' => '门店不存在'
                ]);
            }


            $obj = QwxStore::where('store_id', $data['store_id']);
            $this->t = $obj->count();
            $data = $this->page($obj)->get();
            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);

        } catch (\Exception $exception) {

            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage()
            ]);

        }

    }

    //删除激活码
    public function del_code(Request $request)
    {
        try {
            //获取请求参数
            $public = $this->parseToken();
            $data = $request->all();
            $device_id = $request->get('device_id');
            $store_id = $request->get('store_id');
            $merchant_id = $request->get('merchant_id');


            $check_data = [
                'merchant_id' => '收银员',
                'store_id' => '门店',
            ];
            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }

            if ($public->level > 1) {
                return json_encode(['status' => 2, 'message' => '没有权限删除']);
            }


            $store = Store::where('store_id', $data['store_id'])
                ->select('id')
                ->first();

            if (!$store) {
                return json_encode([
                    'status' => 2,
                    'message' => '门店不存在'
                ]);
            }
            QwxStore::where('device_id', $device_id)
                ->where('store_id', $store_id)
                ->where('merchant_id', $merchant_id)
                ->delete();

            return json_encode([
                'status' => 1,
                'message' => '删除成功',
            ]);

        } catch (\Exception $exception) {

            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage()
            ]);

        }

    }


}