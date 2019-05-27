<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/7/9
 * Time: 下午2:32
 */

namespace App\Api\Controllers\User;


use App\Api\Controllers\BaseController;
use App\Api\Rsa\RsaE;
use App\Models\Store;
use App\Models\StorePayWay;
use App\Models\User;
use App\Models\UserDayOrder;
use App\Models\UserMonthOrder;
use App\Models\UserRate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseController
{

    //app 首页的统计
    public function get_my_data(Request $request)
    {

        try {
            $user = $this->parseToken();
            $user_id = $user->user_id;
            //总下级数量
            $sub_user_count = count($this->getSubIds($user_id, false));
            //总门店数 含下级
            $store_count = Store::whereIn('user_id', $this->getSubIds($user_id, true))->select('id')->count('id');

            //今日
            //总下级数量

            $beginToday = date("Y-m-d 00:00:00", time());
            $endToday = date("Y-m-d H:i:s", time());

            $sub_user_count1 = count($this->getSubIds($user_id, false, $beginToday, $endToday));
            //总门店数 含下级
            $store_count1 = Store::whereIn('user_id', $this->getSubIds($user_id, true, $beginToday, $endToday))->select('id')->count('id');

            //昨日
            //总下级数量
            $beginYesterday = date("Y-m-d 00:00:00", strtotime("-1 day"));
            $endYesterday = date("Y-m-d 23:59:59", strtotime("-1 day"));

            $sub_user_count2 = count($this->getSubIds($user_id, false, $beginYesterday, $endYesterday));
            //总门店数 含下级
            $store_count2 = Store::whereIn('user_id', $this->getSubIds($user_id, true, $beginYesterday, $endYesterday))->select('id')->count('id');


            $new_store_count = $store_count2 - $store_count1;
            $new_sub_user_count = $sub_user_count2 - $sub_user_count1;


            //今日流水
            $day = date('Ymd', time());
            $day_order = UserDayOrder::whereIn('user_id', $this->getSubIds($user_id))
                ->where('day', $day)
                ->select('total_amount')
                ->sum('total_amount');

            //昨日流水
            $old_day = date("Ymd", time() - 24 * 60 * 60);
            $day = date('Ymd', time());
            $old_day_order = UserDayOrder::whereIn('user_id', $this->getSubIds($user_id))
                ->where('day', $old_day)
                ->select('total_amount')
                ->sum('total_amount');

            //月流水
            $month = date('Ym', time());
            $month_order = UserMonthOrder::whereIn('user_id', $this->getSubIds($user_id))
                ->where('month', $month)
                ->select('total_amount')
                ->sum('total_amount');

            return json_encode([
                'status' => 1,
                'message' => '数据请求成功',
                'data' => [
                    'sub_user_count' => '' . $sub_user_count . '',
                    'store_count' => '' . $store_count . '',
                    'new_store_count' => '' . $new_store_count . '',
                    'new_sub_user_count' => '' . $new_sub_user_count . '',
                    'day_order' => '' . $day_order . '',
                    'old_day_order' => '' . $old_day_order . '',
                    'month_order' => '' . $month_order . '',
                ]
            ]);


        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage()
            ]);
        }
    }

    public function get_sub_users(Request $request)
    {
        try {
            $user = $this->parseToken();
            $login_user_id = $user->user_id;
            $user_id = $request->get('user_id', $login_user_id);
            $self = $request->get('self', false);
            $User_id_s = $this->getSubIds($user_id, $self);//不含自己
            $return_type = $request->get('return_type', '');
            $user_name = $request->get('user_name', '');
            $sub_type = $request->get('sub_type', '');

            $where = [];
            $where[] = ['is_delete', '=', 0];
            if ($user_name) {
                $where[] = ['name', 'like', '%' . $user_name . '%'];
            }

            $obj = new  User();

            if ($sub_type == "1") {
                $where[] = ['level', '<', '2'];

            }

            if ($return_type == "layui") {
                $Users = User::where('id', $login_user_id)
                    ->with('children')
                    ->select('id', 'pid', 'money', 'name', 'phone', 'level', 's_code')
                    ->get()->toArray();
                return json_encode([
                    'status' => 1,
                    'data' => $Users
                ]);
            }

            $obj = $obj->where($where)
                ->whereIn('id', $User_id_s);
            $this->t = $obj->count();
            $data = $this->page($obj)->get();
            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);

        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();

            return $this->format();

        }

    }


    //添加代理商或者业务员
    public function add_sub_user(Request $request)
    {
        try {
            $user = $this->parseToken();
            $phone = $request->get('phone');
            $password = $request->get('password');
            $name = $request->get('user_name');
            $user_id = $request->get('user_id', $user->user_id);

            $province_name = $request->get('province_name', '全国');
            $city_name = $request->get('city_name', '全国');
            $area_name = $request->get('area_name', '全国');
            $address = $request->get('address', '全国');


            $user = User::where('id', $user_id)->first();
            if (!$user) {
                return json_encode([
                    'status' => 2,
                    'message' => '用户不存在'
                ]);
            }
            //3级以后就不开了
            if ($user->level > 2) {
                return json_encode([
                    'status' => 2,
                    'message' => '暂不支持开下级账户'
                ]);
            }
            $s_code = rand(1000, 9999);
            $check_data = [
                'phone' => '手机号',
                'password' => '密码',
                'user_name' => '名字'
            ];

            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }


            //验证手机号
            if (!preg_match("/^1[3456789]{1}\d{9}$/", $phone)) {
                return json_encode([
                    'status' => 0,
                    'message' => '手机号码不正确'
                ]);
            }

            $data = $request->all();
            $rules = [
                'phone' => 'required|min:11|max:11|unique:users',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return json_encode([
                    'status' => 2,
                    'message' => '账号已注册请直接登录'
                ]);
            }

            $rules = [
                's_code' => 'required|min:4|max:4|unique:users',
            ];
            $validator = Validator::make(['s_code' => $s_code], $rules);
            if ($validator->fails()) {
                return json_encode([
                    'status' => 2,
                    'message' => '激活码生成失败，请重新提交资料'
                ]);
            }

            //验证密码
            if (strlen($password) < 6) {
                return json_encode([
                    'status' => 2,
                    'message' => '密码长度不符合要求'
                ]);
            }

            $level = $user->level + 1;
            $level_name = "业务员";
            if ($level == 1) {
                $level_name = "服务商";
            }
            if ($level == 2) {
                $level_name = "代理商";
            }

            $dataIn['config_id'] = $user->config_id;
            $dataIn['email'] = $phone . '@139.com';
            $dataIn['pid'] = $user->id;
            $dataIn['pid_name'] = $user->name;
            $dataIn['level'] = $level;
            $dataIn['level_name'] = $level_name;

            $dataIn['password'] = bcrypt($password);
            $dataIn['name'] = $name;
            $dataIn['phone'] = $phone;
            $dataIn['s_code'] = $s_code;
            $dataIn['province_name'] = $province_name;
            $dataIn['city_name'] = $city_name;
            $dataIn['area_name'] = $area_name;
            $dataIn['address'] = $address;
            $dataIn['s_code_url'] = url('/api/user/s_code_url?s_code=' . $s_code);
            $dataIn['sub_code_url'] = url('/api/user/sub_code_url?s_code=' . $s_code);


            //超级服务商
            if ($user->pid == 0) {
                $dataIn['config_id'] = $phone;
                $dataIn['level'] = 1;
            }


            User::create($dataIn);


            return json_encode([
                'status' => 1,
                'message' => '下级账号添加成功',
            ]);
        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage() . $exception->getFile()
            ]);
        }
    }


    //修改代理或者自己的信息
    public function up_user(Request $request)
    {
        try {
            $user = $this->parseToken();
            $user_id = $request->get('user_id', $user->user_id);

            $data = $request->except(['token', 'password', 'phone']);

            $user = User::where('id', $user_id)->first();
            if (!$user) {
                return json_encode([
                    'status' => 2,
                    'message' => '用户不存在'
                ]);
            }
            $user->update($data);
            $user->save();
            return json_encode([
                'status' => 1,
                'message' => '修改成功',
            ]);
        } catch (\Exception $exception) {
            Log::info($exception);
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage() . $exception->getFile()
            ]);
        }
    }


    //设置登录密码
    public function set_password(Request $request)
    {
        try {
            $user = $this->parseToken();
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

            $local = User::where('id', $user->user_id)->first();
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
            User::where('id', $user->user_id)->update($dataIN);


            $data = [
                'status' => 1,
                'message' => '密码修改成功',
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
            $user = $this->parseToken();
            $data = $request->all();
            $password = $request->get('password', '');
            $new_phone = $request->get('phone', '');
            $code_b = $request->get('code_b', '');
            //如果只传password代表校验
            if ($password && $new_phone == "" && $code_b == "") {
                //验证验证码
                $local = User::where('id', $user->user_id)->first();
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
                    ]);
                }
            } else {
                if ($code_b && $new_phone == '' & $password == "") {
                    //验证新手机验证码
                    $msn_local = Cache::get($new_phone . 'editphone-1');
                    if ((string)$code_b != (string)$msn_local) {
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
                if ($new_phone == $user->phone) {
                    return json_encode([
                        'status' => 2,
                        'message' => '手机号码未更改'
                    ]);
                }
                $rules = [
                    'phone' => 'required|min:11|max:11|unique:users',
                ];
                $validator = Validator::make($data, $rules);
                if ($validator->fails()) {
                    return json_encode([
                        'status' => 2,
                        'message' => '账号已注册请更换'
                    ]);
                }


                //验证新手机验证码
                $msn_local = Cache::get($new_phone . 'editphone-1');
                if ((string)$code_b != (string)$msn_local) {
                    return json_encode([
                        'status' => 2,
                        'message' => '新手机号码短信验证码不匹配'
                    ]);
                }

                User::where('id', $user->user_id)->update(['phone' => $new_phone]);


                $data = [
                    'status' => 1,
                    'message' => '手机号修改成功',
                ];
                return json_encode($data);

            }
        } catch
        (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }
    }

    //删除代理
    public function del_sub_user(Request $request)
    {
        try {
            $user = $this->parseToken();
            $user_id = $request->get('user_id', '');

            $province_name = $request->get('province_name', '全国');
            $city_name = $request->get('city_name', '全国');
            $area_name = $request->get('area_name', '全国');
            $address = $request->get('address', '全国');


            $user = User::where('id', $user_id)->first();
            if (!$user) {
                return json_encode([
                    'status' => 2,
                    'message' => '用户不存在'
                ]);
            }

            if ($user->user_id == $user_id) {
                return json_encode([
                    'status' => 2,
                    'message' => '不能删除自己'
                ]);
            }
            $sub_user = User::where('pid', $user_id)->first();
            if ($sub_user) {
                return json_encode([
                    'status' => 2,
                    'message' => '账户存在下级请先删除下级账户'
                ]);
            }

            $user->update(['is_delete' => 1]);
            $user->save();

            return json_encode([
                'status' => 1,
                'message' => '下级账号删除成功',
            ]);
        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage() . $exception->getFile()
            ]);
        }
    }

    //我的信息
    public function my_info(Request $request)
    {
        try {
            $user = $this->parseToken();
            $user_id = $user->user_id;
            $user = User::where('id', $user_id)->first();
            if (!$user) {
                return json_encode([
                    'status' => 2,
                    'message' => '用户不存在'
                ]);
            }
            $sub = $this->getSubIds($user_id, false);
            $user->sub_user_count = "" . count($sub) . "";
            //默认头像
            if ($user->logo == "") {
                $user->logo = url('/app/img/user/fwslogo.png');
            }
            return json_encode([
                'status' => 1,
                'message' => '删除成功',
                'data' => $user
            ]);
        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage() . $exception->getFile()
            ]);
        }
    }

    public function user_info(Request $request)
    {
        try {
            $user = $this->parseToken();
            $user_id = $request->get('user_id', '');
            $user = User::where('id', $user_id)->first();
            if (!$user) {
                return json_encode([
                    'status' => 2,
                    'message' => '用户不存在'
                ]);
            }
            //默认头像
            if ($user->logo == "") {
                $user->logo = url('/app/img/user/fwslogo.png');
            }
            return json_encode([
                'status' => 1,
                'message' => '返回成功',
                'data' => $user
            ]);
        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage() . $exception->getFile()
            ]);
        }
    }


    //查询代理成本费率情况
    public function user_ways_all(Request $request)
    {
        try {
            $user = $this->parseToken();
            $user_id = $request->get('user_id');

            $data = [];
            $store_ways_desc = DB::table('store_ways_desc')->get();
            foreach ($store_ways_desc as $k => $value) {
                $has = UserRate::where('user_id', $user_id)->where('ways_type', $value->ways_type)->first();
                if ($has) {
                    $data[$k]['ways_type'] = $value->ways_type;
                    $data[$k]['ways_desc'] = $value->ways_desc;
                    $data[$k]['rate'] = $has->rate;
                    $data[$k]['rate_a'] = $has->rate_a;
                    $data[$k]['rate_b'] = $has->rate_b;
                    $data[$k]['rate_b_top'] = $has->rate_b_top;
                    $data[$k]['rate_c'] = $has->rate_c;
                    $data[$k]['rate_d'] = $has->rate_d;
                    $data[$k]['rate_d_top'] = $has->rate_d_top;
                    $data[$k]['rate_e'] = $has->rate_e;
                    $data[$k]['rate_f'] = $has->rate_f;
                    $data[$k]['rate_f_top'] = $has->rate_f_top;
                    $data = array_values($data);


                    //如果是刷卡费率读取
                    if (in_array($value->ways_type, [8005, 6005])) {
                        $data[$k]['rate'] = $has->rate_e;
                    }
                    //如果是银联扫码费率读取
                    if (in_array($value->ways_type, [8004, 6004])) {
                        $data[$k]['rate'] = $has->rate_a;
                    }

                } else {
                    $data[$k]['ways_type'] = $value->ways_type;
                    $data[$k]['ways_desc'] = $value->ways_desc;
                    $data[$k]['rate_a'] = $value->rate_a;
                    $data[$k]['rate_b'] = $value->rate_b;
                    $data[$k]['rate_b_top'] = $value->rate_b_top;
                    $data[$k]['rate_c'] = $value->rate_c;
                    $data[$k]['rate_d'] = $value->rate_d;
                    $data[$k]['rate_d_top'] = $value->rate_d_top;
                    $data[$k]['rate_e'] = $value->rate_e;
                    $data[$k]['rate_f'] = $value->rate_f;
                    $data[$k]['rate_f_top'] = $value->rate_f_top;
                    $data[$k]['rate'] = '';

                }

            }

            return json_encode(['status' => 1, 'data' => $data]);


        } catch (\Exception $exception) {
            return json_encode(['status' => 0, 'message' => $exception->getMessage() . ' - ' . $exception->getLine()]);
        }
    }

    //查询代理各个通道商户的默认费率
    public function user_ways_default(Request $request)
    {
        try {
            $user = $this->parseToken();
            $user_id = $request->get('user_id');

            $data = [];
            $store_ways_desc = DB::table('store_ways_desc')->get();
            foreach ($store_ways_desc as $k => $value) {
                $has = UserRate::where('user_id', $user_id)->where('ways_type', $value->ways_type)->first();
                if ($has) {
                    $data[$k]['ways_type'] = $value->ways_type;
                    $data[$k]['ways_desc'] = $value->ways_desc;
                    $data[$k]['store_all_rate'] = $has->store_all_rate;
                    $data[$k]['store_all_rate_a'] = $has->store_all_rate_a;
                    $data[$k]['store_all_rate_b'] = $has->store_all_rate_b;
                    $data[$k]['store_all_rate_b_top'] = $has->store_all_rate_b_top;
                    $data[$k]['store_all_rate_c'] = $has->store_all_rate_c;
                    $data[$k]['store_all_rate_d'] = $has->store_all_rate_d;
                    $data[$k]['store_all_rate_d_top'] = $has->store_all_rate_d_top;
                    $data[$k]['store_all_rate_e'] = $has->store_all_rate_e;
                    $data[$k]['store_all_rate_f'] = $has->store_all_rate_f;
                    $data[$k]['store_all_rate_f_top'] = $has->store_all_rate_f_top;


                    //如果是刷卡费率读取
                    //新大陆刷卡
                    if (in_array($value->ways_type, [8005, 6005])) {

                        $data[$k]['store_all_rate'] = $has->store_all_rate_e;
                    }

                    //银联扫码
                    if (in_array($value->ways_type, [8004, 6004])) {
                        $data[$k]['store_all_rate'] = $has->store_all_rate_a;
                    }


                    $data = array_values($data);


                } else {
                    $data[$k]['ways_type'] = $value->ways_type;
                    $data[$k]['ways_desc'] = $value->ways_desc;
                    $data[$k]['store_all_rate'] = $value->store_all_rate;
                    $data[$k]['store_all_rate_a'] = $value->store_all_rate_a;
                    $data[$k]['store_all_rate_b'] = $value->store_all_rate_b;
                    $data[$k]['store_all_rate_b_top'] = $value->store_all_rate_b_top;
                    $data[$k]['store_all_rate_c'] = $value->store_all_rate_c;
                    $data[$k]['store_all_rate_d'] = $value->store_all_rate_d;
                    $data[$k]['store_all_rate_d_top'] = $value->store_all_rate_d_top;
                    $data[$k]['store_all_rate_e'] = $value->store_all_rate_e;
                    $data[$k]['store_all_rate_f'] = $value->store_all_rate_f;
                    $data[$k]['store_all_rate_f_top'] = $value->store_all_rate_f_top;
                    //如果是刷卡费率读取
                    //新大陆刷卡
                    if (in_array($value->ways_type, [8005, 6005])) {

                        $data[$k]['store_all_rate'] = $value->store_all_rate_e;
                    }

                    //银联扫码
                    if (in_array($value->ways_type, [8004, 6004])) {
                        $data[$k]['store_all_rate'] = $value->store_all_rate_a;
                    }


                }

            }

            return json_encode(['status' => 1, 'data' => $data]);


        } catch (\Exception $exception) {
            return json_encode(['status' => 0, 'message' => $exception->getMessage() . ' - ' . $exception->getLine()]);
        }
    }

    //修改代理带费率-扫码
    public function edit_user_rate(Request $request)
    {

        try {
            $user = $this->parseToken();
            $user_id = $request->get('user_id');
            $ways_type = $request->get('ways_type');

            //扫码
            $rate = $request->get('rate');
            $edit_user = User::where('id', $user_id)->first();
            if (!$edit_user) {
                return json_encode(['status' => 2, 'message' => '代理商不存在', 'data' => []]);
            }


            if ($user_id == $user->user_id && $user->level != 0) {
                return json_encode(['status' => 2, 'message' => '不能设置自己的费率请联系上级', 'data' => []]);
            }

            if ($rate > 1) {
                return json_encode(['status' => 2, 'message' => '费率超过系统上限', 'data' => []]);
            }

            //上级代理商的费率
            $user_pid_rate = UserRate::where('user_id', $edit_user->pid)
                ->where('ways_type', $ways_type)
                ->select('rate')
                ->first();
            if (!$user_pid_rate) {
                return json_encode(['status' => 2, 'message' => '上级代理商未设置费率', 'data' => []]);
            }


            //被设置者是平台以下的账户不能大于上级的成本
            if ($edit_user->level > 0) {
                if ($rate < $user_pid_rate->rate) {
                    return json_encode(['status' => 2, 'message' => '费率不能低于上级代理商的费率', 'data' => []]);
                }
            }

            $astore_ways_desc = DB::table('store_ways_desc')
                ->where('ways_type', $ways_type)
                ->select('company')
                ->first();
            if (!$astore_ways_desc) {
                return json_encode(['status' => 2, 'message' => '通道基础数据不存在']);
            }

            //扫码需要参数
            $data = [
                'user_id' => $user_id,
                'rate' => $rate,
                'company' => $astore_ways_desc->company,
            ];


            $this->send_ways_data($data);

            return json_encode(['status' => 1, 'message' => '修改成功', 'data' => $request->except(['token'])]);


        } catch (\Exception $exception) {
            return json_encode(['status' => 0, 'message' => $exception->getMessage() . ' - ' . $exception->getLine()]);
        }

    }

    //修改代理带费率-刷卡
    public function edit_user_un_rate(Request $request)
    {

        try {
            $user = $this->parseToken();
            $user_id = $request->get('user_id');
            $ways_type = $request->get('ways_type');

            $rate_e = $request->get('rate_e');
            $rate_f = $request->get('rate_f');
            $rate_f_top = $request->get('rate_f_top', '20');

            $edit_user = User::where('id', $user_id)->first();
            if (!$edit_user) {
                return json_encode(['status' => 2, 'message' => '代理商不存在', 'data' => []]);
            }


            if ($user_id == $user->user_id && $user->level != 0) {
                return json_encode(['status' => 2, 'message' => '不能设置自己的费率请联系上级', 'data' => []]);
            }

            //上级代理商的费率
            $user_pid_rate = UserRate::where('user_id', $edit_user->pid)
                ->where('ways_type', $ways_type)
                ->select('rate_e', 'rate_f')
                ->first();

            if (!$user_pid_rate) {
                return json_encode(['status' => 2, 'message' => '上级代理商未设置费率', 'data' => []]);
            }

            //被设置者是平台以下的账户不能大于上级的成本
            if ($edit_user->level > 0) {
                //不能大于代理商的成本
                if ($rate_e < $user_pid_rate->rate_e) {
                    return json_encode(['status' => 2, 'message' => '贷记卡费率不能低于上级代理商的费率', 'data' => []]);
                }
                //不能大于代理商的成本
                if ($rate_f < $user_pid_rate->rate_f) {
                    return json_encode(['status' => 2, 'message' => '借记卡费率不能低于上级代理商的费率', 'data' => []]);
                }
            }

            $astore_ways_desc = DB::table('store_ways_desc')
                ->where('ways_type', $ways_type)
                ->select('company')
                ->first();
            if (!$astore_ways_desc) {
                return json_encode(['status' => 2, 'message' => '通道基础数据不存在']);
            }


            $ways = UserRate::where('user_id', $user_id)->where('ways_type', $ways_type)->first();

            //请先设置扫码通道的费率
            if (!$ways) {
                return json_encode(['status' => 2, 'message' => '请先设置扫码通道的费率']);
            }

            $company = $ways->company;
            $data = [
                'rate_e' => $rate_e,
                'rate_f' => $rate_f,
                'rate_f_top' => $rate_f_top,
            ];
            UserRate:: where('user_id', $user_id)->where('company', $company)
                ->update($data);


            return json_encode(['status' => 1, 'message' => '修改成功', 'data' => $request->except(['token'])]);


        } catch (\Exception $exception) {
            return json_encode(['status' => 0, 'message' => $exception->getMessage() . ' - ' . $exception->getLine()]);
        }

    }

    //修改代理带费率-银联扫码
    public function edit_user_unqr_rate(Request $request)
    {

        try {
            $user = $this->parseToken();
            $user_id = $request->get('user_id');
            $ways_type = $request->get('ways_type');

            $rate_a = $request->get('rate_a');
            $rate_b = $request->get('rate_b');
            $rate_b_top = $request->get('rate_b_top', '20');
            $rate_c = $request->get('rate_c');
            $rate_d = $request->get('rate_d');
            $rate_d_top = $request->get('rate_d_top', '20');

            $edit_user = User::where('id', $user_id)->first();
            if (!$edit_user) {
                return json_encode(['status' => 2, 'message' => '代理商不存在', 'data' => []]);
            }


            //上级代理商的费率
            $user_pid_rate = UserRate::where('user_id', $edit_user->pid)
                ->where('ways_type', $ways_type)
                ->select('rate_a', 'rate_b', 'rate_c', 'rate_d')
                ->first();

            if (!$user_pid_rate) {
                return json_encode(['status' => 2, 'message' => '上级代理商未设置费率', 'data' => []]);
            }

            if ($user_id == $user->user_id && $user->level != 0) {
                return json_encode(['status' => 2, 'message' => '不能设置自己的费率请联系上级', 'data' => []]);
            }

            //被设置者是平台以下的账户不能大于上级的成本
            if ($edit_user->level > 0) {
                //不能大于代理商的成本
                if ($rate_a < $user_pid_rate->rate_a) {
                    return json_encode(['status' => 2, 'message' => '小于1000贷记卡费率不能低于上级代理商的费率', 'data' => []]);
                }
                //不能大于代理商的成本
                if ($rate_b < $user_pid_rate->rate_b) {
                    return json_encode(['status' => 2, 'message' => '小于1000借记卡费率不能低于上级代理商的费率', 'data' => []]);
                }

                //不能大于代理商的成本
                if ($rate_c < $user_pid_rate->rate_c) {
                    return json_encode(['status' => 2, 'message' => '大于1000贷记卡费率不能低于上级代理商的费率', 'data' => []]);
                }
                //不能大于代理商的成本
                if ($rate_d < $user_pid_rate->rate_d) {
                    return json_encode(['status' => 2, 'message' => '大于1000借记卡费率不能低于上级代理商的费率', 'data' => []]);
                }

            }

            $astore_ways_desc = DB::table('store_ways_desc')
                ->where('ways_type', $ways_type)
                ->select('company')
                ->first();
            if (!$astore_ways_desc) {
                return json_encode(['status' => 2, 'message' => '通道基础数据不存在']);
            }


            $ways = UserRate::where('user_id', $user_id)->where('ways_type', $ways_type)->first();

            //请先设置扫码通道的费率
            if (!$ways) {
                return json_encode(['status' => 2, 'message' => '请先设置扫码通道的费率']);
            }
            $company = $ways->company;

            $data = [
                'rate_a' => $rate_a,
                'rate_b' => $rate_b,
                'rate_b_top' => $rate_b_top,
                'rate_c' => $rate_c,
                'rate_d' => $rate_d,
                'rate_d_top' => $rate_d_top,
            ];

            UserRate::where('user_id', $user_id)->where('company', $company)->update($data);


            return json_encode(['status' => 1, 'message' => '修改成功', 'data' => $request->except(['token'])]);


        } catch (\Exception $exception) {
            return json_encode(['status' => 0, 'message' => $exception->getMessage() . ' - ' . $exception->getLine()]);
        }

    }

    //修改商户统一默认费率-扫码
    public function edit_user_store_all_rate(Request $request)
    {

        try {
            $user = $this->parseToken();
            $user_id = $request->get('user_id');
            $ways_type = $request->get('ways_type');

            //扫码
            $store_all_rate = $request->get('store_all_rate');
            $edit_user = User::where('id', $user_id)->first();
            if (!$edit_user) {
                return json_encode(['status' => 2, 'message' => '代理商不存在', 'data' => []]);
            }

            if ($store_all_rate > 1) {
                return json_encode(['status' => 2, 'message' => '费率超过系统上限', 'data' => []]);
            }

            //自己的费率
            $user_rate = UserRate::where('user_id', $user_id)
                ->where('ways_type', $ways_type)
                ->first();
            if (!$user_rate) {
                return json_encode(['status' => 2, 'message' => '通道成本费率未设置请联系上级', 'data' => []]);

            }

            //不能小于自己的成本
            if ($store_all_rate < $user_rate->rate) {
                return json_encode(['status' => 2, 'message' => '费率不能低于成本费率', 'data' => []]);
            }


            $astore_ways_desc = DB::table('store_ways_desc')
                ->where('ways_type', $ways_type)
                ->select('company')
                ->first();
            if (!$astore_ways_desc) {
                return json_encode(['status' => 2, 'message' => '通道基础数据不存在']);
            }

            //扫码需要参数
            $data = [
                'user_id' => $user_id,
                'store_all_rate' => $store_all_rate,
                'company' => $astore_ways_desc->company,
            ];


            //和融通
            if (8999 < $ways_type && $ways_type < 9999) {
                if ($store_all_rate != '0.38') {
                    return json_encode(['status' => 2, 'message' => '和融通费率必须是0.38']);
                }
            }


            $this->send_ways_store_all_data($data);

            return json_encode(['status' => 1, 'message' => '修改成功', 'data' => $request->except(['token'])]);


        } catch (\Exception $exception) {
            return json_encode(['status' => 0, 'message' => $exception->getMessage() . ' - ' . $exception->getLine()]);
        }

    }

    //修改商户默认统一费率-刷卡
    public function edit_user_un_store_all_rate(Request $request)
    {

        try {
            $user = $this->parseToken();
            $user_id = $request->get('user_id');
            $ways_type = $request->get('ways_type');

            $store_all_rate_e = $request->get('store_all_rate_e');
            $store_all_rate_f = $request->get('store_all_rate_f');
            $store_all_rate_f_top = $request->get('store_all_rate_f_top', '20');

            $edit_user = User::where('id', $user_id)->first();
            if (!$edit_user) {
                return json_encode(['status' => 2, 'message' => '代理商不存在', 'data' => []]);
            }


            //自己的费率
            $user_rate = UserRate::where('user_id', $user_id)
                ->where('ways_type', $ways_type)
                ->first();

            if (!$user_rate) {
                return json_encode(['status' => 2, 'message' => '通道成本费率未设置请联系上级', 'data' => []]);

            }

            //不能小于自己的成本
            if ($store_all_rate_e < $user_rate->rate_e) {
                return json_encode(['status' => 2, 'message' => '贷记卡费率不能低于成本费率', 'data' => []]);
            }
            //不能小于自己的成本
            if ($store_all_rate_f < $user_rate->rate_f) {
                return json_encode(['status' => 2, 'message' => '借记卡费率不能低于成本费率', 'data' => []]);
            }

            //不能小于自己的成本
            if ($store_all_rate_f_top < $user_rate->rate_f_top) {
                return json_encode(['status' => 2, 'message' => '借记卡封顶金额不能小于成本', 'data' => []]);
            }


            $astore_ways_desc = DB::table('store_ways_desc')
                ->where('ways_type', $ways_type)
                ->select('company')
                ->first();
            if (!$astore_ways_desc) {
                return json_encode(['status' => 2, 'message' => '通道基础数据不存在']);
            }


            $ways = UserRate::where('user_id', $user_id)->where('ways_type', $ways_type)->first();

            //请先设置扫码通道的费率
            if (!$ways) {
                return json_encode(['status' => 2, 'message' => '请先设置扫码通道的费率']);
            }
            $company = $ways->company;

            $data = [
                'store_all_rate_e' => $store_all_rate_e,
                'store_all_rate_f' => $store_all_rate_f,
                'store_all_rate_f_top' => $store_all_rate_f_top,
            ];

            UserRate::where('user_id', $user_id)->where('company', $company)->update($data);


            return json_encode(['status' => 1, 'message' => '修改成功', 'data' => $request->except(['token'])]);


        } catch (\Exception $exception) {
            return json_encode(['status' => 0, 'message' => $exception->getMessage() . ' - ' . $exception->getLine()]);
        }

    }

    //修改商户默认统一费率-银联扫码
    public function edit_user_unqr_store_all_rate(Request $request)
    {
        try {
            $user = $this->parseToken();
            $user_id = $request->get('user_id');
            $ways_type = $request->get('ways_type');

            $store_all_rate_a = $request->get('store_all_rate_a');
            $store_all_rate_b = $request->get('store_all_rate_b');
            $store_all_rate_b_top = $request->get('store_all_rate_b_top', '20');
            $store_all_rate_c = $request->get('store_all_rate_c');
            $store_all_rate_d = $request->get('store_all_rate_d');
            $store_all_rate_d_top = $request->get('store_all_rate_d_top', '20');

            $edit_user = User::where('id', $user_id)->first();
            if (!$edit_user) {
                return json_encode(['status' => 2, 'message' => '代理商不存在', 'data' => []]);
            }


            //自己的费率
            $user_rate = UserRate::where('user_id', $user_id)
                ->where('ways_type', $ways_type)
                ->first();

            if (!$user_rate) {
                return json_encode(['status' => 2, 'message' => '通道成本费率未设置请联系上级', 'data' => []]);

            }
            //不能小于自己的成本
            if ($store_all_rate_a < $user_rate->rate_a) {
                return json_encode(['status' => 2, 'message' => '小于1000贷记卡费率不能低于自己的成本', 'data' => []]);
            }

            //不能小于自己的成本
            if ($store_all_rate_b < $user_rate->rate_b) {
                return json_encode(['status' => 2, 'message' => '小于1000借记卡费率不能低于自己的成本', 'data' => []]);
            }

            //不能小于自己的成本
            if ($store_all_rate_b_top < $user_rate->rate_b_top) {
                return json_encode(['status' => 2, 'message' => '小于1000借记卡封顶金额不能小于自己的成本', 'data' => []]);
            }


            //不能小于自己的成本
            if ($store_all_rate_c < $user_rate->rate_c) {
                return json_encode(['status' => 2, 'message' => '大于1000贷记卡费率不能低于自己的成本', 'data' => []]);
            }
            //不能小于自己的成本
            if ($store_all_rate_d < $user_rate->rate_d) {
                return json_encode(['status' => 2, 'message' => '大于1000借记卡费率不能低于自己的成本', 'data' => []]);
            }

            //不能小于自己的成本
            if ($store_all_rate_d_top < $user_rate->rate_d_top) {
                return json_encode(['status' => 2, 'message' => '大于1000借记卡封顶金额不能小于自己的成本', 'data' => []]);
            }


            $astore_ways_desc = DB::table('store_ways_desc')
                ->where('ways_type', $ways_type)
                ->select('company')
                ->first();
            if (!$astore_ways_desc) {
                return json_encode(['status' => 2, 'message' => '通道基础数据不存在']);
            }


            $ways = UserRate::where('user_id', $user_id)->where('ways_type', $ways_type)->first();

            //请先设置扫码通道的费率
            if (!$ways) {
                return json_encode(['status' => 2, 'message' => '请先设置扫码通道的费率']);
            }
            $company = $ways->company;

            $data = [
                'store_all_rate_a' => $store_all_rate_a,
                'store_all_rate_b' => $store_all_rate_b,
                'store_all_rate_b_top' => $store_all_rate_b_top,
                'store_all_rate_c' => $store_all_rate_c,
                'store_all_rate_d' => $store_all_rate_d,
                'store_all_rate_d_top' => $store_all_rate_d_top,
            ];


            UserRate::where('user_id', $user_id)->where('company', $company)
                ->update($data);


            return json_encode(['status' => 1, 'message' => '修改成功', 'data' => $request->except(['token'])]);


        } catch (\Exception $exception) {
            return json_encode(['status' => 0, 'message' => $exception->getMessage() . ' - ' . $exception->getLine()]);
        }

    }

    //查询单个通道详细
    public function user_ways_info(Request $request)
    {
        try {
            $user = $this->parseToken();
            $user_id = $request->get('user_id');
            $ways_type = $request->get('ways_type');

            $data = [];
            $store_ways_desc = DB::table('store_ways_desc')->where('ways_type', $ways_type)->first();
            $data['ways_desc'] = $store_ways_desc->ways_desc;
            $data['ways_source'] = $store_ways_desc->ways_source;
            $data['settlement_type'] = $store_ways_desc->settlement_type;
            $data['ways_type'] = $store_ways_desc->ways_type;

            $has = UserRate::where('user_id', $user_id)->where('ways_type', $ways_type)->first();
            if ($has) {
                $data['rate'] = $has->rate;
                $data['rate_a'] = $has->rate_a;
                $data['rate_b'] = $has->rate_b;
                $data['rate_b_top'] = $has->rate_b_top;
                $data['rate_c'] = $has->rate_c;
                $data['rate_d'] = $has->rate_d;
                $data['rate_d_top'] = $has->rate_d_top;
                $data['store_all_rate'] = $has->store_all_rate;
                $data['store_all_rate_a'] = $has->store_all_rate_a;
                $data['store_all_rate_b'] = $has->store_all_rate_b;
                $data['store_all_rate_b_top'] = $has->store_all_rate_b_top;
                $data['store_all_rate_c'] = $has->store_all_rate_c;
                $data['store_all_rate_d'] = $has->store_all_rate_d;
                $data['store_all_rate_d_top'] = $has->store_all_rate_d_top;

            } else {
                $data['rate'] = '';
                $data['store_all_rate'] = '';
                $data['rate_a'] = '';
                $data['rate_b'] = '';
                $data['rate_b_top'] = '';
                $data['store_all_rate_a'] = '';
                $data['store_all_rate_b'] = '';
                $data['store_all_rate_b_top'] = '';
                $data['rate_c'] = '';
                $data['rate_d'] = '';
                $data['rate_d_top'] = '';
                $data['store_all_rate_c'] = '';
                $data['store_all_rate_d'] = '';
                $data['store_all_rate_d_top'] = '';
            }


            return json_encode(['status' => 1, 'data' => $data]);


        } catch (\Exception $exception) {
            return json_encode(['status' => 0, 'message' => $exception->getMessage() . ' - ' . $exception->getLine()]);
        }
    }


    //添加支付密码
    public function add_pay_password(Request $request)
    {
        $rsa = new RsaE();
        try {
            $user = $this->parseToken();
            $user_id = $user->user_id;

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
                User::where('id', $user_id)->update($dataIN);
            } else {
                return json_encode([
                    'status' => 2,
                    'message' => '参数填写不完整'
                ]);
            }

            return json_encode([
                'status' => 1,
                'message' => '支付密码添加成功',
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
            $user = $this->parseToken();
            $user_id = $user->user_id;

            $oldpassword = isset($output['old_pay_password']) ? $output['old_pay_password'] : "";
            $newpassword = isset($output['new_pay_password']) ? $output['new_pay_password'] : "";

            if ($oldpassword && $newpassword == "") {
                $local = User::where('id', $user_id)->first();
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
                    'message' => '密码长度不符合要求'
                ]);
            }
            $dataIN = [
                'pay_password' => bcrypt($newpassword),

            ];

            User::where('id', $user_id)->update($dataIN);

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
            $user = $this->parseToken();
            $user_id = $user->user_id;
            //客户端用的我的公钥加密 我用私钥解密
            $data = $rsa->privDecrypt($request->get('sign'));//解密
            parse_str($data, $output);
            $newpassword = isset($output['new_pay_password']) ? $output['new_pay_password'] : "";
            $code = isset($output['code']) ? $output['code'] : "";
            $msn_local = Cache::get($user->phone . 'editpassword-1');
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


            $User = User::where('id', $user_id)->first();

            //验证验证码
            $msn_local = Cache::get($User->phone . 'editpassword-1');
            if ((string)$code != (string)$msn_local) {
                return json_encode([
                    'status' => 2,
                    'message' => '短信验证码不匹配'
                ]);
            }

            $User->update(['pay_password' => bcrypt($newpassword)]);
            $User->save();

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
            $user = $this->parseToken();
            $user_id = $user->user_id;
            $data = $rsa->privDecrypt($request->get('sign'));//解密
            parse_str($data, $output);

            $pay_password = isset($output['pay_password']) ? $output['pay_password'] : "";


            if (strlen($pay_password) != 6) {
                return json_encode([
                    'status' => 2,
                    'message' => '密码长度不符合要求'
                ]);
            }
            $local_pay_password = User::where('id', $user_id)->first();
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
        $user = $this->parseToken();
        $user_id = $user->user_id;
        $User = User::where('id', $user_id)->first();

        if ($User->pay_password) {

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


    public function send_ways_data($data)
    {
        try {
            //开启事务
            $all_pay_ways = DB::table('store_ways_desc')->where('company', $data['company'])->get();
            foreach ($all_pay_ways as $k => $v) {
                $ways = UserRate::where('user_id', $data['user_id'])->where('ways_type', $v->ways_type)
                    ->first();
                try {
                    DB::beginTransaction();
                    $data = [
                        'rate' => $data['rate'],
                        'settlement_type' => $v->settlement_type,
                        'user_id' => $data['user_id'],
                        'ways_type' => $v->ways_type,
                        'company' => $v->company,
                    ];
                    if ($ways) {
                        $ways->update($data);
                        $ways->save();
                    } else {
                        UserRate::create($data);
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    return [
                        'status' => 2,
                        'message' => '通道入库更新失败',
                    ];
                }
            }


            return [
                'status' => $data['status'],
                'message' => $data['status_desc'],
            ];

        } catch (\Exception $e) {
            return [
                'status' => 2,
                'message' => '通道入库更新失败',
            ];
        }
    }

    public function send_ways_store_all_data($data)
    {
        try {
            //开启事务
            $all_pay_ways = DB::table('store_ways_desc')->where('company', $data['company'])->get();
            foreach ($all_pay_ways as $k => $v) {
                $ways = UserRate::where('user_id', $data['user_id'])->where('ways_type', $v->ways_type)
                    ->first();
                try {
                    DB::beginTransaction();
                    $data = [
                        'store_all_rate' => $data['store_all_rate'],
                        'settlement_type' => $v->settlement_type,
                        'user_id' => $data['user_id'],
                        'ways_type' => $v->ways_type,
                        'company' => $v->company,
                    ];
                    if ($ways) {
                        $ways->update($data);
                        $ways->save();
                    } else {
                        UserRate::create($data);
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    return [
                        'status' => 2,
                        'message' => '通道入库更新失败',
                    ];
                }
            }


            return [
                'status' => $data['status'],
                'message' => $data['status_desc'],
            ];

        } catch (\Exception $e) {
            return [
                'status' => 2,
                'message' => '通道入库更新失败',
            ];
        }
    }
}