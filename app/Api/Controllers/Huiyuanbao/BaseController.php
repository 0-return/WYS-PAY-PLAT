<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/9/6
 * Time: 下午7:36
 */

namespace App\Api\Controllers\Huiyuanbao;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Jdjr\Sdk\BytesUtils;
use Jdjr\Sdk\TDESUtil;
use MyBank\Tools;

class BaseController
{
    // 表单提交字符集编码
    public $postCharset = "UTF-8";
    private $fileCharset = "UTF-8";


    //请求地址
    public $scan_url = 'https://xpay.hybunion.cn/LmfPayFrontService/hyb/micropay';//刷卡支付
    public $send_qr_url = 'https://xpay.hybunion.cn/LmfPayFrontService/hyb/alinative';//动态码
    public $order_query_url = 'https://xpay.hybunion.cn/LmfPayFrontService/hyb/orderquery';//订单查询
    public $refund_url = 'https://xpay.hybunion.cn/LmfPayFrontService/hyb/refund';//退款
    public $refund_query_url = 'https://xpay.hybunion.cn/LmfPayFrontService/hyb/refundorder';//退款查询
    public $jsapiPay_url = 'https://xpay.hybunion.cn/LmfPayFrontService/hyb/jsapiPay';//静态码1
    public $oriJsPay_url = 'https://xpay.hybunion.cn/LmfPayFrontService/hyb/oriJsPay';//静态码2


    //支付用到的
    public $md_key;


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

    /*
        curl发送数据
    */
    public function curl($data, $url)
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

    function curl_get($url)
    {

        $ch = curl_init();

        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//绕过ssl验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        //执行并获取HTML文档内容
        $output = curl_exec($ch);

        //释放curl句柄
        curl_close($ch);
        return $output;
    }


    //执行请求
    public function execute($data, $url)
    {

        //拼接参数
        $string = $this->getSignContent($data);
        $string = $string . '&key=' . $this->md_key;
        // dd($string);
        $sign = md5($string);
        $data['sign'] = strtoupper($sign);
        $xml = $this->arrayToXml($data);
        $return_xml = $this->curlXML($xml, $url);
        $return = $this->xml_to_array($return_xml);
        return $return;


    }

    public static function xml_to_array($xml)
    {
        if (!$xml) {
            return 0;
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }


    public function curlXML($data, $url)
    {
        //启动一个CURL会话
        $ch = curl_init();
        // 设置curl允许执行的最长秒数
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type:application/xml; charset=utf-8"));
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

    //数组转XML
    static public function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
        }
        return $xml . '</xml>';
    }

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


    public function curl_java($data, $Url)
    {

        $ch = curl_init($Url);
        //发送一个常规的POST请求。
        curl_setopt($ch, CURLOPT_POST, 1);
        //要传送的所有数据
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $result = curl_exec($ch);
        return $result;
    }

    public static function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {
        $ch = curl_init();
        $curlVersion = curl_version();
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
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
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


    public function img_content($img_url, $name)
    {
        try {
            $img_url = explode('/', $img_url);
            $img_url = end($img_url);
            $img = public_path() . '/upload/images/' . $img_url;
            if ($img) {
                //压缩图片
                $img_obj = \Intervention\Image\Facades\Image::make($img);
                $img_obj->resize(500, 400);
                $img = public_path() . '/upload/s_images/' . $img_url;
                $img_obj->save($img);
            }

            $content = new \CURLFile(realpath($img), '', $name);

            return $content;

        } catch (\Exception $exception) {
            return '';
        }
    }


    protected function getMillisecond()
    {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf(' % .0f', (floatval($s1) + floatval($s2)) * 1000);

    }


}