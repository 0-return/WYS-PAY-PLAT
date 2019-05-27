<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/10/12
 * Time: 4:01 PM
 */

namespace App\Api\Controllers\Newland;


use App\Api\Controllers\BaseController;
use App\Api\Controllers\Config\NewLandConfigController;
use App\Models\MerchantStore;
use App\Models\NewLandMcc;
use App\Models\NewLandStore;
use App\Models\Store;
use App\Models\StorePayWay;
use Illuminate\Http\Request;

class SelectController extends BaseController
{


    //新大陆查询商户审核状态

    public function store_query(Request $request)
    {
        try {
            $store_id = $request->get('store_id', '');

            $NewLandStore = NewLandStore::where('store_id', $store_id)->first();

            if (!$NewLandStore) {
                return $return_err = json_encode([
                    'status' => 2,
                    'message' => '门店不存在',
                ]);
            }
            $aop = new \App\Common\XingPOS\Aop();
            $aop->key = '9773BCF5BAC01078C9479E67919157B8';
            $aop->version = 'V1.0.1';
            $aop->org_no = '518';
            $aop->url = 'http://sandbox.starpos.com.cn/emercapp';//测试地址

            $sign_data = [
                'mercId' => $NewLandStore->nl_mercId,
            ];

            $request_obj = new  \App\Common\XingPOS\Request\XingStoreShangHuChaXun();
            $request_obj->setBizContent($sign_data);
            $return = $aop->executeStore($request_obj);

            //不成功
            if ($return['msg_cd'] != '000000') {
                return $return_err = json_encode([
                    'status' => 2,
                    'message' => $return['msg_dat'],
                ]);
            }
            return $return_err = json_encode([
                'status' => 1,
                'message' => '返回成功',
                'data' => $return,
            ]);


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }
    }


    //新大陆查询mcc
    public function mcc_query(Request $request)
    {
        try {
            $store_id = $request->get('store_id', '');

            $store = Store::where('store_id', $store_id)->first();
            if (!$store) {
                return json_encode([
                    'status' => 2,
                    'message' => '门店不存在'
                ]);
            }
            $config_id = $store->config_id;
            $store_pid = $store->pid;

            $config = new NewLandConfigController();
            $new_land_config = $config->new_land_config($config_id);
            if (!$new_land_config) {
                return json_encode([
                    'status' => 2,
                    'message' => '新大陆配置不存在请检查配置'
                ]);
            }

            $new_land_merchant = $config->new_land_merchant($store_id, $store_pid);
            if (!$new_land_merchant) {
                return json_encode([
                    'status' => 2,
                    'message' => '商户新大陆通道未开通'
                ]);
            }
            $aop = new \App\Common\XingPOS\Aop();
            $aop->key = $new_land_config->nl_key;
            $aop->version = 'V1.0.1';
            $aop->org_no = $new_land_config->org_no;;

            $request_obj = new  \App\Common\XingPOS\Request\XingStoreMCCChaXun();
            $request_obj->setBizContent();
            $return = $aop->executeStore($request_obj);
            //不成功
            if ($return['msg_cd'] != '000000') {
                return $return_err = json_encode([
                    'status' => 2,
                    'message' => $return['msg_dat'],
                ]);
            }
            return $return_err = json_encode([
                'status' => 1,
                'message' => '返回成功',
                'data' => $return,
            ]);


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }
    }


    //开通D0权限
    public function open_da(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $nl_mercId = $request->get('nl_mercId', '');

            $store = Store::where('store_id', $store_id)
                ->first();

            if (!$store) {
                return json_encode([
                    'status' => 2,
                    'message' => '门店不存在'
                ]);
            }
            $config_id = $store->config_id;
            $store_pid = $store->pid;

            $config = new NewLandConfigController();
            $new_land_config = $config->new_land_config($config_id);
            if (!$new_land_config) {
                return json_encode([
                    'status' => 2,
                    'message' => '新大陆配置不存在请检查配置'
                ]);
            }
            $new_land_merchant = NewLandStore::where('store_id', $store_id)
                ->where('nl_mercId', $nl_mercId)
                ->first();
            if (!$new_land_merchant) {
                return json_encode([
                    'status' => 2,
                    'message' => '商户新大陆通道未开通'
                ]);
            }
            $aop = new \App\Common\XingPOS\Aop();
            $aop->key = $new_land_config->nl_key;
            $aop->version = 'V1.0.0';
            $aop->org_no = $new_land_config->org_no;


            //查询新大陆的信息 -主要是因为防止新大陆的门店号变了
            $aop->version = 'V1.0.1';
            $sign_data = [
                'mercId' => $nl_mercId,
            ];
            $request_obj = new  \App\Common\XingPOS\Request\XingStoreShangHuChaXun();
            $request_obj->setBizContent($sign_data);
            $return = $aop->executeStore($request_obj);
            //不成功
            if ($return['msg_cd'] != '000000') {
                return json_encode([
                    'status' => 2,
                    'message' => $return['msg_dat'],
                ]);
            }

            //查询通过
            if ($return['check_flag'] == 1) {
                $nl_key = $return['key'];
                $nl_stoe_id = $return['REC'][0]['stoe_id'];
            } else {
                return json_encode([
                    'status' => 2,
                    'message' => '商户未开通成功',
                ]);
            }
            //查询新大陆的信息结 -主要是因为防止新大陆的门店号变了

            $reqBody = [
                [
                    'merc_id' => $new_land_merchant->nl_mercId,
                    'stoe_id' => $nl_stoe_id,
                ]
            ];

            $sign_data = [
                'reqBody' => json_encode($reqBody),
                'signType' => "MD5",
            ];


            //关闭d0;
            if ($new_land_merchant->settlement_type == "D0") {
                $request_obj = new  \App\Common\XingPOS\Request\XingStoreShangGuanbiDa();
                $aop->version = 'V1.0.0';
                $request_obj->setBizContent($sign_data, []);
                $return = $aop->executeStore($request_obj);
                $message = '关闭失败';
                $message1 = '关闭成功';
                $settlement_type = "T1";

            } else {

                $request_obj = new  \App\Common\XingPOS\Request\XingStoreShangHuKaitongDa();
                $aop->version = 'V1.0.0';
                $request_obj->setBizContent($sign_data, []);
                $return = $aop->executeStore($request_obj);
                $message = '开通失败';
                $message1 = '开通成功';
                $settlement_type = "D0";
            }


            //关闭D0

            //不成功
            if ($return['repCode'] != '000000') {
                return $return_err = json_encode([
                    'status' => 2,
                    'message' => urldecode($return['repMsg']),
                ]);
            }
            $respBody = urldecode($return['respBody']);
            $respBody = json_decode($respBody, true);

            if (isset($respBody[0]['sts']) && $respBody[0]['sts'] == '1') {
                return $return_err = json_encode([
                    'status' => 2,
                    'message' => $message,
                ]);
            }
            $data_up = [
                'settlement_type' => $settlement_type
            ];

            $new_land_merchant->update($data_up);
            $new_land_merchant->save();

            return $return_err = json_encode([
                'status' => 1,
                'message' => $message1,
                'data' => $reqBody,
            ]);


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }
    }


    //商户号列表
    public function store_list(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $nl_mercId = $request->get('nl_mercId', '');
            $where = [];
            $stores = [];
            if ($store_id) {
                $stores = [
                    'store_id' => $store_id,
                ];
            } else {
                $merchant_id = $merchant->merchant_id;
                $Merchantstore = MerchantStore::where('merchant_id', $merchant_id)
                    ->select('store_id')
                    ->get();
                if (!$Merchantstore->isEmpty()) {
                    $stores = $Merchantstore->toArray();
                }
            }

            if ($nl_mercId) {
                $where[] = ['nl_mercId', 'like', '%' . $nl_mercId . '%'];
            }
            $NewLandStore_onj = NewLandStore::whereIn('store_id', $stores)
                ->where($where);


            $this->t = $NewLandStore_onj->count();
            $data = $this->page($NewLandStore_onj)->get();

            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }
    }


    //获取提现信息
    public function get_da_info(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $nl_mercId = $request->get('nl_mercId', '');

            $store = Store::where('store_id', $store_id)
                ->first();

            if (!$store) {
                return json_encode([
                    'status' => 2,
                    'message' => '门店不存在'
                ]);
            }
            $config_id = $store->config_id;
            $store_pid = $store->pid;

            $config = new NewLandConfigController();
            $new_land_config = $config->new_land_config($config_id);
            if (!$new_land_config) {
                return json_encode([
                    'status' => 2,
                    'message' => '新大陆配置不存在请检查配置'
                ]);
            }
            $new_land_merchant = NewLandStore::where('store_id', $store_id)
                ->where('nl_mercId', $nl_mercId)
                ->first();
            if (!$new_land_merchant) {
                return json_encode([
                    'status' => 2,
                    'message' => '商户新大陆通道未开通'
                ]);
            }
            $aop = new \App\Common\XingPOS\Aop();
            $aop->key = $new_land_config->nl_key;
            $aop->version = 'V1.0.0';
            $aop->org_no = $new_land_config->org_no;
            $sign_data = [
                'merc_id' => $new_land_merchant->nl_mercId,
                'signType' => "MD5",
            ];

            $request_obj = new  \App\Common\XingPOS\Request\XingStoreShangHuKaitongDaInfo();
            $request_obj->setBizContent($sign_data, []);
            $return = $aop->executeStore($request_obj);

            //不成功
            if ($return['repCode'] != '000000') {
                return $return_err = json_encode([
                    'status' => 2,
                    'message' => urldecode($return['repMsg']),
                ]);
            }

            $respBody = urldecode($return['respBody']);
            $respBody = json_decode($respBody, true);

            return $return_err = json_encode([
                'status' => 1,
                'message' => '数据返回成功',
                'data' => $respBody,
            ]);


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }
    }


//提现结果查询
    public function da_out_select(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $ac_dt = $request->get('ac_dt', '');
            $nl_mercId = $request->get('nl_mercId', '');

            $store = Store::where('store_id', $store_id)
                ->first();

            if (!$store) {
                return json_encode([
                    'status' => 2,
                    'message' => '门店不存在'
                ]);
            }
            $config_id = $store->config_id;
            $store_pid = $store->pid;

            $config = new NewLandConfigController();
            $new_land_config = $config->new_land_config($config_id);
            if (!$new_land_config) {
                return json_encode([
                    'status' => 2,
                    'message' => '新大陆配置不存在请检查配置'
                ]);
            }
            $new_land_merchant = NewLandStore::where('store_id', $store_id)
                ->where('nl_mercId', $nl_mercId)
                ->first();
            if (!$new_land_merchant) {
                return json_encode([
                    'status' => 2,
                    'message' => '商户新大陆通道未开通'
                ]);
            }
            $aop = new \App\Common\XingPOS\Aop();
            $aop->key = $new_land_config->nl_key;
            $aop->version = 'V1.0.0';
            $aop->org_no = $new_land_config->org_no;
            $sign_data = [
                'merc_id' => $new_land_merchant->nl_mercId,
                'signType' => "MD5",
            ];
            if ($ac_dt) {
                $sign_data['ac_dt'] = $ac_dt;
            }

            $request_obj = new  \App\Common\XingPOS\Request\XingStoreShangHuKaitongDaOutSelect();
            $request_obj->setBizContent($sign_data, []);
            $return = $aop->executeStore($request_obj);

            //不成功
            if ($return['repCode'] != '000000') {
                return $return_err = json_encode([
                    'status' => 2,
                    'message' => urldecode($return['repMsg']),
                ]);
            }

            $respBody = urldecode($return['respBody']);
            $respBody = json_decode($respBody, true);

            return $return_err = json_encode([
                'status' => 1,
                'message' => '数据返回成功',
                'data' => $respBody,
            ]);


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }
    }

    //提现
    public function get_da(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $txn_amt = $request->get('txn_amt', '');//提现金额
            $nl_mercId = $request->get('nl_mercId', '');

            $tot_amt = $txn_amt;//总金额
            $store = Store::where('store_id', $store_id)
                ->first();

            if (!$store) {
                return json_encode([
                    'status' => 2,
                    'message' => '门店不存在'
                ]);
            }
            $config_id = $store->config_id;
            $store_pid = $store->pid;

            $config = new NewLandConfigController();
            $new_land_config = $config->new_land_config($config_id);
            if (!$new_land_config) {
                return json_encode([
                    'status' => 2,
                    'message' => '新大陆配置不存在请检查配置'
                ]);
            }
            $new_land_merchant = NewLandStore::where('store_id', $store_id)
                ->where('nl_mercId', $nl_mercId)
                ->first();
            if (!$new_land_merchant) {
                return json_encode([
                    'status' => 2,
                    'message' => '商户新大陆通道未开通'
                ]);
            }
            $tot_fee = $new_land_merchant->da_rate * $txn_amt;
            $aop = new \App\Common\XingPOS\Aop();
            $aop->key = $new_land_config->nl_key;
            $aop->version = 'V1.0.0';
            $aop->org_no = $new_land_config->org_no;

            $tot_amt = number_format($tot_amt, 0, '.', '');
            $tot_fee = number_format($tot_fee, 0, '.', '');


            //查询新大陆的信息-防止导入的不对
            $aop->version = 'V1.0.1';
            $sign_data = [
                'mercId' => $nl_mercId,
            ];
            $request_obj = new  \App\Common\XingPOS\Request\XingStoreShangHuChaXun();
            $request_obj->setBizContent($sign_data);
            $return = $aop->executeStore($request_obj);
            //不成功
            if ($return['msg_cd'] != '000000') {
                return json_encode([
                    'status' => 2,
                    'message' => $return['msg_dat'],
                ]);
            }

            //查询通过
            if ($return['check_flag'] == 1) {
                $nl_key = $return['key'];
                $nl_stoe_id = $return['REC'][0]['stoe_id'];
            } else {
                return json_encode([
                    'status' => 2,
                    'message' => '商户未开通成功',
                ]);
            }


            $reqBody = [
                [
                    'merc_id' => $new_land_merchant->nl_mercId,
                    'stoe_id' => $nl_stoe_id,
                    'txn_amt' => $tot_amt,//提现金额
                    'pre_fee' => $tot_fee,//提现手续费
                ]
            ];
            $sign_data = [
                'signType' => "MD5",
                'tot_amt' => $tot_amt,//总金额
                'tot_fee' => $tot_fee,//总手续费
                'reqBody' => json_encode($reqBody),
            ];

            $aop->version = 'V1.0.0';
            $request_obj = new  \App\Common\XingPOS\Request\XingStoreShangHuKaitongGetDa();
            $request_obj->setBizContent($sign_data, []);
            $return = $aop->executeStore($request_obj);

            //不成功
            if ($return['repCode'] != '000000') {
                return $return_err = json_encode([
                    'status' => 2,
                    'message' => urldecode($return['repMsg']),
                ]);
            }


            return $return_err = json_encode([
                'status' => 1,
                'message' => urldecode($return['repMsg']),
                'data' => $reqBody,
            ]);


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }
    }

    //设置提现手续费率
    public function set_da_rate(Request $request)
    {
        try {
            $public = $this->parseToken();
            if ($public->type == "merchant") {
                return json_encode([
                    'status' => 2,
                    'message' => '没有权限调用此接口'
                ]);
            }
            $store_id = $request->get('store_id', '');
            $nl_mercId = $request->get('nl_mercId', '');
            $rate = $request->get('rate', '');

            $store = Store::where('store_id', $store_id)
                ->first();

            if (!$store) {
                return json_encode([
                    'status' => 2,
                    'message' => '门店不存在'
                ]);
            }
            $config_id = $store->config_id;
            $store_pid = $store->pid;

            $config = new NewLandConfigController();
            $new_land_config = $config->new_land_config($config_id);
            if (!$new_land_config) {
                return json_encode([
                    'status' => 2,
                    'message' => '新大陆配置不存在请检查配置'
                ]);
            }
            $new_land_merchant = NewLandStore::where('store_id', $store_id)
                ->where('nl_mercId', $nl_mercId)
                ->first();
            if (!$new_land_merchant) {
                return json_encode([
                    'status' => 2,
                    'message' => '商户新大陆通道未开通'
                ]);
            }

            if ($rate == "") {
                return json_encode([
                    'status' => 1,
                    'message' => '数据返回成功',
                    'data' => [
                        'rate' => $new_land_merchant->da_rate
                    ],
                ]);
            }
            $new_land_merchant->da_rate = $rate;
            $new_land_merchant->save();

            return json_encode([
                'status' => 1,
                'message' => '设置成功',
                'data' => [
                    'rate' => $rate
                ],
            ]);


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }
    }


    //导入商户号
    public function import_mercId(Request $request)
    {
        try {
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;
            $store_id = $request->get('store_id');
            $config_id = $loginer->config_id;

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


            if (empty($store_id)) {

                $this->status = 2;
                $this->message = '门店ID';
                return $this->format();
            }

            $newland_merchant = NewLandStore::where('store_id', $store_id)
                ->first();
            if (empty($newland_merchant)) {
                $this->status = 2;
                $this->message = '新大陆商户ID不存在';
                return $this->format();
            }


            $newland_ways = StorePayWay::where('store_id', $store_id)
                ->where('company', 'newland')
                ->where('status', 1)
                ->first();
            if (empty($newland_ways)) {
                $this->status = 2;
                $this->message = '此门店ID必须先开启通道';
                return $this->format();
            }


            $excel_data = \App\Common\Excel\Excel::_readExcel($file);

            foreach ($excel_data as $k => $v) {

                if ($k == 0) {
                    continue;
                }

                //表格和导入的必须一致
                if (trim($v[0]) != $store_id) {
                    continue;
                }

                $insert = [
                    'store_id' => trim($v[0]),
                    'store_name' => trim($v[1]),
                    'nl_mercId' => trim($v[2]),
                    'nl_stoe_id' => trim($v[3]),
                    'trmNo' => trim($v[4]),
                    'nl_key' => trim($v[5]),
                    'config_id' => $config_id,
                    'jj_status' => 1,
                    'img_status' => 1,
                    'tj_status' => 1,
                    'check_flag' => 1,
                    'check_qm' => 1,
                ];

                $newland_merchant = NewLandStore::where('store_id', $store_id)
                    ->where('nl_mercId', trim($v[1]))
                    ->where('nl_stoe_id', trim($v[2]))
                    ->first();

                if ($newland_merchant) {
                    $newland_merchant->update($insert);
                    $newland_merchant->save();
                } else {
                    NewLandStore::create($insert);
                }


            }

            $this->status = 1;
            $this->message = '导入成功';
            return $this->format();


        } catch (\Exception $e) {
            $message = '表格未导入：系统错误：' . $e->getMessage() . $e->getFile() . $e->getLine();
            $this->status = 2;
            $this->message = $message;
            return $this->format();
        }


    }

    //删除门店
    public function del_store(Request $request)
    {
        try {
            $user = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $nl_mercId = $request->get('nl_mercId', '');

            $nl_mercIds = explode(',', $nl_mercId);

            //目前只有平台可以删除
            if ($user->level > 0) {
                return json_encode([
                    'status' => 2,
                    'message' => '只有平台有删除权限'
                ]);
            }


            foreach ($nl_mercIds as $k => $v) {
                $Store = NewLandStore::where('nl_mercId', $v)
                    ->where('store_id', $store_id)
                    ->first();
                //门店不存在
                if (!$Store) {
                    continue;
                }

                $Store = NewLandStore::where('nl_mercId', $v)
                    ->where('store_id', $store_id)
                    ->delete();
            }
            return json_encode([
                'status' => 1,
                'message' => '门店删除成功',
                'data' => [
                    'store_id' => $store_id,
                    'nl_mercId' => $nl_mercId,
                ]
            ]);
        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage() . $exception->getFile()
            ]);
        }

    }

}