<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/8/13
 * Time: 下午3:24
 */

namespace App\Api\Controllers\Wallet;


use App\Api\Controllers\BaseController;
use App\Models\Merchant;
use App\Models\MerchantAccount;
use App\Models\MerchantWalletDetail;
use App\Models\MerchantWithdrawalsRecords;
use App\Models\SettlementConfig;
use App\Models\SettlementList;
use App\Models\SettlementListInfo;
use App\Models\User;
use App\Models\UserAccount;
use App\Models\UserWalletDetail;
use App\Models\UserWithdrawalsRecords;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SelectController extends BaseController
{

    //返佣类型
    public function source_type(Request $request)
    {
        try {
            $public = $this->parseToken();

            if ($public->type == "merchant") {
                $user_id = $public->merchant_id;
                //  $user = Merchant::where('id', $user_id)->first();
            }

            if ($public->type == "user") {
                $user_id = $public->user_id;
                //  $user = User::where('id', $user_id)->first();

            }


            $data = [
                [
                    'source_type' => 'hb',
                    'source_desc' => '红包码',
                ],
                [
                    'source_type' => 'hbfq',
                    'source_desc' => '分期奖励',
                ],
                [
                    'source_type' => '3000',
                    'source_desc' => '网商银行',
                ],
                [
                    'source_type' => '1000',
                    'source_desc' => '支付宝',
                ],
                [
                    'source_type' => '2000',
                    'source_desc' => '微信支付',
                ],
                [
                    'source_type' => '6000',
                    'source_desc' => '京东金融',
                ],
                [
                    'source_type' => '8000',
                    'source_desc' => '新大陆',
                ],
                [
                    'source_type' => '9000',
                    'source_desc' => '和融通',
                ],
            ];


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


    //返佣列表
    public function source_query(Request $request)
    {
        try {
            $public = $this->parseToken();
            $source_type = $request->get('source_type', '');
            $settlement = $request->get('settlement', '');
            $time_start = $request->get('time_start');
            $time_end = $request->get('time_end');
            $return_type = $request->get('return_type', '');
            $user_id = $request->get('user_id', '');
            $data = [];
            $where = [];

            if ($time_start) {
                $where[] = ['created_at', '>=', $time_start];
            }
            if ($time_end) {
                $where[] = ['created_at', '<=', $time_end];
            }

            if ($source_type) {
                $where[] = ['source_type', '=', $source_type];
            }

            if ($settlement) {
                $where[] = ['settlement', '=', $settlement];
            }

            if ($public->type == "merchant") {
                $user_id = $public->merchant_id;
                $where[] = ['merchant_id', '=', $user_id];


                $merchant = Merchant::where('id', $user_id)
                    ->select('money', 'settlement_money', 'unsettlement_money')
                    ->first();
                $money = $merchant->money;

                //返佣金额
                $settlement_money_where = $where;
                $settlement_money_where[] = ['settlement', '=', '01'];
                $unsettlement_money_where = $where;
                $unsettlement_money_where[] = ['settlement', '=', '02'];
                $settlement_money = MerchantWalletDetail::where($settlement_money_where)->select('money')->sum('money');
                $unsettlement_money = MerchantWalletDetail::where($unsettlement_money_where)->select('money')->sum('money');


                $detail = MerchantWalletDetail::where($where);
            }

            if ($public->type == "user") {

                if ($user_id) {
                    $users = $this->getSubIds($user_id);
                } else {
                    $user_id = $public->user_id;
                    $users = $this->getSubIds($public->user_id);

                }
                if (!in_array($user_id, $users)) {
                    return json_encode(['status' => 2, 'message' => '非上下级关系']);
                }


                $User = User::where('id', $user_id)
                    ->select('money', 'settlement_money', 'unsettlement_money')
                    ->first();

                $money = $User->money;


                //获取下级所有返佣
                if ($request->get('user_id')) {
                    $detail = UserWalletDetail::where($where)
                        ->where('user_id', $request->get('user_id'));
                } else {
                    $user_id = $public->user_id;
                    $detail = UserWalletDetail::where($where)
                        ->whereIn('user_id', $this->getSubIds($public->user_id));
                }


                $where[] = ['user_id', '=', $user_id];
                $settlement_money_where = $where;
                $settlement_money_where[] = ['settlement', '=', '01'];
                $unsettlement_money_where = $where;
                $unsettlement_money_where[] = ['settlement', '=', '02'];
                $settlement_money = UserWalletDetail::where($settlement_money_where)->select('money')->sum('money');
                $unsettlement_money = UserWalletDetail::where($unsettlement_money_where)->select('money')->sum('money');

            }


            $this->t = $detail->count();
            $detail = $this->page($detail)->orderBy('updated_at', 'desc')->get();
            $this->status = 1;
            $this->message = '数据返回成功';


            if ($return_type == '02') {
                $data = $detail;
            } else {
                $data = [
                    'money' => '' . number_format($money, 4, '.', '') . '',
                    'settlement_money' => '' . number_format($settlement_money, 4, '.', '') . '',
                    'unsettlement_money' => '' . number_format($unsettlement_money, 4, '.', '') . '',
                    'detail' => $detail,
                ];
            }


            return $this->format($data);

        } catch (\Exception $exception) {

            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage()
            ]);
        }

    }

    //单个返佣信息查询
    public function source_query_info(Request $request)
    {
        try {
            $public = $this->parseToken();
            $source_query_id = $request->get('source_query_id');
            if ($public->type == "user") {
                $data = UserWalletDetail::where('id', $source_query_id)->first();

            } else {
                $data = MerchantWalletDetail::where('id', $source_query_id)->first();

            }
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

//查询支付宝账户
    public
    function account(Request $request)
    {
        try {
            $public = $this->parseToken();
            if ($public->type == "merchant") {
                $user_id = $public->merchant_id;
                $account = MerchantAccount::where('merchant_id', $user_id)->first();
            }

            if ($public->type == "user") {
                $user_id = $public->user_id;
                $account = UserAccount::where('user_id', $user_id)->first();
            }

            if ($account) {
                $alipay_account = $account->alipay_account;
                $alipay_name = $account->alipay_name;
                $data = [
                    'alipay_account' => $alipay_account,
                    'alipay_name' => $alipay_name,
                ];
                $this->status = 1;
                $this->message = '数据返回成功';
                return $this->format($data);

            } else {
                return json_encode([
                    'status' => 2,
                    'message' => '未绑定支付宝账户'
                ]);
            }

        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage()
            ]);
        }

    }

//添加修改支付宝账户
    public
    function add_account(Request $request)
    {
        try {
            $public = $this->parseToken();
            $alipay_account = $request->get('alipay_account', '');
            $alipay_name = $request->get('alipay_name', '');
            $code = $request->get('code', '');


            //校验短信验证码
            if ($code != "" && $alipay_account == "" && $alipay_name == "") {
                //验证手机验证码
                $msn_local = Cache::get($public->phone . 'account');
                if ((string)$code != (string)$msn_local) {
                    return json_encode([
                        'status' => 2,
                        'message' => '短信验证码不匹配'
                    ]);
                } else {
                    return json_encode([
                        'status' => 1,
                        'message' => '短信验证码匹配成功',
                    ]);
                }
            }


            if ($public->type == "merchant") {
                $user_id = $public->merchant_id;
                $account = MerchantAccount::where('merchant_id', $user_id)->first();
            }

            if ($public->type == "user") {
                $user_id = $public->user_id;
                $account = UserAccount::where('user_id', $user_id)->first();

            }
            //修改
            if ($account) {
                $check_data = [
                    'alipay_account' => '支付宝账户',
                    'alipay_name' => '支付宝名称',
                    'code' => '短信验证码',
                ];
                $check = $this->check_required($request->except(['token']), $check_data);
                if ($check) {
                    return json_encode([
                        'status' => 2,
                        'message' => $check
                    ]);
                }

                $msn_local = Cache::get($public->phone . 'account');
                if ((string)$code != (string)$msn_local) {
                    return json_encode([
                        'status' => 2,
                        'message' => '短信验证码不匹配'
                    ]);
                }


                $account->alipay_account = $alipay_account;
                $account->alipay_name = $alipay_name;
                $account->save();


            } //添加
            else {

                $check_data = [
                    'alipay_account' => '支付宝账户',
                    'alipay_name' => '支付宝名称',
                ];

                $check = $this->check_required($request->except(['token']), $check_data);
                if ($check) {
                    return json_encode([
                        'status' => 2,
                        'message' => $check
                    ]);
                }


                if ($public->type == "merchant") {
                    $user_id = $public->merchant_id;
                    MerchantAccount::create([
                        'merchant_id' => $user_id,
                        'alipay_account' => $alipay_account,
                        'alipay_name' => $alipay_name,
                    ]);
                }

                if ($public->type == "user") {
                    $user_id = $public->user_id;
                    UserAccount::create([
                        'user_id' => $user_id,
                        'alipay_account' => $alipay_account,
                        'alipay_name' => $alipay_name,
                    ]);

                }

            }

            return json_encode([
                'status' => 1,
                'message' => '处理成功',
                'data' => $request->except(['token'])
            ]);


        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage()
            ]);
        }

    }


//提交提现操作
    public
    function out_wallet(Request $request)
    {

        try {
            $public = $this->parseToken();
            $config_id = $public->config_id;
            $total_amount = $request->get('total_amount');
            $alipay_account = $request->get('alipay_account');
            if ($public->type == "merchant") {
                $user_id = $public->merchant_id;
                $money = Merchant::where('id', $user_id)->first()->money;//钱包的钱
                $accounts = MerchantAccount::where('merchant_id', $user_id)->first();
                $SettlementConfig = SettlementConfig::where('dx', '2')->first();
            }

            if ($public->type == "user") {
                $user_id = $public->user_id;
                $money = User::where('id', $user_id)->first()->money;//钱包的钱
                $accounts = UserAccount::where('user_id', $user_id)->first();
                $SettlementConfig = SettlementConfig::where('dx', '1')->first();
            }

            if (!$accounts) {
                return json_encode(['status' => 2, 'message' => '提现账户未设置']);
            }


            if (!$SettlementConfig) {
                return json_encode(['status' => 2, 'message' => '服务商提现未设置']);
            }


            $sxf_amount = $SettlementConfig->sxf_amount;
            $tx_remark = $SettlementConfig->tx_remark;

            if ($total_amount < $sxf_amount) {
                return json_encode(['status' => 2, 'message' => '提现金额小于手续费' . $sxf_amount]);
            }

            if ($total_amount < $SettlementConfig->s_amount) {
                return json_encode(['status' => 2, 'message' => '提现金额不能小于' . $SettlementConfig->s_amount . '元']);
            }


            if ($total_amount > $SettlementConfig->e_amount) {
                return json_encode(['status' => 2, 'message' => '提现金额不能大于' . $SettlementConfig->e_amount . '元']);
            }


            $is_number = is_numeric($total_amount);
            if (!$is_number) {
                return json_encode(['status' => 2, 'message' => '提现金额类型不正确']);
            }


            $total_amount = number_format($total_amount, 2, '.', '');
            $time = date('Y-m-d h:i:s', time());
            $out_trade_no = date('Ymdhis', time());
            $money = number_format($money, 2, '.', '');
            $s = (bccomp($total_amount, $money, 2));//比较浮点型大小

            if ($s == 1) {
                return json_encode(['status' => 2, 'message' => '你的钱包余额里只有' . $money . '元没有办法提现' . $total_amount]);
            }
            if ($accounts && $accounts->alipay_account) {
                //提交过来的支付宝必须是绑定的支付宝
                if ($alipay_account != $accounts->alipay_account) {
                    return json_encode(['status' => 2, 'message' => '提现支付宝和绑定的支付宝不一致']);
                }
                //开启事务
                try {
                    DB::beginTransaction();
                    if ($public->type == "merchant") {
                        $user_id = $public->merchant_id;
                        $updateMonery = $money - $total_amount;
                        $data = [
                            'merchant_id' => $user_id,
                            'out_trade_no' => $out_trade_no,
                            'trade_no' => '',
                            'account' => $accounts->alipay_account,
                            'name' => $accounts->alipay_name,
                            'amount' => $total_amount,
                            'account_amount' => $updateMonery,
                            'status' => 2,
                            'status_desc' => '提现申请中',
                            'config_id' => $config_id,
                            'sxf_amount' => $sxf_amount,
                            'remark' => ''
                        ];
                        MerchantWithdrawalsRecords::create($data);
                        Merchant::where('id', $user_id)->update(['money' => $updateMonery]);

                    }

                    if ($public->type == "user") {
                        $user_id = $public->user_id;
                        $updateMonery = $money - $total_amount;
                        $data = [
                            'user_id' => $user_id,
                            'out_trade_no' => $out_trade_no,
                            'trade_no' => $out_trade_no,
                            'account' => $accounts->alipay_account,
                            'name' => $accounts->alipay_name,
                            'amount' => $total_amount,
                            'account_amount' => $updateMonery,
                            'status' => 2,
                            'status_desc' => '提现申请中',
                            'config_id' => $config_id,
                            'sxf_amount' => $sxf_amount,
                            'remark' => ''
                        ];
                        UserWithdrawalsRecords::create($data);
                        User::where('id', $user_id)->update(['money' => $updateMonery]);

                    }
                    DB::commit();
                } catch (\Exception $e) {
                    Log::info($e);
                    DB::rollBack();
                    return json_encode(['status' => 2, 'message' => $e->getMessage()]);
                }


                $AlipayTransfer = new \App\Common\AlipayTransfer($out_trade_no, $public->type, $total_amount, $alipay_account, $accounts->alipay_name, $config_id, $sxf_amount, $tx_remark);
                $AlipayTransfer->insert();


            } else {
                return json_encode(['status' => 2, 'message' => '没有绑定收银员账号']);
            }


            return json_encode(['status' => 1, 'message' => '提现申请成功', 'data' => $request->except(['token'])]);


        } catch (\Exception $exception) {
            Log::info($exception);
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }


    }

//提现记录
    public
    function out_wallet_list(Request $request)
    {

        try {
            $public = $this->parseToken();
            $time_start = $request->get('time_start', '');
            $time_end = $request->get('time_end', '');
            $user_id = $request->get('user_id', '');
            $where = [];
            if ($time_start) {
                $where[] = ['updated_at', '>=', $time_start];
            }
            if ($time_end) {
                $where[] = ['updated_at', '<=', $time_end];
            }

            if ($public->type == "merchant") {
                $user_id = $public->merchant_id;

                $where[] = ['merchant_id', '=', $user_id];
                $data = MerchantWithdrawalsRecords::where($where);
            }

            if ($public->type == "user") {

                if ($user_id) {
                    $users = $this->getSubIds($user_id);
                } else {
                    $user_id = $public->user_id;
                    $users = $this->getSubIds($public->user_id);

                }
                if (!in_array($user_id, $users)) {
                    return json_encode(['status' => 2, 'message' => '非上下级关系']);
                }
                //获取下级所有返佣
                if ($request->get('user_id')) {
                    $where[] = ['user_id', '=', $request->get('user_id')];
                    $data = UserWithdrawalsRecords::where($where);
                } else {
                    $data = UserWithdrawalsRecords::where($where)
                        ->whereIn('user_id', $this->getSubIds($public->user_id));
                }

            }
            $this->message = '数据返回成功';
            $this->t = $data->count();
            $data = $this->page($data)->orderBy('updated_at', 'desc')->get();
            return $this->format($data);

        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }

    //单个提现记录信息查询
    public function records_query_info(Request $request)
    {
        try {
            $public = $this->parseToken();
            $records_query_id = $request->get('records_query_id');
            if ($public->type == "user") {
                $data = UserWithdrawalsRecords::where('id', $records_query_id)->first();
            } else {
                $data = MerchantWithdrawalsRecords::where('id', $records_query_id)->first();
            }
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


    //结算 目前只支持 代理结算
    public
    function settlement(Request $request)
    {
        try {
            $public = $this->parseToken();
            $user_id = $request->get('user_id');
            $time_start = $request->get('time_start');
            $time_end = $request->get('time_end');
            $pay_password = $request->get('pay_password');
            $source_type = $request->get('source_type');
            $source_type_desc = $request->get('source_type_desc');
            $dx = $request->get('dx', '');
            $rate = $request->get('rate', '');

            $config_id = $public->config_id;
            $s_no = date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999)) . $config_id . $user_id . $source_type;

            $check_data = [
                'time_start' => '开始时间',
                'time_end' => '结束时间',
                'pay_password' => '支付密码',
                'source_type' => '返佣来源',
                'source_type_desc' => '返佣来源说明',
                'dx' => '对象',
                'rate' => '税点'

            ];


            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }


            //如果有未结算的记录 不允许结算
            $SettlementList = SettlementList::where('is_true', 0)
                ->where('config_id', $config_id)
                ->select('id')
                ->first();

            if ($SettlementList) {
                return json_encode([
                    'status' => 2,
                    'message' => '请先处理未结算订单'
                ]);
            }

            //服务商
            if ($dx == "1") {
                //查出时间段的返佣未结算用户列表
                $where = [];
                $where[] = ['settlement', '02'];
                $where[] = ['money','!=','0.0000'];

                if ($source_type) {
                    $where[] = ['source_type', $source_type];
                }
                if ($time_start) {
                    $where[] = ['created_at', '>=', $time_start];
                }
                if ($time_end) {
                    $where[] = ['created_at', '<=', $time_end];
                }


                //选择某个服务商
                if ($user_id && $user_id != "NULL") {
                    return json_encode([
                        'status' => 2,
                        'message' => '暂不支持单独结算服务商'
                    ]);

                    $user_ids = $this->getSubIds($user_id);
                    $users_obj = UserWalletDetail::where($where)
                        ->whereIn('user_id', $user_ids)
                        ->select('user_id')
                        ->distinct('user_id')//去重
                        ->get();
                } else {
                    $users_obj = UserWalletDetail::where($where)
                        ->select('user_id')
                        ->distinct('user_id')//去重
                        ->get();
                }


                if ($users_obj->isEmpty()) {
                    return json_encode([
                        'status' => 2,
                        'message' => '没有找到结算记录'
                    ]);
                }

                //开启事务
                try {
                    DB::beginTransaction();

                    $total_amount = 0;
                    $total_get_amount = 0;
                    //整个大的平台
                    $insert1 = [
                        'config_id' => $config_id,
                        's_no' => $s_no,
                        'dx' => $dx,
                        's_time' => $time_start,
                        'e_time' => $time_end,
                        'source_type' => $source_type,
                        'source_type_desc' => $source_type_desc,
                        'total_amount' => $total_amount,
                        'get_amount' => $total_get_amount,
                        'rate' => $rate,
                        'is_true' => 0,
                    ];
                    $SettlementList = SettlementList::create($insert1);
                    $settlement_list_id = $SettlementList->id;

                    foreach ($users_obj as $k => $v) {
                        $user_id = $v->user_id;
                        $UserWalletDetail = UserWalletDetail::where('source_type', $source_type)
                            ->where('created_at', '>', $time_start)
                            ->where('created_at', '<', $time_end)
                            ->where('settlement', '02')
                            ->where('user_id', $v->user_id)
                            ->select('money');


                        $money = $UserWalletDetail->sum('money');
                        //总金额
                        $total_amount = $total_amount + $money;
                        $j = number_format($rate / 100, 4, '.', '');
                        $get_amount = $money - (($money * $j));
                        $get_amount = number_format($get_amount, 4, '.', '');
                        $total_get_amount = $total_get_amount + $get_amount;

                        //个人金额
                        $insert = [
                            's_no' => $s_no,
                            'config_id' => $config_id,
                            'dx' => $dx,
                            's_time' => $time_start,
                            'e_time' => $time_end,
                            'source_type' => $source_type,
                            'source_type_desc' => $source_type_desc,
                            'total_amount' => $money,
                            'get_amount' => $get_amount,
                            'user_id' => $user_id,
                            'is_true' => 0,
                            'rate' => $rate,
                            'settlement_list_id' => $settlement_list_id
                        ];
                        SettlementListInfo::create($insert);
                    }

                    SettlementList::where('id', $settlement_list_id)->update([
                        'get_amount' => $total_get_amount,
                        'total_amount' => $total_amount,
                    ]);


                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    return json_encode(['status' => 2, 'message' => $e->getMessage()]);
                }


            } else {
                return json_encode([
                    'status' => 2,
                    'message' => '商户结算暂时未开放'
                ]);
            }

            return json_encode(['status' => 1, 'message' => '处理完毕！请确认']);

        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage()
            ]);
        }

    }


    //结算列表
    public function settlement_lists(Request $request)
    {
        try {
            $public = $this->parseToken();
            $config_id = $public->config_id;
            $dx = $request->get('dx', '');
            $source_type = $request->get('source_type', '');


            $where = [];
            if ($dx) {
                $where[] = ['dx', $dx];
            }
            if ($config_id) {
                $where[] = ['config_id', $config_id];
            }
            if ($source_type) {
                $where[] = ['source_type', $source_type];
            }

            $obj = SettlementList::where($where);
            $this->t = $obj->count();
            $data = $this->page($obj)->orderBy('updated_at', 'desc')->get();
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

    //结算明细
    public function settlement_list_infos(Request $request)
    {
        try {
            $public = $this->parseToken();
            $dx = $request->get('dx', '');
            $settlement_list_id = $request->get('settlement_list_id', '');
            $user_name = $request->get('user_name', '');
            $user_id = $request->get('user_id', '');

            $where = [];
            $config_id = $public->config_id;

            if ($settlement_list_id) {
                $where[] = ['settlement_list_infos.settlement_list_id', $settlement_list_id];
            }
            if ($user_id) {
                $where[] = ['settlement_list_infos.user_id', $user_id];
            }

            if ($settlement_list_id) {
                $where[] = ['settlement_list_infos.settlement_list_id', $settlement_list_id];
            }

            if ($dx) {
                $where[] = ['settlement_list_infos.dx', $dx];
            }
            if ($config_id) {
                $where[] = ['settlement_list_infos.config_id', $config_id];
            }

            if ($user_name) {
                if (is_numeric($user_name)) {
                    $obj = SettlementListInfo::where($where)
                        ->join('users', 'settlement_list_infos.user_id', '=', 'users.id')
                        ->where('users.phone', $user_name);
                } else {
                    $obj = SettlementListInfo::where($where)
                        ->join('users', 'settlement_list_infos.user_id', '=', 'users.id')
                        ->where('users.name', $user_name);
                }

            } else {
                $obj = SettlementListInfo::join('users', 'settlement_list_infos.user_id', '=', 'users.id')
                    ->where($where);

            }

            $this->t = $obj->count();
            $data = $this->page($obj)->select('settlement_list_infos.*', 'users.name as user_name', 'users.phone')->get();
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


    //删除结算列表明细
    public function settlement_list_del(Request $request)
    {
        try {
            $public = $this->parseToken();
            $config_id = $public->config_id;
            $settlement_list_id = $request->get('settlement_list_id', '');
            $where = [];


            $s = SettlementList::where('id', $settlement_list_id)
                ->where('config_id', $config_id)
                ->first();
            if (!$s) {
                return json_encode([
                    'status' => 2,
                    'message' => '结算ID不存在'
                ]);
            }

            if ($s->is_true) {
                return json_encode([
                    'status' => 2,
                    'message' => '已经确认的结算无法删除'
                ]);
            }

            //开启事务
            try {
                DB::beginTransaction();

                //删除列表
                $s->delete();

                //删除明细
                SettlementListInfo::where('settlement_list_id', $settlement_list_id)
                    ->where('config_id', $config_id)
                    ->delete();

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return json_encode(['status' => 2, 'message' => $e->getMessage()]);
            }

            $this->status = 1;
            $this->message = '删除成功';
            return $this->format([]);

        } catch (\Exception $exception) {
            return json_encode(
                [
                    'status' => -1,
                    'message' => $exception->getMessage()
                ]
            );
        }
    }


    //确认结算
    public function settlement_list_true(Request $request)
    {
        try {
            $public = $this->parseToken();
            $config_id = $public->config_id;
            $settlement_list_id = $request->get('settlement_list_id', '');

            $where = [];
            $list_info_id = $request->get('list_info_id', '');

            if ($list_info_id) {
                $SettlementListInfo = SettlementListInfo::where('id', $list_info_id)
                    ->where('config_id', $config_id)
                    ->first();

                if ($SettlementListInfo) {
                    $SettlementListInfo->is_true = 1;
                    $SettlementListInfo->save();
                }
                return json_encode([
                    'status' => 1,
                    'message' => '确认成功'
                ]);

            }


            if ($settlement_list_id) {
                $s = SettlementList::where('id', $settlement_list_id)
                    ->where('config_id', $config_id)
                    ->first();

                if (!$s) {
                    return json_encode([
                        'status' => 2,
                        'message' => 'settlement_list_id不存在'
                    ]);
                }


            } else {
                return json_encode([
                    'status' => 2,
                    'message' => 'settlement_list_id不存在'
                ]);
            }


            //开启事务
            try {
                DB::beginTransaction();

                //1.状态全部改成结算
                $s_time = $s->s_time;//开始时间
                $e_time = $s->e_time;//结束时间
                $source_type = $s->source_type;
                $s_no = $s->s_no;
                $dx = $s->dx;
                //服务商
                if ($dx == 1) {
                    UserWalletDetail::where('source_type', $source_type)
                        ->where('created_at', '>=', $s_time)
                        ->where('created_at', '<=', $e_time)
                        ->where('settlement', '02')
                        ->update([
                            'settlement' => '01',
                            'settlement_desc' => "已结算",
                        ]);

                    //2.将已经结算的金额入库
                    $SettlementListInfo = SettlementListInfo::where('settlement_list_id', $settlement_list_id)
                        ->where('s_no', $s_no)
                        ->select('get_amount', 'user_id')
                        ->get();

                    foreach ($SettlementListInfo as $k => $v) {

                        if ($v->get_amount == 0.0000) {
                            continue;
                        }

                        //金额相加
                        $user = User::where('id', $v->user_id)->first();

                        if (!$user) {
                            continue;
                        }
                        $user->money = $user->money + $v->get_amount;
                        $user->save();
                    }

                    $s->is_true = 1;
                    $s->save();

                }

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return json_encode(['status' => 2, 'message' => $e->getMessage()]);
            }

            $this->status = 1;
            $this->message = '结算成功';
            return $this->format([]);

        } catch (\Exception $exception) {
            return json_encode(['status' => -1,
                'message' => $exception->getMessage()]);
        }
    }

}