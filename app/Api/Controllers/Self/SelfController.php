<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/9/3
 * Time: 下午3:56
 */

namespace App\Api\Controllers\Self;


//自助收银设备
use Alipayopen\Sdk\AopClient;
use Alipayopen\Sdk\Request\ZolozAuthenticationCustomerSmileliveInitializeRequest;
use Alipayopen\Sdk\Request\ZolozAuthenticationCustomerSmilepayInitializeRequest;
use App\Api\Controllers\Config\AlipayIsvConfigController;
use App\Api\Controllers\Config\WeixinConfigController;
use App\Models\AlipayAppOauthUsers;
use App\Models\AlipayIsvConfig;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SelfController extends BaseController
{


    //支付宝通过该接口获取支付宝刷脸服务的初始化信息。
    public function smilepay_initialize(Request $request)
    {

        try {
            $data = $request->all();
            //验证签名
            $check = $this->check_md5($data);
            if ($check['return_code'] == 'FALL') {
                return $this->return_data($check);
            }
            $store_id = $data['store_id'];
            $store = Store::where('store_id', $store_id)->first();
            $store_pid = $store->pid;
            $config_id = $store->config_id;
            $notify_url = '';
            //配置
            $isvconfig = new AlipayIsvConfigController();
            $AlipayAppOauthUsers = $isvconfig->alipay_auth_info($store_id, $store_pid);
            if (!$AlipayAppOauthUsers) {
                $msg = '支付宝授权信息不存在';
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => $msg,
                ];
                return $this->return_data($err);

            }
            $app_auth_token = $AlipayAppOauthUsers->app_auth_token;
            $config = $isvconfig->AlipayIsvConfig($config_id, '01');


            $aop = new AopClient();
            $aop->apiVersion = "2.0";
            $aop->appId = $config->app_id;
            $aop->rsaPrivateKey = $config->rsa_private_key;
            $aop->alipayrsaPublicKey = $config->alipay_rsa_public_key;
            $aop->method = 'zoloz.authentication.customer.smilepay.initialize';
            $aop->notify_url = $notify_url;
            $aop->signType = "RSA2";//升级算法
            $aop->gatewayUrl = $config->alipay_gateway;
            $aop->format = "json";
            $aop->charset = "GBK";
            $aop->version = "2.0";

            $requests = new ZolozAuthenticationCustomerSmilepayInitializeRequest();


            //参数
            $zimmetainfo = $data['zimmetainfo'];
            $extInfo = json_decode($zimmetainfo, true);
            $data_re = [
                'zimmetainfo' => $extInfo,
            ];

            $data_re = json_encode($data_re);
            $requests->setBizContent($data_re);
            $result = $aop->execute($requests, null, $app_auth_token);
            $responseNode = str_replace(".", "_", $requests->getApiMethodName()) . "_response";
            $resultCode = $result->$responseNode->code;

            if ($resultCode == 10000) {
                $re_data = [
                    'return_code' => 'SUCCESS',//SUCCESS/FALL 此字段是通信标识，非交易标识，交易是否成功需要查看result_code来判断
                    'return_msg' => null,
                    'result_code' => 'SUCCESS',
                    'result_msg' => '数据返回成功',
                    'device_id' => $data['device_id'],
                    'device_type' => $data['device_type'],
                    'store_id' => $data['store_id'],
                    'result' => $result->$responseNode->result,
                ];
            } else {
                $re_data = [
                    'return_code' => 'FALL',//SUCCESS/FALL 此字段是通信标识，非交易标识，交易是否成功需要查看result_code来判断
                    'return_msg' => $result->$responseNode->msg,
                    'device_id' => $data['device_id'],
                    'device_type' => $data['device_type'],
                    'store_id' => $data['store_id'],
                ];
            }


            return $this->return_data($re_data);


        } catch (\Exception $exception) {
            $err = [
                'return_code' => 'FALL',
                'return_msg' => $exception->getMessage() . $exception->getLine(),
            ];
            return $this->return_data($err);
        }

    }


    //微信通过该接口获取支付宝刷脸服务的初始化信息。
    public function wxfacepay_initialize(Request $request)
    {

        try {
            $data = $request->all();
            //验证签名
            $check = $this->check_md5($data);
            if ($check['return_code'] == 'FALL') {
                return $this->return_data($check);
            }

            $device_id = $data['device_id'];
            $store_name = $data['store_name'];
            $store_id = $data['store_id'];
            $rawdata = $data['rawdata'];


            $store = Store::where('store_id', $store_id)->first();
            $store_pid = $store->pid;
            $config_id = $store->config_id;

            $config = new WeixinConfigController();
            $options = $config->weixin_config($config_id);
            $weixin_store = $config->weixin_merchant($store_id, $store_pid);
            if (!$weixin_store) {
                $err = [
                    'return_code' => 'FALL',
                    'return_msg' => '微信商户号不存在',
                ];
                return $this->return_data($err);
            }

            $wx_sub_merchant_id = $weixin_store->wx_sub_merchant_id;

            //公共配置
            $config = [
                'appid' => 'wx94b87b679e8677aa',//$options['app_id'],
                "mch_id" => '1494729062',//$options['payment']['merchant_id'],
                "version" => "1",
                "sign_type" => 'MD5',
                "now" => '' . time() . '',
                "nonce_str" => '' . time() . '',
                "store_id" => $store_id,
                "store_name" => $store_name,
                "device_id" => $device_id,
                'rawdata' => $rawdata,
            ];
            //子商户
            if ($wx_sub_merchant_id) {
                $config['sub_mch_id'] = '1518150321';
            }
            $obj = new WxBaseController();
            $url = $obj->get_wxpayface_authinfo_url;
            $key ='sahadiiPPKh2209373757hhffrrrfhjk'; //$options['payment']['key'];
            $config['sign'] = $obj->MakeSign($config, $key);
            $xml = $obj->ToXml($config);

            $re_data = $obj::postXmlCurl($config, $xml, $url, $useCert = false, $second = 30);
            $re_data = $obj::xml_to_array($re_data);

            if ($re_data['return_code'] == 'SUCCESS') {
                $re_data = [
                    'return_code' => 'SUCCESS',//SUCCESS/FALL 此字段是通信标识，非交易标识，交易是否成功需要查看result_code来判断
                    'return_msg' => null,
                    'result_code' => 'SUCCESS',
                    'result_msg' => '数据返回成功',
                    'device_id' => $data['device_id'],
                    'device_type' => $data['device_type'],
                    'store_id' => $data['store_id'],
                    'authinfo' => $re_data['authinfo'],
                ];
            } else {
                $re_data = [
                    'return_code' => 'FALL',//SUCCESS/FALL 此字段是通信标识，非交易标识，交易是否成功需要查看result_code来判断
                    'return_msg' => $re_data['return_msg'],
                    'device_id' => $data['device_id'],
                    'device_type' => $data['device_type'],
                    'store_id' => $data['store_id'],
                ];
            }


            return $this->return_data($re_data);


        } catch (\Exception $exception) {
            $err = [
                'return_code' => 'FALL',
                'return_msg' => $exception->getMessage() . $exception->getLine(),
            ];
            return $this->return_data($err);
        }

    }
}