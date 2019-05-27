<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/9/6
 * Time: 下午7:36
 */

namespace App\Api\Controllers\Self;


use Illuminate\Support\Facades\Validator;

class BaseController
{
    // 表单提交字符集编码
    public $postCharset = "UTF-8";

    private $fileCharset = "UTF-8";

    public $key = "88888888";


    //处理完返回
    public function return_data($data)
    {
        $key = $this->key;
        $string = $this->getSignContent($data) . '&key=' . $key;
        $data['sign'] = md5($string);
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


}