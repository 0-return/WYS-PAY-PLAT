<?php

namespace App\Common\XingPOS;

/*
	


*/
use Illuminate\Support\Facades\Log;

class Aop
{


    public function __construct($config = [])
    {
        foreach ($config as $k => $v) {
            $this->$k = $v;
        }
    }

    /*
      进件需要的公共请求参数
    */
    public $key = null;//秘钥
    public $version = 'V1.0.1';//版本号  V1.0.1
    public $org_no = null;//合作商机构号
    public $url = 'https://gateway.starpos.com.cn/emercapp';//正式地址


    /*
        支付时的公共参数
    */
    public $env = false;//默认使用上线请求地址
    public $op_sys = null;
    public $character_set = '00';
    public $latitude = null;
    public $longitude = null;
    public $merc_id = null;
    public $trm_no = null;
    public $opr_id = null;
    public $trm_typ = null;
    public $trade_no = null;
    public $txn_time = null;
    public $sign_type = 'MD5';
    public $sign_value = null;
    public $add_field = null;


    // 支付接口文档中公共参数中需要签名的---请求
    private $common_pay_request_need_sign_field = [
        'opSys',
        'characterSet',
        'orgNo',
        'mercId',
        'trmTyp',
        'trmNo',
        'tradeNo',
        'txnTime',
        'signType',
        'version'
    ];

    // 支付接口文档中公共参数中需要签名的--返回
    private $common_pay_back_need_sign_field = [
        'tradeNo',
        'returnCode',
        'sysTime',
        'message',
        'mercId'
    ];


    public function executeStore($request_obj)
    {

        $send_data = $request_obj->getBizContent();
        $sign_data = $request_obj->getSignBizContent();
        $send_data["version"] = $this->version;
        $send_data["orgNo"] = $this->org_no;

        $sign_data["version"] = $this->version;
        $sign_data["orgNo"] = $this->org_no;
        $send_data["signValue"] = $this->sign($sign_data);
        //清空数组为空到数组
        $send_data = array_filter($send_data, function ($v) {
            if ($v == "") {
                return false;
            } else {
                return true;
            }
        });

        $cin = json_encode($send_data);
        $result = $this->curlJsonPost($cin, $this);

        $cout = iconv('gbk', 'utf-8', $result);

        return json_decode($cout, true);
    }


    public function curlJsonPost($data)
    {

        $ch = curl_init($this->url); //请求的URL地址
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);//$data JSON类型字符串
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data)));
        $data = curl_exec($ch);
        return $data;
    }


    // 第1步组合 --  参数签名   返回签名字符串
    public function sign($data)
    {

        if (isset($data['signValue'])) {
            unset($data['signValue']);
        }
        //图片不参与
        if (isset($data['imgFile'])) {
            unset($data['imgFile']);
        }

        ksort($data);
        $pre_str = '';
        foreach ($data as $k => $v)
            $pre_str .= $v;
        $pre_str = $pre_str . $this->key;
        $sign_str = md5($pre_str);

        return $sign_str;
    }


    /*
        支付文档请求时
        对需要约束的参数签名
    */
    public function payRequestSign($data, $request_obj)
    {

        // 所有需要签名的字段
        $all_sign_field = array_merge($this->common_pay_request_need_sign_field, $request_obj->request_sign_field);

        sort($all_sign_field);//对数组的值升序排序，无返回值

        $pre_str = '';
        foreach ($all_sign_field as $v) {
            $pre_str .= isset($data[$v]) ? $data[$v] : '';
        }
        $pre_str = $pre_str . $this->key;


        $sign_str = md5($pre_str);
        return $sign_str;
    }


    public function executePay($request_obj)
    {

        $send_data = [];
        $send_data["opSys"] = $this->op_sys;
        $send_data["characterSet"] = $this->character_set;
// $send_data["latitude"] = $this->latitude;//文档上有，但是不能传
// $send_data["longitude"] = $this->longitude;//文档上有，但是不能传
        $send_data["orgNo"] = $this->org_no;
        $send_data["mercId"] = $this->merc_id;
        $send_data["trmNo"] = $this->trm_no;
        // $send_data["oprId"] = $this->opr_id;//文档上有，但是不能传
        $send_data["trmTyp"] = $this->trm_typ;
        $send_data["tradeNo"] = $this->trade_no;
        $send_data["txnTime"] = $this->txn_time;
        $send_data["signType"] = $this->sign_type;
// $send_data["signValue"] = $this->sign_value;
        $send_data["addField"] = $this->add_field;
        $send_data["version"] = $this->version;

// echo '<pre>'; print_r($send_data);

        $send_data = array_merge($send_data, $request_obj->getBizContent());

        // 参数过滤
        foreach ($send_data as $k => $v) {
            if ($k == 'signValue') {
                unset($send_data[$k]);
                continue;
            }
            if (is_null($v) || $v == '') {
                unset($send_data[$k]);
                continue;
            }
            $v = trim($v);
            if ($v == '') {
                unset($send_data[$k]);
                continue;
            }
        }
        // $send_data=array_filter($send_data);

        $send_data["signValue"] = $this->payRequestSign($send_data, $request_obj);
        $cin = json_encode($send_data, JSON_UNESCAPED_UNICODE);

// echo '请求报文：<br>'; echo $cin; echo '<hr>';
        $cin = iconv('utf-8', 'gbk', $cin);
        Log::info('新大陆请求');
        Log::info($cin);
        $result = $this->curlJsonPost($cin);
        $cout = urldecode($result);
        Log::info('新大陆返回');
        Log::info($cout);
// echo $this->url;echo '<hr>'; echo '返回报文：<br>'; echo $cout;

        return json_decode($cout, true);
    }


    public function checkSign($data)
    {

        $third_sign = $data['signValue'];
        $sign = $this->sign($data);
        return $third_sign == $sign;
    }


    public function execute($request_obj)
    {
        // echo $request_obj->pdf_type;die;
        // 执行第一篇文档的接口
        if (!isset($request_obj->pdf_type)) {
            return $this->executeStore($request_obj);

            // 执行第2篇支付文档的接口
        } else {
            if (isset($request_obj->url) && !empty($request_obj->url)) {
                // 测试环境
                if ($this->env) {
                    $this->url = $request_obj->url_sandbox;
                    // 线上环境
                } else {
                    $this->url = $request_obj->url;
                }
            }
            return $this->executePay($request_obj);
        }
    }
}