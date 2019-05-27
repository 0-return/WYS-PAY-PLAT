<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2017/10/13
 * Time: 下午10:33
 */

namespace App\Api\Controllers\Fuiou;


use App\Api\Controllers\Config\FuiouConfigController;
use App\Models\FuiouStore;
use App\Models\MyBankStore;
use App\Models\StorePayWay;
use Illuminate\Http\Request;

class SelectController extends \App\Api\Controllers\BaseController
{

    //商户号列表
    public function store_list(Request $request)
    {
        try {
            $public = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $mchnt_cd = $request->get('mchnt_cd', '');
            $where = [];

            if ($mchnt_cd) {
                $where[] = ['mchnt_cd', 'like', '%' . $mchnt_cd . '%'];
            }
            $MyBankStore = FuiouStore::where('store_id', $store_id)
                ->where($where);


            $this->t = $MyBankStore->count();
            $data = $this->page($MyBankStore)->get();

            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);


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

            $mybank_ways = StorePayWay::where('store_id', $store_id)
                ->where('company', 'fuiou')
                ->where('status', 1)
                ->first();
            if (empty($mybank_ways)) {
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
                    'mchnt_cd' => trim($v[1]),
                    'config_id' => $config_id,
                ];

                $MyBankStore = FuiouStore::where('store_id', trim($v[0]))
                    ->where('mchnt_cd', trim($v[1]))
                    ->first();

                if ($MyBankStore) {
                    $MyBankStore->update($insert);
                    $MyBankStore->save();
                } else {
                    FuiouStore::create($insert);
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
            $mchnt_cd = $request->get('mchnt_cd', '');

            $MerchantIds = explode(',', $mchnt_cd);

            //目前只有平台可以删除
            if ($user->level > 0) {
                return json_encode([
                    'status' => 2,
                    'message' => '只有平台有删除权限'
                ]);
            }


            foreach ($MerchantIds as $k => $v) {
                $Store = FuiouStore::where('mchnt_cd', $v)
                    ->where('store_id', $store_id)
                    ->first();
                //门店不存在
                if (!$Store) {
                    continue;
                }

                $Store = FuiouStore::where('mchnt_cd', $v)
                    ->where('store_id', $store_id)
                    ->delete();
            }
            return json_encode([
                'status' => 1,
                'message' => '门店删除成功',
                'data' => [
                    'store_id' => $store_id,
                    'mchnt_cd' => $MerchantIds,
                ]
            ]);
        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage() . $exception->getFile()
            ]);
        }

    }


    //开通d0
    public function open_da(Request $request)
    {
        try {
            $user = $this->parseToken();

            $store_id = $request->get('store_id');
            $mchnt_cd = $request->get('mchnt_cd');
            $trans_zero_flag = $request->get('trans_zero_flag', 1);
            $open_desc = $request->get('open_desc', 'd0');

            $fuiou__merchant = FuiouStore::where('store_id', $store_id)
                ->where('mchnt_cd', $mchnt_cd)
                ->first();
            if (!$fuiou__merchant) {
                return json_encode([
                    'status' => 2,
                    'message' => '富友商户号不存在请检查'
                ]);
            }


            $config = new FuiouConfigController();
            $fuiou_config = $config->fuiou_config($fuiou__merchant->config_id);

            if (!$fuiou_config) {
                return json_encode([
                    'status' => 2,
                    'message' => '富友配置不存在请检查配置'
                ]);
            }

            $obj = new \App\Api\Controllers\Fuiou\BaseController();

            $key = $fuiou_config->md_key;
            $url = $obj->da_url;
            $request = [
                'trace_no' => time(),
                'ins_cd' => $fuiou_config->ins_cd,//机构号
                'mchnt_cd' => $fuiou__merchant->mchnt_cd,//商户号
                'trans_zero_flag' => $trans_zero_flag,
                'trans_zero_set_cd' => 'MA002',//
                'open_desc' => $open_desc,//
            ];

            $str = $obj->getSignContentNONULL($request);
            $request['sign'] = $obj->sign($str, $key, 'md5');
            $re = $obj->send($request, $url);

            //成功
            if ($re['ret_code'] == "0000") {
                $fuiou__merchant->trans_zero_flag = $request['trans_zero_flag'];
                $fuiou__merchant->trans_zero_set_cd = $request['trans_zero_set_cd'];
                $fuiou__merchant->save();

                return json_encode([
                    'status' => 1,
                    'message' => '开通成功',
                    'data' => $re,
                ]);
            } else {
                return json_encode([
                    'status' => 2,
                    'message' => $re['ret_msg'],
                ]);
            }

        } catch (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'message' => $exception->getMessage(),
            ]);
        }


    }


    //等待提现金额
    public function select_money(Request $request)
    {

        try {
            $user = $this->parseToken();

            $store_id = $request->get('store_id');
            $mchnt_cd = $request->get('mchnt_cd');


            $fuiou__merchant = FuiouStore::where('store_id', $store_id)
                ->where('mchnt_cd', $mchnt_cd)
                ->first();
            if (!$fuiou__merchant) {
                return json_encode([
                    'status' => 2,
                    'message' => '富友商户号不存在请检查'
                ]);
            }


            $config = new FuiouConfigController();
            $fuiou_config = $config->fuiou_config($fuiou__merchant->config_id);

            if (!$fuiou_config) {
                return json_encode([
                    'status' => 2,
                    'message' => '富友配置不存在请检查配置'
                ]);
            }
            $key = $fuiou_config->my_private_key;
            $obj = new \App\Api\Controllers\Fuiou\BaseController();
            $url = $obj->select_money;

            $request = [
                'ins_cd' => $fuiou_config->ins_cd,//机构号
                'mchnt_cd' => $fuiou__merchant->mchnt_cd,//商户号
                'random_str' => time(),
            ];

            $str = $obj->getSignContentNONULL($request);
            $request['sign'] = $obj->sign($str, $key, 'rsa');
            $re = $obj->send($request, $url);
            //成功

            //用户输入密码
            if ($re['result_code'] == "SUCCESS") {
                //交易成功
                return json_encode([
                    'status' => 1,
                    'message' => '查询成功',
                    'data' => [
                        'not_settle_amt' => number_format($re['not_settle_amt'] / 100, 2, '.', '')
                    ],
                ]);
            } else {
                return json_encode([
                    'status' => 2,
                    'message' => $re['err_code_des'],
                ]);
            }

        } catch (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'message' => $exception->getMessage(),
            ]);
        }
    }


    //提现
    public function out_money(Request $request)
    {

        try {
            $user = $this->parseToken();

            $store_id = $request->get('store_id');
            $mchnt_cd = $request->get('mchnt_cd');
            $amt = $request->get('amt');
            $fuiou__merchant = FuiouStore::where('store_id', $store_id)
                ->where('mchnt_cd', $mchnt_cd)
                ->first();
            if (!$fuiou__merchant) {
                return json_encode([
                    'status' => 2,
                    'message' => '富友商户号不存在请检查'
                ]);
            }


            $config = new FuiouConfigController();
            $fuiou_config = $config->fuiou_config($fuiou__merchant->config_id);

            if (!$fuiou_config) {
                return json_encode([
                    'status' => 2,
                    'message' => '富友配置不存在请检查配置'
                ]);
            }
            $key = $fuiou_config->my_private_key;
            $obj = new \App\Api\Controllers\Fuiou\BaseController();

            //先查询手续费
            $url = $obj->select_rate;

            $request = [
                'ins_cd' => $fuiou_config->ins_cd,//机构号
                'mchnt_cd' => $fuiou__merchant->mchnt_cd,//商户号
                'random_str' => time(),
                'amt' => $amt * 100,
            ];

            $str = $obj->getSignContentNONULL($request);
            $request['sign'] = $obj->sign($str, $key, 'rsa');
            $re = $obj->send($request, $url);
            $fee_amt = "";
            //用户输入密码
            if ($re['result_code'] == "SUCCESS") {
                $fee_amt = $re['fee_amt'];
            } else {
                return json_encode([
                    'status' => 2,
                    'message' => $re['err_code_des'],
                ]);
            }


            //提现
            $url = $obj->out_money;

            $request = [
                'ins_cd' => $fuiou_config->ins_cd,//机构号
                'mchnt_cd' => $fuiou__merchant->mchnt_cd,//商户号
                'random_str' => time(),
                'amt' => $amt * 100,
                'fee_amt' => $fee_amt,
                'txn_type' => '1',
            ];

            $str = $obj->getSignContentNONULL($request);
            $request['sign'] = $obj->sign($str, $key, 'rsa');
            $re = $obj->send($request, $url);

            //用户输入密码
            if ($re['result_code'] == "SUCCESS") {
                
                return json_encode([
                    'status' => 1,
                    'message' => '提现成功',
                    'data' => $re,

                ]);

            } else {
                return json_encode([
                    'status' => 2,
                    'message' => '提现失败',
                    'data' => $re,
                ]);
            }


        } catch (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'message' => $exception->getMessage(),
            ]);
        }
    }


}