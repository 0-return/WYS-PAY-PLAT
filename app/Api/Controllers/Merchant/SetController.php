<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/6/29
 * Time: 下午2:42
 */

namespace App\Api\Controllers\Merchant;


use App\Api\Controllers\BaseController;
use App\Api\Rsa\RsaE;
use App\Models\AppOem;
use App\Models\Merchant;
use App\Models\MerchantStore;
use App\Models\QrList;
use App\Models\QrListInfo;
use App\Models\QrPayInfo;
use App\Models\Store;
use App\Models\StorePayWay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class SetController extends BaseController
{


    //设置登录密码
    public function set_password(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $oldpassword = $request->get('oldpassword', '');
            $newpassword = $request->get('newpassword', '');
            $newpassword_confirmed = $request->get('newpassword_confirmed', '');


            $check_data = [
                'oldpassword' => '旧密码',
                'newpassword' => '新密码',
                'newpassword_confirmed' => '确认新密码'
            ];

            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }

            $local = Merchant::where('id', $merchant->merchant_id)->first();
            //验证旧的密码
            if (!Hash::check($oldpassword, $local->password)) {
                return json_encode([
                    'status' => 2,
                    'message' => '旧的登陆密码不匹配'
                ]);
            }
            if ($newpassword !== $newpassword_confirmed) {
                return json_encode([
                    'status' => 2,
                    'message' => '两次密码不一致'
                ]);
            }
            if (strlen($newpassword) < 6) {
                return json_encode([
                    'status' => 2,
                    'message' => '密码长度不符合要求'
                ]);
            }
            $dataIN = [
                'password' => bcrypt($newpassword),

            ];
            Merchant::where('id', $merchant->merchant_id)->update($dataIN);


            $data = [
                'status' => 1,
                'message' => '密码修改成功',
                'data' => []
            ];
            return json_encode($data);

        } catch (\Exception $exception) {
            return json_encode(['status' => 2, 'message' => $exception->getMessage()]);
        }
    }

    //修改登录手机号
    public function edit_login_phone(Request $request)
    {

        try {
            $merchant = $this->parseToken();
            $data = $request->all();
            $password = $request->get('password', '');
            $new_phone = $request->get('phone', '');
            $code_b = $request->get('code_b', '');
            //如果只传password代表校验
            if ($password && $new_phone == "" && $code_b == "") {
                //验证验证码
                $local = Merchant::where('id', $merchant->merchant_id)->first();
                //验证旧的密码
                if (!Hash::check($password, $local->password)) {
                    return json_encode([
                        'status' => 2,
                        'message' => '密码不匹配'
                    ]);
                } else {
                    return json_encode([
                        'status' => 1,
                        'message' => '密码匹配成功',
                        'data' => [],
                    ]);
                }
            } else {
                if ($code_b && $new_phone == '' & $password == "") {
                    //验证新手机验证码
                    $msn_local = Cache::get($new_phone . 'editphone-2');
                    if (0 && $code_b != $msn_local) {
                        return json_encode([
                            'status' => 2,
                            'message' => '新手机号码短信验证码不匹配'
                        ]);
                    } else {
                        return json_encode([
                            'status' => 1,
                            'message' => '短信验证码匹配成功',
                            'data' => [],
                        ]);
                    }
                }

                //换手机号码
                //验证新的手机号
                if (!preg_match("/^1[3456789]{1}\d{9}$/", $new_phone)) {
                    return json_encode([
                        'status' => 2,
                        'message' => '手机号码不正确'
                    ]);
                }
                if ($new_phone == $merchant->phone) {
                    return json_encode([
                        'status' => 2,
                        'message' => '手机号码未更改'
                    ]);
                }
                $rules = [
                    'phone' => 'required|min:11|max:11|unique:merchants',
                ];
                $validator = Validator::make($data, $rules);
                if ($validator->fails()) {
                    return json_encode([
                        'status' => 2,
                        'message' => '账号已注册请更换'
                    ]);
                }


                //验证新手机验证码
                $msn_local = Cache::get($new_phone . 'editphone-2');
                if (0 && $code_b != $msn_local) {
                    return json_encode([
                        'status' => 2,
                        'message' => '新手机号码短信验证码不匹配'
                    ]);
                }

                Merchant::where('id', $merchant->merchant_id)->update(['phone' => $new_phone]);


                $data = [
                    'status' => 1,
                    'message' => '手机号修改成功',
                    'data' => [

                    ]
                ];
                return json_encode($data);

            }
        } catch
        (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }
    }

    //扣款顺序列表
    public function pay_ways_sort(Request $request)
    {
        try {
            $merchant = $this->parseToken();//
            $store_id = $request->get('store_id', '');
            $merchant_id = $merchant->merchant_id;
            $MerchantStore = MerchantStore::where('merchant_id', $merchant_id)
                ->orderBy('created_at', 'asc')
                ->first();
            if ($MerchantStore) {
                $store_id = $MerchantStore->store_id;
            }
            if ($store_id) {
                $alipay = DB::table('store_pay_ways')
                    ->where('store_id', $store_id)
                    ->where('ways_source', 'alipay')
                    ->select('id as store_pay_ways_id', 'ways_desc', 'ways_type', 'store_id', 'sort', 'ways_source')
                    ->where('status', 1)
                    ->orderBy('store_pay_ways.sort', 'asc')
                    ->get();
                $weixin = DB::table('store_pay_ways')
                    ->where('store_id', $store_id)
                    ->where('ways_source', 'weixin')
                    ->select('id as store_pay_ways_id', 'ways_desc', 'ways_type', 'store_id', 'sort', 'ways_source')
                    ->where('status', 1)
                    ->orderBy('store_pay_ways.sort', 'asc')
                    ->get();


                $jd = DB::table('store_pay_ways')
                    ->where('store_id', $store_id)
                    ->where('ways_source', 'jd')
                    ->select('id as store_pay_ways_id', 'ways_desc', 'ways_type', 'store_id', 'sort', 'ways_source')
                    ->where('status', 1)
                    ->orderBy('store_pay_ways.sort', 'asc')
                    ->get();
                $unionpayqr = DB::table('store_pay_ways')
                    ->where('store_id', $store_id)
                    ->where('ways_source', 'unionpay')
                    ->select('id as store_pay_ways_id', 'ways_desc', 'ways_type', 'store_id', 'sort', 'ways_source')
                    ->where('status', 1)
                    ->orderBy('store_pay_ways.sort', 'asc')
                    ->get();

                return json_encode(['status' => 1, 'is_open' => 1, 'message' => '数据返回成功', 'data' => ['ailpay' => $alipay, 'weixin' => $weixin, 'jd' => $jd, 'unionpayqr' => $unionpayqr]]);

            } else {
                return json_encode(['status' => 2, 'message' => '没有绑定店铺']);
            }

        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }
    }


    //扣款顺序修改
    public function pay_ways_sort_edit(Request $request)
    {
        try {
            $merchant = $this->parseToken();//
            $store_pay_ways_id = (int)$request->get('store_pay_ways_id');
            $new_sort = $request->get('new_sort');

            $check_data = [
                'store_pay_ways_id' => '通道类型id',
                'new_sort' => '新位置',
            ];

            //收银员
            if ($merchant->merchant_type == 2) {
                return json_encode([
                    'status' => 2,
                    'message' => '收银员没有权限'
                ]);
            }

            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }

            $ch_storePayWay = StorePayWay::where('id', $store_pay_ways_id)->first();
            $store_id = $ch_storePayWay->store_id;

            $ways_source = $ch_storePayWay->ways_source;
            $sort = $ch_storePayWay->sort;//旧的位置
            if ((int)$sort == (int)$new_sort) {
                return json_encode(['status' => 2, 'message' => '位置没有任何改动']);

            }

            $old_StorePayWay = StorePayWay::where('sort', $new_sort)
                ->where('store_id', $store_id)
                ->where('ways_source', $ways_source)
                ->first();

            //开启事务
            try {
                DB::beginTransaction();

                //先零时配置一个
                $old_StorePayWay->update([
                    'sort' => 100,
                ]);

                $ch_storePayWay->update([
                    'sort' => $new_sort,
                ]);
                $ch_storePayWay->save();
                $old_StorePayWay->save();


                //修正
                $old_StorePayWay->update([
                    'sort' => $sort,
                ]);
                $old_StorePayWay->save();


                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return json_encode(['status' => 2, 'message' => $e->getMessage()]);
            }


            return json_encode(['status' => 1, 'message' => '顺序修改成功']);

        } catch (\Exception $exception) {

            return json_encode(['status' => 0, 'message' => $exception->getMessage()]);
        }
    }


    public function pay_ways_open(Request $request)
    {
        try {
            $merchant = $this->parseToken();//
            $is_open = $request->get('is_open', '');
            $store_id = $request->get('store_id', '');
            $merchant_id = $merchant->merchant_id;
            $MerchantStore = MerchantStore::where('merchant_id', $merchant_id)
                ->orderBy('created_at', 'asc')
                ->first();
            if ($MerchantStore) {
                $store_id = $MerchantStore->store_id;
            }
            $store_pay_ways_open = 0;
            $store = Store::where('store_id', $store_id)->first();
            if ($store) {
                $store_pay_ways_open = $store->store_pay_ways_open;
            }
            if ($is_open == "") {
                return json_encode([
                        'status' => 1,
                        'message' => '数据返回成功',
                        'data' => [
                            'store_pay_ways_open' => $store_pay_ways_open,
                        ],
                    ]
                );

            } else {
                $store->update([
                    'store_pay_ways_open' => $is_open
                ]);

                return json_encode([
                        'status' => 1,
                        'message' => '修改成功',
                        'data' => [
                            'store_pay_ways_open' => $is_open,
                        ],
                    ]
                );
            }


        } catch (\Exception $exception) {
            return json_encode(['status' => 0, 'message' => $exception->getMessage()]);
        }
    }

    //添加支付密码
    public function add_pay_password(Request $request)
    {
        $rsa = new RsaE();
        try {
            $merchant = $this->parseToken();
            $merchant_id = $merchant->merchant_id;

            //客户端用的我的公钥加密 我用私钥解密
            $data = $rsa->privDecrypt($request->get('sign'));//解密
            parse_str($data, $output);
            $pay_password = isset($output['pay_password']) ? $output['pay_password'] : "";
            $pay_password_confirmed = isset($output['pay_password_confirmed']) ? $output['pay_password_confirmed'] : "";

            if ($pay_password !== $pay_password_confirmed) {
                return json_encode([
                    'status' => 2,
                    'message' => '两次密码不一致'
                ]);
            }

            if ($pay_password) {
                //验证密码
                if (strlen($pay_password) != 6) {
                    return json_encode([
                        'status' => 2,
                        'message' => '密码长度不符合要求'
                    ]);
                }
                $dataIN = [
                    'pay_password' => bcrypt($pay_password),

                ];
                Merchant::where('id', $merchant_id)->update($dataIN);
            } else {
                return json_encode([
                    'status' => 2,
                    'message' => '参数填写不完整'
                ]);
            }

            return json_encode([
                'status' => 1,
                'message' => '支付密码添加成功',
                'data' => []
            ]);


        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage()
            ]);
        }
    }


    //修改支付密码
    public function edit_pay_password(Request $request)
    {
        $rsa = new RsaE();
        try {
            $data = $rsa->privDecrypt($request->get('sign'));//解密
            parse_str($data, $output);
            $merchant = $this->parseToken();
            $merchant_id = $merchant->merchant_id;

            $oldpassword = isset($output['old_pay_password']) ? $output['old_pay_password'] : "";
            $newpassword = isset($output['new_pay_password']) ? $output['new_pay_password'] : "";

            if ($oldpassword && $newpassword == "") {
                $local = Merchant::where('id', $merchant_id)->first();
                if (!Hash::check($oldpassword, $local->pay_password)) {
                    return json_encode([
                        'status' => 2,
                        'message' => '旧的支付密码不匹配'
                    ]);
                } else {
                    return json_encode([
                        'status' => 1,
                        'message' => '旧的支付密码匹配'
                    ]);
                }
            }

            if (strlen($newpassword) != 6) {
                return json_encode([
                    'status' => 2,
                    'msg' => '密码长度不符合要求'
                ]);
            }
            $dataIN = [
                'pay_password' => bcrypt($newpassword),

            ];

            Merchant::where('id', $merchant_id)->update($dataIN);

            return json_encode([
                'status' => 1,
                'message' => '支付密码修改成功',
                'data' => []
            ]);
        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage()
            ]);
        }
    }


    //忘记支付密码
    public function forget_pay_password(Request $request)
    {
        $rsa = new RsaE();
        try {
            $merchant = $this->parseToken();
            $merchant_id = $merchant->merchant_id;
            //客户端用的我的公钥加密 我用私钥解密
            $data = $rsa->privDecrypt($request->get('sign'));//解密
            parse_str($data, $output);
            $newpassword = isset($output['new_pay_password']) ? $output['new_pay_password'] : "";
            $code = isset($output['code']) ? $output['code'] : "";
            $msn_local = Cache::get($merchant->phone . 'editpassword-2');

            //验证验证码
            if ($code != "" && $newpassword == "") {
                if ($code != $msn_local) {
                    return json_encode([
                        'status' => 2,
                        'message' => '短信验证码不匹配'
                    ]);
                } else {
                    return json_encode([
                        'status' => 1,
                        'message' => '短信验证码正确'
                    ]);
                }
            }

            //验证密码
            if (strlen($newpassword) < 6) {
                return json_encode([
                    'status' => 2,
                    'message' => '密码长度不符合要求'
                ]);
            }


            $Merchant = Merchant::where('id', $merchant_id)->first();

            //验证验证码
            $msn_local = Cache::get($Merchant->phone . 'editpassword-2');
            if ((string)$code != (string)$msn_local) {
                return json_encode([
                    'status' => 2,
                    'message' => '短信验证码不匹配'
                ]);
            }

            $Merchant->update(['pay_password' => bcrypt($newpassword)]);
            $Merchant->save();

            return json_encode([
                'status' => 1,
                'message' => '支付密码修改成功',
                'data' => [],
            ]);


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }

    //校验支付密码
    public function check_pay_password(Request $request)
    {
        $rsa = new RsaE();
        try {
            $merchant = $this->parseToken();
            $merchant_id = $merchant->merchant_id;
            $data = $rsa->privDecrypt($request->get('sign'));//解密
            parse_str($data, $output);

            $pay_password = isset($output['pay_password']) ? $output['pay_password'] : "";


            if (strlen($pay_password) != 6) {
                return json_encode([
                    'status' => 0,
                    'msg' => '密码长度不符合要求'
                ]);
            }
            $local_pay_password = Merchant::where('id', $merchant_id)->first();
            if ($local_pay_password && $local_pay_password->pay_password) {
                if (Hash::check($pay_password, $local_pay_password->pay_password)) {
                    return json_encode([
                        'status' => 1,
                        'message' => '支付密码匹配'
                    ]);
                } else {
                    return json_encode([
                        'status' => 2,
                        'message' => '支付密码不匹配'
                    ]);
                }
            } else {
                return json_encode([
                    'status' => 2,
                    'message' => '账号未设置支付密码'
                ]);
            }


        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage()
            ]);
        }
    }

    //检测是否设置过支付密码
    public function is_pay_password(Request $request)
    {
        $merchant = $this->parseToken();
        $merchant_id = $merchant->merchant_id;
        $Merchant = Merchant::where('id', $merchant_id)->first();

        if ($Merchant->pay_password) {

            $is_pay_password = 1;
        } else {
            $is_pay_password = 0;
        }

        return json_encode([
            'status' => 1,
            'data' => [
                'is_pay_password' => $is_pay_password,
            ]
        ]);
    }


    //扫一扫
    public function bind_store_qr(Request $request)
    {
        $merchant = $this->parseToken();
        $code = $request->get('code');
        $store_id = $request->get('store_id', '');
        $merchant_id = $request->get('merchant_id', '');
        $is_qr = substr($code, 0, 4);

        $check_data = [
            'store_id' => '门店ID',
            'code' => '码编号',
        ];
        $check = $this->check_required($request->except(['token']), $check_data);
        if ($check) {
            return json_encode([
                'status' => 2,
                'message' => $check
            ]);
        }

        //绑定空码
        if ($is_qr == "http") {
            $url = basename($code);//获取链接
            $data = $this->getParams($url);
            $code = $data['no'];
            $QrListInfo = QrListInfo::where('code_number', $code)->first();
            if ($QrListInfo) {
                //已经绑定支付码
                if ($QrListInfo->code_type) {
                    return json_encode(['status' => 2, 'message' => '二维码已经被其他店铺绑定！请更换']);
                } else {
                    if ($store_id == "") {
                        return json_encode(['status' => 2, 'message' => '请先开通门店']);
                    }
                    //未绑定
                    $datainfo = $QrListInfo->toArray();
                    $datainfo['store_id'] = $store_id;
                    $datainfo['code_type'] = 1;
                    $datainfo['merchant_id'] = $merchant_id;

                    //开启事务
                    try {
                        DB::beginTransaction();
                        QrPayInfo::create($datainfo);
                        $QrListInfo->update(
                            [
                                'code_type' => 1,
                                'store_id' => $store_id,

                            ]
                        );
                        $QrListInfo->save();

                        //已经使用加 1
                        $QrList = QrList::where('cno', $QrListInfo->cno)->first();
                        $s_num = $QrList->s_num;
                        $QrList->s_num = $s_num + 1;
                        $QrList->save();

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                    }
                    return json_encode(['status' => 1, 'message' => '绑定收款二维码成功']);
                }
            } else {
                //空码不存在
                if ($store_id == "") {
                    return json_encode(['status' => 2, 'message' => '请先开通门店']);
                }

                //未绑定
                $datainfo = [
                    'user_id' => '1',
                    'code_number' => $code,
                    'code_type' => 1,
                    'store_id' => $store_id,
                    'cno' => '1',
                ];
                //开启事务
                try {
                    DB::beginTransaction();
                    QrListInfo::create($datainfo);
                    $datainfo['merchant_id'] = $merchant_id;
                    QrPayInfo::create($datainfo);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                }
                return json_encode(['status' => 1, 'message' => '绑定收款二维码成功']);
            }

        }

        return json_encode(['status' => 2, 'message' => '不识别的二维码']);
    }

    public function getParams($url)
    {

        $refer_url = parse_url($url);
        $params = $refer_url['query'];

        $arr = array();
        if (!empty($params)) {
            $paramsArr = explode('&', $params);

            foreach ($paramsArr as $k => $v) {
                $a = explode('=', $v);
                $arr[$a[0]] = $a[1];
            }
        }
        return $arr;
    }


}