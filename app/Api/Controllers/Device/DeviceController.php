<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/7/23
 * Time: 下午1:51
 */

namespace App\Api\Controllers\Device;


use App\Api\Controllers\BaseController;
use App\Models\Device;
use App\Models\Merchant;
use App\Models\MerchantStore;
use App\Models\Store;
use App\Models\VConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MyBank\Tools;

class DeviceController extends BaseController
{


    public function add(Request $request)
    {

        try {
            $user_merchant = $this->parseToken();
            $store_id = $request->get('store_id');
            $merchant_id = $request->input('merchant_id', "");
            $device_type = $request->get('device_type', '');
            $device_name = $request->get('device_name', '');
            $device_no = $request->get('device_no', '');
            $device_key = $request->get('device_key', '');
            $type = $request->get('type', '');
            $config_id = $user_merchant->config_id;
            $data = $request->except(['token']);
            $check_data = [
                'store_id' => '门店id',
                'device_type' => '设备类型',
                'device_no' => '设备编号',
                'device_key' => '设备秘钥',
                'device_name' => '设备名称'
            ];
            $check = $this->check_required($data, $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }
            $merchant_name = "";


            if ($merchant_id == "") {
                $merchant_id = "";
            }

            $store = Store::where('store_id', $store_id)
                ->select('store_name')
                ->first();

            if (!$store) {
                return json_encode([
                    'status' => 2,
                    'message' => '门店不存在'
                ]);
            }

            $Device = Device::where('device_no', $device_no)
                ->where('device_type', $device_type)
                ->where('store_id', '!=', $store_id)
                ->first();

            if ($Device) {
                return json_encode([
                    'status' => 2,
                    'message' => '设备已经被' . $Device->store_name . '绑定',
                ]);
            }


            $store_name = $store->store_name;


            $VConfig = VConfig::where('config_id', $config_id)
                ->select(
                    'zw_token',
                    'zlbz_token'
                )->first();

            if (!$VConfig) {
                $VConfig = VConfig::where('config_id', '1234')
                    ->select(
                        'zw_token',
                        'zlbz_token'
                    )->first();
            }

            //云喇叭添加-智联
            if ($device_type == 'v_zlbz_1') {
                //强制解绑
                $datadel = [
                    'id' => $device_no,
                    'm' => '4',
                    'token' => $VConfig->zlbz_token
                ];
                $curl = Tools::curl($datadel, 'http://39.106.131.149/bind.php');
                $curl = json_decode($curl, true);
                //绑定
                $datap = [
                    'id' => $device_no,
                    'm' => '1',
                    'uid' => $store_id,
                    'token' => $VConfig->zlbz_token
                ];

                $curl = Tools::curl($datap, 'http://39.106.131.149/bind.php');
                $curl = json_decode($curl, true);
                if ($curl['errcode'] != 0) {
                    return json_encode([
                        'status' => 2,
                        'message' => $curl['errcode'] . '-' . $curl['errmsg'],
                    ]);
                }
            }


            //v_zw_1 智网云喇叭
            if ($device_type == 'v_zw_1') {
                //1.强制解绑
                $url = "http://cloudspeaker.smartlinkall.com/bind.php?id=" . $device_no . "&m=4&uid=" . $store_id . $device_no . "&token=" . $VConfig->zw_token . "&seq=" . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                $data = Tools::curl_get($url);

                //2.绑定
                $url = "http://cloudspeaker.smartlinkall.com/bind.php?id=" . $device_no . "&m=1&uid=" . $store_id . $device_no . "&token=" . $VConfig->zw_token . "&seq=" . date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));
                $data = Tools::curl_get($url);
                $data = json_decode($data, true);

                if ($data['errcode'] != 0) {
                    return json_encode([
                        'status' => 2,
                        'message' => $data['errmsg'],
                    ]);
                }
            }


            //易联云打印机 k4
            if ($device_type == 'p_yly_k4') {
                $push = new YlianyunAopClient();
                $push_id = "8978";
                $push_key = "7a67e62b938e35dffdd1e0eee039bc83060070df";
                $push_user_name = "有梦想科技";
                $print_key = $device_key;
                $print_name = $device_no;

                $add = $push->action_addprint($push_id, $print_name, $push_user_name, $print_name, '1', $push_key, $print_key);
                if ($add != '1') {
                    $msg = '添加失败';
                    if ($add == '2') {
                        $msg = '不要重复添加打印机';
                    }
                    return json_encode([
                        'status' => 2,
                        'message' => $msg,
                    ]);
                }
            }


            if ($merchant_id) {
                $merchant_ids = explode(',', $merchant_id);

                foreach ($merchant_ids as $k => $v) {

                    $merchant_s = Merchant::where('id', $v)
                        ->select('name')
                        ->first();
                    if ($merchant_s) {
                        $merchant_name = $merchant_s->name;
                    } else {
                        continue;
                    }
                    $indata = [
                        'store_id' => $store_id,
                        'merchant_id' => $v,
                        'merchant_name' => $merchant_name,
                        'store_name' => $store_name,
                        'device_type' => $device_type,
                        'device_name' => $device_name,
                        'device_no' => $device_no,
                        'device_key' => $device_key,
                        'type' => $type,
                        'config_id' => $config_id
                    ];
                    Device::create($indata);
                }

            } else {

                $merchant_s = Merchant::where('id', $merchant_id)
                    ->select('name')
                    ->first();
                if ($merchant_s) {
                    $merchant_name = $merchant_s->name;
                }

                $indata = [
                    'store_id' => $store_id,
                    'merchant_id' => $merchant_id,
                    'merchant_name' => $merchant_name,
                    'store_name' => $store_name,
                    'device_type' => $device_type,
                    'device_name' => $device_name,
                    'device_no' => $device_no,
                    'device_key' => $device_key,
                    'type' => $type,
                    'config_id' => $config_id
                ];
                Device::create($indata);
            }


            $this->status = 1;
            $this->message = "添加成功";
            return $this->format($data);


        } catch (\Exception $exception) {
            Log::info('添加设备');
            Log::info($exception);
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }

    public function up(Request $request)
    {

        try {
            $user_merchant = $this->parseToken();
            $id = $request->get('id');
            $merchant_id = $request->get('merchant_id', '');
            $store_id = $request->get('store_id', '');
            $config_id = $user_merchant->config_id;
            $data = $request->except(['token']);
            $check_data = [
                'id' => '设备id',
            ];
            $check = $this->check_required($data, $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }
            $merchant_name = "";
            if ($merchant_id == "" || $merchant_id == "NULL") {
                $merchant_id = "";
            }
            if ($merchant_id) {
                $merchant_s = Merchant::where('id', $merchant_id)->first();
                if ($merchant_s) {
                    $merchant_name = $merchant_s->name;
                } else {
                    return json_encode([
                        'status' => 2,
                        'message' => '收银员不存在'
                    ]);
                }
            }
            $store = Store::where('store_id', $store_id)
                ->select('store_name')
                ->first();

            if (!$store) {
                return json_encode([
                    'status' => 2,
                    'message' => '门店不存在'
                ]);
            }

            $merchant_ids = explode(',', $merchant_id);
            if (count($merchant_ids) > 1) {
                return json_encode([
                    'status' => 2,
                    'message' => '修改不支持添加多个'
                ]);
            }


            $store_name = $store->store_name;
            $data['merchant_name'] = $merchant_name;
            $data['store_name'] = $store_name;
            $data['config_id'] = $config_id;


            Device::where('id', $id)->update($data);

            $this->status = 1;
            $this->message = "修改成功";
            return $this->format($data);


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }

    public function del(Request $request)
    {

        try {
            $user_merchant = $this->parseToken();
            $id = $request->get('id');
            $data = $request->except(['token']);
            $check_data = [
                'id' => '设备id',
            ];
            $check = $this->check_required($data, $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }
            $device = Device::where('id', $id)->first();
            if (!$device) {
                return json_encode([
                    'status' => 2,
                    'message' => '设备不存在'
                ]);
            }

            $VConfig = VConfig::where('config_id', $device->config_id)
                ->select(
                    'zw_token',
                    'zlbz_token'
                )->first();

            if (!$VConfig) {
                $VConfig = VConfig::where('config_id', '1234')
                    ->select(
                        'zw_token',
                        'zlbz_token'
                    )->first();
            }

            //云喇叭删除
            if ($device->device_type == 'v_zlbz_1') {
                $data = [
                    'id' => $device->device_no,
                    'm' => '0',
                    'uid' => $device->store_id,
                    'token' => $VConfig->zlbz_token
                ];
                $curl = Tools::curl($data, 'http://39.106.131.149/bind.php');
                $curl = json_decode($curl, true);
                if ($curl['errcode'] != 0) {
                    return json_encode([
                        'status' => 2,
                        'message' => $curl['errcode'] . '-' . $curl['errmsg'],
                    ]);
                }
            }


            //易联云删除
            if ($device->device_type == "p_yly_k4") {
                $push = new YlianyunAopClient();
                $push_id = "8978";
                $push_key = "7a67e62b938e35dffdd1e0eee039bc83060070df";
                $print_key = $device->device_key;
                $print_name = $device->device_no;
                $delete = $push->action_removeprinter($push_id, $print_name, $push_key, $print_key);
                if ($delete != '1') {
                    $msg = '删除设备失败';
                    if ($delete == '2') {
                        $msg = '没这个设备';
                    }
                    return json_encode([
                        'status' => 2,
                        'message' => $msg,
                    ]);
                }
            }


            Device::where('id', $id)->delete();

            $this->status = 1;
            $this->message = "删除成功";
            return $this->format($data);


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }

    public function select(Request $request)
    {

        try {
            $user_merchant = $this->parseToken();
            $id = $request->get('id');
            $data = $request->except(['token']);
            $check_data = [
                'id' => '设备id',
            ];
            $check = $this->check_required($data, $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }


            $RE = Device::where('id', $id)->first();
            if (!$RE) {
                return json_encode([
                    'status' => 2,
                    'message' => 'id有误'
                ]);
            }
            $this->status = 1;
            $this->message = "查询成功";
            return $this->format($RE);


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }

    public function lists(Request $request)
    {

        try {
            $user_merchant = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $merchant_id = $request->get('merchant_id', '');
            $device_type = $request->get('device_type', '');
            $type = $request->get('type', '');

            $where = [];
            $whereIn = [];
            $store_ids = [];
            if ($merchant_id) {
                $where[] = ['merchant_id', '=', $merchant_id];
            }

            if ($store_id) {
                $store_ids = [
                    [
                        'store_id' => $store_id,
                    ]
                ];
            } else {
                $MerchantStore = MerchantStore::where('merchant_id', $user_merchant->merchant_id)
                    ->select('store_id')
                    ->get();

                if (!$MerchantStore->isEmpty()) {
                    $store_ids = $MerchantStore->toArray();
                }
            }

            if ($device_type) {
                $where[] = ['device_type', '=', $device_type];
            }
            if ($type) {
                $where[] = ['type', '=', $type];
            }
            $data = Device::where($where)->whereIn('store_id', $store_ids);
            $this->t = $data->count();
            $data = $this->page($data)->get();
            $this->status = 1;
            $this->message = "数据返回成功";
            return $this->format($data);


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }

    public function device_type(Request $request)
    {

        try {
            $user_merchant = $this->parseToken();
            $type = $request->get('type', 'p');

            if ($type == "p") {
                $data = [
                    [
                        'device_type' => 'p_zlbz_1',
                        'device_name' => '打印机(智联博众)'
                    ], [
                        'device_type' => 'p_yly_k4',
                        'device_name' => '打印机(易联云)'
                    ]
                ];
            }

            if ($type == "s") {
                $data = [
                    [
                        'device_type' => 's_bp_sl51',
                        'device_name' => '扫码盒子(sl51)'
                    ],
                    [
                        'device_type' => 's_bp_sl58',
                        'device_name' => '扫码设备(sl58)'
                    ],
                    [
                        'device_type' => 's_bp_sl56',
                        'device_name' => '扫码设备(sl56)'
                    ],
                    [
                        'device_type' => 's_ps_ys',
                        'device_name' => '扫码盒子(品生)'
                    ], [
                        'device_type' => 'face_cm01',
                        'device_name' => '刷脸设备(织点cm01)'
                    ]
                ];
            }


            if ($type == "f") {
                $data = [
                    [
                        'device_type' => 'face_cm01',
                        'device_name' => '刷脸设备(织点cm01)'
                    ]
                ];
            }

            if ($type == "v") {
                $data = [
                    [
                        'device_type' => 'v_zlbz_1',
                        'device_name' => '播报设备(智联博众)'
                    ], [
                        'device_type' => 'v_zw_1',
                        'device_name' => '播报设备(智网云)'
                    ], [
                        'device_type' => 'v_bp_1',
                        'device_name' => '播报设备(波普SLX1)'
                    ], [
                        'device_type' => 'v_kd_58',
                        'device_name' => '播报设备(新国都KD58)'
                    ]
                    , [
                        'device_type' => 'v_jbp_d30',
                        'device_name' => '播报设备(聚宝盆D30)'
                    ]

                ];
            }


            $this->status = 1;
            $this->message = "数据返回成功";
            return $this->format($data);

        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }

    public function get_device(Request $request)
    {

        try {
            $user_merchant = $this->parseToken();
            $data = $request->except(['token']);

            $check_data = [
                'type' => '设备类型',
                'name' => '收件人名字',
                'phone' => '收件人电话',
                'address' => '详细地址',
                'province_code' => '省',
                'city_code' => '市',
                'area_code' => '区',

            ];
            $check = $this->check_required($data, $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }

            $this->status = 1;
            $this->message = "申请成功";
            return $this->format($data);

        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }


    //配置
    public function v_config(Request $request)
    {

        try {
            $user_merchant = $this->parseToken();
            $config_id = $user_merchant->config_id;
            $zlbz_token = $request->get('zlbz_token', "");
            $zw_token = $request->input('zw_token', "");

            $VConfig = VConfig::where('config_id', $config_id)->first();

            if ($zlbz_token == "" && $zw_token == "") {
                $this->status = 1;
                $this->message = "查询成功";
                return $this->format($VConfig);
            }
            $data = [
                'config_id' => $config_id,
                'zlbz_token' => $zlbz_token,
                'zw_token' => $zw_token,
            ];
            if ($VConfig) {

                $VConfig->update($data);
                $VConfig->save();

            } else {
                VConfig::create($data);
            }


            $this->status = 1;
            $this->message = "添加成功";
            return $this->format($data);


        } catch (\Exception $exception) {
            Log::info('添加设备');
            Log::info($exception);
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }


    }

}