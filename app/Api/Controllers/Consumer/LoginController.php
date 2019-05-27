<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/5/25
 * Time: 下午2:21
 */

namespace App\Api\Controllers\Consumer;


use App\Api\Controllers\ApiController;
use App\Api\Controllers\BaseController;
use App\Api\Controllers\Push\JpushController;
use App\Models\Consumer;
use App\Models\Merchant;
use App\Models\MerchantStore;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class LoginController extends BaseController
{
    use AuthenticatesUsers;

    protected function guard()
    {

        return auth()->guard('consumerApi');//检查用户是否是登陆
    }

    //登录
    public function login(Request $request)
    {
        $phone = $request->get('phone', '');
        $code = $request->get('code', '');

        if ($phone == "") {
            return json_encode([
                'status' => 2,
                'message' => '登录手机号必填'
            ]);
        }

        if ($code == "") {
            return json_encode([
                'status' => 2,
                'message' => '验证码必填'
            ]);
        }

     /*   //验证验证码
        $msn_local = Cache::get($phone . 'register-3');
        if ((string)$code != (string)$msn_local) {
            return json_encode([
                'status' => 2,
                'message' => '短信验证码不匹配'
            ]);
        }*/


        $Consumer = Consumer::where('phone', $phone)->first();

        if ($Consumer) {
            $token = JWTAuth::fromUser($Consumer);//根据用户得到token

        } else {

            $dataIN = [
                'name' => $phone,
                'email' => $phone . '@11.com',
                'password' => bcrypt($phone . $code),
                'phone' => $phone,
            ];
            $Consumer = Consumer::create($dataIN);
            if ($Consumer) {
                $token = JWTAuth::fromUser($Consumer);//根据用户得到token
            } else {
                $token = '';
            }
        }

        return json_encode([
            'status' => 1,
            'data' => [
                'token' => $token
            ]
        ]);
    }

    public
    function username()
    {
        return 'phone';
    }

}