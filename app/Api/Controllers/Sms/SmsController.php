<?php
/**
 * Created by PhpStorm.
 * User: dmk
 * Date: 2017/4/6
 * Time: 15:07
 */

namespace App\Api\Controllers\Sms;


use Aliyun\AliSms;
use App\Api\Rsa\RsaE;
use App\Models\AppConfigMsg;
use App\Models\Merchant;
use App\Models\MerchantStore;
use App\Models\MyBankStore;
use App\Models\SmsConfig;
use App\Models\Store;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\IFTTTHandler;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class SmsController extends BaseController
{
    //api发送
    public function send(Request $request)
    {
        try {
            $token = $request->get('token');
            $phone = "";
            $config_id = '1234';
            $info = '';
            $type = '';
            if ($token) {
                JWTAuth::setToken(JWTAuth::getToken());
                $claim = JWTAuth::getPayload();
                $config_id = $claim['sub']['config_id'];
                $output = [
                    'phone' => $request->get('phone'),
                    'type' => $request->get('type'),
                    'info' => $request->get('info'),
                    'store_id' => $request->get('store_id'),
                    'config_id' => $config_id,
                ];
                $phone = $request->get('phone');
                $type = $request->get('type');
                $info = $request->get('info');
            }
            $rsa = new RsaE();
            $sign = $request->get('sign');
            if ($sign) {
                $data = $rsa->privDecrypt($sign);//解密
                parse_str($data, $output);
                $phone = isset($output['phone']) ? $output['phone'] : $phone;
                $type = isset($output['type']) ? $output['type'] : $type;
                $info = isset($output['info']) ? $output['info'] : $type;
                $info = (string)$info;
                //软件app_id;
                if (isset($output['app_id'])) {
                    $apps = AppConfigMsg::where('app_id', $output['app_id'])->select('config_id')->first();
                    if ($apps) {
                        $config_id = $apps->config_id;
                    }
                }
            }

            Log::info($output);

            $code = rand(1000, 9999);
            //验证手机号
            if (!preg_match("/^1[3456789]{1}\d{9}$/", $phone)) {
                return json_encode([
                    'status' => 2,
                    'message' => '手机号码不正确'
                ]);
            }
            //商户注册
            if ($type == 'register' && $info == '2') {
                $m = Merchant::where('phone', $phone)->first();
                if ($m) {
                    return json_encode([
                        'message' => '此号码已经注册过了',
                        'status' => 2
                    ]);
                }
                $config = SmsConfig::where('type', '3')->where('config_id', $config_id)->first();
                if (!$config) {
                    $config = SmsConfig::where('type', '3')->where('config_id', '1234')->first();
                }
                if ($config) {
                    $data = ["code" => $code];

                    //次数
                    $register_count = Cache::get($phone . 'register_count');
                    if ($register_count) {
                        $count = $register_count - 1;
                    } else {
                        $count = 4;
                    }
//
//                    if ($count == 0) {
//                        return json_encode([
//                            'message' => '今天短信次数已经用完,请明天再试',
//                            'status' => 0
//                        ]);
//                    }


                    $return = $this->sendSms($phone, $config->app_key, $config->app_secret, $config->SignName, $config->TemplateCode, $data);
                    try {
                        if ($return->Code == 'OK') {
                            Cache::put('' . $phone . 'register-2', $code, 1);

                            Cache::put('' . $phone . 'register_count', $count, 720);//5次


                            return json_encode([
                                'message' => '发送成功',
                                'status' => 1
                            ]);
                        } else {
                            return json_encode([
                                'message' => $return->Message,
                                'status' => 2
                            ]);
                        }
                    } catch (\Exception $exception) {
                        return json_encode([
                            'message' => $return->sub_msg,
                            'status' => 2
                        ]);
                    }
                } else {
                    throw new BadRequestHttpException('配置信息不存在');
                }
            }

            //商户验证码登录
            if ($type == 'login' && $info == '2') {
                $m = Merchant::where('phone', $phone)
                    ->first();
                if (!$m) {
                    return json_encode([
                        'message' => '此号码未注册账户',
                        'status' => 2
                    ]);
                }
                $config = SmsConfig::where('type', '11')->where('config_id', $config_id)->first();
                if (!$config) {
                    $config = SmsConfig::where('type', '11')->where('config_id', '1234')->first();
                }
                if ($config) {
                    $data = ["code" => $code];

                    $return = $this->sendSms($phone, $config->app_key, $config->app_secret, $config->SignName, $config->TemplateCode, $data);
                    try {
                        if ($return->Code == 'OK') {
                            Cache::put('' . $phone . 'login-11', $code, 1);
                            return json_encode([
                                'message' => '发送成功',
                                'status' => 1
                            ]);
                        } else {
                            return json_encode([
                                'message' => $return->Message,
                                'status' => 2
                            ]);
                        }
                    } catch (\Exception $exception) {
                        return json_encode([
                            'message' => $return->sub_msg,
                            'status' => 2
                        ]);
                    }
                } else {
                    throw new BadRequestHttpException('配置信息不存在');
                }
            }

            //个人绑定修改支付宝账户
            if ($type == 'account') {

                $config = SmsConfig::where('type', '4')->where('config_id', $config_id)->first();
                if (!$config) {
                    $config = SmsConfig::where('type', '4')->where('config_id', '1234')->first();
                }
                if ($config) {
                    $data = ["code" => $code];
                    $return = $this->sendSms($phone, $config->app_key, $config->app_secret, $config->SignName, $config->TemplateCode, $data);
                    try {
                        if ($return->Code == 'OK') {
                            Cache::put('' . $phone . 'account', $code, 100);
                            return json_encode([
                                'message' => '发送成功',
                                'status' => 1
                            ]);
                        } else {
                            return json_encode([
                                'message' => $return->Message,
                                'status' => 2
                            ]);
                        }
                    } catch (\Exception $exception) {
                        return json_encode([
                            'message' => $return->sub_msg,
                            'status' => 2
                        ]);
                    }
                } else {
                    throw new BadRequestHttpException('配置信息不存在');
                }
            }


            //商户业务员忘记密码
            if ($type == 'editpassword' && $info == '2') {

                $m = Merchant::where('phone', $phone)->first();
                if (!$m) {
                    return json_encode([
                        'message' => '此号码未注册账号',
                        'status' => 2
                    ]);
                }

                $config = SmsConfig::where('type', '2')->where('config_id', $config_id)->first();
                if (!$config) {
                    $config = SmsConfig::where('type', '2')->where('config_id', '1234')->first();
                }
                if ($config) {
                    $data = ["code" => $code];
                    $return = $this->sendSms($phone, $config->app_key, $config->app_secret, $config->SignName, $config->TemplateCode, $data);
                    try {
                        if ($return->Code == 'OK') {
                            Cache::put('' . $phone . 'editpassword-2', $code, 1);
                            return json_encode([
                                'message' => '发送成功',
                                'status' => 1
                            ]);
                        } else {
                            return json_encode([
                                'message' => $return->Message,
                                'status' => 2
                            ]);
                        }
                    } catch (\Exception $exception) {
                        return json_encode([
                            'message' => $return->sub_msg,
                            'status' => 2
                        ]);
                    }
                } else {
                    throw new BadRequestHttpException('配置信息不存在');
                }
            }
            //商户业务员更换手机号码
            if ($type == 'editphone' && $info == '2') {
                $config = SmsConfig::where('type', '4')->where('config_id', $config_id)->first();
                if (!$config) {
                    $config = SmsConfig::where('type', '4')->where('config_id', '1234')->first();
                }
                if ($config) {
                    $data = ["code" => $code];
                    $return = $this->sendSms($phone, $config->app_key, $config->app_secret, $config->SignName, $config->TemplateCode, $data);
                    try {
                        if ($return->Code == 'OK') {
                            Cache::put('' . $phone . 'editphone-2', $code, 1);
                            return json_encode([
                                'message' => '发送成功',
                                'status' => 1
                            ]);
                        } else {
                            return json_encode([
                                'message' => $return->Message,
                                'status' => 2
                            ]);
                        }
                    } catch (\Exception $exception) {
                        return json_encode([
                            'message' => $return->sub_msg,
                            'status' => 2
                        ]);
                    }
                } else {
                    throw new BadRequestHttpException('配置信息不存在');
                }
            }
            //商户业务员解绑支付宝
            if ($type == 'del' && $info == '2') {
                $config = SmsConfig::where('type', '4')->where('config_id', $config_id)->first();
                if (!$config) {
                    $config = SmsConfig::where('type', '4')->where('config_id', '1234')->first();
                }
                if ($config) {
                    $data = ["code" => $code];
                    $return = $this->sendSms($phone, $config->app_key, $config->app_secret, $config->SignName, $config->TemplateCode, $data);
                    try {
                        if ($return->Code == 'OK') {
                            Cache::put('' . $phone . 'del-2', $code, 1);
                            return json_encode([
                                'message' => '发送成功',
                                'status' => 1
                            ]);
                        } else {
                            return json_encode([
                                'message' => $return->Message,
                                'status' => 2
                            ]);
                        }
                    } catch (\Exception $exception) {
                        return json_encode([
                            'message' => $return->sub_msg,
                            'status' => 2
                        ]);
                    }
                } else {
                    throw new BadRequestHttpException('配置信息不存在');
                }
            }
            //代理商忘记密码
            if ($type == 'editpassword' && $info == '1') {
                $m = User::where('phone', $phone)->first();
                if (!$m) {
                    return json_encode([
                        'message' => '此号码未注册账号',
                        'status' => 2
                    ]);
                }

                $config = SmsConfig::where('type', '2')->where('config_id', $config_id)->first();
                if (!$config) {
                    $config = SmsConfig::where('type', '2')->where('config_id', '1234')->first();
                }
                if ($config) {
                    $data = ["code" => $code];
                    $return = $this->sendSms($phone, $config->app_key, $config->app_secret, $config->SignName, $config->TemplateCode, $data);
                    try {
                        if ($return->Code == 'OK') {
                            Cache::put('' . $phone . 'editpassword-1', $code, 1);
                            return json_encode([
                                'message' => '发送成功',
                                'status' => 1
                            ]);
                        } else {
                            return json_encode([
                                'message' => $return->Message,
                                'status' => 2
                            ]);
                        }
                    } catch (\Exception $exception) {
                        return json_encode([
                            'message' => $return->sub_msg,
                            'status' => 2
                        ]);
                    }
                } else {
                    throw new BadRequestHttpException('配置信息不存在');
                }
            }
            //代理商更换手机号码
            if ($type == 'editphone' && $info == '1') {
                $config = SmsConfig::where('type', '4')->where('config_id', $config_id)->first();
                if (!$config) {
                    $config = SmsConfig::where('type', '4')->where('config_id', '1234')->first();
                }
                if ($config) {
                    $data = ["code" => $code];
                    $return = $this->sendSms($phone, $config->app_key, $config->app_secret, $config->SignName, $config->TemplateCode, $data);
                    try {
                        if ($return->Code == 'OK') {
                            Cache::put('' . $phone . 'editphone-1', $code, 1);
                            return json_encode([
                                'message' => '发送成功',
                                'status' => 1
                            ]);
                        } else {
                            return json_encode([
                                'message' => $return->Message,
                                'status' => 2
                            ]);
                        }
                    } catch (\Exception $exception) {
                        return json_encode([
                            'message' => $return->sub_msg,
                            'status' => 2
                        ]);
                    }
                } else {
                    throw new BadRequestHttpException('配置信息不存在');
                }
            }
            //代理商解绑支付宝
            if ($type == 'del' && $info == '1') {
                $config = SmsConfig::where('type', '4')->where('config_id', $config_id)->first();
                if (!$config) {
                    $config = SmsConfig::where('type', '4')->where('config_id', '1234')->first();

                }
                if ($config) {
                    $data = ["code" => $code];
                    $return = $this->sendSms($phone, $config->app_key, $config->app_secret, $config->SignName, $config->TemplateCode, $data);
                    try {
                        if ($return->Code == 'OK') {
                            Cache::put('' . $phone . 'del-1', $code, 1);
                            return json_encode([
                                'message' => '发送成功',
                                'status' => 1
                            ]);
                        } else {
                            return json_encode([
                                'message' => $return->Message,
                                'status' => 2
                            ]);
                        }
                    } catch (\Exception $exception) {
                        return json_encode([
                            'message' => $return->sub_msg,
                            'status' => 2
                        ]);
                    }
                } else {
                    throw new BadRequestHttpException('配置信息不存在');
                }
            }


            //申请网商银行通道
            if ($type == '3001' || $type == '3002') {
                $SettleModeType = '01'; //结算方式 01 他行卡 02 余利宝
                if ($SettleModeType) {
                    $BizType = '04';//
                } else {
                    $BizType = '01';//

                }
                if ($token) {
                    JWTAuth::setToken(JWTAuth::getToken());
                    $claim = JWTAuth::getPayload();
                    if ($claim['sub']['type'] == "merchant") {
                        $merchant_id = $claim['sub']['merchant_id'];
                        $MerchantStore = MerchantStore::where('merchant_id', $merchant_id)
                            ->orderBy('created_at', 'asc')
                            ->first();
                        $store_id = $MerchantStore->store_id;
                    } else {
                        $store_id = $output['store_id'];;
                    }
                    //店铺都是取缓存
                    if (Cache::has($store_id)) {
                        $store = Cache::get($store_id);
                    } else {
                        $store = Store::where('store_id', $store_id)->first();
                        Cache::put($store_id, $store, 1);
                    }
                }
                $aop = new \App\Api\Controllers\MyBank\BaseController();
                $ao = $aop->aop();
                $ao->url = env("MY_BANK_request2");
                $ao->Function = "ant.mybank.merchantprod.sendsmscode";
                $data = [
                    'BizType' => $BizType,
                    'Mobile' => $phone,
                    'OutTradeNo' => date('YmdHis') . time() . rand(10000, 99999),
                ];

                // dd($data);
                $re = $ao->Request($data);

                if ($re['status'] == 2) {
                    return json_encode($re);
                }
                $body = $re['data']['document']['response']['body'];

                if ($body['RespInfo']['ResultStatus'] == "S") {
                    return json_encode([
                        'message' => '发送成功',
                        'status' => 1
                    ]);
                } else {
                    return json_encode([
                        'message' => $body['RespInfo']['ResultMsg'],
                        'status' => 2
                    ]);
                }
            }

            //
            //添加绑定银行卡和换绑定银行卡
            if ($type == '01' || $type == '02' || $type == '03' || $type == '04' || $type == '05') {
                if ($token) {
                    Log::info($output);
                    JWTAuth::setToken(JWTAuth::getToken());
                    $claim = JWTAuth::getPayload();
                    if (isset($output['store_id'])) {
                        $store_id = $output['store_id'];
                    } else {
                        $store_id = "1";
                    }
                    if ($store_id == "") {
                        $MerchantStore = MerchantStore::where('merchant_id', $claim['sub']['merchant_id'])
                            ->orderBy('created_at', 'asc')
                            ->first();
                        $store_id = $MerchantStore->store_id;
                    }


                    $MyBankStore = MyBankStore::where('OutMerchantId', $store_id)->first();
                    //店铺都是取缓存
                    //  dd($MyBankStore);
                    //如果存在网商银行的通道
                    //结算到余利宝的情况下更改需要发短信
                    if ($MyBankStore && $MyBankStore->SettleMode == '02') {
                        $data['MerchantId'] = $MyBankStore->MerchantId;
                        $aop = new \App\Api\Controllers\MyBank\BaseController();
                        $ao = $aop->aop();
                        $ao->url = env("MY_BANK_request2");
                        $ao->Function = "ant.mybank.merchantprod.sendsmscode";
                        $data = [
                            'BizType' => $type,
                            'OutTradeNo' => date('YmdHis') . time() . rand(10000, 99999),
                        ];

                        if ($type == '01' || $type == '03' || $type == '04') {
                            $data['Mobile'] = $phone;
                        }
                        $re = $ao->Request($data);
                        if ($re['status'] == 0) {
                            return json_encode($re);
                        }
                        $body = $re['data']['document']['response']['body'];

                        if ($body['RespInfo']['ResultStatus'] == "S") {
                            return json_encode([
                                'message' => '发送成功',
                                'status' => 1
                            ]);
                        } else {
                            return json_encode([
                                'message' => $body['RespInfo']['ResultMsg'],
                                'status' => 2
                            ]);
                        }

                    }


                    //还没有通过网商银行审核直接发内部消息
                    $config = SmsConfig::where('type', '4')->where('config_id', $config_id)->first();
                    if (!$config) {
                        $config = SmsConfig::where('type', '4')->where('config_id', '1234')->first();
                    }
                    if ($config) {
                        $data = ["code" => $code];
                        $return = $this->sendSms($phone, $config->app_key, $config->app_secret, $config->SignName, $config->TemplateCode, $data);
                        try {
                            if ($return->Code == 'OK') {
                                Cache::put('' . $phone . '02-1', $code, 1);
                                return json_encode([
                                    'message' => '发送成功',
                                    'status' => 1
                                ]);
                            } else {
                                return json_encode([
                                    'message' => $return->Message,
                                    'status' => 2
                                ]);
                            }
                        } catch (\Exception $exception) {
                            return json_encode([
                                'message' => $return->sub_msg,
                                'status' => 2
                            ]);
                        }
                    } else {
                        throw new BadRequestHttpException('配置信息不存在');
                    }
                }


            }

            if ($type == '1000') {
                return json_encode([
                    'message' => '请在->我的->认证中心->支付宝授权',
                    'status' => 2
                ]);
            }


            //公共的身份验证
            $config = SmsConfig::where('type', '10')->where('config_id', $config_id)->first();
            if (!$config) {
                $config = SmsConfig::where('type', '10')->where('config_id', '1234')->first();
            }
            if ($config) {
                $data = ["code" => $code];
                $return = $this->sendSms($phone, $config->app_key, $config->app_secret, $config->SignName, $config->TemplateCode, $data);
                try {
                    if ($return->Code == 'OK') {
                        Cache::put('' . $phone . '10-1', $code, 1);
                        return json_encode([
                            'message' => '发送成功',
                            'status' => 1
                        ]);
                    } else {
                        return json_encode([
                            'message' => $return->Message,
                            'status' => 2
                        ]);
                    }
                } catch (\Exception $exception) {
                    return json_encode([
                        'message' => $return->sub_msg,
                        'status' => 2
                    ]);
                }
            } else {
                throw new BadRequestHttpException('配置信息不存在');
            }


        } catch (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'message' => $exception->getMessage() . $exception->getLine()
            ]);
        }
    }

    public function sendSms($phone, $app_key, $app_secret, $SignName, $TemplateCode, $data)
    {

        $demo = new AliSms($app_key, $app_secret);
        $code = rand(100000, 999999);
        $response = $demo->sendSms(
            $SignName, // 短信签名
            $TemplateCode, // 短信模板编号
            $phone, // 短信接收者
            $data
        /* Array(  // 短信模板中字段的值
         "code"=>$code,
         )*/
        );
        return $response;

    }
}