<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2019/1/22
 * Time: 1:05 PM
 */

namespace App\Api\Controllers\Ltf;


class BaseController
{

    // 表单提交字符集编码
    public $postCharset = "UTF-8";
    private $fileCharset = "UTF-8";
    //请求地址
    public $scan_url = 'http://api.liantuofu.com/open/pay';//刷卡支付
    public $send_qr_url = 'https://api.liantuofu.com/open/precreate';//动态码
    public $order_query_url = 'http://api.liantuofu.com/open/pay/query';//订单查询
    public $refund_url = 'http://api.liantuofu.com/open/refund';//退款
    public $refund_query_url = 'http://api.liantuofu.com/open/refund/query';//退款查询
    public $unified_url = 'http://api.liantuofu.com/open/jspay';//静态聚合码 返回链接
    public $jspay_url = "https://api.liantuofu.com/open/precreate";//静态码  返回订单号
    /**
     * 数据签名,验签处理工具类
     */

    /**
     * 生成签名
     * $paras 请求参数字符串
     * $key 密钥
     * return 生成的签名
     */
    public function createSign($paras, $key)
    {
        $sort_array = array_sort(array_filter($paras));                //删除数组中的空值并排序
        $prestr = $this->create_linkstring($sort_array);                    //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串

        $prestr = $this->getSignContent($paras);
        $prestr = $prestr . "&key=" . $key;                                        //把拼接后的字符串再与安全校验码直接连接起来

        $mysgin = $this->sign($prestr, 'MD5');              //把最终的字符串签名，获得签名结果
        return $mysgin;
    }

    /**
     * 进件生成签名
     * $paras 请求参数字符串
     * $key 密钥
     * return 生成的签名
     */
    public function createSign_JinJian($paras, $key)
    {
        $sort_array = array_sort(array_filter($paras));             //删除数组中的空值并排序
        $prestr = $this->create_linkstring($sort_array);                   //把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
        $prestr = $prestr . $key;                                   //把拼接后的字符串再与安全校验码直接连接起来
        $mysgin = $this->sign($prestr, 'MD5');              //把最终的字符串签名，获得签名结果
        return $mysgin;
    }

    /**
     * 对数组排序
     * $array 排序前的数组
     * return 排序后的数组
     */
    public function array_sort($array)
    {
        ksort($array);                                                //按照key值升序排列数组
        return $array;
    }

    /**
     * 把数组所有元素，按照“参数=参数值”的模式用“&”字符拼接成字符串
     * $array 需要拼接的数组
     * return 拼接完成以后的字符串
     */
    public function create_linkstring($array)
    {
        $str = "";
        while (list ($key, $val) = each($array)) {
            //键值为空的参数不参与排序，键名为key的参数不参与排序
            if ($val != null && $val != "" && $key != "key" && $key != "sign_type")
                $str .= $key . "=" . $val . "&";
        }
        $str = substr($str, 0, count($str) - 2);                        //去掉最后一个&字符
        return $str;                                                //返回参数
    }

    /**
     * 签名字符串
     * $prestr 需要签名的字符串
     * $sign_type 签名类型，也就是sec_id
     * return 签名结果
     */
    public function sign($prestr, $sign_type)
    {
        $sign = '';
        if ($sign_type == 'MD5') {
            $sign = md5($prestr);                                    //MD5加密
        } elseif ($sign_type == 'DSA') {
            //DSA 签名方法待后续开发
            die("DSA 签名方法待后续开发，请先使用MD5签名方式");
        } elseif ($sign_type == "") {
            die("sign_type为空，请设置sign_type");
        } else {
            die("暂不支持" . $sign_type . "类型的签名方式");
        }
        return strtolower($sign);                                    //返回参数并小写
    }


    public function requestAsHttpPOST($data, $service_url)
    {
        $HTTP_TIME_OUT = "20";
        $http_data = array_sort(array_filter($data)); //删除数组中的空值并排序
        $post_data = http_build_query($http_data);
        $options = array(
            'http' => array(
                'method' => 'POST',
                'header' => 'Content-type:application/x-www-form-urlencoded;charset=MD5',
                'content' => $post_data,
                'timeout' => $HTTP_TIME_OUT * 1000 //超时时间,*1000将毫秒变为秒（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($service_url, false, $context);
        return $result;
    }

    public function send($data, $service_url) // 发送 请求
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $service_url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $content = curl_exec($curl);
        curl_close($curl);
        return $content;
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


}