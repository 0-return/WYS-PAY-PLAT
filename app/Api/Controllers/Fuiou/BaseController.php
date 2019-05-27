<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/9/6
 * Time: 下午7:36
 */

namespace App\Api\Controllers\Fuiou;


use Illuminate\Support\Facades\Log;

class BaseController
{
    private $xml = null;
    // 表单提交字符集编码
    public $postCharset = "GBK2312";
    private $fileCharset = "GBK2312";

    //d0
    public $da_url = "http://www-1.fuiou.com:28090/wmp/wxMchntMng.fuiou?action=mchntOpenTZ";
    public $select_money = "https://fundwx.fuiou.com/queryWithdrawAmt";
    public $select_rate = "https://fundwx.fuiou.com/queryFeeAmt";
    public $out_money = "https://fundwx.fuiou.com/withdraw";

    function __construct()
    {
        $this->xml = new \XMLWriter();
    }


    public function send($data, $url)
    {
        //完整的xml格式
        $a = "<?xml version=\"1.0\" encoding=\"GBK\" standalone=\"yes\"?><xml>" . $this->toXml($data) . "</xml>";
        //$a = $this->ToXml_w($data);
        //经过两次urlencode()之后的字符串
        Log::info($a);

        $b = "req=" . urlencode(urlencode($a));
//通过curl的post方式发送接口请求
//返回的xml字符串
        $resultXml = URLdecode($this->SendDataByCurl($url, $b));
//将xml转化成对象

        $ob = simplexml_load_string($resultXml);
        $xmljson = json_encode($ob);//将对象转换个JSON
        $xmlarray = json_decode($xmljson, true);//将json转换成数组
        //输出结果
        return $xmlarray;

    }

    /**
     * 输出xml字符
     * @throws
     **/
    public function ToXml_w($data)
    {
        $xml = "<?xml version=\"1.0\" encoding=\"GBK\" standalone=\"yes\"?><xml>";
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

    //数组转xml
    function toXml($data, $eIsArray = FALSE)
    {
        if (!$eIsArray) {
            $this->xml->openMemory();
        }
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $this->xml->startElement($key);
                $this->toXml($value, TRUE);
                $this->xml->endElement();
                continue;
            }
            $this->xml->writeElement($key, $value);
        }
        if (!$eIsArray) {
            $this->xml->endElement();
            return $this->xml->outputMemory(true);
        }
    }

    //签名加密流程
    public function sign($data, $pem, $type = "rsa")
    {

        if ($type == "rsa") {
            //读取密钥文件
            // $pem = file_get_contents(dirname(__FILE__) . '/keypem.pem');
            //获取私钥
            $pkeyid = openssl_pkey_get_private($pem);
            //MD5WithRSA私钥加密
            openssl_sign($data, $sign, $pkeyid, OPENSSL_ALGO_MD5);
            //返回base64加密之后的数据
            $t = base64_encode($sign);

            //解密-1:error验证错误 1:correct验证成功 0:incorrect验证失败
//        $pub_pem='-----BEGIN PUBLIC KEY-----
//MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCYAMw/HxLwR0E8sVBHivet5o84jFhu58aYvqQzHbVompHOsVYYW2oqS2h6OMFSPdgNsK96bRkNf2LAEhB5t5tsBjqU9r629i5/0u5c9UoY0ymk/FOqyoAnaUDR1Li4QUJaSXq9pnGBMxv5xs3MmpTgoFwv+gskoiQliZj8keOWyQIDAQAB
//-----END PUBLIC KEY-----';
//         $pubkey = openssl_pkey_get_public($pub_pem);
//        $ok = openssl_verify($data,base64_decode($t),$pubkey,OPENSSL_ALGO_MD5);
//         var_dump($ok);
            return $t;
        }


        if ($type == "md5") {
            $str = $data . '&key=' . $pem;
            $t = md5($str);
            return $t;
        }


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

    //通过curl模拟post的请求；
    function SendDataByCurl($url, $data)
    {
        //对空格进行转义

        $url = str_replace(' ', '+', $url);
        $ch = curl_init();
        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, "$url");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3); //定义超时3秒钟
        // POST数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // 把post的变量加上
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);  //所需传的数组用http_bulid_query()函数处理一下，就ok了
        //执行并获取url地址的内容
        $output = curl_exec($ch);
        $errorCode = curl_errno($ch);
        //释放curl句柄
        curl_close($ch);
        if (0 !== $errorCode) {
            return false;
        }
        return $output;
    }

    //参数拼接
    public function getSignContent($params)
    {
        ksort($params);
        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            $v = $this->characet($v, $this->postCharset);

            if ($i == 0) {
                $stringToBeSigned .= "$k" . "=" . "$v";
            } else {
                $stringToBeSigned .= "&" . "$k" . "=" . "$v";
            }
            $i++;
        }

        unset ($k, $v);
        return $stringToBeSigned;
    }

    //参数拼接 去除空
    //参数拼接
    public function getSignContentNONULL($params)
    {
        ksort($params);
        $stringToBeSigned = "";
        $i = 0;
        foreach ($params as $k => $v) {
            if (false === $this->checkEmpty($v) && "@" != substr($v, 0, 1)) {
                // 转换成目标字符集
                //  $v=iconv('utf8','gb2312',$v);
                // 转换成目标字符集
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

}