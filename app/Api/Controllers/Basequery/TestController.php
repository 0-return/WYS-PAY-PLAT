<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/7/26
 * Time: 下午5:46
 */

namespace App\Api\Controllers\Basequery;


use Alipayopen\Sdk\AopClient;
use Aliyun\Core\Config;
use Aliyun\Core\DefaultAcsClient;
use Aliyun\Core\Profile\DefaultProfile;
use Aliyun\OSS\Exceptions\OSSException;
use Aliyun\OSS\OSSClient;
use App\Api\Controllers\Config\AlipayIsvConfigController;
use App\Api\Controllers\Config\JdConfigController;
use App\Api\Controllers\Config\MyBankConfigController;
use App\Api\Controllers\Config\WeixinConfigController;
use App\Api\Controllers\Deposit\AliDepositController;
use App\Api\Controllers\Deposit\WxDepositController;
use App\Api\Controllers\Jd\PayController;
use App\Api\Controllers\Jd\StoreController;
use App\Api\Controllers\MyBank\BaseController;
use App\Api\Controllers\MyBank\MyBankController;
use App\Api\Controllers\Qmtt\AliBaseController;
use App\Api\Controllers\Self\PospalApiClientController;
use App\Api\Controllers\Self\WxBaseController;
use App\Common\Tool\Mqtt;
use App\Models\HConfig;
use App\Models\JpushConfig;
use App\Models\Merchant;
use App\Models\MerchantStore;
use App\Models\MqttConfig;
use App\Models\NewLandConfig;
use App\Models\NewLandStore;
use App\Models\Order;
use App\Models\QrList;
use App\Models\QrListInfo;
use App\Models\SelfShop;
use App\Models\Store;
use App\Models\StoreBank;
use App\Models\StoreImg;
use App\Models\StorePayWay;
use App\Models\User;
use App\Models\WeixinNotify;
use App\Services\OSS;
use EasyWeChat\Factory;
use EasyWeChat\Kernel\Messages\Text;
use Freyo\LaravelQueueCMQ\Queue\Connectors\CMQConnector;
use Freyo\LaravelQueueCMQ\Queue\Driver\Account;
use Freyo\LaravelQueueCMQ\Queue\Driver\CMQExceptionBase;
use Freyo\LaravelQueueCMQ\Queue\Driver\Message;
use Freyo\LaravelQueueCMQ\Queue\Driver\QueueMeta;
use Illuminate\Http\Request;
use Illuminate\Queue\Jobs\Job;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;
use Intervention\Image\Facades\Image;
use Iot\Request\V20170420\ApplyDeviceWithNamesRequest;
use Iot\Request\V20170420\PubRequest;
use Iot\Request\V20170420\RRpcRequest;
use Jdjr\Sdk\TDESUtil;
use JohnLui\AliyunOSS;
use JPush\Client;
use karpy47\PhpMqttClient\MQTTClient;
use MyBank\Tools;
use Ons\Request\V20170918\OnsTopicCreateRequest;
use function Sodium\crypto_aead_aes256gcm_decrypt;
use TencentYoutuyun\Conf;
use TencentYoutuyun\YouTu;
use WeixinApp\WXBizDataCrypt;
use Ecs\Request\V20140526 as Ecs;


use Lzq\Mqtt\SamMessage;
use Lzq\Mqtt\SamConnection;


class TestController
{

    // 表单提交字符集编码
    public $postCharset = "UTF-8";

    private $fileCharset = "UTF-8";

    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    function characet($data, $targetCharset)
    {


        if (!empty($data)) {
            $fileType = '';
            if (strcasecmp($fileType, $targetCharset) != 0) {

                $data = mb_convert_encoding($data, $targetCharset);
                //				$data = iconv($fileType, $targetCharset.'//IGNORE', $data);
            }
        }


        return $data;
    }

    protected function getMillisecond()
    {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    /**
     * curl post java对接  传输数据流
     * */
    public function curlPost_java($data, $Url)
    {
        $ch = curl_init($Url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//$data JSON类型字符串
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($data)));
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }


    function base64UrlEncode($str)
    {
        $find = array('+', '/');
        $replace = array('-', '_');
        return str_replace($find, $replace, base64_encode($str));
    }

    public function getSignature($str, $key)
    {
        $signature = "";
        if (function_exists('hash_hmac')) {
            $signature = bin2hex(hash_hmac("sha1", $str, $key, true));
        } else {
            $blocksize = 64;
            $hashfunc = 'sha1';
            if (strlen($key) > $blocksize) {
                $key = pack('H*', $hashfunc($key));
            }
            $key = str_pad($key, $blocksize, chr(0x00));
            $ipad = str_repeat(chr(0x36), $blocksize);
            $opad = str_repeat(chr(0x5c), $blocksize);
            $hmac = pack(
                'H*', $hashfunc(
                    ($key ^ $opad) . pack(
                        'H*', $hashfunc(
                            ($key ^ $ipad) . $str
                        )
                    )
                )
            );
            $signature = bin2hex($hmac);
        }
        return $signature;
    }

    public function curlJsonPost($data, $url)
    {

        $ch = curl_init($url); //请求的URL地址
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//$data JSON类型字符串
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Content-Length: ' . strlen($data)));
        $data = curl_exec($ch);
        return $data;
    }

    public function curl_c($data, $url)
    {
        //启动一个CURL会话
        $ch = curl_init();
        // 设置curl允许执行的最长秒数
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        // 获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //发送一个常规的POST请求。
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        //要传送的所有数据
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        // 执行操作
        $res = curl_exec($ch);
        $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($res == NULL) {
            curl_close($ch);
            return false;
        } else if ($response != "200") {
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        return $res;
    }


    public function img_content($img_url, $name, $name_desc = '')
    {
        try {
            //文件流
            $content = new \CURLFile(realpath($img_url));
            return $content;
        } catch (\Exception $exception) {
            return '';
        }
    }

    public function httpsRequest($url, $data, array $headers = [], $userCert = false, $timeout = 30)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        //设置超时
        curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
        if ($userCert) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);//严格校验
            curl_setopt($curl, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($curl, CURLOPT_SSLKEYTYPE, 'PEM');
            list($sslCertPath, $sslKeyPath) = [$data['sslCertPath'], $data['sslKeyPath']];
            curl_setopt($curl, CURLOPT_SSLCERT, $sslCertPath);
            curl_setopt($curl, CURLOPT_SSLKEY, $sslKeyPath);

        } else {
            if (substr($url, 0, 5) == 'https') {
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false); // 信任任何证书
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 检查证书中是否设置域名
            }
        }
        if (!empty($headers)) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            // curl_setopt($curl, CURLINFO_HEADER_OUT, true); //TRUE 时追踪句柄的请求字符串，从 PHP 5.1.3 开始可用。这个很关键，就是允许你查看请求header
            // $headers = curl_getinfo($curl, CURLINFO_HEADER_OUT); //官方文档描述是“发送请求的字符串”，其实就是请求的header。这个就是直接查看请求header，因为上面允许查看
        }
        curl_setopt($curl, CURLOPT_HEADER, true);    // 是否需要响应 header
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);    // 获得响应结果里的：头大小
        $response_header = substr($output, 0, $header_size);    // 根据头大小去获取头信息内容
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);    // 获取响应状态码
        $response_body = substr($output, $header_size);
        $error = curl_error($curl);
        curl_close($curl);

        return $response_body;
    }

    public static function encrypt_no_pad($str, $key)
    {
        $key = base64_decode($key);

        // $str = self::pkcs5_pad($str, 8);
        if (strlen($str) % 8) {
            $str = str_pad($str, strlen($str) + 8 - strlen($str) % 8, "\0");
        }

        $str = openssl_encrypt($str, 'DES-EDE3', $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, '');


        return $str;

    }


    public static function decrypt($str, $key)
    {
        $key = base64_decode($key);
        $str = pack("H*", $str);
        $str = openssl_decrypt($str, 'DES-EDE3', $key, OPENSSL_RAW_DATA, '');
        return $str;
    }

    public function test(Request $request)
    {


        $obj = new \App\Api\Controllers\Fuiou\BaseController();
        $data = array();
        $data['version'] = "1";
        $data['ins_cd'] = "08A9999999";
        $data['mchnt_cd'] = "0002900F0370542";
        $data['term_id'] = "88888888";
        $data['random_str'] = time();
        $data['order_type'] = "WECHAT";
        $data['goods_des'] = "你好";
        $data['mchnt_order_no'] = time();
        $data['order_amt'] = 100000000;
        $data['term_ip'] = "117.29.110.187";
        $data['txn_begin_ts'] = date('YmdHis', time());
        $data['auth_code'] = "134763049207917612";

        $data['goods_detail'] = "";
        $data['addn_inf'] = "";
        $data['curr_type'] = "";
        $data['goods_tag'] = "";
        $data['sence'] = "";

        $pem = file_get_contents(public_path() . '/keypem.pem');
        $str = $obj->getSignContent($data);

        $url = "https://fundwx.fuiou.com/micropay";

        $data['reserved_expire_minute'] = 1;
        $data['reserved_sub_appid'] = "";
        $data['reserved_limit_pay'] = "";
        $data['reserved_fy_term_id'] = "";
        $data['reserved_fy_term_type'] = "";
        $data['reserved_fy_term_sn'] = "";

        $data['sign'] = $obj->sign($str, $pem);


        $re = $obj->send($data, $url);
        dd($re);


        $config_id = '1234';
        $MqttConfig = MqttConfig::where('config_id', $config_id)->first();
        if (!$MqttConfig) {
            $MqttConfig = MqttConfig::where('config_id', '1234')->first();
        }

        if (!$MqttConfig) {
            return false;
        }
        $device_id = '0110066666';
        $message = '0100000217';
        $orderNum = '0100000217';

        $content = json_encode([
            'message' => $message,
            'orderNum' => $orderNum,
            'type' => '1',//1 支付宝 2 微信
            'price' => '1'//分
        ]);


        $server = 'post-cn-v0h101b0n0g.mqtt.aliyuncs.com';
        $port = 1883;


        $username = 'Signature|LTAIO0UpPBX6aao4|post-cn-v0h101b0n0g';
        $password = 'xGUQmqTX+DUX8kq3N7be8d/nHbU=';
        $client_id = 'GID_123@@@KD58T000001';


        $username = 'Signature|LTAIO0UpPBX6aao4|post-cn-v0h101b0n0g';
        $password = 'I5WcBhn/Q69vDcoGfbQiTBQgHaU=';
        $client_id = 'GID_123@@@912345';


        $username = 'Signature|LTAIO0UpPBX6aao4|post-cn-v0h101b0n0g';
        $password = 'o585WZbAzneTXyWyIMzspRKTiRI=';
        $client_id = 'GID_123@@@0100001582';


        $server_client_id = 'GID_123@@@123456';
        $username = 'Signature|LTAIO0UpPBX6aao4|post-cn-v0h101b0n0g';
        $password = 'xa6mfrA1WbZGzrDcwMEdXG1X6VU=';
        $client_id = 'GID_123@@@0100001582';


        $topic = 'xy123' . "/p2p/" . $client_id;

        for ($i = 0; $i < 100; $i++) {
            sleep(2);
            echo $i;
            $mqtt = new AliBaseController($server, $port, $server_client_id);
            $re = $mqtt->connect(false, NULL, $username, $password);
            $mqtt->publish($topic, $content, 1);
            $mqtt->close();


        }

        dd('end');


        $client = new MQTTClient($server, 1883);
        $client->setAuthentication($username, $password);
        $success = $client->sendConnect($server_client_id, false, 120);  // set your client ID
        if ($success) {
            //$client->sendSubscribe([$topic], 1);
            $client->sendPublish($topic, $content, 1);
            $client->sendDisconnect();
        }
        $client->close();


        //微信子商户支付配置接口
        $aop = new BaseController();
        $ao = $aop->aop();
        $ao->url = env("MY_BANK_request2");
        $ao->Function = "ant.mybank.merchantprod.merchant.addMerchantConfig";
        $data = [
            'Appid' => 'wx94b87b679e8677aa',
            'SubscribeAppid' => 'wx94b87b679e8677aa',
            'MerchantId' => '226801000010576145286',
            'WechatChannel' => '248078474',
            'Path' => 'https://yh.yihoupay.com/api/mybank/weixin/',
            'OutTradeNo' => date('YmdHis') . time() . rand(10000, 99999),
        ];

        $re = $ao->Request($data);
        dd($re);
        $wx_AppId =


        $data = [
            "BankCertName" => '黄舒焓',//名称
            "BankCardNo" => '6217002020021610407',//银行卡号
            "AccountType" => '01',//账户类型。可选值：01：对私账 02对公账户
            "BankCode" => '中国建设银行',//开户行名称
            "BranchName" => '中国建设银行股份有限公司南昌瑶湖支行',//开户支行名称
            "ContactLine" => '105428000173',//联航号
            "BranchProvince" => '360000',//省编号
            "BranchCity" => '360700',//市编号
            "CertType" => "01",//持卡人证件类型。可选值： 01：身份证
            "CertNo" => '360735199601240028',//持卡人证件号码
            "CardHolderAddress" => '江西省赣州市章贡区文清路永平街44号',//持卡人地址
            "MerchantId" => '226801000010132312071',
            'config_id' => '1234',
        ];

        $obj = new MyBankController();
        $abc = $obj->up_bank($data);
        dd($abc);


        //微信酒店预授权
        $obj = new WxDepositController();


        //预授权

        $data = [

        ];
        $re = $obj->base_fund_freeze($data);
        dd($re);


        //支付宝预授权
        $obj = new AliDepositController();

        $isvconfig = new AlipayIsvConfigController();
        $config = $isvconfig->AlipayIsvConfig('1234');
        $merchanr_info = $isvconfig->alipay_auth_info('2018061205492993161', 0);


        //预授权

        $data = [
            'app_id' => $config->app_id,
            'rsa_private_key' => $config->rsa_private_key,
            'alipay_rsa_public_key' => $config->alipay_rsa_public_key,
            'alipay_gateway' => $config->alipay_gateway,
            'notify_url' => '',
            'app_auth_token' => $merchanr_info->app_auth_token,
            'out_order_no' => time(),
            'out_request_no' => time(),
            'auth_code' => '280054382619209759',
            'order_title' => '280000',
            'amount' => '0.1',
            'pay_timeout' => '5m',
            'payee_user_id' => $merchanr_info->alipay_user_id,
            'sys_service_provider_id' => $config->alipay_pid,
        ];
        //  $re = $obj->base_fund_freeze($data);
        // dd($re);


        //查询
        $data = [
            'app_id' => $config->app_id,
            'rsa_private_key' => $config->rsa_private_key,
            'alipay_rsa_public_key' => $config->alipay_rsa_public_key,
            'alipay_gateway' => $config->alipay_gateway,
            'notify_url' => '',
            'app_auth_token' => $merchanr_info->app_auth_token,
            'out_order_no' => '1554088070',
            'operation_id' => '20190401973096436602',
            'auth_no' => '2019040110002001660278626497',
            'out_request_no' => '1554088070',

        ];
        // $re = $obj->base_fund_order_query($data);

        // dd($re);


        //押金转支付

        $data = [
            'app_id' => $config->app_id,
            'rsa_private_key' => $config->rsa_private_key,
            'alipay_rsa_public_key' => $config->alipay_rsa_public_key,
            'alipay_gateway' => $config->alipay_gateway,
            'notify_url' => '',
            'app_auth_token' => $merchanr_info->app_auth_token,
            'out_trade_no' => time(),
            'total_amount' => '0.01',
            'auth_no' => '2019040110002001660277531401',
            'buyer_id' => '2088802043117668',
            'seller_id' => $merchanr_info->alipay_user_id,

        ];

        //  $re = $obj->base_fund_pay($data);

        //  dd($re);


        //支付查询
        $data = [
            'app_id' => $config->app_id,
            'rsa_private_key' => $config->rsa_private_key,
            'alipay_rsa_public_key' => $config->alipay_rsa_public_key,
            'alipay_gateway' => $config->alipay_gateway,
            'notify_url' => '',
            'app_auth_token' => $merchanr_info->app_auth_token,
            'out_trade_no' => '1554091317',

        ];

        // $re = $obj->base_fund_pay_query($data);
        // dd($re);


        //预授权撤销
        $data = [
            'app_id' => $config->app_id,
            'rsa_private_key' => $config->rsa_private_key,
            'alipay_rsa_public_key' => $config->alipay_rsa_public_key,
            'alipay_gateway' => $config->alipay_gateway,
            'app_auth_token' => $merchanr_info->app_auth_token,
            'out_order_no' => '1554098717',
            'operation_id' => '20190401981956396602',
            'auth_no' => '2019040110002001660279622317',
            'out_request_no' => '1554098717',
            'remark' => '预授权撤销'

        ];
        $re = $obj->base_fund_cancel($data);
        dd($re);


        //队列
        $a = dispatch(new \App\Jobs\StoreDayMonthOrder($data['store_id'], $data['user_id'], $data['merchant_id'], $data['total_amount'], $data['ways_type'], $data['ways_source']));
        dd($a);

        $url = $request->url();
        $is_https = substr($url, 0, 5);

        if ($is_https != "https") {
            $url = $request->server();
            return redirect('https://' . $url['SERVER_NAME'] . $url['REQUEST_URI']);
        }
        dd($url);
        $server = "post-cn-v0h101b0n0g.mqtt.aliyuncs.com";
        $port = 1883;
        $username = "Signature|LTAIO0UpPBX6aao4|post-cn-v0h101b0n0g";
        $password = "pZ24w2mR1BQOSGm15Aqtq3ink7I=";
        $client_id = "GID_123@@@12345";

        $topic = 'xy123';
        $topic = $topic . "/p2p/" . $client_id;

        $content = json_encode([
            'message' => '微信到账' . rand(10, 100) . '元',
            'orderNum' => time(),
        ]);
        $mqtt = new AliBaseController($server, $port, $client_id);
        if ($mqtt->connect(true, NULL, $username, $password)) {
            $mqtt->publish($topic, $content, 0);
            $mqtt->close();
            echo "send success!";
        } else {
            echo "Time out!\n";
        }
        dd($mqtt);


        $data = [
            'https://www.cmcczf.com/api/huiyuanbao/store_notify',
            'https://ss.tonlot.com/api/huiyuanbao/store_notify'
        ];
        try {
            foreach ($data as $k => $v) {
                $url = $v;
                $return = Tools::curl(json_encode([]), $url);
                return $return;
            }

        } catch (\Exception $exception) {

        }

        $server = $request->server();
        dd($server['SERVER_NAME']);
        $data = [
            'store_id' => '2018061205492993161',
            'config_id' => '1234',
            'sign_url' => 'https://www.baidu.com',

        ];
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
            $a = $app->customer_service->message($message)->to($openId)->send();


        }


        //获取平台证书
        $obj = new WxBaseController();
        $url = 'https://api.mch.weixin.qq.com/applyment/micro/getstate';
        $key = 'jsjsdjsdajsda8988hjhj89727321hdh';
        //公共配置
        $config = [
            "version" => "1.0",
            "mch_id" => '1421410702',//$options['payment']['merchant_id'],
            "sign_type" => 'HMAC-SHA256',
            "nonce_str" => '' . time() . '',
            "applyment_id" => "2000002124264342",
        ];

        $config['sign'] = $obj->MakeSign($config, $key, 'HMAC-SHA256');
        //不参与签名
        $config['sslCertPath'] = public_path() . '/upload/images/15511587733660.pem';
        $config['sslKeyPath'] = public_path() . '/upload/images/15511588002322.pem';

        $xml = $obj->ToXml($config);
        $re_data = $obj::postXmlCurl($config, $xml, $url, $useCert = true, $second = 30);
        $re_data = $obj::xml_to_array($re_data);

        dd(json_decode($re_data['audit_detail'], true)['audit_detail'][0]['reject_reason']);

        $config = [
            'aid' => '8a179b8c67bcf908016938901d3628ae',//应用ID
            'key' => 'shudh|m2|20190301',//密钥
            'api_id' => array('PAYING' => 'eb_trans@agent_for_paying', 'RESULT' => 'eb_trans@get_order_deal_result'),//接口ID
            'debug' => 'false',//是否调试模式
            'url' => 'https://api.gomepay.com/CoreServlet',//10步模式接口地址
            'mode' => '0',
            'url_ac' => 'https://api.gomepay.com/CoreServlet',//url_ac：4步模式的M2服务地址，4步模式时必填项。
            'url_ac_token' => 'https://api.gomepay.com/access_token',//url_ac_token：4步模式的得到访问令牌的M2服务地址，4步模式时必填项。
            'max_token' => 2,//获取令牌最大次数
            'is_data_sign' => '1',//是否对数据包执行签名 1是  0否
            'memcache_open' => false,//是否使用memcache
            'memcached_server' => '127.0.0.1:11211',//memcache地址
        ];

        $data = [
            'req_no' => time(),
            'app_code' => 'apc_02000004943',
            'app_version' => '1.0.0',
            'plat_form' => '01',
            'merchant_number' => 'SHID20190220190',//
            'order_number' => time(),
            'service_code' => 'sne_00000000002',
            'wallet_id' => '0100851892326086',
            'asset_id' => '260d65fb2d33445ba26c087c9a556902',
            'business_type' => '1',
            'money_model' => '1',
            'source' => '0',
            'password_type' => '02',
            'encrypt_type' => '02',
            'pay_password' => md5('123456'),
            'customer_type' => '01',
            'customer_name' => '邵良斌',
            'account_number' => '6214625121000227264',
            'issue_bank_name' => '广发银行',
            'currency' => 'CNY',
            'amount' => '0.01',
            'async_notification_addr' => url('/api/dfpay/pay_notify'),
            'memo' => '你好',

        ];
        $obj = new \App\Api\Controllers\DfPay\BaseController($config);
        $data = $obj->url_data('PAYING', $data, "POST");

        $res = json_decode($data, true);

        dd($res);


        //获取平台证书
        $obj = new WxBaseController();
        $url = 'https://api.mch.weixin.qq.com/risk/getcertficates';
        $key = 'jsjsdjsdajsda8988hjhj89727321hdh';
        //公共配置
        $config = [
            "mch_id" => '1421410702',//$options['payment']['merchant_id'],
            "sign_type" => 'HMAC-SHA256',
            "nonce_str" => '' . time() . '',
        ];

        $config['sign'] = $obj->MakeSign($config, $key, 'HMAC-SHA256');
        $xml = $obj->ToXml($config);
        $re_data = $obj::postXmlCurl($config, $xml, $url, $useCert = false, $second = 30);
        $re_data = $obj::xml_to_array($re_data);


        $encryptCertificate = json_decode($re_data['certificates'], true)['data'][0]['encrypt_certificate'];
        $ciphertext = base64_decode($encryptCertificate['ciphertext']);
        $associated_data = $encryptCertificate['associated_data'];
        $nonce = $encryptCertificate['nonce'];
        // sodium_crypto_aead_aes256gcm_decrypt >=7.2版本，去php.ini里面开启下libsodium扩展就可以，之前版本需要安装libsodium扩展，具体查看php.net（ps.使用这个函数对扩展的版本也有要求哦，扩展版本 >=1.08）
        $plaintext = sodium_crypto_aead_aes256gcm_decrypt($ciphertext, $associated_data, $nonce, $key);

        dd($plaintext);
        //图片上传
        $obj = new WxBaseController();
        $url = 'https://api.mch.weixin.qq.com/secapi/mch/uploadmedia';
        $key = 'jsjsdjsdajsda8988hjhj89727321hdh';
        $img = public_path() . '/123.jpg';
        $name = 'sd';
        //公共配置
        $data = [
            "media_hash" => md5_file($img),
            "mch_id" => '1421410702',
            "sign_type" => 'HMAC-SHA256',
        ];
        $data['sign'] = $obj->MakeSign($data, $key, 'HMAC-SHA256');

        //不参与签名
        $data['sslCertPath'] = public_path() . '/upload/images/15424337206713.pem';
        $data['sslKeyPath'] = public_path() . '/upload/images/15424337285856.pem';
        $data["media"] = new \CURLFile($img);

        $header = [
            "content-type:multipart/form-data",
        ];
        $re_data = $this->httpsRequest($url, $data, $header, true);
        $re_data = $obj::xml_to_array($re_data);
        dd($re_data);


        dd($re_data);
        $xml = $obj->ToXml($config);
        $re_data = $obj::postXmlCurl($config, $xml, $url, $useCert = true, $second = 30);


        $server = "post-cn-v0h101b0n0g.mqtt.aliyuncs.com";
        $port = 1883;
        $username = "Signature|LTAIO0UpPBX6aao4|post-cn-v0h101b0n0g";
        $password = "pZ24w2mR1BQOSGm15Aqtq3ink7I=";
        $client_id = "GID_123@@@12345";

        $topic = 'xy123';
        $content = json_encode([
            'message' => '微信到账' . rand(10, 100) . '元',
            'orderNum' => time(),
        ]);
        $mqtt = new AliBaseController($server, $port, $client_id);
        if ($mqtt->connect(true, NULL, $username, $password)) {
            $mqtt->publish($topic, $content, 0);
            $mqtt->close();
            echo "send success!";
        } else {
            echo "Time out!\n";
        }
        dd($mqtt);


        $str = 'GID_123@@@12345';
        $key = '3N9ttMilrbNC9EpuT9MZ8I4meI6bvo';
        $str = mb_convert_encoding($str, "UTF-8");
        $password = base64_encode(hash_hmac("sha1", $str, $key, true));

        dd($password);


        $SecretKey = '3N9ttMilrbNC9EpuT9MZ8I4meI6bvo';
        $str = hash_hmac("sha1", 'GID_123@@@12345', $SecretKey);
        $signature = $this->getSignature($str, $SecretKey);

        dd($signature);


        $device_id = $request->get('device_id');
        $str = hash_hmac("sha1", 'GID_123@@@12345', '3N9ttMilrbNC9EpuT9MZ8I4meI6bvo');
        dd(base64_encode($str));

        $phone = $request->get('phone', '');
        $merchant = Merchant::where('phone', $phone)->first();
        if ($merchant) {
            $merchant_id = $merchant->id;
            $merchant->delete();
            $MerchantStore = MerchantStore::where('merchant_id', $merchant_id)->first();

            if ($MerchantStore) {
                $store_id = $MerchantStore->store_id;
                $MerchantStore->delete();
                Store::where('store_id', $store_id)->delete();
                StoreImg::where('store_id', $store_id)->delete();
                StoreBank::where('store_id', $store_id)->delete();
            }
        }
        dd('处理完毕');


        $url = url("/api/devicepay/refund");//请求地址
        $data = [
            'out_trade_no' => 'ali_scan20190126135054008674528',
            'config_id' => '1234',
            'merchant_id' => '1',
            'merchant_name' => '1',
            'code' => '134537714563254362',
            'refund_amount' => '0.01',
            'shop_price' => '1',
            'remark' => '',
            'device_id' => '5100010525',
            'device_type' => 's_bp_sl51',
            'shop_name' => '1',
            'shop_desc' => '1',
            'time_start' => '2019-01-01',
            'time_end' => '2019-01-08',
            'store_id' => '2018061205492993161',
            'other_no' => '20190109112523000019',
            'total_amount' => '0.01',
            'ways_source' => 'weixin',
            'ways_type' => '2000',

        ];

        $obj = new \App\Api\Controllers\Newland\BaseController();
        $string = $obj->getSignContent($data);
        $string = $string . '&key=88888888';
        $data['sign'] = md5($string);

        $return = Tools::curl(json_encode($data), $url);

        return $return;

        //下个版本去掉

        $QrList = QrList::all();

        foreach ($QrList as $k => $v) {
            $cno = $v->cno;
            $count = QrListInfo::where('cno', $cno)
                ->where('code_type', 1)
                ->count('id');

            QrList::where('cno', $cno)->update([
                's_num' => $count
            ]);
        }


        $data = $_SERVER['SERVER_NAME'];
        dd();
        $obj = new \App\Api\Controllers\Ltf\PayController();

        $data = [
            'key' => 'f0329e22fb506a4e26ccb29b0a6c5af3',
            'code' => '287675899230961800',
            'appId' => 'EW_N5946005323',//EW_N5547359239
            'merchant_no' => 'EW_N4130797151',//EW_N6706819008
            'out_trade_no' => "" . time() . "",
            'request_url' => 'http://api.liantuofu.com/open/pay',
            'remark' => '',
            'device_id' => '5100010525',
            'shop_name' => '测试订单1',
            'shop_desc' => '1',
            'total_amount' => '0.01',
            'pay_type' => 'weixin',
            'notify_url' => '2000',
            'return_params' => '20190109112523000019',


        ];
        $re = $obj->scan_pay($data);
        dd($re);


        $url = url("/api/devicepay/GetRequestApiUrl");//请求地址
        $data = [
            'out_trade_no' => '',
            'config_id' => '1234',
            'merchant_id' => '1',
            'merchant_name' => '1',
            'code' => '134537714563254362',
            'refund_amount' => '0.01',
            'shop_price' => '1',
            'remark' => '',
            'device_id' => '5100010525',
            'device_type' => 's_bp_sl51',
            'shop_name' => '1',
            'shop_desc' => '1',
            'time_start' => '2019-01-01',
            'time_end' => '2019-01-08',
            'store_id' => '2018061205492993161',
            'other_no' => '20190109112523000019',
            'total_amount' => '0.01',
            'ways_source' => 'weixin',
            'ways_type' => '2000',

        ];

        $obj = new \App\Api\Controllers\Newland\BaseController();
        $string = $obj->getSignContent($data);
        $string = $string . '&key=88888888';
        $data['sign'] = md5($string);
        dd(json_encode($data));

        //绑定
        $url = "http://cloudspeaker.smartlinkall.com/bind.php?id=10001080&m=4&uid=123456&token=101245268154&seq=" . time();
        $data = Tools::curl_get($url);
        $return = json_decode($data, true);
        dd($return);
        var_dump($data);
        for ($i = 0; $i < 20; $i++) {
            echo $sql = time() . $i;
            // sleep(1);
            $url = 'http://cloudspeaker.smartlinkall.com/add.php?token=101245268154&id=10001080&m=1&uid=12345&seq=' . $sql . '&price=100000&pt=1&vol=100';
            $data = Tools::curl_get($url);
            var_dump($data);
        }
        dd(1);


        $return = $this->curlPost_java(json_encode($in_data), 'http://ex.grypay.com:7632/jdjhPay/notify?pay_status=1');
        dd($return);


        $phone = $request->get('phone', '');
        $merchant = Merchant::where('phone', $phone)->first();
        if ($merchant) {
            $merchant_id = $merchant->id;
            $merchant->delete();
            $MerchantStore = MerchantStore::where('merchant_id', $merchant_id)->first();

            if ($MerchantStore) {
                $store_id = $MerchantStore->store_id;
                $MerchantStore->delete();
                Store::where('store_id', $store_id)->delete();
                StoreImg::where('store_id', $store_id)->delete();
                StoreBank::where('store_id', $store_id)->delete();
            }
        }
        dd('处理完毕');


        $NewLandStore = NewLandStore::where('store_id', '2018061205492993161')->first();

        if (!$NewLandStore) {
            return $return_err = json_encode([
                'status' => 2,
                'message' => '门店不存在',
            ]);
        }
        $config = NewLandConfig::where('config_id', '1234')->first();
        $aop = new \App\Common\XingPOS\Aop();
        $aop->key = $config->nl_key;
        $aop->version = 'V1.0.1';
        $aop->org_no = $config->org_no;
        $aop->url = 'https://gateway.starpos.com.cn/emercapp';//测试地址

        $sign_data = [
            'mercId' => $NewLandStore->nl_mercId,
        ];

        $request_obj = new  \App\Common\XingPOS\Request\XingStoreShangHuChaXun();
        $request_obj->setBizContent($sign_data);
        $return = $aop->executeStore($request_obj);
        dd($return);


        $obj = new \App\Api\Controllers\Huiyuanbao\PayController();


        $data = [
            'mid' => 'LMFPAY100002127',
            'userId' => "",
            'app_id' => '',
            'total_amount' => '0.1',
            'remark' => '',
            'return_params' => '',
            'device_id' => '',
            'shop_name' => '',
            'out_trade_no' => time(),
            'nonceStr' => time(),
            'orgNo' => '01001246',
            'request_url' => 'https://xpay.hybunion.cn/LmfPayFrontService/hyb/jsapiPay',
            'md_key' => 'test00001u9eiejfsdflajlghsjfjaljfdljfalsls',
            'totalFee' => '0.1',
            'outTradeNo' => 'aliQR20181225084306633211605',
            'payChannel' => 'wx',
            'notify_url' => 'https://pay.umxnt.com/api/huiyuanbao/notify_url',
            'callBackUrl' => 'https://pay.umxnt.com',
            'appid' => '2018040302496510',
            'userid' => '2088802043117668',

        ];
        $re = $obj->qr_submit($data);
        dd($re);

        //和融通交易接口

        $obj = new \App\Api\Controllers\Huiyuanbao\BaseController();
        $obj->md_key = "test00001u9eiejfsdflajlghsjfjaljfdljfalsls";
        $url = $obj->refund_query_url;//反扫接口


        $data = [
            'mid' => 'LMFPAY100002127',
            'refundtransactionId' => 'RF20181225065956185433',
            'nonceStr' => time(),
            'orgNo' => '01001246',

        ];
        $re = $obj->execute($data, $url);
        dd($re);

        $time = '2018-2-1';
        return date('Y-m-d', strtotime($time));

        $user = User::where('id', 1)->first();
        $permissions = $user->getAllPermissions();
        $data = [];
        foreach ($permissions as $k => $v) {
            $data[$k]['name'] = $v->name;
        }
        dd($data);

        $img_url = "https://pay.umxnt.com/upload/images/15325026971701.jpg";
        $img_url = "http://xiangyongimg.oss-cn-beijing.aliyuncs.com/165356_328.jpg";
        $img_url = explode('/', $img_url);
        $img_url = end($img_url);
        $img = public_path() . '/upload/images/' . $img_url;
        dd($img);

        $true = strpos($img_url, url(''));
        //本地服务器
        if ($true !== false) {
            $path = parse_url($img_url)['path'];
            $img = public_path() . $path;
        } else {
            $path = parse_url($img_url)['path'];
            $img = public_path() . '/upload/images' . $path;
        }
        dd($img);

        $phone = $request->get('phone', '');
        $merchant = Merchant::where('phone', $phone)->first();
        if ($merchant) {
            $merchant_id = $merchant->id;
            $merchant->delete();
            $MerchantStore = MerchantStore::where('merchant_id', $merchant_id)->first();

            if ($MerchantStore) {
                $store_id = $MerchantStore->store_id;
                $MerchantStore->delete();
                Store::where('store_id', $store_id)->delete();
                StoreImg::where('store_id', $store_id)->delete();
                StoreBank::where('store_id', $store_id)->delete();
            }
        }
        dd('处理完毕');


        $data = [
            'opsys' => '1',
            'characterset' => '00',
            'orgno' => '609',
            'mercid' => '800361000003595',
            'trmno' => '95526608',
            'tradeno' => time(),
            'trmtyp' => 'T',
            'txntime' => date('Ymdhis', time()),//设备交易时间
            'signtype' => 'MD5',
            'version' => 'V1.0.0',
            'amount' => '1',
            'total_amount' => '1',
            'paychannel' => 'ALIPAY',
            'paysuccurl' => url('')

        ];
        ksort($data);
        $stringToBeSigned = "";
        $i = 0;
        foreach ($data as $k => $v) {
            $stringToBeSigned .= $v;
            $i++;

        }
        $url = 'https://gateway.starpos.com.cn/sysmng/bhpspos4/5533020.do';
        unset ($k, $v);
        $key = '5201454E2B59503B49BEBA865385C457';
        $stringToBeSigned = $stringToBeSigned . $key;
        $data['signvalue'] = md5($stringToBeSigned);

        $i1 = 0;
        $stringToBeSigned1 = '';
        foreach ($data as $k => $v) {
            // 转换成目标字符集
            $v = $this->characet($v, $this->postCharset);

            if ($i1 == 0) {
                $stringToBeSigned1 .= "$k" . "=" . "$v";
            } else {
                $stringToBeSigned1 .= "&" . "$k" . "=" . "$v";
            }
            $i1++;
        }


        unset ($k, $v);
        $url = $url . '?' . $stringToBeSigned1;


        dd($url);
        $re = Tools::curl_get($url);
        dd($re);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $postBodyString = "";
        $encodeArray = Array();

        $postMultipart = false;

        if (is_array($data) && 0 < count($data)) {


            foreach ($data as $k => $v) {
                if ("@" != substr($v, 0, 1)) //判断是不是文件上传
                {

                    $postBodyString .= "$k=" . urlencode($this->characet($v, $this->postCharset)) . "&";
                    $encodeArray[$k] = $this->characet($v, $this->postCharset);
                } else //文件上传用multipart/form-data，否则用www-form-urlencoded
                {
                    $postMultipart = true;
                    $encodeArray[$k] = $v;

                }

            }
            unset ($k, $v);
            curl_setopt($ch, CURLOPT_POST, true);
            if ($postMultipart) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $encodeArray);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, -1));
            }
        }

        if ($postMultipart) {

            $headers = array('content-type: multipart/form-data;charset=' . "UTF-8" . ';boundary=' . $this->getMillisecond());
        } else {

            $headers = array('content-type: application/x-www-form-urlencoded;charset=' . "UTF-8");
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


        $reponse = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new \Exception(curl_error($ch), 0);
        } else {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $httpStatusCode) {
                throw new \Exception($reponse, $httpStatusCode);
            }
        }
        curl_close($ch);
        dd($reponse);


        $aop = new \App\Common\XingPOS\Aop();
        $aop->key = '9FF13E7726C4DFEB3BED750779F59711';

        $aop->op_sys = '3';//操作系统
        // $aop->character_set = '01';
        //  $aop->latitude = '0';//纬度
//  $aop->longitude = '0';//精度
        $aop->org_no = '11658';//机构号
        $aop->merc_id = '800290000007906';//商户号
        $aop->trm_no = 'XB006439';//设备号
        $aop->opr_id = '8972';//操作员
        $aop->trm_typ = 'T';//设备类型，P-智能 POS A- app 扫码 C-PC端  T-台牌扫码
        $aop->trade_no = time();//商户单号
        $aop->txn_time = date('Ymdhis', time());//设备交易时间
        $aop->add_field = 'V1.0.1';
        $aop->version = 'V1.0.0';


        //支付
        $data = [
            'amount' => '1',
            'total_amount' => '1',
            'payChannel' => 'WXPAY',
        ];

        $request_obj_pay = new  \App\Common\XingPOS\Request\XingPaySaoMaZhiFuKeHuZhuSao();
        $request_obj_pay->setBizContent($data);
        $return = $aop->execute($request_obj_pay);
        dd($return);


        $phone = $request->get('phone', '');
        $merchant = Merchant::where('phone', $phone)->first();
        if ($merchant) {
            $merchant_id = $merchant->id;
            $merchant->delete();
            $MerchantStore = MerchantStore::where('merchant_id', $merchant_id)->first();

            if ($MerchantStore) {
                $store_id = $MerchantStore->store_id;
                $MerchantStore->delete();
                Store::where('store_id', $store_id)->delete();
                StoreImg::where('store_id', $store_id)->delete();
                StoreBank::where('store_id', $store_id)->delete();
            }
        }
        dd('处理完毕');

        $aop = new \App\Common\XingPOS\Aop();
        $aop->key = '9FF13E7726C4DFEB3BED750779F59711';

        $aop->op_sys = '3';//操作系统
        // $aop->character_set = '01';
        //  $aop->latitude = '0';//纬度
//  $aop->longitude = '0';//精度
        $aop->org_no = '11658';//机构号
        $aop->merc_id = '800290000007906';//商户号
        $aop->trm_no = 'XB006439';//设备号
        $aop->opr_id = '8972';//操作员
        $aop->trm_typ = 'T';//设备类型，P-智能 POS A- app 扫码 C-PC端  T-台牌扫码
        $aop->trade_no = time();//商户单号
        $aop->txn_time = date('Ymdhis', time());//设备交易时间
        $aop->add_field = 'V1.0.1';
        $aop->version = 'V1.0.0';


        //支付
//        $data = [
//            'amount' => '1',
//            'total_amount' => '1',
//            'payChannel' => 'WXPAY',
//            'authCode' => '137348913686378847',
//        ];
//
//        $request_obj_pay = new  \App\Common\XingPOS\Request\XingPaySaoMaZhiFuShangHuZhuSao();
//        $request_obj_pay->setBizContent($data);
//        $return = $aop->execute($request_obj_pay);


        $request_obj_pay = new  \App\Common\XingPOS\Request\XingPaySaoMaZhiFuKeHuZhuSao();
        $request_obj_pay->setBizContent($data);
        $return = $aop->execute($request_obj_pay);


        $data = [
            'qryNo' => 'ali_scan20181212170804879970213',
        ];

        $request_obj_pay = new  \App\Common\XingPOS\Request\XingPayDingDanChaXun();
        $request_obj_pay->setBizContent($data);
        $return = $aop->execute($request_obj_pay);
        dd($return);

        // 发送给订阅号信息,创建socket,无sam队列
        $server = "mqtt-cn-v0h0vd45402.mqtt.aliyuncs.com";     // 服务代理地址(mqtt服务端地址)
        $port = 1883;                     // 通信端口
        $username = "LTAIO0UpPBX6aao4";                   // 用户名(如果需要)
        $password = "sy8+ZNZbFRN6qi0o9XF/Z7IJdRk=";                   // 密码(如果需要
        $client_id = "CID-xiangconsumer"; // 设置你的连接客户端id
        $mqtt = new Mqtt($server, $port, $client_id); //实例化MQTT类
        if ($a = $mqtt->connect(true, NULL, $username, $password)) {
            //如果创建链接成功
            $mqtt->publish("xiangyong", "setr=3xxxxxxxxx", 0);
            // 发送到 xxx3809293670ctr 的主题 一个信息 内容为 setr=3xxxxxxxxx Qos 为 0
            $mqtt->close();    //发送后关闭链接
        } else {
            echo "Time out!\n";
        }

        dd($a);
        dd($mqtt);


        $file_name = $request->get('id');
        $cno = QrList::where('id', $request->get('id'))
            ->select('cno')
            ->first()->cno;

        $data = QrListInfo::where('cno', $cno)->get();


        foreach ($data as $v) {
            $cout[] = [
                "code_url" => url('/qr?no=') . $v->code_number,
            ];
        }


        $objPHPExcel = new \PHPExcel();

        $first_sheet = $objPHPExcel->setActiveSheetIndex(0);

        $str = '';
        $line = 1;
        foreach ($cout as $ex_v) {
            $word = ord("A");
            $line++;
            foreach ($ex_v as $ex_value) {
                $column = chr($word) . $line;
                $first_sheet->setCellValue($column, $ex_value);
                $word++;
            }
        }

// 下载时的文件名
// $filename='易付生活数据导出';

// Redirect output to a client’s web browser (Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $file_name . '.xlsx"');
        header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit();


        $config = [
            'app_id' => 'wx672a40d48c45eef8',
            'secret' => '9fe5b109611434001d6234b0b9214c14',
        ];

        $app = Factory::officialAccount($config);
        $re = $app->template_message->send([
            'touser' => 'o1HzZvxctE9WLc271wa1ds3UM78s',
            'template_id' => '7mXIzw2Eqp9O0BqMdPWgZ7uIW-p6ZO1mtZbgG_V2Ahs',
            'url' => 'https://easywechat.org',
            'data' => [
                'keyword1' => '戴明康个人店铺',
                'keyword2' => '支付宝',
                'keyword3' => '100.00元',
                'keyword4' => '不要辣椒',
                'keyword5' => date('Y-m-d H:i:s', time()),

            ],
        ]);

        dd($re);
        for ($i = 0; $i < 100; $i++) {
            echo $sql = time() . $i;
            $url = 'http://cloudspeaker.smartlinkall.com/add.php?token=101245268154&id=10000281&m=1&uid=12345&seq=' . $sql . '&price=100000&pt=1&vol=100';
            $data = Tools::curl_get($url);
        }
        dd($data);
        $user = User::where('id', 1)->first();
        $data = $user->hasPermissionTo('1536025359');
        dd($data);

        $OBJ = new StoreController();
        $data1 = [
            'request_url' => 'https://psi.jd.com/merchant/status/queryMerchantKeys',
            'agentNo' => '110847567',
            'merchantNo' => '111209268',
            'serialNo' => "" . time() . "",
            'store_md_key' => '8data998mnwepxugnk03-2zirb',
            'store_des_key' => 'OAKe5dySFQL0EDhXwqj0wQQOaxDQHz5u',
        ];
        $re = $OBJ->store_keys($data1);
        dd($re);

        $order_money = '14.6';
        $order_money = floor($order_money * 100);
        $order_money /= 100;

        dd($order_money);

        //微信子商户支付配置接口
        $aop = new BaseController();
        $ao = $aop->aop();
        $ao->url = env("MY_BANK_request2");
        $ao->Function = "ant.mybank.merchantprod.merchant.addMerchantConfig";
        $data = [
            'MerchantId' => '226801000007402335143',
            'Path' => env('APP_URL') . '/api/mybank/weixin/',
            'OutTradeNo' => date('YmdHis') . time() . rand(10000, 99999),
        ];

        $re = $ao->Request($data);
        dd($re);


        // 发送给订阅号信息,创建socket,无sam队列
        $server = "mqtt-cn-v0h0vd45402.mqtt.aliyuncs.com";     // 服务代理地址(mqtt服务端地址)
        $port = 1883;                     // 通信端口
        $username = "LTAIO0UpPBX6aao4";                   // 用户名(如果需要)
        $password = "sy8+ZNZbFRN6qi0o9XF/Z7IJdRk=";                   // 密码(如果需要
        $client_id = "CID-xiangconsumer"; // 设置你的连接客户端id
        $mqtt = new Mqtt($server, $port, $client_id); //实例化MQTT类
        if ($a = $mqtt->connect(true, NULL, $username, $password)) {
            //如果创建链接成功
            $mqtt->publish("xiangyong", "setr=3xxxxxxxxx", 0);
            // 发送到 xxx3809293670ctr 的主题 一个信息 内容为 setr=3xxxxxxxxx Qos 为 0
            $mqtt->close();    //发送后关闭链接
        } else {
            echo "Time out!\n";
        }

        dd($a);
        dd($mqtt);


//iot
        $accessKey = 'LTAIO0UpPBX6aao4';
        $accessSecret = '3N9ttMilrbNC9EpuT9MZ8I4meI6bvo';
        $regionId = 'cn-hangzhou';
        $endpointName = 'cn-hangzhou';
        $product = 'Ons';
        $domain = 'ons.cn-hangzhou.aliyuncs.com';


        $iClientProfile = \DefaultProfile::getProfile($regionId, $accessKey, $accessSecret);
        $client = new \DefaultAcsClient($iClientProfile);


        $request = new ApplyDeviceWithNamesRequest();
        $response = $client->getAcsResponse($request);

        dd($response);

        $request = new RRpcRequest();
        $request->setProductKey('');
        $request->setDeviceName('');
        $request->setRequestBase64Byte(base64_encode('123'));

        $request->setTimeout(1000);
        $response = $client->getAcsResponse($request);

        dd($response);

        $addEndpoint = \DefaultProfile::addEndpoint($endpointName, $regionId, $product, $domain);
        $iClientProfile = \DefaultProfile::getProfile($regionId, $accessKey, $accessSecret);
        $client = new \DefaultAcsClient($iClientProfile);

        $request = new OnsTopicCreateRequest();
        $request->setAcceptFormat('json');
        $request->setTopic('xinagyong');
        $request->setQps(1000);
        $request->setRemark("test");
        $request->setStatus(0);
        $request->setOnsRegionId("daily");


        $response = $client->getAcsResponse($request);
        print_r($response);
        dd($response);

        $request = new PubRequest();
        $request->setProductKey("ID-xiangyong");
        $request->setMessageContent("aGVsbG93b3JsZA="); //hello world Base64 String.
        $request->setTopicFullName("/PID-xiangyong/deviceName/get"); //消息发送到的Topic全名.

        $response = $client->getAcsResponse($request);
        print_r($response);
        dd();


//腾讯mqc
        $queue_name = 'xiangyong1';
        $host = env('CMQ_QUEUE_HOST');
        $secret_key = env('CMQ_SECRET_KEY');
        $secret_id = env('CMQ_SECRET_ID');
        $my_account = new Account($host, $secret_id, $secret_key);
        $my_queue = $my_account->get_queue($queue_name);

//创建队列
        $queue_meta = new QueueMeta();
        $queue_meta->queueName = $queue_name;
        $queue_meta->pollingWaitSeconds = 10;
        $queue_meta->visibilityTimeout = 10;
        $queue_meta->maxMsgSize = 1024;
        $queue_meta->msgRetentionSeconds = 3600;

        try {
            $my_queue->create($queue_meta);
            echo $queue_meta;
        } catch (CMQExceptionBase $e) {
            echo $e;

        }
        dd();
//发消息

        $msg_body = "I am test message.";
        $msg = new Message($msg_body);

        try {
            $re_msg = $my_queue->send_message($msg);
        } catch (CMQExceptionBase $e) {
            echo $e;

        }

        dd($re_msg);


//1.通过code 拿到 openid  session_key
        $url = 'https://api.weixin.qq.com/sns/jscode2session';
        $appid = 'wx3e05af18a81047d5';
        $secret = 'ac1882ef0957429f4dea3ae2efe75d89';
        $js_code = '0019aFxj1pej3w0bZuBj1nQMxj19aFxO';
        $url = $url . '?appid=' . $appid . '&secret=' . $secret . '&js_code=' . $js_code . '&grant_type=authorization_code';
        $re = Tools::curl([], $url);
        $re_arr = json_decode($re, true);

        if (isset($re_arr['errcode'])) {
            return json_encode([
                'status' => 2,
                'message' => $re_arr['errmsg'],
            ]);
        }
        Log::info($re_arr);
//  $open_id = $re_arr['open_id'];
        $session_key = $re_arr['session_key'];


//2.解析数据

        $encryptedData = 'xKyWgu0tFIk9eZnVV3Q50x4+PCiamIWNsvbsKMoOpCh4qDLExefl0WE8EJ1ozEiARd4aSLM38q8cXZxSRIu9Ne1XKipJfJFCTLgy7qKF6H9Tui/XZv9mvRtFHxi4L8FEa8FMttzZhyxG+MvIUSE/y3pOiCt2dCMjAATRdoIohrXPdxjuEAx7Wf1YiADacMZr84oMcsgnzg2LuasBK2iARQ==';

        $iv = 'P++xE0Bt4ziI2f0JaBd06w==';

        $pc = new WXBizDataCrypt($appid, $session_key);
        $errCode = $pc->decryptData($encryptedData, $iv, $re_data);

        if ($errCode == 0) {
            print($re_data . "\n");
        } else {
            print($errCode . "\n");
        }


        $url = 'http://xiangyongimg.oss-cn-beijing.aliyuncs.com/images/163847_922.jpg';
        dd(parse_url($url)['path']);
        $true = strpos(parse_url($url)['path'], 'images');
//本地服务器
        if ($true) {
            $img = public_path() . parse_url($url)['path'];
        } else {
            $img = public_path() . '/images/' . parse_url($url)['path'];
        }


        $data = Merchant::where('id', 1)->first();
        if (!$data) {
            $this->status = 2;
            $this->message = '收银员不存在';
            return $this->format();
        }

        $data->pay_qr = '1111';

        return json_encode(['DTAT' => $data]);

//
        $data['store_id'] = '2018061205492993161';
        $data['phone'] = '18851186776';
        $data['email'] = 'dmk123@umxnt.com';
        $data['buildOrRepair'] = '0';//入驻标识0：新入驻，1：修改
        $data['store_md_key'] = '8data998mnwepxugnk03-2zirb';//入驻标识0：新入驻，1：修改
        $data['store_des_key'] = 'XdD4NyO2LG03DThegNm/bhmP6jR6zvg3';//入驻标识0：新入驻，1：修改
        $data['agentNo'] = '110770481';//
        $data['serialNo'] = "" . time() . "";
        $data['request_url'] = 'https://psi.jd.com/merchant/enterSingle';//入驻标识0：新入驻，1：修改
//$data['request_url'] = 'http://pay.umxnt.com/api/basequery/test1';//入驻标识0：新入驻，1：修改
        $OBJ = new StoreController();
// $data = $OBJ->open_store($data);

        $data1 = [
            'request_url' => 'https://psi.jd.com/merchant/applySingle',
            'agentNo' => '110770481',
            'serialNo' => "" . time() . "",
            'merchantNo' => '111203174',
            'store_md_key' => '8data998mnwepxugnk03-2zirb',
            'store_des_key' => 'XdD4NyO2LG03DThegNm/bhmP6jR6zvg3',
            'productId' => '35',
            'payToolId' => '885',
            'mfeeType' => '2',
            'mfee' => '0.3',
        ];
        $re = $OBJ->store_open_ways($data1);
        dd($re);


        dd($data);
        $out_trade_no = 'ali_scan20181023160323225104485';
        $config_id = '1234';
        $store_id = '2018061205492993161';
        $store_pid = 0;
        $config = new JdConfigController();
        $jd_config = $config->jd_config($config_id);
        if (!$jd_config) {
            return json_encode([
                'status' => 2,
                'message' => '京东配置不存在请检查配置'
            ]);
        }

        $jd_merchant = $config->jd_merchant($store_id, $store_pid);
        if (!$jd_merchant) {
            return json_encode([
                'status' => 2,
                'message' => '京东商户号不存在'
            ]);
        }
        $obj = new \App\Api\Controllers\Jd\PayController();
        $data = [];
        $data['out_trade_no'] = $out_trade_no;
        $data['request_url'] = $obj->order_query_url;//请求地址;
        $data['merchant_no'] = $jd_merchant->merchant_no;
        $data['md_key'] = $jd_merchant->md_key;//
        $data['des_key'] = $jd_merchant->des_key;//
        $data['systemId'] = $jd_config->systemId;//
        $return = $obj->order_query($data);

        dd($return);

        $obj = new PayController();
        $data = [];
        $data['out_trade_no'] = time();
        $data['code'] = '2088';
        $data['total_amount'] = '1';
        $data['remark'] = '备注';
        $data['device_id'] = "设备id";
        $data['shop_name'] = '购买商品名称';
        $data['notify_url'] = url('');//回调地址
        $data['request_url'] = 'http://testapipayx.jd.com/m/pay';//请求地址;
        $data['pay_type'] = 'WX';//;
        $data['merchant_no'] = '110826750';
        $data['return_params'] = '原样返回';//原样返回
        $data['md_key'] = 'WbSoWU3bLKtFQCRXvfHk9YLv98QXvFwv';//原样返回
        $data['des_key'] = 'E4Otx5GiMXMpDg0snXrQzacWwUrm+DQx';//原样返回
        $data['systemId'] = 'GUEST_HOTEL';//原样返回


//dd($data);
        dd($obj->scan_pay($data));

//银豹系统
        $aop = new PospalApiClientController();
        $url = 'https://area23-win.pospal.cn:443//pospal-api2/openapi/v1/productOpenApi/queryProductPages';
        $sendData = [
            'appId' => '3C1A7D6DC783490F60668216CA819A67',
            'categoryUid' => '1539927993559318201',
        ];
        $key = '839357223459871896';
        $re = $aop->doApiRequest($url, $sendData, $key);
        return $re;

        $data = [[
            'product_id' => '343389447295593113',
            'sku_id' => '20198821',
            'title' => '大杯',
            'img_url' => 'http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/f80d057a-02b4-4086-bc75-f0a899fbe661.png',
            'description' => '大杯大杯大杯大杯大杯大杯大杯大杯大杯大杯大杯大杯',
            'category_id' => '1536662759094630756',
            'product_code' => '1809111844263',
            'buy_price' => '800',
            'sell_price' => '1',
            'customer_price' => '900',
            'stock' => '99989',
            'is_cd' => '1',
        ], [
            'product_id' => '343389447295593113',
            'sku_id' => '20198823',
            'title' => '中杯',
            'img_url' => 'http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/f80d057a-02b4-4086-bc75-f0a899fbe661.png',
            'description' => '中杯中杯中杯中杯中杯中杯中杯中杯中杯中杯',
            'category_id' => '1536662759094630756',
            'product_code' => '1809111844263',
            'buy_price' => '900',
            'sell_price' => '600',
            'customer_price' => '700',
            'stock' => '99989',
            'is_cd' => '1',
        ], [
            'product_id' => '343389447295593113',
            'sku_id' => '20198824',
            'title' => '小杯',
            'img_url' => 'http://pospalstoreimg.area23.pospal.cn:80/productImages/3453302/f80d057a-02b4-4086-bc75-f0a899fbe661.png',
            'description' => '小杯小杯小杯小杯小杯小杯小杯小杯小杯小杯小杯',
            'category_id' => '1536662759094630756',
            'product_code' => '1809111844263',
            'buy_price' => '700',
            'sell_price' => '800',
            'customer_price' => '600',
            'stock' => '99989',
            'is_cd' => '1',
        ]];

        return json_encode($data);
        $in = DB::table("self_shops")->get();

        return json_encode($in);
//银豹系统
        $aop = new PospalApiClientController();
        $url = 'https://area23-win.pospal.cn:443//pospal-api2/openapi/v1/productOpenApi/queryProductPages';
        $sendData = [
            'appId' => '3C1A7D6DC783490F60668216CA819A67',
        ];
        $key = '839357223459871896';
        $re = $aop->doApiRequest($url, $sendData, $key);
        return $re;

        $data = [
            [

                'store_id' => '1',
                'product_id' => '20183297650',
                'title' => '购物袋小号',
                'img_url' => '',
                'description' => '购物袋小号',
                'category_id' => '201801',
                'product_code' => '',
                'buy_price' => '10',
                'sell_price' => '10',
                'customer_price' => '10',
                'stock' => '1020',
                'is_cd' => '1',
                'supplier' => '',
                'status' => '1',
                's_date' => '',
                'e_date' => '',
                'e_day' => '',//保质期
                'weight' => '',//g
                'qc' => '1',//客户端需要
                'discount' => json_encode([
                    [
                        's' => '2000',
                        'e' => '10000',
                        'r' => '500'
                    ],
                    [
                        's' => '10000',
                        'e' => '2000000000',
                        'r' => '1000'
                    ],

                ]),
                'created_at' => '2018-01-01',
                'updated_at' => '2018-01-01',
            ],
            [
                'store_id' => '1',
                'product_id' => '20189327651',
                'title' => '购物袋中号',
                'img_url' => '',
                'description' => '购物袋中号',
                'category_id' => '201802',
                'product_code' => '',
                'buy_price' => '20',
                'sell_price' => '20',
                'customer_price' => '20',
                'stock' => '1020',
                'is_cd' => '1',
                'supplier' => '',
                'status' => '1',
                's_date' => '',
                'e_date' => '',
                'e_day' => '',//保质期
                'weight' => '',//g
                'qc' => '1',//客户端需要
                'discount' => json_encode([
                    [
                        's' => '2000',
                        'e' => '10000',
                        'r' => '500'
                    ],
                    [
                        's' => '10000',
                        'e' => '2000000000',
                        'r' => '1000'
                    ],

                ]),
                'created_at' => '2018-01-01',
                'updated_at' => '2018-01-01',

            ],
        ];


        $in = DB::table("self_shops")->insert($data);

        dd($in);
//银豹系统
        $aop = new PospalApiClientController();
        $url = 'https://area23-win.pospal.cn:443//pospal-api2/openapi/v1/productOpenApi/queryProductPages';
        $sendData = [
            'appId' => '3C1A7D6DC783490F60668216CA819A67',
        ];
        $key = '839357223459871896';
        $re = $aop->doApiRequest($url, $sendData, $key);
        return $re;


        $data = strlen('长期');
        dd($data);
//youtu

// 设置APP 鉴权信息 请在http://open.youtu.qq.com 创建应用

        $appid = env('YOUTU_appid');
        $secretId = env('YOUTU_secretId');
        $secretKey = env('YOUTU_secretKey');
        $userid = env('YOUTU_userid');

        Conf::setAppInfo($appid, $secretId, $secretKey, $userid, conf::API_YOUTU_END_POINT);


        $uploadRet = YouTu::bizlicenseocrurl('http://xiangyongimg.oss-cn-beijing.aliyuncs.com/181042_117.jpg', 1);

        foreach ($uploadRet['items'] as $k => $v) {
            if (isset($v['item'])) {
                if ($v['item'] == '注册号' || $v['item'] == "营业期限") {
                    echo $v['item'];
                    if ($v['item'] == '注册号') {
                        //营业执照编号
                        $data_return['store_license_no'] = $v['itemstring'];
                    }
                    if ($v['item'] == '营业期限') {
                        $ex = explode('至', $v['itemstring']);
                        if (isset($ex[1]) && strlen($ex[1]) > 4) {
                            $str = $ex[1];
                            $str = str_replace("年", " - ", $str);
                            $str = str_replace("月", " - ", $str);
                            $str = str_replace("日", "", $str);
                            $data_return['store_license_time'] = $str;
                        }
                    }

                } else {
                    continue;
                }
            } else {
                break;
            }
        }

        dd($uploadRet);
        if (isset($uploadRet['items'][7]['itemstring'])) {
            $data = explode('至', $uploadRet['items'][7]['itemstring']);

            if (isset($data[1]) && count($data[1]) > 4) {
                $str = $data[1];
                $str = '2017年08月03日';
                $str = str_replace("年", " - ", $str);
                $str = str_replace("月", " - ", $str);
                $str = str_replace("日", "", $str);

                dd($str);
            }
        }

        if (isset($uploadRet['errorcode']) && $uploadRet['errorcode'] == 0) {
            if (isset($uploadRet['valid_date'])) {
                $valid_date = explode(" - ", $uploadRet['valid_date']);
                if (isset($valid_date[1])) {
                    $data_return['sfz_time'] = $valid_date[1];
                }
            }
        }

        dd($data_return);
//
        if (isset($uploadRet['items'][0]['itemstring'])) {

        }

        dd($uploadRet['items'][0]['itemstring']);

//新大陆

        $aop = new \App\Common\XingPOS\Aop();
        $aop->key = '9773BCF5BAC01078C9479E67919157B8';
        $aop->org_no = '518';
        $aop->url = 'http://sandbox.starpos.com.cn/emercapp';//测试地址


        $data = [
            "mercId" => "800301000000045",
            'log_no' => time(),
            'stoe_id' => time(),
            'imgTyp' => '1',
            'imgNm' => '15288005034651.jpg',
            'imgFile' => base64_encode(file_get_contents(public_path() . '/upload/images/store/15288005034651.png'))
        ];

        $request_obj = new  \App\Common\XingPOS\Request\XingStoreTuPianShangChuan();

        $request_obj->setBizContent($data);
        $return = $aop->executeStore($request_obj);

        dd($return);


        $aop = new \App\Common\XingPOS\Aop();
        $aop->key = '9FF13E7726C4DFEB3BED750779F59711';

        $aop->op_sys = '3';//操作系统
// $aop->character_set = '01';
//  $aop->latitude = '0';//纬度
//  $aop->longitude = '0';//精度
        $aop->org_no = '11658';//机构号
        $aop->merc_id = '800290000007906';//商户号
        $aop->trm_no = 'XB006439';//设备号
        $aop->opr_id = '8972';//操作员
// $aop->trm_typ = 'T';//设备类型，P-智能 POS A- app 扫码 C-PC端  T-台牌扫码
        $aop->trade_no = time();//商户单号
        $aop->txn_time = date('Ymdhis', time());//设备交易时间
//  $aop->add_field = 'V1.0.1';
        $aop->version = 'V1.0.0';


        $data = [
            'amount' => '1',
            'total_amount' => '1',
            'payChannel' => 'WXPAY',
        ];

        $request_obj_pay = new  \App\Common\XingPOS\Request\XingPaySaoMaZhiFuKeHuZhuSao();
        $request_obj_pay->setBizContent($data);
        $return = $aop->executeStore($request_obj_pay);
        dd($return);


        $config = JpushConfig::where('config_id', '1234')->first();
        $client = new Client($config->DevKey, $config->API_DevSecret);
        $push = $client->push();

        $RegistrationId = $request->get('RegistrationId', '100d855909429519028');

        $data = [
            [
                'type' => 'app',
                'app_id' => 'skxq',
                'title' => '收款100元',
                'url' => "",
                'alert' => "",
                'out_trade_no' => "wx_qr20180726224206187272"
            ],
            [
                'type' => 'app',
                'title' => '花呗分期收款100元',
                'app_id' => 'hbqfxq',
                'url' => "",
                'alert' => "",
                'out_trade_no' => "12321342313456q"
            ],
            [
                'type' => 'app',
                'title' => '余利宝下架产品通道',
                'app_id' => 'news',
                'url' => "",
                'alert' => "",
                'out_trade_no' => ""
            ],
            [
                'type' => 'url',
                'title' => '金砖金句｜第二个“金色十年，习主席指明金砖合作四大方向',
                'app_id' => '',
                'url' => "http://news.china.com.cn/2018-07/27/content_57837292.htm",
                'alert' => "",
                'out_trade_no' => ""
            ],
        ];
        foreach ($data as $k => $v) {
            $alert = $push->setPlatform(['ios', 'android'])
                ->iosNotification($v['title'], [
                    'extras' => [
                        'type' => $v['type'],
                        'app_id' => $v['app_id'],
                        'url' => $v['url'],
                        'out_trade_no' => $v['out_trade_no'],
                    ]
                ])
                ->androidNotification($v['title'], [
                    'extras' => [
                        'type' => $v['type'],
                        'app_id' => $v['app_id'],
                        'url' => $v['url'],
                        'out_trade_no' => $v['out_trade_no'],
                    ]
                ])
                ->addRegistrationId($RegistrationId)
                ->options(array(
                    'apns_production' => 0,
                ))
                //->message($v['title'])
                ->send();
        }


        dd('1');

        $data = array(
            'gmt_create' => '2018-07-26 17:22:27',
            'charset' => 'GBK',
            'seller_email' => 'ali12@umxnt.com',
            'subject' => '2018-开学缴费',
            'sign' => 'nV2+wWSTnsjS32tlYkN8MPYKEp39/GX72M7vBOMn0yjwpkOYx6frXPWFdRIWUHpPY9uwnF/g1enzKRJeNoFROu7Wk13Suy7fwReEj7y1V3Z+HZ8FzeuzXahDvmLAkoMSr2XbaAigrT1Fhj4ELKjXhd2k1gPEkZDviGGzNNM3N5g=',
            'buyer_id' => '2088802043117668',
            'invoice_amount' => '0.02',
            'notify_id' => '756179f50171594bca256ea32ef053al3h',
            'fund_bill_list' => '[{"amount":"0.02","fundChannel":"ALIPAYACCOUNT"}]',
            'notify_type' => 'trade_status_sync',
            'trade_status' => 'TRADE_SUCCESS',
            'receipt_amount' => '0.02',
            'buyer_pay_amount' => '0.02',
            'app_id' => '2016112803504802',
            'sign_type' => 'RSA',
            'seller_id' => '2088031790260468',
            'gmt_payment' => '2018-07-26 17:22:28',
            'notify_time' => '2018-07-26 17:36:03',
            'passback_params' => 'b3JkZXJObz01YjU5M2ZkYTUwZDM5ZjE4NjRkMGVjZWImaXN2T3JkZXJObz0yMDE4MDcyNjExMjY1OTkwMTMyJml0ZW1zPTEtMXwyLTE=',
            'version' => '1.0',
            'out_trade_no' => '5b5992ccd638753d31cdd096',
            'total_amount' => '0.02',
            'trade_no' => '2018072621001004660561007966',
            'auth_app_id' => '2018040302496510',
            'buyer_logon_id' => 'dmk***@ccmknt.com',
            'point_amount' => '0.00',
        );


//配置
        $isvconfig = new AlipayIsvConfigController();
        $config = $isvconfig->AlipayIsvConfig('2345', '01');


//1.接入参数初始化
        $aop = new AopClient();
        $aop->apiVersion = "2.0";
        $aop->appId = $config->app_id;
        $aop->rsaPrivateKey = $config->rsa_private_key;
        $aop->alipayrsaPublicKey = $config->alipay_rsa_public_key;
        $aop->signType = "RSA2";//升级算法
        $aop->gatewayUrl = $config->alipay_gateway;
        $aop->format = "json";
        $aop->charset = "UTF-8";


        $check = $aop->rsaCheckUmxnt($data, $config->alipay_rsa_public_key);
        dd($check);

    }


    public
    function test1(Request $request)
    {
        dd($request->all());
    }

    public function curl($url, $https = true, $method = 'get', $data = null, $BOUNDARY)
    {
        $ch = curl_init($url);
        $timeout = 30;

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('content-type: multipart/form-data;charset=' . $this->postCharset . ';boundary=' . $BOUNDARY));
        curl_setopt($ch, CURLOPT_POST, 1);


        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

        if ($https === true) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        if ($method === 'post') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }

        $reponse = curl_exec($ch);

        if (curl_errno($ch)) {

            throw new \Exception(curl_error($ch), 0);

        } else {

            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (200 !== $httpStatusCode) {
                throw new \Exception($reponse, $httpStatusCode);
            }
        }

        curl_close($ch);
        // dump($reponse);
        return $reponse;

    }

    /*
       curl发送数据
   */
    public function curl1($data, $url)
    {
        //启动一个CURL会话
        $ch = curl_init();
        // 设置curl允许执行的最长秒数
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        // 获取的信息以文件流的形式返回，而不是直接输出。
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        //发送一个常规的POST请求。
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        //要传送的所有数据
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        // 执行操作
        $res = curl_exec($ch);
        $response = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        if ($res == NULL) {
            curl_close($ch);
            return false;
        } else if ($response != "200") {
            curl_close($ch);
            return false;
        }
        curl_close($ch);
        return $res;
    }

}