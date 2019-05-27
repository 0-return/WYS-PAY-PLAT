<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/9/6
 * Time: 下午7:36
 */

namespace App\Api\Controllers\Tfpay;


class BaseController
{


    public $debug = false; //环境模式
    public $test_url = 'http://10.77.0.119/newRetail'; //测试地址
    public $pro_url = 'https://newretail.tf56pay.com/newRetail'; //正式地址
    public $mch_id = '6000000132'; //商户号
    public $sign_type = 'RSA'; //签名方式
    public $md5_key = 'fZQlhyvRcnZfysbUQ66d14PC8npbNsXQ'; //md5加密可以
    public $pri_key = 'MIIEvQIBADANBgkqhkiG9w0BAQEFAASCBKcwggSjAgEAAoIBAQDJvnNUh4r/3FKXbznrE31TPc0G8/7tsZ5+hDHDzeA+p3CVOITSS0KUnYNceZeX7k0HXdOLBtR4g6V+aqp/tZbGkTWqxrBt28Sn1gmZhgehFbXmpZ8jnkC0TXoDx/MTh5+gkbGFFpmPxJpl5UDMX5Jwaghy4VFpbrIAzCbv6UKcZnakMQXOCgTg0a8IdmlQvbIdM7nA44I1+Cq6/29pZZbRUmHPTa5M+30eBFaQ9o/iBqRF86LZjQCsVkfKzEj62edAUjGdnJIysocwgD4vUXar2/xuOOWCXxa6kJoXXr2jOO0r9XzbuCjzqJBbKe6XCdw5z+sLtsEEr1q4GrohInmzAgMBAAECggEAGnk7eSQwQVMQI38dApQUJhA+D2OFWHuuaLvALAmgG5itVWeNRmtJ2WayDjiGhBFpWkYdtGi5CPd9iBFHPmr91iDIAhkAnenw7HVR3SuRZLoMnK+vKmVh6Ecic8yRQUbS06dKvEQy8oLCIAzta+Q+uzGu2iRnIoa8JQ6lLWZWr18m6uyirNAIFaFishsR7DPxfODBM/5ZKBGLA8WrpuvQVxk8QvwHBWjSOMgOetsUz2dBe1qpQEg7FpNZoj9kgXd6OSM0rdeTu603LM3eGKFQre7Jv6mmdH2bFH2f6xUINEBXzCuMl6BhYNFVcJIcOelHvXhq5cKjIzh5kwr2rA4DAQKBgQD2hzC2l6wVqAgzQSOtI2BZhTQwm2W6aqtw5ZldSH3xqg4HniyZRR/vydKjS+Acx4XcM8MZ9qDlTMUe4uXrMcVvFtrSAofbCe7ZGVbPEjnAhXRccbJzqDginOfBeNzUPu0PWJjfrLE5xc0HL5AdYbI4xVN3SutLI0rCP+0Os41ggwKBgQDRfsVSgNL0DeDPvOlbg6J5q7JvhjB4TXtPOhyAugtjVxYNn54Ls1Kh/9MuTSNRsKXnqnm+nktDLZqt2/Zb2tJlF66v096cZAhq4ABjQFrT8JnhwlFlST4Q3ksgRzN3SOTepNMvTo03D8Vw0snHUy8bCcuA569VUa/xbc2l+uXbEQKBgF+zUVzAekQZ670B33iZ0BBQXlc0LAR23kDAUI3e001aB4I7i/Kf2+r4/PT5QnHJnpRB384XaJQ6/hakXD62Hn+mbqGx681DCN5sML/HwnWTGP2+AVlWhxwgrvLzGT6ngt0/NnE2F8Jmn9XXR+mwAEB7kZAwnDCZ7a6EpGDdoNdDAoGARkOrsRFOL9cbyPFGn5AWBZMF5Qvv49mw+xC1kKNVwHrsBaO8oZYCqB0i2ou6xeJmsr3l2X5EJgL8t0VoasSI/qkjyQtZxcBzZk09NAzYqe8v3Z2MKVmYfJXiQrA+3cpQITDNODeze31Jrp60WKtYyEvPuqt6jjY4udQnPn1ZbNECgYEA4tSoQlSYk06s2gakwIqSApTLn7uirMb9VtPR12ERRoq+rQRURmSnhrmlywgkZw8NL34kigYgOrtoInwWp1VOiLiedpO9ErhhIVZ0KX1CDqeagWIS9JL1GmUfmNMs+HiTncRS6otaQk9/m2OWonojQn9aEZCABT3K9F9Hog9HGvg='; //rsa私钥
    public $pub_key = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAyb5zVIeK/9xSl2856xN9Uz3NBvP+7bGefoQxw83gPqdwlTiE0ktClJ2DXHmXl+5NB13TiwbUeIOlfmqqf7WWxpE1qsawbdvEp9YJmYYHoRW15qWfI55AtE16A8fzE4efoJGxhRaZj8SaZeVAzF+ScGoIcuFRaW6yAMwm7+lCnGZ2pDEFzgoE4NGvCHZpUL2yHTO5wOOCNfgquv9vaWWW0VJhz02uTPt9HgRWkPaP4gakRfOi2Y0ArFZHysxI+tnnQFIxnZySMrKHMIA+L1F2q9v8bjjlgl8WupCaF169ozjtK/V827go86iQWynulwncOc/rC7bBBK9auBq6ISJ5swIDAQAB'; //rsa公钥

    /**上传文件方法
     * @return string
     * User: WangMingxue
     * Email cumt_wangmingxue@126.com
     */
    public function __construct()
    {
        date_default_timezone_set('Asia/Shanghai');
    }


    /**请求统一处理接口
     * @param $business_parameters
     * @param $method
     * @param string $sign_type
     * @param bool $is_upload
     * @return mixed
     * User: WangMingxue
     * Email cumt_wangmingxue@126.com
     */
    public function api($business_parameters = [], $method = null, $is_upload = false)
    {
        $common_parameters = [
            'mch_id' => $this->mch_id,
            'nonce_str' => uniqid(),
            'sign_type' => $this->sign_type,
            'timestamp' => date('YmdHis')
        ];
        $post_data = array_merge($common_parameters, $business_parameters);
        //生成签名
        $post_data['sign'] = $this->makeSign($post_data);
        $this->debug ? $url = $this->test_url : $url = $this->pro_url;
        $url = $url . $method;
        $result = $this->httpCurl($url, $post_data, $is_upload);
        if ($result['code'] === 0 && isset($result['data']['sign'])) {
            if ($this->signVerify($result['data'])) {
                return $result;
            } else {
                return ['info' => '签名错误'];
            }
        } else {
            return $result;
        }
    }

    /*
       curl发送数据
   */
    public function curl($data, $method)
    {
        $this->debug ? $url = $this->test_url : $url = $this->pro_url;
        $url = $url . $method;

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

    /**生成订单号
     * @return string
     * User: WangMingxue
     * Email cumt_wangmingxue@126.com
     */
    public function makeNo()
    {
        $no = 'DD' . time() . str_pad(1, 12, 0, STR_PAD_LEFT);

        return $no;
    }

    /**远程请求
     * @param $url
     * @param null $post_data
     * @param int $timeout
     * @param int $cookie_type
     * @param null $cookie_jar
     * @return mixed
     * User: WangMingxue
     * Email cumt_wangmingxue@126.com
     */

    public function httpCurl($url, $post_data = null, $is_upload = false, $timeout = 60)
    {
        $con = curl_init((string)$url);
        curl_setopt($con, CURLOPT_HEADER, false);
        curl_setopt($con, CURLOPT_POST, true);
        curl_setopt($con, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($con, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($con, CURLOPT_TIMEOUT, (int)$timeout);
        curl_setopt($con, CURLOPT_POSTFIELDS, $post_data);

        $json = curl_exec($con);
        curl_close($con);
        $result = json_decode($json, 1);
        if (!$result) {
            $result = $json;
        }
        return $result;
    }

    /**
     * 数组转字符串
     **/
    private function arrayToString($params)
    {
        ksort($params);
        if (isset($params['sign']) || isset($params['file'])) {
            unset($params['sign']);
            unset($params['file']);
        }
        $sign_str = '';
        //空值和0值不参与签名
        foreach ($params as $key => $val) {
            if ($val) {
                $sign_str .= sprintf("%s=%s&", $key, $val);
            }
        }
        return substr($sign_str, 0, strlen($sign_str) - 1);
    }

    /**生成签名
     * @param $sign_str
     * @return string
     * User: WangMingxue
     * Email cumt_wangmingxue@126.com
     */
    public function makeSign($data)
    {
        switch ($sign_type = $this->sign_type) {
            case 'RSA':
                $sign = $this->rsaSign($data);
                break;
            default:
                $sign = $this->md5Sign($data);
        }
        return $sign;
    }

    /**签名验证
     * @param $data
     * @return bool
     * User: WangMingxue
     * Email cumt_wangmingxue@126.com
     */
    public function signVerify($data)
    {
        switch ($sign_type = $this->sign_type) {
            case 'RSA':
                $check_status = $this->rsaVerify($data, $data['sign']);
                break;
            default:
                $client_sign = $this->md5Sign($data);
                $client_sign == $data['sign'] ? $check_status = true : $check_status = false;
        }
        return $check_status;
    }

    /**md5加密
     * @param $data
     * @return string
     * User: WangMingxue
     * Email cumt_wangmingxue@126.com
     */
    public function md5Sign($data)
    {
        $sign_str = $this->arrayToString($data);

        $str = $sign_str . '&key=' . $this->md5_key;
        $sign = strtoupper(md5($str));
        return $sign;
    }

    /**
     * @description rsa签名
     * @param content 待签名内容
     * @param pri_key RSA私钥
     *
     */
    public function rsaSign($content)
    {
        $content = $this->arrayToString($content);
        $pri_key = $this->pri_key;
        $pri_key_f = $this->formatPriKey($pri_key);
        $privKeyId = openssl_pkey_get_private($pri_key_f);
        $signature = '';
        openssl_sign($content, $signature, $privKeyId, OPENSSL_ALGO_SHA256);
        openssl_free_key($privKeyId);
        //base64编码
        return base64_encode($signature);
    }

    /**
     * @description rsa验签
     * @param content 待签名内容
     * @param pri_key RSA公钥
     * @param sign 签名
     *
     */
    public function rsaVerify($content, $sign)
    {
        $content = $this->arrayToString($content);

        $pub_key = $this->pub_key;
        $pub_key_f = $this->formatPubKey($pub_key);
        $publicKeyId = openssl_pkey_get_public($pub_key_f);
        $sign = base64_decode($sign);
        $result = openssl_verify($content, $sign, $publicKeyId, OPENSSL_ALGO_SHA256);
        openssl_free_key($publicKeyId);
        return $result === 1 ? true : false;
    }

    /**格式化rsa私钥
     * @param $priKey
     * @return string
     * User: WangMingxue
     * Email cumt_wangmingxue@126.com
     */
    public function formatPriKey($priKey)
    {
        $fKey = "-----BEGIN PRIVATE KEY-----\n";
        $len = strlen($priKey);
        for ($i = 0; $i < $len;) {
            $fKey = $fKey . substr($priKey, $i, 64) . "\n";
            $i += 64;
        }
        $fKey .= "-----END PRIVATE KEY-----";
        return $fKey;
    }

    /**格式化rsa公钥
     * @param $pubKey
     * @return string
     * User: WangMingxue
     * Email cumt_wangmingxue@126.com
     */
    public function formatPubKey($pubKey)
    {
        $fKey = "-----BEGIN PUBLIC KEY-----\n";
        $len = strlen($pubKey);
        for ($i = 0; $i < $len;) {
            $fKey = $fKey . substr($pubKey, $i, 64) . "\n";
            $i += 64;
        }
        $fKey .= "-----END PUBLIC KEY-----";
        return $fKey;
    }
}