<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/11/6
 * Time: 3:34 PM
 */

namespace App\Api\Controllers\DevicePay;


class WxBaseController
{


    // 表单提交字符集编码
    public $postCharset = "UTF-8";
    private $fileCharset = "UTF-8";

    //初始化接口
    public $get_wxpayface_authinfo_url = 'https://payapp.weixin.qq.com/face/get_wxpayface_authinfo';
    //刷脸支付接口
    public $facepay_url = 'https://api.mch.weixin.qq.com/pay/facepay';

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
        if (isset($params['media'])) {
            $params['media'] = '';
        }
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


}