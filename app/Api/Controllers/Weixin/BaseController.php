<?php
/**
 * Created by PhpStorm.
 * User: dmk
 * Date: 2017/1/11
 * Time: 14:57
 */

namespace App\Api\Controllers\Weixin;


use App\Api\Controllers\Config\WeixinConfigController;
use App\Models\WeixinConfig;

class BaseController extends \App\Api\Controllers\BaseController
{


    // 表单提交字符集编码
    public $postCharset = "UTF-8";
    private $fileCharset = "UTF-8";

    /**
     * 生成签名
     * @param
     * @param bool $needSignType 是否需要补signtype
     * @return
     */
    public function MakeSign($data, $key, $sign_type = 'MD5')
    {

        //签名步骤一：按字典序排序参数
        $string = $this->getSignContent($data);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" . $key;
        //签名步骤三：MD5加密
        if ($sign_type == "MD5") {
            $string = md5($string);
        } else if ($sign_type == "HMAC-SHA256") {
            $string = hash_hmac("sha256", $string, $key);
        } else {
            die('类型不支持');
        }
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }


    /**
     * 以post方式提交xml到对应的接口url
     *
     * @param  $config
     * @param string $xml 需要post的xml数据
     * @param string $url url
     * @param bool $useCert 是否需要证书，默认不需要
     * @param int $second url执行超时时间，默认30s
     * @throws
     */
    public static function postXmlCurl($config, $xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        $curlVersion = curl_version();
        $ua = "WXPaySDK/3.0.9 (" . PHP_OS . ") PHP/" . PHP_VERSION . " CURL/" . $curlVersion['version'] . " "
            . $config['mch_id'];

        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);

        $proxyHost = "0.0.0.0";
        $proxyPort = 0;

        //如果有配置代理这里就设置代理
        if ($proxyHost != "0.0.0.0" && $proxyPort != 0) {
            curl_setopt($ch, CURLOPT_PROXY, $proxyHost);
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxyPort);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);//严格校验
        curl_setopt($ch, CURLOPT_USERAGENT, $ua);
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if ($useCert == true) {
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            //证书文件请放入服务器的非web目录下
            $sslCertPath = $config['sslCertPath'];
            $sslKeyPath = $config['sslKeyPath'];
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLCERT, $sslCertPath);
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLKEY, $sslKeyPath);
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            die($error);
        }
    }

    /**
     * 输出xml字符
     * @throws
     **/
    public function ToXml($data)
    {
        $xml = "<xml>";
        foreach ($data as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }

    //参数拼接
    public function getSignContent($params)
    {
        ksort($params);
        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {

                // 转换成目标字符集
                $v = $this->characet($v, $this->postCharset);

                if ($i == 0) {
                    $stringToBeSigned .= "$k" . "=" . "$v";
                } else {
                    $stringToBeSigned .= "&" . "$k" . "=" . "$v";
                }
                $i++;
            }
        }

        unset ($k, $v);
        return $stringToBeSigned;
    }


    /**
     * 转换字符集编码
     * @param $data
     * @param $targetCharset
     * @return string
     */
    function characet($data, $targetCharset)
    {


        if (!empty($data)) {
            $fileType = $this->fileCharset;
            if (strcasecmp($fileType, $targetCharset) != 0) {

                $data = mb_convert_encoding($data, $targetCharset);
                //				$data = iconv($fileType, $targetCharset.'//IGNORE', $data);
            }
        }


        return $data;
    }


    /**
     * 校验$value是否非空
     *  if not set ,return true;
     *    if is null , return true;
     **/
    protected function checkEmpty($value)
    {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;

        return false;
    }


    static function xml_to_array($xml)
    {
        if (!$xml) {
            die("xml数据异常！");
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }


    public function Options($config_id = '1234')
    {

        $config = new WeixinConfigController();
        $WeixinConfig = $config->weixin_config_obj($config_id);


        $options = [
            'app_id' => $WeixinConfig->app_id,
            'app_secret' => $WeixinConfig->app_secret,
            'payment' => [
                'merchant_id' => $WeixinConfig->wx_merchant_id,
                'key' => $WeixinConfig->key,
                'cert_path' => public_path() . $WeixinConfig->cert_path, // XXX: 绝对路径！！！！
                'key_path' => public_path() . $WeixinConfig->key_path,      // XXX: 绝对路径！！！！
                'notify_url' => $WeixinConfig->notify_url,       // 你也可以在下单时单独设置来想覆盖它
            ],
        ];

        return $options;
    }

    /*
       通讯数据加密
   */
    public static function encode($data)
    {
        return $data = base64_encode(json_encode((array)$data));
    }

    /*
        通讯数据解密
    */
    public static function decode($data)
    {
        return json_decode(base64_decode((string)$data), true);
    }


    //上传图片请求
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

    //region 敏感字段加密start
    public function getEncrypt($str, $public_key)
    {
        //$str是待加密字符串
        $encrypted = '';
        openssl_public_encrypt($str, $encrypted, $public_key);
        //base64编码
        $sign = base64_encode($encrypted);
        return $sign;
    }
    //endregion 敏感字段加密end
}