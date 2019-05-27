<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/5/25
 * Time: 下午2:21
 */

namespace App\Api\Controllers\Merchant;


use App\Api\Controllers\ApiController;
use App\Api\Controllers\BaseController;
use App\Api\Controllers\Push\JpushController;
use App\Models\Merchant;
use App\Models\MerchantStore;
use App\Models\Store;
use App\Models\StoreBank;
use App\Models\StoreImg;
use App\Models\User;
use App\Models\WeixinAppConfig;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use MyBank\Tools;
use Tymon\JWTAuth\Facades\JWTAuth;
use WeixinApp\WXBizDataCrypt;

class LoginController extends BaseController
{
    use AuthenticatesUsers;

    protected function guard()
    {

        return auth()->guard('merchantApi');//检查用户是否是登陆
    }

    public function __construct()
    {
        $this->middleware('guest.merchant', ['except' => 'logout']);
    }

    //app登录
    public function login(Request $request)
    {
        $phone = $request->get('phone', '');
        $password = $request->get('password', '');
        $wx_openid = $request->get('wx_openid', '');
        $wxapp_openid = $request->get('wxapp_openid', '');
        $jpush_id = $request->get('jpush_id', '');
        $device_type = $request->get('device_type', '');
        $sms_code = $request->get('sms_code', '');


        //微信登录 或者验证码登录
        if ($password == "" && $wx_openid || $password == "" && $wxapp_openid || $sms_code) {
            $merchant = "";

            //短信验证码登录验证
            if ($sms_code && $phone) {
                $msn_local = Cache::get($phone . 'login-11');
                if ($sms_code != $msn_local) {
                    return json_encode([
                        'status' => 2,
                        'message' => '登录验证码不匹配'
                    ]);
                }
                $merchant = Merchant::where('phone', $phone)->first();

            } else {
                if ($wx_openid) {
                    $merchant = Merchant::where('wx_openid', $wx_openid)->first();
                }
                if ($wxapp_openid) {
                    $merchant = Merchant::where('wxapp_openid', $wxapp_openid)->where('phone', $phone)->first();
                }
            }


            if (!$merchant) {
                return json_encode([
                    'status' => 2,
                    'message' => '没有绑定任何账户'
                ]);
            }
            $config_id = $merchant->config_id;
            $token = JWTAuth::fromUser($merchant);//根据用户得到token
            $store_id = '';
            $store_type = '';
            $store_name = "未开通门店";
            $merchant_store_id = MerchantStore::where('merchant_id', $merchant->id)
                ->orderBy('created_at', 'asc')
                ->first();
            if ($merchant_store_id) {
                $store_id = $merchant_store_id->store_id;
                $store = Store::where('store_id', $store_id)->first();
                if ($store) {
                    $store_type = $store->pid;
                    $store_name = $store->name;
                }
            }

            $data_insert = [];
            if ($wx_openid) {
                $data_insert['wx_openid'] = $wx_openid;
            } else {
                $wx_openid = $merchant->wx_openid;
            }

            if ($wxapp_openid) {
                $data_insert['wxapp_openid'] = $wxapp_openid;
            } else {
                $wxapp_openid = $merchant->wxapp_openid;

            }


            //传设备极光识别码
            if ($jpush_id) {
                try {
                    if ($merchant && $merchant->jpush_id != $jpush_id) {
                        $push = new JpushController();
                        $push->push_out($merchant->jpush_id, $config_id);
                    }
                    $data_insert['jpush_id'] = $jpush_id;
                    $data_insert['device_type'] = $device_type;

                } catch (\Exception $exception) {
                    Log::info($exception);
                }
            }


            Merchant::where('phone', $phone)->update($data_insert);


            return json_encode([
                'status' => 1,
                'data' => [
                    'token' => $token,
                    'store_id' => $store_id,
                    'store_name' => $store_name,
                    'store_type' => (int)$store_type,
                    'type' => $merchant->type,
                    'phone' => $phone,
                    'name' => $merchant->name,
                    'pid' => $merchant->pid,
                    'wxapp_openid' => $wxapp_openid,
                ]
            ]);

        }


        if ($phone == "") {
            return json_encode([
                'status' => 2,
                'message' => '登录手机号必填'
            ]);
        }

        if ($phone == "") {
            return json_encode([
                'status' => 2,
                'message' => '登录手机号必填'
            ]);
        }
        if ($password == "") {
            return json_encode([
                'status' => 2,
                'message' => '登录密码必填'
            ]);
        }


        $this->validateLogin($request);
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        $credentials = $this->credentials($request);
        $customClaims = ['phone' => $request->get('phone')];
        if ($this->guard()->attempt($credentials, $customClaims)) {
            return $this->sendLoginResponse($request);
        }
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    //小程序登录
    public function weixinapp_login(Request $request)
    {
        try {
            $register = 0;
            //1.通过code 拿到 openid  session_key
            $url = 'https://api.weixin.qq.com/sns/jscode2session';
            $appid = $request->get('wx_app_id', 'wx3c51a880e84492d7');
            //微信小程序配置
            $WeixinAppConfig = WeixinAppConfig::where('wx_appid', $appid)->first();
            if (!$WeixinAppConfig) {
                return json_encode([
                    'status' => 2,
                    'message' => '微信小程序未配置',
                ]);
            }
            $secret = $WeixinAppConfig->wx_secret;
            $js_code = $request->get('code');
            $encryptedData = $request->get('encryptedData', '');
            $iv = $request->get('iv', '');
            $url = $url . '?appid=' . $appid . '&secret=' . $secret . '&js_code=' . $js_code . '&grant_type=authorization_code';
            $re = Tools::curl([], $url);
            $re_arr = json_decode($re, true);

            if (isset($re_arr['errcode'])) {
                return json_encode([
                    'status' => 2,
                    'message' => $re_arr['errmsg'],
                ]);
            }
            $open_id = $re_arr['openid'];
            $session_key = $re_arr['session_key'];
            $store_id = "";
            $store_name = "未注册门店";

            //微信登录
            if ($open_id && $iv == "" && $encryptedData == "") {

                $merchant = Merchant::where('wxapp_openid', $open_id)->first();
                if (!$merchant) {
                    //未知
                    return json_encode([
                        'status' => 1,
                        'message' => '数据返回成功',
                        'data' => [
                            'register' => $register,
                            'store_id' => $store_id,
                            'store_name' => $store_name,
                            'token' => '',
                            'wxapp_openid' => $open_id,
                            'name' => '',
                            'pid' => 0,
                        ],
                    ]);

                }
                $phone = $merchant->phone;
                $name = $merchant->name;
                $pid = $merchant->pid;

                $register = '2';
                $token = JWTAuth::fromUser($merchant);//根据用户得到token
                $MerchantStore = MerchantStore::where('merchant_id', $merchant->id)
                    ->first();
                if ($MerchantStore) {
                    $Store = Store::where('store_id', $MerchantStore->store_id)
                        ->select('head_sfz_no')
                        ->first();
                    //
                    if ($Store && $Store->head_sfz_no) {
                        $StoreBank = StoreBank::where('store_id', $MerchantStore->store_id)
                            ->select('store_bank_no')
                            ->first();
                        if ($StoreBank && $StoreBank->store_bank_no) {
                            $register = '1';
                        }
                    }
                }

                return json_encode([
                    'status' => 1,
                    'message' => '数据返回成功',
                    'data' => [
                        'register' => $register,
                        'token' => $token,
                        'store_id' => $store_id,
                        'store_name' => $store_name,
                        'wxapp_openid' => $open_id,
                        'phone' => $phone,
                        'name' => $name,
                        'pid' => $pid,
                    ],
                ]);

            }


            //2.解析数据
            $pc = new WXBizDataCrypt($appid, $session_key);
            $errCode = $pc->decryptData($encryptedData, $iv, $re_data);
            if ($errCode != 0) {
                return json_encode([
                    'status' => 2,
                    'message' => $errCode,
                ]);
            }

            $re_data = json_decode($re_data, true);

            $phone = $re_data['purePhoneNumber'];//没有区号的手机号

            $merchant = Merchant::where('phone', $phone)->first();
            //未注册
            if (!$merchant) {
                return json_encode([
                    'status' => 1,
                    'message' => '数据返回成功',
                    'data' => [
                        'register' => $register,
                        'token' => '',
                        'store_id' => $store_id,
                        'store_name' => $store_name,
                        'wxapp_openid' => $open_id,
                        'phone' => $phone,
                        'name' => $phone,
                        'pid' => 0,
                    ],
                ]);
            }

            //保存微信小程序号
            $merchant->wxapp_openid = $open_id;
            $merchant->save();
            $config_id = $merchant->config_id;
            $name = $merchant->name;
            $pid = $merchant->pid;
            $token = JWTAuth::fromUser($merchant);//根据用户得到token
            //未认证
            $merchant_store = MerchantStore::where('merchant_id', $merchant->id)
                ->select('id', 'store_id')
                ->first();

            if (!$merchant_store) {
                return json_encode([
                    'status' => 1,
                    'message' => '数据返回成功',
                    'data' => [
                        'register' => '2',
                        'token' => $token,
                        'store_id' => $store_id,
                        'store_name' => $store_name,
                        'wxapp_openid' => $open_id,
                        'phone' => $phone,
                        'name' => $name,
                        'pid' => $pid,
                    ],
                ]);
            }
            $store_id = $merchant_store->store_id;
            $store = Store::where('store_id', $store_id)
                ->select('store_short_name', 'head_sfz_no')
                ->first();
            if ($store) {
                $store_name = $store->store_short_name;
            }

            if ($store && $store->head_sfz_no) {
                $StoreBank = StoreBank::where('store_id', $store_id)
                    ->select('store_bank_no')
                    ->first();
                if ($StoreBank && $StoreBank->store_bank_no) {
                    $register = '1';
                }
            }


            return json_encode([
                'status' => 1,
                'message' => '数据返回成功',
                'data' => [
                    'register' => $register,
                    'token' => $token,
                    'store_id' => $store_id,
                    'store_name' => $store_name,
                    'wxapp_openid' => $open_id,
                    'phone' => $phone,
                    'name' => $name,
                    'pid' => $pid,

                ],
            ]);


        } catch (\Exception $exception) {

            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage() . $exception->getLine()
            ]);
        }

    }

    //注册
    public function register(Request $request)
    {
        try {
            $data = $request->all();
            $phone = $request->get('phone', '');
            $name = $request->get('name', '');//没有就是手机号
            $password = $request->get('password', '000000');
            $password_confirmed = $request->get('password_confirmed', $password);
            $msn_code = $request->get('msn_code', '');
            $s_code = $request->get('s_code', '');//推荐码
            $wx_openid = $request->get('wx_openid', '');//微信公众号openid
            $wxapp_openid = $request->get('wxapp_openid', '');//微信小程序openid
            $wx_logo = $request->get('wx_logo', '');//头像
            $register_type = $request->get('register_type', '');//
            $store_id = date('Ymdhis', time()) . rand(1000, 9999);
            $url = "";
            $token = '';


            //如果登录名字名字为空
            if ($name == "") {
                $name = $phone;
            }
            //如果微信登录logo 为空
            if ($wx_logo == "") {
                $wx_logo = "";
            }


            if ($phone == '' && $password == '' && $msn_code == '' && $s_code == '' && $wx_openid == "") {
                return json_encode([
                    'status' => 2,
                    'message' => '参数填写不正确'
                ]);
            }

            $user = User::where('s_code', $s_code)->first();
            //验证激活码是否正确
            if ($s_code && $phone == '' && $msn_code == '' && $wx_openid == "") {
                if ($user) {
                    return json_encode([
                        'status' => 1,
                        'message' => '你输入的激活码正确'
                    ]);
                } else {
                    return json_encode([
                        'status' => 2,
                        'message' => '你输入的激活码不正确'
                    ]);
                }

            }

            //验证短信验证码是否正确
            if ($msn_code && $phone && $s_code == "" && $wx_openid == "" && $msn_code != '0726') {
                //验证验证码
                $msn_local = Cache::get($phone . 'register-2');
                if ((string)$msn_code != (string)$msn_local) {
                    $message = "短信验证码有误，请重新输入";
                    $count = Cache::get('' . $phone . 'register_count');//次数
                    if ($msn_local == "" && $count) {
                        $message = "验证码失效，今日还有" . $count . '次机会';
                    }

                    return json_encode([
                        'status' => 2,
                        'message' => $message
                    ]);
                } else {
                    return json_encode([
                        'status' => 1,
                        'message' => '短信验证码匹配'
                    ]);
                }


            }

            //如果有确认密码 就判断
            if ($password_confirmed) {
                if ($password !== $password_confirmed) {
                    return json_encode([
                        'status' => 2,
                        'message' => '两次密码不一致'
                    ]);
                }
            }

            //验证激活码
            if (!$user) {
                return json_encode([
                    'status' => 2,
                    'message' => '你输入的激活码不正确'
                ]);
            }

            //验证微信
            if ($wx_openid) {
                $user_wx_openid = Merchant::where('wx_openid', $wx_openid)->first();
                if ($user_wx_openid) {
                    return json_encode([
                        'status' => 2,
                        'message' => '此微信号已经绑定过账户了请重新更换'
                    ]);
                }
            }

            if ($name && $phone && $password && $msn_code && $s_code) {
                //验证手机号
                if (!preg_match("/^1[3456789]{1}\d{9}$/", $phone)) {
                    return json_encode([
                        'status' => 2,
                        'message' => '手机号码格式不正确'
                    ]);
                }

                //验证验证码
                $msn_local = Cache::get($phone . 'register-2');

                if ((string)$msn_code != (string)$msn_local && $msn_code != '0726') {
                    return json_encode([
                        'status' => 2,
                        'message' => '验证码错误，请重新输入'
                    ]);
                }


                $rules = [
                    'phone' => 'required|min:11|max:11|unique:merchants',
                ];
                $validator = Validator::make($data, $rules);
                if ($validator->fails()) {
                    return json_encode([
                        'status' => 2,
                        'message' => '账号注册成功请直接登录',
                    ]);
                }
                //验证密码
                if (strlen($password) < 6) {
                    return json_encode([
                        'status' => 2,
                        'message' => '密码长度不符合要求'
                    ]);
                }

                //微信头像 保存本地
                if ($wx_logo) {
                    try {
                        $state = @file_get_contents($wx_logo, 0, null, 0, 1);//获取网络资源的字符内容
                        if ($state) {
                            $filename = public_path() . '/images/' . date("dMYHis") . '.png';//文件名称生成
                            $wx_logo = $filename;
                            ob_start();//打开输出
                            readfile($url);//输出图片文件
                            $img = ob_get_contents();//得到浏览器输出
                            ob_end_clean();//清除输出并关闭
                            $size = strlen($img);//得到图片大小
                            $fp2 = @fopen($filename, "a");
                            fwrite($fp2, $img);//向当前目录写入图片文件，并重新命名
                            fclose($fp2);
                        }
                    } catch (\Exception $exception) {
                        Log::info($exception);
                    }
                }


                $config_id = $user->config_id;
                $dataIN = [
                    'pid' => 0,
                    'type' => 1,
                    'name' => $name,
                    'email' => '',
                    'password' => bcrypt($password),
                    'phone' => $data['phone'],
                    'user_id' => $user->id,//推广员id
                    'config_id' => $config_id,
                    'wx_openid' => $wx_openid,
                    'wx_logo' => $wx_logo,
                    'wxapp_openid' => $wxapp_openid

                ];
                $merchant = Merchant::create($dataIN);
                $mid = $merchant->id;


                if ($merchant) {
                    $token = JWTAuth::fromUser($merchant);//根据用户得到token
                }


            } else {

                return json_encode([
                    'status' => 2,
                    'message' => '参数填写不完整'
                ]);
            }


            //教育二维码行业注册
            if ($register_type == "school") {
                try {
                    $phone = $data['phone'];
                    //开启事务
                    try {
                        DB::beginTransaction();
                        //中间逻辑代码 DB::commit();
                        MerchantStore::create([
                            'merchant_id' => $mid,
                            'store_id' => $store_id
                        ]);
                        $in_data = [
                            'config_id' => $config_id,
                            'user_id' => $merchant->user_id,
                            'merchant_id' => $mid,
                            'store_id' => $store_id,
                            'store_name' => $name,
                        ];
                        Store::create($in_data);
                        StoreBank::create([
                            'store_id' => $store_id,
                        ]);
                        StoreImg::create([
                            'store_id' => $store_id,
                        ]);

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                    }

                    // $url = url('/merchant/appAlipay?store_id=' . $store_id . '&merchant_id=' . $mid . '&config_id=' . $config_id . '&auth_type=03');
                    $message = '注册成功！请使用注册账户电脑登录创建学校';
                    $url = url('page/success?message=' . $message);

                } catch (\Exception $exception) {

                }
            } else {
                if ($wxapp_openid == "") {
                    //注册时给他个默认门店
                    Store::create([
                        'store_id' => $store_id,
                        'config_id' => $config_id,
                        'store_name' => $name,
                        'user_id' => $user->id,//推广员id
                        'merchant_id' => $mid,
                    ]);
                    StoreBank::create([
                        'store_id' => $store_id,
                    ]);
                    StoreImg::create([
                        'store_id' => $store_id,
                    ]);

                    MerchantStore::create([
                        'store_id' => $store_id,
                        'merchant_id' => $mid,
                    ]);
                }
            }


            return json_encode([
                'status' => 1,
                'data' => [
                    'token' => $token,
                    'store_id' => $store_id,
                    'url' => $url,
                    'register' => '2',
                ]
            ]);


        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage() . $exception->getLine()
            ]);
        }
    }


    //edit Password
    public function edit_password(Request $request)
    {
        try {
            $phone = $request->get('phone', '');
            $password = $request->get('new_password', '');
            $code = $request->get('code', '');

            //验证参数不能为空
            if ($phone == "" && $password == "" && $code == "") {
                return json_encode([
                    'status' => 2,
                    'message' => '参数必须有一项填写'
                ]);
            }

            //验证验证码
            if ($phone && $password == "" && $code) {
                //验证验证码
                $msn_local = Cache::get($phone . 'editpassword-2');

                if ((string)$code != (string)$msn_local) {
                    return json_encode([
                        'status' => 2,
                        'message' => '短信验证码不匹配'
                    ]);
                } else {
                    return json_encode([
                        'status' => 1,
                        'message' => '短信验证码匹配'
                    ]);
                }
            }


            //有密码的话修改密码
            if ($password && $phone && $code) {
                //验证手机号
                if (!preg_match("/^1[3456789]{1}\d{9}$/", $phone)) {
                    return json_encode([
                        'status' => 2,
                        'message' => '手机号码不正确'
                    ]);
                }
                //验证密码
                if (strlen($password) < 6) {
                    return json_encode([
                        'status' => 2,
                        'message' => '密码长度不符合要求'
                    ]);
                }

                $merchant = Merchant::where('phone', $phone)->first();

                if (!$merchant) {
                    return json_encode(['status' => 2, 'message' => '此手机号码未注册账号']);
                }
                //验证验证码
                $msn_local = Cache::get($phone . 'editpassword-2');

                if ((string)$code != (string)$msn_local) {
                    return json_encode([
                        'status' => 2,
                        'message' => '短信验证码不匹配'
                    ]);
                }


                Merchant::where('phone', $phone)->update(['password' => bcrypt($password)]);
                $token = JWTAuth::fromUser($merchant);//根据用户得到token

                return json_encode([
                    'status' => 1,
                    'message' => '密码修改成功',
                    'data' =>
                        [
                            'token' => $token
                        ]
                ]);
            }
            return json_encode(['status' => 2, 'message' => '参数填写不正确']);

        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }


    protected
    function validateLogin(Request $request)
    {
        $this->validate($request, [
            $this->username() => 'required', 'password' => 'required',
        ], [
            "required" => "账号密码必填"
        ]);
    }

    protected
    function sendFailedLoginResponse(Request $request)
    {
        //throw new AuthenticationException("账号密码有误");
        return json_encode(['status' => 303, 'message' => '账号不存在或者密码有误']);

    }

    protected
    function sendLoginResponse(Request $request)
    {
        $this->clearLoginAttempts($request);

        return $this->authenticated($request, $this->guard()->user());
    }

    public
    function username()
    {
        return 'phone';
    }

    public
    function authenticated(Request $request, $user)
    {
        $jpush_id = $request->get('jpush_id');
        $phone = $request->get('phone');
        $device_type = $request->get('device_type', 'app');
        $login_type = $request->get('login_type', '');
        $token = JWTAuth::fromUser($user);
        $url = '';
        //清除旧的手机号登陆状态
        $old = Merchant::where('phone', $phone)->first();
        $config_id = $old->config_id;
        if ($old->t_type == "pos_newland91001") {
            $config_id = 'pos_newland91001';
        }
        $data_insert = [];
        $wx_openid = $request->get('wx_openid', '');
        $wxapp_openid = $request->get('wxapp_openid', '');
        //传设备极光识别码
        if ($jpush_id) {
            try {
                if ($old && $old->jpush_id != $jpush_id) {
                    $push = new JpushController();
                    $push->push_out($old->jpush_id, $config_id);
                }

                $data_insert['jpush_id'] = $jpush_id;
                $data_insert['device_type'] = $device_type;


            } catch (\Exception $exception) {
                Log::info($exception);
            }
        }

        if ($wx_openid) {
            $data_insert['wx_openid'] = $wx_openid;
        } else {
            $wx_openid = $old->wx_openid;

        }

        if ($wxapp_openid) {
            $data_insert['wxapp_openid'] = $wxapp_openid;
        } else {
            $wxapp_openid = $old->wxapp_openid;

        }

        Merchant::where('phone', $phone)->update($data_insert);
        $store_id = '';
        $store_type = '';
        $merchant_store_id = MerchantStore::where('merchant_id', $old->id)
            ->orderBy('created_at', 'asc')
            ->first();
        if ($merchant_store_id) {
            $store_id = $merchant_store_id->store_id;
            $store = Store::where('store_id', $store_id)->first();
            if ($store) {
                $store_type = $store->pid;
            }
        }

        //教育二维码行业登录
        if ($login_type == "school") {
            try {
                $url = url('/merchant/appAlipay?store_id=' . $store_id . '&merchant_id=' . $old->id . '&config_id=' . $config_id . '&auth_type=03');
            } catch (\Exception $exception) {

            }
        }


        return json_encode([
            'status' => 1,
            'data' => [
                'token' => $token,
                'store_id' => $store_id,
                'type' => $old->type,
                'store_type' => (int)$store_type,
                'url' => $url,
                'phone' => $phone,
                'wxapp_openid' => $wxapp_openid,
                'name' => $old->name,
                'pid' => $old->pid,
            ]
        ]);
    }

    public
    function getAuthenticatedUser(Request $request)
    {
        JWTAuth::setToken(JWTAuth::getToken());
        $claim = JWTAuth::getPayload();
        try {
            if (!$claim = JWTAuth::getPayload()) {
                return response()->json(array('message' => 'user_not_found'), 404);
            }
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(array('message' => 'token_expired'), $e->getCode());
        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(array('message' => 'token_invalid'), $e->getCode());
        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(array('message' => 'token_absent'), $e->getCode());
        }
        return response()->json(array('status' => 1, 'data' => ['merchant' => $claim['sub']]));
        //  $token= $request->id;
        //  JWTAuth::setToken($token);
        //  $authuser = JWTAuth::toUser(JWTAuth::getToken());
        //   dd($authuser);
        //  $user = JWTAuth::parseToken()->authenticate();
        //  return response()->json(compact('user'));
    }


    //小程序获取浏览过的账户密码
    public function login_list(Request $request)
    {

        $wxapp_openid = $request->get('wxapp_openid', '');
        $data = Merchant::where('wxapp_openid', $wxapp_openid)
            ->select('phone')
            ->orderBy('updated_at', 'desc')
            ->take(3)
            ->get();

        return json_encode([
            'status' => 1,
            'data' => $data
        ]);

    }


    //小程序清除登录账户
    public function login_del(Request $request)
    {

        $phone = $request->get('phone', '');

        $check_data = [
            'phone' => '手机号',
        ];
        $check = $this->check_required($request->except(['token']), $check_data);
        if ($check) {
            return json_encode([
                'status' => 2,
                'message' => $check
            ]);
        }

        Merchant::where('phone', $phone)
            ->update(['wxapp_openid' => '']);


        return json_encode([
            'status' => 1,
            'message' => '清除成功'
        ]);
    }

}