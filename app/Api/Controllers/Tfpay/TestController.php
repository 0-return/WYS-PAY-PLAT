<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/9/6
 * Time: 下午7:36
 */

namespace App\Api\Controllers\Tfpay;


class TestController
{


    public $debug = true; //环境模式
    public $test_url = 'http://10.77.0.119/newRetail'; //测试地址
    public $pro_url = 'https://newretail.tf56pay.com/newRetail'; //正式地址
    public $mch_id = '6000000132'; //商户号
    public $sign_type = 'RSA'; //签名方式
    public $md5_key = 'Y32ImYwvBdHrqXmNDMIybLcOmxwiPfFh'; //md5加密可以
    public $pri_key = 'MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQDCspzjSfvYzR5S7m+ji/1l6CGxh/8eUGL8Fneq1miNlqJMWmJyINn2OJXIPgLivF5LSDKaUnEep0ZNbRADi2+pC776azea6g0ch3j9eH0Ia5yKxbBzOMGCQBClxFMemzlDp4qwBokZ1itoVEAGTsteeOqCgHaPrikjoxjlVYou0zHm7y0C99lVSFnfa33J6mn++8uXp3S/9/VeR/gaJL+JPZfZ2v8Fj8xsj7sTQ4hTLOHTaJTCvWLJU3yXf9Ppzr8jz5NvNJj5qxIiYZ1LqLTsi1DYYLkk9XXZ9gXrFF13z/9eW/b6uV5PIzGN/mq+BraeO4f+wodl+nm14xun6TaBAgMBAAECggEAW0NeRyxm4TlE/ZrGueLk5N/q60zUSWFlBMWDUpEucTAq4596hgTgJopfq31l3OJvUNqG3c/HNpcyXRjCaObzcEoRO1EGv5b2jmjyTd4svcWzm1kPXPM9wdBF/W4JiE86iHwAhoKJwZKixkS3vj0xFxeW1ZVh/felUQ8inF18aHXFntrqDXa9dyuYKJ0k8kO5nuCno3v3WqAyCEmt1fjPvrbjd133ta27pMzvl/Zlj0XAFTeMqVrGyBxArepvZQNvVw7wsgilUiWHmbbTn7310/dY7yL2MMH1FvvfdptTT4xx4tRCPncpJ9rY8m1Q/3jaC4fxOvYdtH3Wu2wBG5pgAQKBgQDhblF7Vb9T6toh3+pkNkjH21j/b9w3HRM+ALUtpCTBe+3J+7fHAVXmzDcJk9H2+Le+Rrzt5l2zvZVgOEtDK/YKjRGquqezjdZ3Cpjp0NZKiUqijZFsthfe0yZeUKcWBE01X3I4VydrzO5UVZFPPkYo0wk8hqqg2P6JdJvL9CJPcwKBgQDdGWlaaCXDbuNsoTGFLn4RUUicHvbAHV0k5RNoEFg2H2PJNzE5/HKNeM7sY+1SX24T26xvpz6/0mJN4xzavwXegFstf0XEqiPboe4woAI8XySSxYK4iRB5fEURUN7L5NikzUMJJkqvDNb9pKQDIDQTFy4OHJDC/uNPxx9XGwO9OwKBgQDeN9w0gwtOkrSiLdMlI+nMsSni6waegZfFR/dRXXwqCpsVv3+iuWtPTsF2PHP+S6D8/UgiDzMRCnJkXjm6pmwsbHEc5lCIC+p8gELKQeXrpbif0oOnMT0IlwY8dK6wYl8lnMfASBGBD4tkMNFD1zRb6+Qv+OPcGOuY3gzyGN91hwKBgEl+vfeQcSUiwjSE7Kohx0RIacODw3AfEqxF/Yp1DG2JR6lGUHW/BfEi/F215dig4j11oz9GL+ShsY3Edf38y52nuydHjFCQYfULQdsmBTg+RDEJuKdQ4IoRlf/oivbp7l3x4Vu0P9Uqhniv9tkXJlhGN0f3lONMyRDm1vMkhryrAoGBANiCQtTwhSRV/W/x/z5fv3OH3aC3E4E1C9RDckbMhpHY7coa7uM+GGewJXo2gn6fKi8ALJ3iWECZBW9ImMPe7Z7FgLq2FVy3cyA3p1ekGaxut0/JOwSoBxk43CA/QzcR7oA3kQ8DY1CeyKuU7ttM+EOqygYkGGjkx/2Nxf41ODNZ'; //rsa私钥
    public $pub_key = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAwrKc40n72M0eUu5vo4v9ZeghsYf/HlBi/BZ3qtZojZaiTFpiciDZ9jiVyD4C4rxeS0gymlJxHqdGTW0QA4tvqQu++ms3muoNHId4/Xh9CGucisWwczjBgkAQpcRTHps5Q6eKsAaJGdYraFRABk7LXnjqgoB2j64pI6MY5VWKLtMx5u8tAvfZVUhZ32t9yepp/vvLl6d0v/f1Xkf4GiS/iT2X2dr/BY/MbI+7E0OIUyzh02iUwr1iyVN8l3/T6c6/I8+TbzSY+asSImGdS6i07ItQ2GC5JPV12fYF6xRdd8//Xlv2+rleTyMxjf5qvga2njuH/sKHZfp5teMbp+k2gQIDAQAB'; //rsa公钥

    /**上传文件方法
     * @return string
     * User: WangMingxue
     * Email cumt_wangmingxue@126.com
     */
    public function __construct()
    {
        date_default_timezone_set('Asia/Shanghai');
    }

    public function merchantUpload()
    {
        $method = '/openapi/picture/upload'; //方法名
        $file = dirname(__FILE__) . '/demo.png'; //文件本地路径
        $post_data['file'] = curl_file_create($file, 'image/png', 'file');

        $result = $this->api($post_data, $method, true);
        return json_encode($result);
    }

    /**商户查询
     * @return string
     * User: WangMingxue
     * Email cumt_wangmingxue@126.com
     */
    public function merchantQuery()
    {
        $method = '/openapi/merchant/query';
        $post_data['sub_mch_id'] = '6000000087';

        $result = $this->api($post_data, $method, true);
        return json_encode($result);
    }

    /**商户注册
     * @return string
     * User: WangMingxue
     * Email cumt_wangmingxue@126.com
     */
    public function merchantRegister()
    {
        $method = '/openapi/merchant/register';
        $bankcard = [
            'type' => '2',
            'bank_code' => 'ABC',
            'account_name' => '张三',
            'account_number' => '张三',
            'bank_province_code' => '100',
            'bank_city_code' => '100',
            'branch_name' => '中国银行金沙湖支行',
        ];
        $file_pics = [
            'idcard_front_pic' => 'group2/M00/10/92/CgcN7FzAFFOALkfIAAEJ4wzIlMA938.jpg',
            'idcard_back_pic' => 'group2/M00/10/92/CgcN7FzAFFOALkfIAAEJ4wzIlMA938.jpg',
            'license_pic' => 'group2/M00/10/92/CgcN7FzAFFOALkfIAAEJ4wzIlMA938.jpg',
            'bankcard_pic' => 'group2/M00/10/92/CgcN7FzAFFOALkfIAAEJ4wzIlMA938.jpg',
            'door_pic' => 'group2/M00/10/92/CgcN7FzAFFOALkfIAAEJ4wzIlMA938.jpg',
        ];
        $post_data = [
            'organization_type' => 3,
            'name' => '传化金服',
            'shortname' => '传化',
            'mcc_code' => '1001',
            'sub_mcc_code' => '4214',
            'contact' => '张三',
            'contact_type' => 'LEGAL_PERSON',
            'contact_business_type' => '02',
            'service_phone' => '13656814630',
            'id_card_number' => '412828199911020116',
            'license_type' => 'NATIONAL_LEGAL',
            'license_number' => 'qa4567896789jkghjkl',
            'province_code' => '100',
            'city_code' => '100',
            'district_code' => '100',
            'address' => '这是我的地址',
            'bankcard' => json_encode($bankcard),
            'file_pics' => json_encode($file_pics),
            'notify_url' => 'www.baidu.com'
        ];

        $result = $this->api($post_data, $method, true);
        return json_encode($result);

    }

    /**商户更新
     * @return string
     * User: WangMingxue
     * Email cumt_wangmingxue@126.com
     */
    public function merchantUpdate()
    {
        $method = '/openapi/merchant/update';
        $bankcard = [
            'type' => '2',
            'bank_code' => 'ABC',
            'account_name' => '张三',
            'account_number' => '张三',
            'bank_province_code' => '100',
            'bank_city_code' => '100',
            'branch_name' => '中国银行金沙湖支行',
        ];
        $file_pics = [
            'idcard_front_pic' => 'group2/M00/10/92/CgcN7FzAFFOALkfIAAEJ4wzIlMA938.jpg',
            'idcard_back_pic' => 'group2/M00/10/92/CgcN7FzAFFOALkfIAAEJ4wzIlMA938.jpg',
            'license_pic' => 'group2/M00/10/92/CgcN7FzAFFOALkfIAAEJ4wzIlMA938.jpg',
            'bankcard_pic' => 'group2/M00/10/92/CgcN7FzAFFOALkfIAAEJ4wzIlMA938.jpg',
            'door_pic' => 'group2/M00/10/92/CgcN7FzAFFOALkfIAAEJ4wzIlMA938.jpg',
        ];
        $post_data = [
            'sub_mch_id' => '6000000087',
            'organization_type' => 3,
            'name' => '传化金服',
            'shortname' => '传化',
            'mcc_code' => '1001',
            'sub_mcc_code' => '4214',
            'contact' => '张三',
            'contact_type' => 'LEGAL_PERSON',
            'contact_business_type' => '02',
            'service_phone' => '13656814630',
            'id_card_number' => '412828199911020116',
            'license_type' => 'NATIONAL_LEGAL',
            'license_number' => 'qa4567896789jkghjkl',
            'province_code' => '100',
            'city_code' => '100',
            'district_code' => '100',
            'address' => '这是我的地址',
            'bankcard' => json_encode($bankcard),
            'file_pics' => json_encode($file_pics),
            'notify_url' => 'www.baidu.com'
        ];
        $result = $this->api($post_data, $method, true);
        return json_encode($result);

    }

    /**统一下单接口
     * @return string
     * User: WangMingxue
     * Email cumt_wangmingxue@126.com
     */
    public function payGateway()
    {
        $method = '/openapi/pay/gateway';
        $post_data = [
            'channel' => 'ALIPAY_SCAN',
            'total_fee' => 100,
            'out_trade_no' => $this->makeNo(),
            'body' => '我是订单',
            'wx_appid' => '',
            'openid' => 'ddfadfde457789876589',
            'client_ip' => '127.0.0.1',
            'notify_url' => 'www.baidu.com'
        ];
        $result = $this->api($post_data, $method, true);
        return json_encode($result);
    }

    /**条码支付接口
     * @return string
     * User: WangMingxue
     * Email cumt_wangmingxue@126.com
     */
    public function payMicropay()
    {
        $method = '/openapi/pay/micropay';
        $post_data = [
            'channel' => 'WECHAT_POS',
            'total_fee' => 1,
            'out_trade_no' => $this->makeNo(),
            'body' => '我是订单',
            'auth_code' => '135614400074712274',
            'client_ip' => '127.0.0.1',
            'notify_url' => 'www.baidu.com'
        ];
        $result = $this->api($post_data, $method, true);
        return json_encode($result);
    }

    /**订单查询接口
     * @return string
     * User: WangMingxue
     * Email cumt_wangmingxue@126.com
     */
    public function payQuery()
    {
        $method = '/openapi/pay/query';
        $post_data = [
            'date' => '2019-04-23',
            'out_trade_no' => 'DD864549549kjdkfeeee',
        ];
        $result = $this->api($post_data, $method, true);
        return json_encode($result);
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