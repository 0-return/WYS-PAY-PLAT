<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2017/10/13
 * Time: 下午10:33
 */

namespace App\Api\Controllers\MyBank;


use App\Api\Controllers\Config\MyBankConfigController;
use App\Models\MerchantStore;
use App\Models\MyBankStore;
use App\Models\Store;
use App\Models\StorePayWay;
use Illuminate\Http\Request;

class SelectController extends BaseController
{

    //商户账户交易明细查询
    public function accountinfoquery(Request $request)
    {
        try {
            $user = $this->getUserMerchantInfo();
            //商户类型可以不传store_id
            $store_id = $request->get('store_id', '');
            if ($user['type'] == 'merchant') {
                $store_id = $request->get('store_id', $user['store_id']);
            }
            $MyBankStore = $this->MybankStore($store_id);
            if ($MyBankStore['status'] == 0) {
                return json_encode($MyBankStore);
            }
            $MyBankStore = $MyBankStore['data'];
            $MerchantId = $MyBankStore->MerchantId;
            if (!$MerchantId) {
                return json_encode([
                    'status' => 2,
                    'message' => '通网商银行商户号不存在',
                ]);
            }

            $ao = $this->aop();
            $ao->url = env("MY_BANK_request2");
            $ao->Function = "ant.mybank.stmtcore.accountinfo.query";
            $data = [
                'MerchantId' => $MerchantId,
                'FundCode' => '001529',
                'QueryStartDate' => '20171010',
                'QueryEndDate' => '20171010',
                'OutTradeNo' => date('YmdHis') . time() . rand(10000, 99999),
                'PageSize' => 10,
                'PageIndex' => 1,
                'CardNo' => '',
                'BizTypeList' => '',
                'TradeInStatusList' => '',
                'FundChannel' => 'MYBANK_ISV',
                'TransTypes' => '11,12,13,14,15',
                'TransStatuses' => 'inprocess,success,failure',
            ];
            $re = $ao->Request($data);
            if ($re['status'] == 0) {
                return json_encode($re);
            }
            $body = $re['data']['document']['response']['body'];

            if ($body['RespInfo']['ResultStatus'] == "S") {
                return json_encode([
                    'status' => 1,
                    'data' => $body,
                ]);
            } else {
                return json_encode([
                    'status' => 2,
                    'message' => $body['RespInfo']['ResultMsg'] . '-错误码' . $body['RespInfo']['ResultCode'],
                ]);
            }
        } catch (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'message' => $exception->getMessage() . '-' . $exception->getLine(),
            ]);
        }
    }


    //商户账户换绑手机
    public function accountbindmobile(Request $request)
    {
        try {
            $user = $this->getUserMerchantInfo();
            //商户类型可以不传store_id
            $store_id = $request->get('store_id', '');
            $NewMobile = $request->get('NewMobile', '');
            $OldMobileAuthCode = $request->get('OldMobileAuthCode', '88888');
            $NewMobileAuthCode = $request->get('NewMobileAuthCode', '88888');

            if ($user['type'] == 'merchant') {
                $store_id = $request->get('store_id', $user['store_id']);
            }
            $MyBankStore = $this->MybankStore($store_id);
            if ($MyBankStore['status'] == 0) {
                return json_encode($MyBankStore);
            }
            $MyBankStore = $MyBankStore['data'];
            $MerchantId = $MyBankStore->MerchantId;
            if (!$MerchantId) {
                return json_encode([
                    'status' => 2,
                    'message' => '通网商银行商户号不存在',
                ]);
            }

            $ao = $this->aop();
            $ao->url = env("MY_BANK_request2");
            $ao->Function = "ant.mybank.merchantprod.account.bindmobile";
            $data = [
                'MerchantId' => $MerchantId,
                'NewMobile' => $NewMobile,
                'OldMobileAuthCode' => $OldMobileAuthCode,
                'NewMobileAuthCode' => $NewMobileAuthCode,
                'OutTradeNo' => date('YmdHis') . time() . rand(10000, 99999),
            ];
            $re = $ao->Request($data);
            if ($re['status'] == 0) {
                return json_encode($re);
            }
            $body = $re['data']['document']['response']['body'];

            if ($body['RespInfo']['ResultStatus'] == "S") {
                return json_encode([
                    'status' => 1,
                    'message' => $body['RespInfo']['ResultMsg'],
                ]);
            } else {
                return json_encode([
                    'status' => 2,
                    'message' => $body['RespInfo']['ResultMsg'] . '-错误码' . $body['RespInfo']['ResultCode'],
                ]);
            }
        } catch (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'message' => $exception->getMessage() . '-' . $exception->getLine(),
            ]);
        }
    }


    //待清算金额查询
    public function balanceQuery(Request $request)
    {
        try {
            $user = $this->getUserMerchantInfo();
            //商户类型可以不传store_id
            $store_id = $request->get('store_id', '');
            if ($user['type'] == 'merchant') {
                $store_id = $request->get('store_id', $user['store_id']);
            }
            $MyBankStore = $this->MybankStore($store_id);
            if ($MyBankStore['status'] == 0) {
                return json_encode($MyBankStore);
            }
            $MyBankStore = $MyBankStore['data'];
            $MerchantId = $MyBankStore->MerchantId;
            if (!$MerchantId) {
                return json_encode([
                    'status' => 2,
                    'message' => '通网商银行商户号不存在',
                ]);
            }

            $ao = $this->aop();
            $ao->url = env("MY_BANK_request2");
            $ao->Function = "ant.mybank.bkmerchantsettle.balanceQuery";
            $data = [
                'MerchantId' => $MerchantId,
            ];
            $re = $ao->Request($data);
            if ($re['status'] == 0) {
                return json_encode($re);
            }
            $body = $re['data']['document']['response']['body'];
            if ($body['RespInfo']['ResultStatus'] == "S") {
                $data = [
                    "AvailableBalance" => $body['AvailableBalance'],
                    "ActualBalance" => $body['ActualBalance'],
                    "FreezeAmount" => $body['FreezeAmount'],
                    "Ccy" => $body['Ccy'],
                ];
                return json_encode([
                    'status' => 1,
                    'data' => $data,
                ]);
            } else {
                return json_encode([
                    'status' => 2,
                    'message' => $body['RespInfo']['ResultMsg'] . '-错误码' . $body['RespInfo']['ResultCode'],
                ]);
            }
        } catch (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'message' => $exception->getMessage() . '-' . $exception->getLine(),
            ]);
        }
    }


    //打款查询
    public function payResultQuery(Request $request)
    {
        try {
            $user = $this->parseToken();
            $config_id = $user->config_id;
            //商户类型可以不传store_id
            $store_id = $request->get('store_id');
            $time = $request->get('time', date('Y-m-d', time()));
            $store = Store::where('store_id', $store_id)->first();
            if (!$store) {
                return json_encode([
                    'status' => 2,
                    'message' => '门店不存在',
                ]);
            }
            $store_pid = $store->pid;
            $MyBankStore_obj = new MyBankConfigController();
            $MyBankStore = $MyBankStore_obj->mybank_merchant($store_id, $store_pid);
            if (!$MyBankStore) {
                return json_encode([
                    'status' => 2,
                    'message' => '通网商银行商户号不存在',
                ]);
            }

            $MerchantId = $MyBankStore->MerchantId;
            if (!$MerchantId) {
                return json_encode([
                    'status' => 2,
                    'message' => '通网商银行商户号不存在',
                ]);
            }
            $QueryMode = 'T1';
            if ($MyBankStore->SupportPrepayment == 'Y') {
                $QueryMode = 'T0';
            }
            $ao = $this->aop($config_id);
            $ao->url = env("MY_BANK_request2");
            $ao->Function = "ant.mybank.bkmerchantsettle.payResultQuery";
            $data = [
                'MerchantId' => $MerchantId,
                'PayDate' => $time,
                'QueryMode' => $QueryMode,
            ];
            $re = $ao->Request($data);

            if ($re['status'] == 2) {
                return json_encode($re);
            }
            $body = $re['data']['document']['response']['body'];
            if ($body['RespInfo']['ResultStatus'] == "S") {
                $data = json_decode(base64_decode($body['ResultList']), true);
                //成功
                if ($data[0]['status'] == "SUCCESS") {
                    return json_encode([
                        'status' => 1,
                        'message' => '打款成功',
                        'data' => [
                            'amount' => $data[0]['amount'] / 100,
                            'payTime' => $data[0]['payTime'],
                        ],
                    ]);
                } else {
                    return json_encode([
                        'status' => 1,
                        'message' => $data['failedReason'],
                        'data' => [
                            'amount' => $data[0]['amount'] / 100,
                            'payTime' => $data[0]['payTime'],
                        ],
                    ]);
                }

            } else {
                return json_encode([
                    'status' => 2,
                    'message' => $body['RespInfo']['ResultMsg'] . '-错误码' . $body['RespInfo']['ResultCode'],
                ]);
            }
        } catch (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'message' => $exception->getMessage() . '-' . $exception->getLine(),
            ]);
        }
    }


    //打款结果通知
    public function PayResult(Request $request)
    {
        try {
            $user = $this->getUserMerchantInfo();
            //商户类型可以不传store_id
            $store_id = $request->get('store_id');
            $FailCount = $request->get('FailCount', '200');
            if ($user['type'] == 'merchant') {
                $store_id = $request->get('store_id', $user['store_id']);
            }
            $MyBankStore = $this->MybankStore($store_id);
            if ($MyBankStore['status'] == 0) {
                return json_encode($MyBankStore);
            }
            $MyBankStore = $MyBankStore['data'];
            $MerchantId = $MyBankStore->MerchantId;
            if (!$MerchantId) {
                return json_encode([
                    'status' => 2,
                    'message' => '通网商银行商户号不存在',
                ]);
            }
            $ao = $this->aop();
            $ao->url = env("MY_BANK_request2");
            $ao->Function = "ant.mybank.bkmerchantsettle.notifyPayResult";
            $data = [
                'MerchantId' => $MerchantId,
                'FailCount' => $FailCount,
            ];
            $re = $ao->Request($data);
            if ($re['status'] == 0) {
                return json_encode($re);
            }
            $body = $re['data']['document']['response']['body'];
            if ($body['RespInfo']['ResultStatus'] == "S") {
                $data = [
                    "NotifyList" => json_decode(base64_decode($body['NotifyList']), true),
                ];
                return json_encode([
                    'status' => 1,
                    'data' => $data,
                ]);
            } else {
                return json_encode([
                    'status' => 2,
                    'message' => $body['RespInfo']['ResultMsg'] . '-错误码' . $body['RespInfo']['ResultCode'],
                ]);
            }
        } catch (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'message' => $exception->getMessage() . '-' . $exception->getLine(),
            ]);
        }
    }


    // 商户在网商银行的信息
    public function storeinfo(Request $request)
    {
        try {
            $user = $this->parseToken();
            $store_id = $request->get('store_id', '');

            $store = Store::where('store_id', $store_id)->first();
            if (!$store) {
                return json_encode([
                    'status' => 2,
                    'message' => '门店不存在',
                ]);
            }
            $store_pid = $store->pid;
            $MyBankStore_obj = new MyBankConfigController();
            $MyBankStore = $MyBankStore_obj->mybank_merchant($store_id, $store_pid);
            if (!$MyBankStore) {
                return json_encode([
                    'status' => 2,
                    'message' => '通网商银行商户号不存在',
                ]);
            }

            $MerchantId = $MyBankStore->MerchantId;

            $ao = $this->aop();
            $ao->url = env("MY_BANK_request2");
            $ao->Function = "ant.mybank.merchantprod.merchant.query";
            $data = [
                'MerchantId' => $MerchantId,
                'OutTradeNo' => date('YmdHis') . time() . rand(10000, 99999),
            ];
            $re = $ao->Request($data);
            if ($re['status'] == 2) {
                return json_encode($re);
            }
            $body = $re['data']['document']['response']['body'];
            if ($body['RespInfo']['ResultStatus'] == "S") {
                $body['MerchantDetail'] = json_decode(base64_decode($body['MerchantDetail']), true);
                $body['FeeParamList'] = json_decode(base64_decode($body['FeeParamList']), true);
                $body['BankCardParam'] = json_decode(base64_decode($body['BankCardParam']), true);
                $body['WechatChannelList'] = json_decode(base64_decode($body['WechatChannelList']), true);
                return json_encode([
                    'status' => 1,
                    'data' => $body,
                ]);
            } else {
                return json_encode([
                    'status' => 2,
                    'message' => $body['RespInfo']['ResultMsg'] . '-错误码' . $body['RespInfo']['ResultCode'],
                ]);
            }
        } catch (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'message' => $exception->getMessage(),
            ]);
        }
    }


    //设置微信目录
    public function set_weixin_path(Request $request)
    {
        try {
            $user = $this->parseToken();
            $store_id = $request->get('store_id', '');

            $store = Store::where('store_id', $store_id)->first();
            if (!$store) {
                return json_encode([
                    'status' => 2,
                    'message' => '门店不存在',
                ]);
            }
            $store_pid = $store->pid;
            $MyBankStore_obj = new MyBankConfigController();
            $MyBankStore = $MyBankStore_obj->mybank_merchant($store_id, $store_pid);
            if (!$MyBankStore) {
                return json_encode([
                    'status' => 2,
                    'message' => '通网商银行商户号不存在',
                ]);
            }

            $MerchantId = $MyBankStore->MerchantId;

            $ao = $this->aop();
            $ao->url = env("MY_BANK_request2");
            $ao->Function = "ant.mybank.merchantprod.merchant.addMerchantConfig";
            $data = [
                'MerchantId' => $MerchantId,
                'Path' => url('/api/mybank/weixin') . '/',
                'OutTradeNo' => date('YmdHis') . time() . rand(10000, 99999),
            ];
            $re = $ao->Request($data);
            dd($re);
        } catch (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'message' => $exception->getMessage(),
            ]);
        }
    }


    //商户号列表
    public function store_list(Request $request)
    {
        try {
            $public = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $MerchantId = $request->get('MerchantId', '');
            $where = [];

            if ($MerchantId) {
                $where[] = ['MerchantId', 'like', '%' . $MerchantId . '%'];
            }
            $MyBankStore = MyBankStore::where('OutMerchantId', $store_id)
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
                ->where('company', 'mybank')
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
                    'OutMerchantId' => trim($v[0]),
                    'MerchantName' => trim($v[1]),
                    'MerchantId' => trim($v[2]),
                    'config_id' => $config_id,
                    'MerchantType'=>'02',
                    'Mcc'=>'2015062600004525',
                    'MerchantDetail'=>"1",
                    'FeeParamList'=>'1',


                ];

                $MyBankStore = MyBankStore::where('OutMerchantId', trim($v[0]))
                    ->where('MerchantId', trim($v[2]))
                    ->first();

                if ($MyBankStore) {
                    $MyBankStore->update($insert);
                    $MyBankStore->save();
                } else {
                    MyBankStore::create($insert);
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
            $MerchantId = $request->get('MerchantId', '');

            $MerchantIds = explode(',', $MerchantId);

            //目前只有平台可以删除
            if ($user->level > 0) {
                return json_encode([
                    'status' => 2,
                    'message' => '只有平台有删除权限'
                ]);
            }


            foreach ($MerchantIds as $k => $v) {
                $Store = MyBankStore::where('MerchantId', $v)
                    ->where('OutMerchantId', $store_id)
                    ->first();
                //门店不存在
                if (!$Store) {
                    continue;
                }

                $Store = MyBankStore::where('MerchantId', $v)
                    ->where('OutMerchantId', $store_id)
                    ->delete();
            }
            return json_encode([
                'status' => 1,
                'message' => '门店删除成功',
                'data' => [
                    'store_id' => $store_id,
                    'MerchantId' => $MerchantId,
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