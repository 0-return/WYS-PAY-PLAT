<?php

namespace App\Console\Commands;

use Aliyun\AliSms;
use EasyWeChat\Factory;
use App\Api\Controllers\Config\WeixinConfigController;
use App\Models\SmsConfig;
use App\Models\Store;
use App\Models\StoreImg;
use App\Models\StorePayWay;
use App\Models\WeixinNotify;
use App\Models\WeixinStore;
use App\Models\WeixinStoreItem;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WxStoreAudit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wx-audit';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'wx-audit';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $WeixinStoreItem = WeixinStoreItem::all();
            if ($WeixinStoreItem->isEmpty()) {
                return '';
            }
            $WeixinStoreItem = $WeixinStoreItem->toArray();
            foreach ($WeixinStoreItem as $k => $v) {

                //小微审核中 查询小微状态 升级接口 或者个体没有升级
                if ($v['xw_status'] == 2 || $v['qy_status'] == 0) {

                    //申请入驻接口提交小微商户查询接口
                    $config = new WeixinConfigController();
                    $weixin_config = $config->weixin_config_obj($v['config_id']);
                    $obj = new \App\Api\Controllers\Weixin\BaseController();
                    $url = 'https://api.mch.weixin.qq.com/applyment/micro/getstate';
                    $key = $weixin_config->key;
                    //公共配置
                    $config = [
                        "version" => "1.0",
                        "mch_id" => $weixin_config->wx_merchant_id,
                        "sign_type" => 'HMAC-SHA256',
                        "nonce_str" => '' . time() . '',
                        "applyment_id" => $v['applyment_id'],
                    ];

                    $config['sign'] = $obj->MakeSign($config, $key, 'HMAC-SHA256');
                    //不参与签名
                    $config['sslCertPath'] = public_path() . $weixin_config->cert_path;
                    $config['sslKeyPath'] = public_path() . $weixin_config->key_path;

                    $xml = $obj->ToXml($config);
                    $re_data = $obj::postXmlCurl($config, $xml, $url, $useCert = true, $second = 30);
                    $return = $obj::xml_to_array($re_data);
                    //返回状态码
                    if ($return['return_code'] == "FAIL") {
                        $in_data = [
                            'status' => 3,
                            'status_desc' => $return['return_msg'],
                        ];

                        StorePayWay::where('store_id', $v['store_id'])
                            ->where('company', 'weixin')
                            ->update($in_data);
                    }

                    if ($return['result_code'] == "FAIL") {
                        $in_data = [
                            'status' => 3,
                            'status_desc' => $return['err_code_des'],
                        ];

                        StorePayWay::where('store_id', $v['store_id'])
                            ->where('company', 'weixin')
                            ->update($in_data);
                    }

                    //审核中
                    if ($return['applyment_state'] == "AUDITING") {
                        continue;
                    }


                    //已驳回
                    if ($return['applyment_state'] == "REJECTED") {
                        $in_data = [
                            'status' => 3,
                            'status_desc' => json_decode($return['audit_detail'], true)['audit_detail'][0]['reject_reason'],
                        ];

                        StorePayWay::where('store_id', $v['store_id'])
                            ->where('company', 'weixin')
                            ->update($in_data);
                    }


                    //已冻结
                    if ($return['applyment_state'] == "FROZEN") {
                        $in_data = [
                            'status' => 3,
                            'status_desc' => json_decode($return['audit_detail'], true)['audit_detail'][0]['reject_reason'],
                        ];
                        StorePayWay::where('store_id', $v['store_id'])
                            ->where('company', 'weixin')
                            ->update($in_data);
                    }


                    // 待签约 完成
                    if ($return['applyment_state'] == "TO_BE_SIGNED" || $return['applyment_state'] == "FINISH") {

                        $sub_mch_id = $return['sub_mch_id'];
                        $sign_url = $return['sign_url'];

                        //如果是个人类型
                        if ($v['store_type'] == 3) {
                            //待签约
                            if ($return['applyment_state'] == "TO_BE_SIGNED") {
                                //发送链接给商户
                                $in_data = [
                                    'status' => 2,
                                    'status_desc' => '审核通过,待商户微信扫码签约',
                                ];

                                //发公众号消息
                                $notify = [
                                    'config_id' => $v['config_id'],
                                    'store_id' => $v['store_id'],
                                    'sign_url' => $sign_url,
                                ];
                                self::weixin_notify($notify);

                            } else {
                                $in_data = [
                                    'status' => 1,
                                    'status_desc' => '开通成功',
                                ];
                                $m_data = [
                                    'config_id' => $v['config_id'],
                                    'store_id' => $v['store_id'],
                                    'secret' => "",
                                    'wx_sub_merchant_id' => $sub_mch_id,
                                    'status' => 1,
                                    'status_desc' => '开通成功',
                                ];
                                $WeixinStore = WeixinStore::where('store_id', $v['store_id'])->first();
                                if ($WeixinStore) {
                                    $WeixinStore->update($m_data);
                                    $WeixinStore->save();
                                } else {
                                    WeixinStore::create($m_data);
                                }

                                //删除临时库
                                WeixinStoreItem::where('store_id', $v['store_id'])
                                    ->delete();
                            }

                            StorePayWay::where('store_id', $v['store_id'])
                                ->where('company', 'weixin')
                                ->update($in_data);
                        }

                        //如果是个体需要升级
                        if ($v['store_type'] == 1) {

                            $in_data = [
                                'status' => 2,
                                'status_desc' => '小微开通成功,等待升级个体类型',
                            ];

                            StorePayWay::where('store_id', $v['store_id'])
                                ->where('company', 'weixin')
                                ->update($in_data);

                            //更改临时库
                            WeixinStoreItem::where('store_id', $v['store_id'])
                                ->update([
                                    'xw_status' => 1,
                                    'sub_mch_id' => $sub_mch_id,
                                ]);


                            //调用升级接口
                            $config = new WeixinConfigController();
                            $weixin_config = $config->weixin_config_obj($v['config_id']);
                            $obj = new \App\Api\Controllers\Weixin\StoreController();

                            $business = '719';
                            $Store = Store::where('store_id', $v['store_id'])
                                ->select(
                                    'store_license_no',
                                    'store_name',
                                    'store_address',
                                    'head_name',
                                    'store_license_stime',
                                    'store_license_time',
                                    'store_short_name'
                                )
                                ->first();
                            $StoreImg = StoreImg::where('store_id', $v['store_id'])
                                ->select('store_license_img')
                                ->first();
                            if (!$Store) {
                                continue;
                            }

                            if (!$StoreImg) {
                                continue;
                            }

                            $data_info = [
                                //参数信息
                                'mch_id' => $weixin_config->wx_merchant_id,
                                'key' => $weixin_config->key,
                                'getcertficates_request_url' => 'https://api.mch.weixin.qq.com/risk/getcertficates',
                                'upload_img_request_url' => 'https://api.mch.weixin.qq.com/secapi/mch/uploadmedia',
                                'submit_request_url' => 'https://api.mch.weixin.qq.com/applyment/micro/submitupgrade',//提交升级申请单
                                'sslCertPath' => public_path() . $weixin_config->cert_path,
                                'sslKeyPath' => public_path() . $weixin_config->key_path,
                                'public_key_path' => public_path() . $weixin_config->key_path,
                                "sub_mch_id" => $sub_mch_id,
                                //门店信息
                                "store_type" => '4',//个体
                                "store_license_no" => $Store->store_license_no,
                                "store_name" => $Store->store_name,
                                "store_address" => $Store->store_address,
                                "head_name" => $Store->head_name,
                                "store_license_stime" => $Store->store_license_stime,
                                "store_license_time" => $Store->store_license_time,
                                "store_short_name" => $Store->store_short_name,
                                "business" => $business,
                            ];

                            $img_data = [
                                "store_license_img" => $StoreImg->store_license_img,
                            ];

                            $return = $obj->qy_store($data_info, $img_data);

                            //报错
                            if ($return['status'] == 2) {
                                $in_data = [
                                    'status' => 2,
                                    'status_desc' => $return['message'],
                                ];
                                StorePayWay::where('store_id', $v['store_id'])
                                    ->where('company', 'weixin')
                                    ->update($in_data);

                            } else {

                                $in_data = [
                                    'status' => 2,
                                    'status_desc' => '提交升级申请审核中',
                                ];
                                StorePayWay::where('store_id', $v['store_id'])
                                    ->where('company', 'weixin')
                                    ->update($in_data);
                                //更改临时库
                                WeixinStoreItem::where('store_id', $v['store_id'])
                                    ->update([
                                        'qy_status' => 2,
                                    ]);
                            }

                        }

                    }


                }


                //升级查询
                if ($v['xw_status'] == 1 && $v['qy_status'] == 2) {

                    //查询升级申请单状态接口
                    $config = new WeixinConfigController();
                    $weixin_config = $config->weixin_config_obj($v['config_id']);
                    $obj = new \App\Api\Controllers\Weixin\BaseController();
                    $url = 'https://api.mch.weixin.qq.com/applyment/micro/getupgradestate';
                    $key = $weixin_config->key;
                    //公共配置
                    $config = [
                        "version" => "1.0",
                        "mch_id" => $weixin_config->wx_merchant_id,
                        "sign_type" => 'HMAC-SHA256',
                        "nonce_str" => '' . time() . '',
                        "sub_mch_id" => $v['sub_mch_id'],
                    ];

                    $config['sign'] = $obj->MakeSign($config, $key, 'HMAC-SHA256');
                    //不参与签名
                    $config['sslCertPath'] = public_path() . $weixin_config->cert_path;
                    $config['sslKeyPath'] = public_path() . $weixin_config->key_path;

                    $xml = $obj->ToXml($config);
                    $re_data = $obj::postXmlCurl($config, $xml, $url, $useCert = true, $second = 30);
                    $return = $obj::xml_to_array($re_data);


                    //资料校验中
                    if ($return['applyment_state'] == "CHECKING") {
                        continue;
                    }
                    //审核中
                    if ($return['applyment_state'] == "AUDITING") {
                        continue;
                    }

                    //已驳回
                    if ($return['applyment_state'] == "REJECTED") {
                        $in_data = [
                            'status' => 3,
                            'status_desc' => $return['applyment_state_desc'],
                        ];

                        StorePayWay::where('store_id', $v['store_id'])
                            ->where('company', 'weixin')
                            ->update($in_data);
                    }


                    //待签约
                    if ($return['applyment_state'] == "NEED_SIGN") {
                        $sign_url = $return['sign_qrcode'];

                        //发公众号消息
                        $notify = [
                            'config_id' => $v['config_id'],
                            'store_id' => $v['store_id'],
                            'sign_url' => $sign_url,
                        ];
                        self::weixin_notify($notify);

                        $in_data = [
                            'status' => 2,
                            'status_desc' => '升级通过,待商户微信扫码签约',
                        ];
                        StorePayWay::where('store_id', $v['store_id'])
                            ->where('company', 'weixin')
                            ->update($in_data);

                    }

                    //完成
                    if ($return['applyment_state'] == "FINISH") {

                        $in_data = [
                            'status' => 1,
                            'status_desc' => '开通成功',
                        ];
                        $m_data = [
                            'config_id' => $v['config_id'],
                            'store_id' => $v['store_id'],
                            'secret' => "",
                            'wx_sub_merchant_id' => $v['sub_mch_id'],
                            'status' => 1,
                            'status_desc' => '开通成功',
                        ];
                        $WeixinStore = WeixinStore::where('store_id', $v['store_id'])->first();
                        if ($WeixinStore) {
                            $WeixinStore->update($m_data);
                            $WeixinStore->save();
                        } else {
                            WeixinStore::create($m_data);
                        }

                        //删除临时库
                        WeixinStoreItem::where('store_id', $v['store_id'])
                            ->delete();
                        StorePayWay::where('store_id', $v['store_id'])
                            ->where('company', 'weixin')
                            ->update($in_data);
                    }


                    //已冻结
                    if ($return['applyment_state'] == "FROZEN") {
                        $in_data = [
                            'status' => 3,
                            'status_desc' => $return['applyment_state_desc'],
                        ];
                        StorePayWay::where('store_id', $v['store_id'])
                            ->where('company', 'weixin')
                            ->update($in_data);
                    }
                }

            }

        } catch (\Exception $exception) {
            Log::info('微信小微进件定时');
            Log::info($exception);
        }
    }


    //微信公众号消息提醒提醒
    public static function weixin_notify($data)
    {
        try {
            $WeixinNotify = WeixinNotify::where('store_id', $data['store_id'])
                ->select('wx_open_id')
                ->get();
            foreach ($WeixinNotify as $k => $v) {
                $config_id = $data['config_id'];
                //判断服务商是否有设置模版消息
                $config = new WeixinConfigController();
                $config_obj = $config->weixin_config_obj($config_id);
                $config = [
                    'app_id' => $config_obj->wx_notify_appid,
                    'secret' => $config_obj->wx_notify_secret,
                ];
                $app = Factory::officialAccount($config);
                $openId = $v->wx_open_id;
                $message = "你的微信官方通道审核通过,请点击此链接签约：" . $data['sign_url'];
                $app->customer_service->message($message)->to($openId)->send();
            }


        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::info($exception);
        }

    }

    public
    function send($name, $status, $phone, $config_id = '1234')
    {
        try {

            $config = SmsConfig::where('type', '7')->where('config_id', $config_id)->first();
            if (!$config) {
                $config = SmsConfig::where('type', '7')->where('config_id', '1234')->first();
            }
            $data = ["name" => $name, 'status' => $status];
            $this->sendSms($phone, $config->app_key, $config->app_secret, $config->SignName, $config->TemplateCode, $data);
        } catch (\Exception $exception) {
        }
    }


    public
    function sendSms($phone, $app_key, $app_secret, $SignName, $TemplateCode, $data)
    {

        $demo = new AliSms($app_key, $app_secret);
        $response = $demo->sendSms(
            $SignName, // 短信签名
            $TemplateCode, // 短信模板编号
            $phone, // 短信接收者
            $data
        );
        return $response;

    }

}
