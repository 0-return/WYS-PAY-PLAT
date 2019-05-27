<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/9/6
 * Time: 下午7:36
 */

namespace App\Api\Controllers\Qwx;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BaseController
{
    // 表单提交字符集编码
    public $postCharset = "UTF-8";

    private $fileCharset = "UTF-8";

    public $key = "88888888";

    public $needpage = false;//默认不要分页/

    public $l = 15;
    public $p = 1;
    public $t = 0;

    public $status = 1;
    public $message = 'ok';


    //处理完返回
    public function return_data($data)
    {
        $key = $this->key;
        $string = $this->getSignContent($data) . '&key=' . $key;
        $data['sign'] = md5($string);
        Log::info('微收银返回');
        Log::info($data);
        return response()->json($data);
    }

    //校验md5
    public function check_md5($data)
    {
        try {
            $key = $this->key;
            $sign = $data['sign'];
            $data['sign'] = null;
            $string = $this->getSignContent($data) . '&key=' . $key;

            if ($sign == md5($string)) {
                return [
                    'return_code' => 'SUCCESS',
                    'return_msg' => '验证通过'
                ];
            } else {
                return [
                    'return_code' => 'FALL',
                    'return_msg' => '验证不通过'
                ];
            }

        } catch (\Exception $exception) {
            return [
                'return_code' => 'FALL',
                'return_msg' => $exception->getMessage()
            ];
        }


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


    /*
        curl发送数据
    */
    static function curl($data, $url)
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

    /**
     * 校验必填字段
     */
    public function check_required($check, $data)
    {
        $rules = [];
        $attributes = [];
        foreach ($data as $k => $v) {
            $rules[$k] = 'required';
            $attributes[$k] = $v;
        }
        $messages = [
            'required' => ':attribute不能为空',
        ];
        $validator = Validator::make($check, $rules,
            $messages, $attributes);
        $message = $validator->getMessageBag();
        return $message->first();
    }

    public function format($cin = [])
    {
        $data = [
            /*            'l' => $this->l,//每页显示多少条
                        'p' => $this->p,//当前页
                        't' => $this->t,//当前页*/

            'status' => $this->status,
            'message' => $this->message,
            'data' => $cin
        ];
        if ($this->needpage) {

            $data['l'] = $this->l;
            $data['p'] = $this->p;
            $data['t'] = $this->t;

        }

        return response()->json($data);
    }

    /*
        返回分页数据
    */
    public function page($obj, $request = '')
    {

        if (empty($request))
            $request = app('request');

        $this->p = abs(trim($request->get('p', 1)));
        $this->l = abs(trim($request->get('l', 15)));

        $this->needpage = true;

        $start = abs(($this->p - 1) * $this->l);
        return $obj->offset($start)->limit($this->l);
    }


}