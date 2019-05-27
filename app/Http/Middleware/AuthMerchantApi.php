<?php
/**
 * Created by PhpStorm.
 * User: dmk
 * Date: 2017/2/26
 * Time: 18:33
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthMerchantApi
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @param  string|null $guard
     * @return mixed
     */
    public function handle($request, Closure $next, $guard = null)
    {
        try {
            $token = $request->get('token');
            if (!$token) {
                return response()->json(['message' => 'Token参数必须填写', 'status' => 2]);
            }

            $token = JWTAuth::setToken(JWTAuth::getToken());

            $claim = JWTAuth::getPayload();
            if ($claim['sub']['type'] != "merchant") {
                return response()->json(['message' => '此账号不是商户账户无法登录', 'status' => -1]);
            }


        } catch (JWTException $e) {
            if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
                return response()->json(['message' => 'Token参数无效', 'status' => 2]);
            } else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
                return response()->json(['message' => 'Token 过期', 'status' => -501]);
            } else {
                return response()->json(['message' => '系统出错了', 'status' => -1]);
            }
        }

        return $next($request);

    }

}