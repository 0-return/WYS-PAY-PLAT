<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/5/25
 * Time: 下午2:21
 */

namespace App\Api\Controllers\User;


use Alipayopen\Sdk\Request\AlipayTradePayRequest;
use App\Api\Controllers\ApiController;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;

class LoginController extends ApiController
{


    public $successStatus = 200;


    public function login(Request $request)
    {
        //表名 小写

        // 这里的登陆验证是email和password
        $credentials = $request->only('phone', 'password');
        $wx_openid = $request->get('wx_openid', '');
        $permission = $request->get('permission', 0);
        //微信登录
        if ($wx_openid) {
            $user = User::where('wx_openid', $wx_openid)->first();
            if (!$user) {
                return json_encode([
                    'status' => 2,
                    'message' => '你的微信号没有绑定账户'
                ]);
            }

            if ($user->is_delete) {
                return json_encode([
                    'status' => 2,
                    'message' => '账户已经删除'
                ]);
            }
            $token = JWTAuth::fromUser($user);//根据用户得到token
            $return_data = [
                'status' => 1,
                'message' => '登录成功',
                'data' => [
                    'token' => $token,
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'name' => $user->name,
                    'phone' => $user->phone,
                    'pid' => $user->pid,
                    'level' => $user->level,
                    's_code' => $user->s_code,
                    'config_id' => $user->config_id,
                ]
            ];

            //返回权限集合
            if ($permission) {
                $permissions = $user->getAllPermissions();
                $data = [];
                foreach ($permissions as $k => $v) {
                    $data[$k]['name'] = $v->name;
                }
                $return_data['permissions'] = $data;
            }

            return json_encode($return_data);

        }

        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['status' => 2, 'message' => '账号密码不正确！']);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['status' => -1, 'message' => $e->getMessage()]);

        }
        $user = User::where('phone', $request->get('phone'))->first();
        if ($user->is_delete) {
            return json_encode([
                'status' => 2,
                'message' => '账户暂时无法登录',
            ]);
        }

        $return_data = [
            'status' => 1,
            'message' => '登录成功',
            'data' => [
                'token' => $token,
                'user_id' => $user->id,
                'user_name' => $user->name,
                'name' => $user->name,
                'phone' => $user->phone,
                'pid' => $user->pid,
                'level' => $user->level,
                's_code' => $user->s_code,
                'config_id' => $user->config_id,
            ]
        ];

        //返回权限集合
        if ($permission) {
            $permissions = $user->getAllPermissions();
            $data = [];
            foreach ($permissions as $k => $v) {
                $data[$k]['name'] = $v->name;
            }
            $return_data['permissions'] = $data;
        }
        return json_encode($return_data);
    }

    //注册用户接口 返回token
    public function register(Request $request)
    {
        $newUser = [
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'phone' => $request->get('phone'),
            'password' => bcrypt($request->get('password'))
        ];

        $user = User::create($newUser);
        $token = JWTAuth::fromUser($user);//根据用户得到token
        return response()->json(compact('token'));
        // $token = JWTAuth::fromUser($user);//根据用户得到token


    }

    public function username()
    {
        return 'phone';
    }

    public function getAuthenticatedUser()
    {
        try {

            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());
        }
        // the token is valid and we have found the user via the sub claim

        return response()->json(compact('user'));
    }

    //PC端忘记密码
    public function edit_password1(Request $request)
    {

        try {
            $phone = $request->get('phone');
            $password = $request->get('newpassword');
            $code = $request->get('code');
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

                $users = User::where('phone', $phone)->first();

                if (!$users) {
                    return json_encode(['status' => 2, 'message' => '此手机号码未注册账号']);
                }
                //验证验证码
                $msn_local = Cache::get($phone . 'editpassword-1');

                if ((string)$code != (string)$msn_local) {
                    return json_encode([
                        'status' => 2,
                        'message' => '短信验证码不匹配'
                    ]);
                }

                User::where('phone', $phone)->update(['password' => bcrypt($password)]);

                return json_encode(['status' => 1, 'message' => '密码修改成功']);
            }
            return json_encode(['status' => 2, 'message' => '参数不正确']);

        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }
    }

    //edit Password
    public function edit_password(Request $request)
    {
        try {

            $phone = $request->get('phone', '');
            $password = $request->get('newpassword', '');
            $password = $request->get('new_password', '' . $password . '');
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
                $msn_local = Cache::get($phone . 'editpassword-1');

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

                $merchant = User::where('phone', $phone)->first();

                if (!$merchant) {
                    return json_encode(['status' => 2, 'message' => '此手机号码未注册账号']);
                }
                //验证验证码
                $msn_local = Cache::get($phone . 'editpassword-1');

                if ((string)$code != (string)$msn_local) {
                    return json_encode([
                        'status' => 2,
                        'message' => '短信验证码不匹配'
                    ]);
                }


                User::where('phone', $phone)->update(['password' => bcrypt($password)]);
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


}