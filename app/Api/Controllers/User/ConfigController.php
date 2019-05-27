<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/7/9
 * Time: 下午6:51
 */

namespace App\Api\Controllers\User;


use App\Api\Controllers\BaseController;
use App\Models\AlipayIsvConfig;
use App\Models\HConfig;
use App\Models\JdConfig;
use App\Models\MqttConfig;
use App\Models\NewLandConfig;
use App\Models\User;
use App\Models\WeixinConfig;
use Illuminate\Http\Request;

class ConfigController extends BaseController
{
    //支付宝配置
    public function alipay_isv_config(Request $request)
    {
        try {
            $user = $this->parseToken();
            $data = $request->except(['token', 'type']);
            $config_type = $request->get('config_type', '01');
            $type = $request->get('type', '2');

            $user = User::where('id', $user->user_id)->first();
            $hasPermission = $user->hasPermissionTo('支付宝应用配置');

            if ($user->level > 1 || !$hasPermission) {
                return json_encode(['status' => 2, 'message' => '没有权限配置']);
            }


            $config_id = $user->config_id;//配置的id
            $AlipayIsvConfig = AlipayIsvConfig::where('config_id', $config_id)
                ->where('config_type', $config_type)
                ->first();

            //查询
            if ($type == '2') {
                if ($AlipayIsvConfig) {
                    return json_encode(['status' => 1, 'data' => $AlipayIsvConfig]);
                } else {
                    return json_encode(['status' => 2, 'data' => []]);
                }
            }
            //添加修改
            if ($type == '1') {
                $check_data = [
                    'app_id' => '应用appid',
                    'alipay_pid' => '支付宝返佣pid',
                    'rsa_private_key' => '软件生成的私钥',
                    'alipay_rsa_public_key' => '支付宝公钥',
                    'callback' => '回调地址',
                    'notify' => '异步地址',
                    'isv_phone' => 'isv电话',
                    'isv_name' => 'isv名称',
                ];

                $check = $this->check_required($request->except(['token']), $check_data);
                if ($check) {
                    return json_encode([
                        'status' => 2,
                        'message' => $check
                    ]);
                }


                $data['config_id'] = $config_id;
                $data['app_id'] = trim($data['app_id']);
                $data['alipay_pid'] = trim($data['alipay_pid']);
                $data['rsa_private_key'] = trim($data['rsa_private_key']);
                $data['alipay_rsa_public_key'] = trim($data['alipay_rsa_public_key']);
                $data['callback'] = trim($data['callback']);
                $data['notify'] = trim($data['notify']);

                if ($AlipayIsvConfig) {
                    $AlipayIsvConfig->update($data);
                    $AlipayIsvConfig->save();
                } else {
                    AlipayIsvConfig::create($data);
                }

            }


            return json_encode(['status' => 1, 'message' => '添加成功', 'data' => $data]);


        } catch (\Exception $exception) {
            return json_encode(['status' => 2, 'message' => $exception->getMessage()]);
        }
    }


    //微信配置
    public function weixin_config(Request $request)
    {
        try {
            $user = $this->parseToken();
            $data = $request->except(['token', 'type']);


            $user = User::where('id', $user->user_id)->first();
            $hasPermission = $user->hasPermissionTo('微信应用配置');

            if ($user->level > 1 || !$hasPermission) {
                return json_encode(['status' => 2, 'message' => '没有权限配置']);
            }

            $data['config_id'] = $user->config_id;//配置的id
            $config = WeixinConfig::where('config_id', $user->config_id)->first();
            //查询
            if ($request->get('type') == '2') {
                if ($config) {
                    return json_encode(['status' => 1, 'data' => $config]);
                } else {
                    return json_encode(['status' => 1, 'data' => []]);

                }
            }

            //添加修改
            if ($request->get('type') == '1') {

                $check_data = [
                    'app_id' => '公众号appid',
                    'app_secret' => '秘钥',
                    'wx_merchant_id' => '服务商商户号',
                    'key' => '微信支付key',
                    'cert_path' => '证书文件',
                    'key_path' => '秘钥文件',
                    'auth_path' => '域名授权地址',
                ];


                $check = $this->check_required($request->except(['token']), $check_data);
                if ($check) {
                    return json_encode([
                        'status' => 2,
                        'message' => $check
                    ]);
                }

                if ($config) {
                    $re = WeiXinConfig::where('config_id', $user->config_id)->update($data);
                } else {
                    $re = WeiXinConfig::create($data);
                }
                return json_encode([
                    'status' => 1,
                    'message' => '保存成功',
                    'data' => $data,
                ]);
            }
            return json_encode(['status' => 2, 'message' => 'type_参数不正确']);
        } catch (\Exception $exception) {
            return json_encode(['status' => 2, 'message' => $exception->getMessage()]);
        }
    }


    //京东聚合配置
    public function jd_config(Request $request)
    {

        try {
            $user = $this->parseToken();
            $data = $request->except(['token', 'type']);

            if ($user->level > 1) {
                return json_encode(['status' => 2, 'message' => '没有权限配置']);
            }

            $user = User::where('id', $user->user_id)->first();
            $hasPermission = $user->hasPermissionTo('京东金融配置');

            if ($user->level > 1 || !$hasPermission) {
                return json_encode(['status' => 2, 'message' => '没有权限配置京东金融']);
            }

            $data['config_id'] = $user->config_id;//配置的id
            $config = JdConfig::where('config_id', $user->config_id)->first();

            //查询
            if ($request->get('type') == '2') {
                if ($config) {
                    return json_encode(['status' => 1, 'data' => $config]);

                } else {
                    return json_encode(['status' => 1, 'data' => []]);

                }
            }

            //添加修改
            if ($request->get('type') == '1') {
                $check_data = [
                    'systemId' => '系统名称ID',
                    'store_md_key' => "加签密钥",
                    'store_des_key' => "加密密钥",
                    'agentNo' => "服务商商户号",
                ];


                $check = $this->check_required($request->except(['token']), $check_data);
                if ($check) {
                    return json_encode([
                        'status' => 2,
                        'message' => $check
                    ]);
                }

                if ($config) {
                    $re = JdConfig::where('config_id', $user->config_id)->update($data);
                } else {
                    $re = JdConfig::create($data);
                }
                return json_encode([
                    'status' => 1,
                    'message' => '保存成功',
                    'data' => $data,
                ]);
            }
            return json_encode(['status' => 2, 'message' => 'type_参数不正确']);
        } catch (\Exception $exception) {
            return json_encode(['status' => 2, 'message' => $exception->getMessage()]);
        }

    }


    //新大陆配置
    public function new_land_config(Request $request)
    {

        try {
            $user = $this->parseToken();
            $data = $request->except(['token', 'type']);

            if ($user->level > 1) {
                return json_encode(['status' => 2, 'message' => '没有权限配置']);
            }
            $user = User::where('id', $user->user_id)->first();
            $hasPermission = $user->hasPermissionTo('新大陆配置');

            if ($user->level > 1 || !$hasPermission) {
                return json_encode(['status' => 2, 'message' => '新大陆配置没有权限']);
            }

            $data['config_id'] = $user->config_id;//配置的id
            $config = NewLandConfig::where('config_id', $user->config_id)->first();

            //查询
            if ($request->get('type') == '2') {
                if ($config) {
                    return json_encode(['status' => 1, 'data' => $config]);

                } else {
                    return json_encode(['status' => 1, 'data' => []]);

                }
            }

            //添加修改
            if ($request->get('type') == '1') {
                $check_data = [
                    'org_no' => '机构号',
                    'nl_key' => '机构密钥',
                ];


                $check = $this->check_required($request->except(['token']), $check_data);
                if ($check) {
                    return json_encode([
                        'status' => 2,
                        'message' => $check
                    ]);
                }

                if ($config) {
                    $re = NewLandConfig::where('config_id', $user->config_id)->update($data);
                } else {
                    $re = NewLandConfig::create($data);
                }
                return json_encode([
                    'status' => 1,
                    'message' => '保存成功',
                    'data' => $data,
                ]);
            }
            return json_encode(['status' => 2, 'message' => 'type_参数不正确']);
        } catch (\Exception $exception) {
            return json_encode(['status' => 2, 'message' => $exception->getMessage()]);
        }

    }


    //和融通聚合配置
    public function h_config(Request $request)
    {

        try {
            $user = $this->parseToken();
            $data = $request->except(['token', 'type']);
            if ($user->level > 1) {
                return json_encode(['status' => 2, 'message' => '没有权限配置']);
            }
            $user = User::where('id', $user->user_id)->first();
            $hasPermission = $user->hasPermissionTo('和融通配置');

            if ($user->level > 1 || !$hasPermission) {
                return json_encode(['status' => 2, 'message' => '和融通配置没有权限']);
            }

            $data['config_id'] = $user->config_id;//配置的id
            $config = HConfig::where('config_id', $user->config_id)->first();

            //查询
            if ($request->get('type') == '2') {
                if ($config) {
                    return json_encode(['status' => 1, 'data' => $config]);

                } else {
                    return json_encode(['status' => 1, 'data' => []]);

                }
            }

            //添加修改
            if ($request->get('type') == '1') {
//                $check_data = [
//                    'orgNo' => '机构号',
//                    'md_key' => "加签密钥",
//                    'ali_pid' => '支付宝合作者pid',
//                    '	wx_appid' => "微信公众号appid",
//                    'wx_secret' => "微信公众号密钥",
//                ];
//
//
//                $check = $this->check_required($request->except(['token']), $check_data);
//                if ($check) {
//                    return json_encode([
//                        'status' => 2,
//                        'message' => $check
//                    ]);
//                }

                if ($config) {
                    $re = HConfig::where('config_id', $user->config_id)->update($data);
                } else {
                    $re = HConfig::create($data);
                }
                return json_encode([
                    'status' => 1,
                    'message' => '保存成功',
                    'data' => $data,
                ]);
            }
            return json_encode(['status' => 2, 'message' => 'type_参数不正确']);
        } catch (\Exception $exception) {
            return json_encode(['status' => 2, 'message' => $exception->getMessage()]);
        }

    }


    //阿里云mqtt配置
    public function mqtt_config(Request $request)
    {
        try {
            $user = $this->parseToken();
            $data = $request->except(['token']);
            $access_key_id = $request->get('access_key_id', '');

            $user = User::where('id', $user->user_id)->first();
            $hasPermission = $user->hasPermissionTo('MQTT推送');

            if ($user->level > 1 || !$hasPermission) {
                return json_encode(['status' => 2, 'message' => '没有权限配置']);
            }


            $config_id = $user->config_id;//配置的id
            $MqttConfig = MqttConfig::where('config_id', $config_id)->first();

            //查询
            if ($access_key_id == '') {
                if ($MqttConfig) {
                    return json_encode(['status' => 1, 'data' => $MqttConfig]);
                } else {
                    return json_encode(['status' => 2, 'data' => []]);
                }
            }
            //添加修改

            $check_data = [
                'access_key_id' => '阿里云access_key_id',
                'access_key_secret' => '阿里云access_key_secret',
                'server' => "公网接入点地址",
                'port' => '端口号',
                'instance_id' => 'MQTT实例 ID',
                'group_id' => 'Group ID',
                'topic' => '主题Topic',
            ];

            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }


            $data['config_id'] = $config_id;

            if ($MqttConfig) {
                $MqttConfig->update($data);
                $MqttConfig->save();
            } else {
                MqttConfig::create($data);
            }


            return json_encode(['status' => 1, 'message' => '添加成功', 'data' => $data]);


        } catch (\Exception $exception) {
            return json_encode(['status' => 2, 'message' => $exception->getMessage()]);
        }
    }


}