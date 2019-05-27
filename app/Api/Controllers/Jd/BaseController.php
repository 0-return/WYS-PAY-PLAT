<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/9/6
 * Time: 下午7:36
 */

namespace App\Api\Controllers\Jd;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Jdjr\Sdk\BytesUtils;
use Jdjr\Sdk\TDESUtil;

class BaseController
{
    // 表单提交字符集编码
    public $postCharset = "UTF-8";
    private $fileCharset = "UTF-8";


    //请求地址
    public $scan_url = 'http://apipayx.jd.com/m/pay';//刷卡支付
    public $send_qr_url = 'https://payx.jd.com/getScanUrl';//动态码
    public $order_query_url = 'http://apipayx.jd.com/m/querytrade';//订单查询
    public $refund_url = 'http://apipayx.jd.com/m/refund';//退款
    public $refund_query_url = 'http://apipayx.jd.com/m/queryrefund';//退款查询
    public $unified_url = 'https://apipayx.jd.com/m/unifiedOrder';//静态码

    //支付用到的
    public $md_key;
    public $des_key;
    public $systemId;

    //进件用到的
    public $store_md_key;
    public $store_des_key;


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


    public static function encrypt2HexStr($keys, $sourceData)
    {
        $source = array();

        // 元数据
        $source = BytesUtils::getBytes($sourceData);

        // 1.原数据byte长度
        $merchantData = count($source);
        // echo "原数据据:" . htmlspecialchars($sourceData) . "<br/>";
        // echo "原数据byte长度:" . $merchantData . "<br/>";
        // echo "原数据HEX表示:" . ByteUtils::bytesToHex ( $source ) . "<br/>";
        // 2.计算补位
        $x = ($merchantData + 4) % 8;
        $y = ($x == 0) ? 0 : (8 - $x);
        // echo ("需要补位 :" . $y . "<br/>");
        // 3.将有效数据长度byte[]添加到原始byte数组的头部
        $sizeByte = BytesUtils::integerToBytes($merchantData);
        $resultByte = array();

        for ($i = 0; $i < 4; $i++) {
            $resultByte [$i] = $sizeByte [$i];
        }
        //var_dump($sizeByte);
        // 4.填充补位数据
        for ($j = 0; $j < $merchantData; $j++) {
            $resultByte [4 + $j] = $source [$j];
        }
        //var_dump($resultByte);
        for ($k = 0; $k < $y; $k++) {
            $resultByte [$merchantData + 4 + $k] = 0x00;
        }
        //var_dump($resultByte);
        //echo ("补位后的byte数组长度:" . count ( $resultByte ) . "<br/>");
        //echo ("补位后数据HEX表示:" . ByteUtils::bytesToHex ( $resultByte ) . "<br/>");
        //echo ("秘钥HEX表示:" . ByteUtils::strToHex ( $keys ) . "<br/>");
        //echo ("秘钥长度:" . count ( ByteUtils::getBytes ( $keys ) ) . "<br/>");
        //echo ByteUtils::toStr ( $resultByte );
        $desdata = TDESUtil::encrypt(BytesUtils::toStr($resultByte), $keys);
        return BytesUtils::strToHex($desdata);
    }

    function encrypt($str, $key)
    {
        $key = base64_decode($key);

        $str = $this->pkcs5_pad($str, 8);
        if (strlen($str) % 8) {
            $str = str_pad($str, strlen($str) + 8 - strlen($str) % 8, "\0");
        }

        $str = openssl_encrypt($str, 'DES-EDE3', $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, '');


        return $str;

    }


    function encrypt_no_pad($str, $key)
    {
        $key = base64_decode($key);

        // $str = $this->pkcs5_pad($str, 8);
        if (strlen($str) % 8) {
            $str = str_pad($str, strlen($str) + 8 - strlen($str) % 8, "\0");
        }

        $str = openssl_encrypt($str, 'DES-EDE3', $key, OPENSSL_RAW_DATA | OPENSSL_NO_PADDING, '');


        return $str;

    }


    function decrypt($str, $key)
    {
        $key = base64_decode($key);
        $str = pack("H*", $str);
        $str = openssl_decrypt($str, 'DES-EDE3', $key, OPENSSL_RAW_DATA, '');
        return $str;
    }


    function pkcs5_pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    function pkcs5_unpad($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }
        return substr($text, 0, -1 * $pad);

    }

    //执行请求
    public function execute($data, $url)
    {
        //请求数据md5加签
        $string = $this->getSignContent($data) . $this->md_key;
        $sign = md5($string);
        //请求数据3des加密

        ksort($data);
        $en_data = json_encode($data);

        $key = $this->des_key;
        $str = $this->encrypt($en_data, $key);

        $re_data = [
            'merchantNo' => $data['merchantNo'],
            'cipherJson' => bin2hex($str),//16进制
            'sign' => $sign,
            'systemId' => $this->systemId, //base64_encode($this->des_ecb_encrypt('GUEST_HOTEL', base64_decode('E4Otx5GiMXMpDg0snXrQzacWwUrm+DQx'))),
        ];

        $returen = $this->curlPost_java(json_encode($re_data), $url);
        $arr = json_decode($returen, true);

        if ($arr['success']) {
            //对数据解密
            $cipherJson = $this->decrypt($arr['cipherJson'], $key);
            if (!$cipherJson) {
                return [
                    'status' => 0,
                    'message' => '解密失败请检测key是否正确'
                ];
            }

            //验证sign
            $re_data = json_decode($cipherJson, true);
            $string = $this->getSignContent($re_data) . $this->md_key;
            $sign = md5($string);

            if ($sign == $arr['sign']) {
                return [
                    'status' => 1,
                    'data' => $re_data
                ];
            } else {
                return [
                    'status' => 0,
                    'message' => '签名验证失败'
                ];
            }

        } else {
            return [
                'status' => 0,
                'message' => $arr['errCodeDes']
            ];
        }

    }


    //sign
    public function sign($data)
    {
        //1.拼接
        $string = $data['serialNo'] . $data['lepCardNo'] . $data['bankAccountNo'] . $data['settleCardPhone'];
        /// $string = TDESUtil::encrypt2HexStr($this->store_des_key, $string);
        $sign = md5($string);
        return $sign;

    }

    function StrToBin($str)
    {
        //1.列出每个字符
        $arr = preg_split('/(?<!^)(?!$)/u', $str);
        //2.unpack字符
        foreach ($arr as &$v) {
            $temp = unpack('H*', $v);
            $v = base_convert($temp[1], 16, 2);
            unset($temp);
        }

        return join(' ', $arr);
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


    function curl_img($url, $https = true, $method = 'post', $data = null, $BOUNDARY)
    {
        $ch = curl_init($url);
        $timeout = 300;

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


    public function img_content($img_url, $name, $img_name_desc = "")
    {
        try {
            $img_url = explode('/', $img_url);
            $img_url = end($img_url);
            $img = public_path() . '/upload/images/' . $img_url;
            if ($img) {
                try {
                    //压缩图片
                    $img_obj = \Intervention\Image\Facades\Image::make($img);
                    $img_obj->resize(500, 400);
                    $img = public_path() . '/upload/s_images/' . $img_url;
                    $img_obj->save($img);

                } catch (\Exception $exception) {
                    throw new \Exception($img_name_desc . '存在问题');

                }
            }

            $content = new \CURLFile(realpath($img), '', $name);

            return $content;

        } catch (\Exception $exception) {
            return '';
        }
    }


    public function exec($data, $url)
    {
        if ($this->checkEmpty($this->postCharset)) {
            $this->postCharset = "UTF-8";
        }
        $this->fileCharset = mb_detect_encoding($data['agentNo'], "UTF-8,GBK");
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

        // 传普通字段---先传
        foreach ($data as $k => $v) {
            if ($k == 'images') {
                continue;
            }
            $body .= "\r\n";
            $body .= "Content-Disposition: form-data; name=\"";
            $body .= $k . "\"";
            $body .= "\r\n\r\n";
            $body .= $v;
            $body .= "\r\n";
            $body .= $contentBody;
        }

        // 传图片---最后传
        foreach ($data['images'] as $k1 => $v1) {
            $body .= "\r\n";
            $body .= "Content-Disposition:form-data; name=\"";
            $body .= "$k1" . "\";";
            $body .= " filename=";
            $body .= "\"$k1\"";
            $body .= "\r\n";
            // $body .= "Content-Type:{$file['type']}";
            $body .= "Content-Type:application/octet-stream";
            $body .= "\r\n";
            $body .= "\r\n";
            $body .= $v1[0];
            $body .= "\r\n";
            $body .= $contentBody;
        }

        $body .= $endBoundary;

        try {
            $resp = $this->curl_img($url, $https = true, $method = 'post', $body, $BOUNDARY);

        } catch (\Exception $e) {
            die($e->getMessage());
        }

        //发起HTTP请求
        //解析AOP返回结果
        $respWellFormed = false;
        // 将返回结果转换本地文件编码
        $respObject = iconv($this->postCharset, $this->fileCharset . "//IGNORE", $resp);

        return $respObject;

    }


    protected function getMillisecond()
    {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf(' % .0f', (floatval($s1) + floatval($s2)) * 1000);

    }


}