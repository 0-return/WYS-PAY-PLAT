<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2017/9/19
 * Time: 下午7:40
 */

namespace App\Api\Controllers\MyBank;


use App\Api\Controllers\Config\MyBankConfigController;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use MyBank\AopClient;

class BaseController extends \App\Api\Controllers\BaseController
{


    public $privateKey;
    public $gatewayUrl;
    public $postCharset = '';
    public $fileCharset = '';
    public $appId;
    public $gatewayUrl_photo;


    public function aop($config_id = '1234')
    {

        $config_id = '1234';
        $mbconfig = new MyBankConfigController();
        $MyBankConfig = $mbconfig->MyBankConfig($config_id);
        $aop = new AopClient();
        $aop->Appid = $MyBankConfig->Appid;
        $aop->IsvOrgId = $MyBankConfig->IsvOrgId;
        $aop->ReqMsgId = date('Ymdhis', time()) . rand(10000, 99999);
        $aop->partner_private_key = $MyBankConfig->partner_private_key;
        $aop->mybank_public_key = $MyBankConfig->mybank_public_key;
        $aop->ali_pid = $MyBankConfig->ali_pid;
        return $aop;
    }

    protected function getMillisecond()
    {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);

    }

    public function exec($data, $picName, $filename)
    {
        if ($this->checkEmpty($this->postCharset)) {
            $this->postCharset = "UTF-8";
        }
        $this->fileCharset = mb_detect_encoding($data['AppId'], "UTF-8,GBK");
        //  如果两者编码不一致，会出现签名验签或者乱码
        if (strcasecmp($this->fileCharset, $this->postCharset)) {
            // writeLog("本地文件字符集编码与表单提交编码不一致，请务必设置成一样，属性名分别为postCharset!");
            throw new \Exception("文件编码：[" . $this->fileCharset . "] 与表单提交编码：[" . $this->postCharset . "]两者不一致!");
        }

        // 每个post参数之间的分隔。随意设定，只要不会和其他的字符串重复即可。
        $BOUNDARY = "----" . $this->getMillisecond();
        $contentBody = "--" . $BOUNDARY;
        // 尾
        $endBoundary = "\r\n--" . $BOUNDARY . "--\r\n";
        $body = $contentBody;

        foreach ($data as $k => $v) {
            if ($k != "Picture") {
                $body .= "\r\n";
                $body .= "Content-Disposition: form-data; name=\"";
                $body .= $k . "\"";
                $body .= "\r\n\r\n";
                $body .= $v;
                $body .= "\r\n";
                $body .= $contentBody;
            } else {
                $body .= "\r\n";
                $body .= "Content-Disposition:form-data; name=\"";
                $body .= "$k" . "\";";
                $body .= " filename=";
                $body .= "\"$picName\"";
                $body .= "\r\n";
                $body .= "Content-Type:application/octet-stream";
                $body .= "\r\n";
                //$body.=$v;
                $body .= "\r\n";
            }
        }
        $body .= $endBoundary;

        $requestUrl = $this->gatewayUrl_photo;

        try {
            $resp = $this->curl($requestUrl, $https = true, $method = 'post', $body, $BOUNDARY);
            // dump($resp);
        } catch (\Exception $e) {
            dd($e);
        }

        //发起HTTP请求
        //解析AOP返回结果
        $respWellFormed = false;
        // 将返回结果转换本地文件编码
        $respObject = iconv($this->postCharset, $this->fileCharset . "//IGNORE", $resp);

        return $respObject;

    }


    function curl($url, $https = true, $method = 'get', $data = null, $BOUNDARY)
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


    function sign($data, $rsaPrivateKeyFilePath, $rsaPrivateKey, $signType = "RSA")
    {
        if ($this->checkEmpty($rsaPrivateKeyFilePath)) {
            $priKey = $rsaPrivateKey;
            $res = "-----BEGIN RSA PRIVATE KEY-----\n" .
                wordwrap($priKey, 64, "\n", true) .
                "\n-----END RSA PRIVATE KEY-----";
        } else {
            $priKey = file_get_contents($rsaPrivateKeyFilePath);
            $res = openssl_get_privatekey($priKey);
        }

        ($res) or die('您使用的私钥格式错误，请检查RSA私钥配置');

        if ("RSA2" == $signType) {
            openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256);
        } else {
            openssl_sign($data, $sign, $res);
        }

        if (!$this->checkEmpty($rsaPrivateKeyFilePath)) {
            openssl_free_key($res);
        }
        $sign = base64_encode($sign);
        return $sign;
    }

    function checkEmpty($value)
    {
        if (!isset($value))
            return true;
        if ($value === null)
            return true;
        if (trim($value) === "")
            return true;

        return false;
    }


    function getOrderSignContent($params, $postCharset)
    {
        ksort($params);
        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            // if (false === checkEmpty($v) && "@" != substr($v, 0, 1)) {
            if (!empty($v) && "@" != substr($v, 0, 1)) {
                // 转换成目标字符集
                $v = $this->characet($v, $postCharset);
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


    function characet($data, $targetCharset)
    {
        if (!empty($data)) {
            $fileType = "UTF-8";
            if (strcasecmp($fileType, $targetCharset) != 0) {
                $data = mb_convert_encoding($data, $targetCharset, $fileType);
            }
        }
        return $data;
    }


}