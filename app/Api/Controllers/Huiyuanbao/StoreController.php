<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/10/25
 * Time: 1:50 PM
 */

namespace App\Api\Controllers\Huiyuanbao;


use App\Models\HStore;
use App\Models\MyBankCategory;
use App\Models\Store;
use App\Models\StoreBank;
use App\Models\StoreImg;
use Illuminate\Support\Facades\Log;
use Jdjr\Sdk\TDESUtil;
use MyBank\Tools;
use TencentYoutuyun\Conf;
use TencentYoutuyun\YouTu;

class StoreController extends BaseController
{
    public $postCharset = 'UTF-8';
    public $fileCharset = 'UTF-8';

    //进件
    public function open_store($data)
    {
        try {
            //拼接参数
            $store_id = $data['store_id'];
            $phone = $data['phone'];
            $email = $data['email'];
            $url = $data['request_url'];
            $isHighQualityMer = $data['isHighQualityMer'];//否是优质客户  0是，1否
            $scanRate = $data['scanRate'];//优质商户固定为0.0038 非优质商户0.0025~0.006
            $settleType = $data['settleType'];//结算类型: T：T+1结算D：D+1结算
            $saleId = $data['saleId'];
            $jh_mid = $data['jh_mid'];

            $Store = Store::where('store_id', $store_id)->first();
            if (!$Store) {
                return ['status' => 2, 'message' => '门店未认证请认证门店'];
            }
            $MyBankCategory = MyBankCategory::where('category_id', $Store->category_id)
                ->select('h_mcc')->first();
            if (!$MyBankCategory) {
                return ['status' => 2, 'message' => '请选择正确的门店类目'];
            }
            $StoreBank = StoreBank::where('store_id', $store_id)->first();
            if (!$StoreBank) {
                return ['status' => 2, 'message' => '没有绑定银行卡,请先绑定银行卡'];
            }
            $StoreImg = StoreImg::where('store_id', $store_id)->first();
            if (!$StoreImg) {
                return ['status' => 2, 'message' => '没有商户资料'];
            }

            if (!$email) {
                return ['status' => 2, 'message' => '请绑定常用邮箱'];
            }

            $store_type = $Store->store_type;//经营性质 1-个体，2-企业，3-个人
            $store_bank_type = $StoreBank->store_bank_type?$StoreBank->store_bank_type:'01';//01 对私人 02 对公
            $config_id = $StoreBank->config_id;
            if ($store_type == 1) {
                $areaType = '5';
            } elseif ($store_type == 2) {
                $areaType = '4';

            } else {
                $areaType = '6';
            }

            if ($store_bank_type == '01') {
                $accType = '2';//对私
            } else {
                $accType = '1';//对公

            }

            //读取识别图片信息
            //法人身份证有效期
            //识别法人身份证反面
            $is_card_b = 1;
            if ($is_card_b) {
                try {
                    // 设置APP 鉴权信息 请在http://open.youtu.qq.com 创建应用
                    $appid = env('YOUTU_appid');
                    $secretId = env('YOUTU_secretId');
                    $secretKey = env('YOUTU_secretKey');
                    $userid = env('YOUTU_userid');
                    Conf::setAppInfo($appid, $secretId, $secretKey, $userid, conf::API_YOUTU_END_POINT);
                    $uploadRet = YouTu::idcardocrurl($StoreImg->head_sfz_img_b, 1);

                    if (isset($uploadRet['errorcode']) && $uploadRet['errorcode'] == 0) {
                        if (isset($uploadRet['valid_date'])) {
                            $valid_date = explode("-", $uploadRet['valid_date']);
                            if (isset($valid_date[1])) {
                                $head_sfz_time = $valid_date[1];
                                $Store->head_sfz_time = str_replace(".", "-", $head_sfz_time);
                                $Store->save();
                            }
                        }
                    }
                } catch (\Exception $exception) {

                }
            }


            //对私判断结算人和法人是否是一个人
            //结算身份证信息默认都是法人
            $bank_sfz_no = $Store->head_sfz_no;
            $bank_sfz_stime = $Store->head_sfz_stime;
            $bank_sfz_time = $Store->head_sfz_time;

            //法人名字和结算人名字不一样读取结算人的身份证
            if ($Store->head_name != $StoreBank->store_bank_name) {

                //读取识别图片信息
                //法人身份证有效期
                //识别结算卡身份证正面
                $is_card_a = 1;
                if ($is_card_a && $StoreBank->bank_sfz_no == "") {
                    try {
                        // 设置APP 鉴权信息 请在http://open.youtu.qq.com 创建应用
                        $appid = env('YOUTU_appid');
                        $secretId = env('YOUTU_secretId');
                        $secretKey = env('YOUTU_secretKey');
                        $userid = env('YOUTU_userid');
                        Conf::setAppInfo($appid, $secretId, $secretKey, $userid, conf::API_YOUTU_END_POINT);
                        $uploadRet = YouTu::idcardocrurl($StoreImg->bank_sfz_img_a, 0);
                        if (isset($uploadRet['errorcode']) && $uploadRet['errorcode'] == 0) {
                            if (isset($uploadRet['name'])) {
                                //身份证名字
                                //$uploadRet['name'];
                            }
                            if (isset($uploadRet['id'])) {
                                //身份证号码
                                $StoreBank->bank_sfz_no = $uploadRet['id'];
                                $StoreBank->save();
                            }

                        }
                    } catch (\Exception $exception) {

                    }
                }

                //识别身份证反面
                $is_card_b = 1;
                if ($is_card_b && $StoreBank->bank_sfz_time == "") {
                    $data_return['sfz_time'] = '';
                    try {
                        // 设置APP 鉴权信息 请在http://open.youtu.qq.com 创建应用
                        $appid = env('YOUTU_appid');
                        $secretId = env('YOUTU_secretId');
                        $secretKey = env('YOUTU_secretKey');
                        $userid = env('YOUTU_userid');
                        Conf::setAppInfo($appid, $secretId, $secretKey, $userid, conf::API_YOUTU_END_POINT);
                        $uploadRet = YouTu::idcardocrurl($StoreImg->bank_sfz_img_b, 1);

                        if (isset($uploadRet['errorcode']) && $uploadRet['errorcode'] == 0) {
                            if (isset($uploadRet['valid_date'])) {
                                $valid_date = explode("-", $uploadRet['valid_date']);
                                if (isset($valid_date[1])) {
                                    $StoreBank->bank_sfz_time = str_replace(".", "-", $valid_date[1]);//2019.01.01换成2019-01-01
                                    $StoreBank->save();
                                }
                            }
                        }

                    } catch (\Exception $exception) {

                    }
                }


                $bank_sfz_no = $StoreBank->bank_sfz_no;
                $bank_sfz_stime = $StoreBank->bank_sfz_stime;
                $bank_sfz_time = $StoreBank->bank_sfz_time;
            }


            //识别营业执照
            $is_yyzz = 1;
            if ($is_yyzz && $store_type != 3 && $Store->store_license_time == "") {
                $data_return['store_license_no'] = '';
                $data_return['store_license_time'] = '';
                $data_return['is_long_time'] = '0';
                try {
                    // 设置APP 鉴权信息 请在http://open.youtu.qq.com 创建应用
                    $appid = env('YOUTU_appid');
                    $secretId = env('YOUTU_secretId');
                    $secretKey = env('YOUTU_secretKey');
                    $userid = env('YOUTU_userid');

                    Conf::setAppInfo($appid, $secretId, $secretKey, $userid, conf::API_YOUTU_END_POINT);
                    $uploadRet = YouTu::bizlicenseocrurl($StoreImg->store_license_img);
                    if (isset($uploadRet['errorcode']) && $uploadRet['errorcode'] == 0) {
                        foreach ($uploadRet['items'] as $k => $v) {
                            if (isset($v['item'])) {
                                //
                                if ($v['item'] == '注册号' || $v['item'] == "营业期限" || $v['item'] == '经营范围') {
                                    if ($v['item'] == '注册号') {
                                        //营业执照编号
                                        $store_license_no = $v['itemstring'];
                                    }
                                    if ($v['item'] == '营业期限') {
                                        $ex = explode('至', $v['itemstring']);
                                        if (isset($ex)) {
                                            //开始时间
                                            $str_s = $ex[0];
                                            $str_s = str_replace("年", "-", $str_s);
                                            $str_s = str_replace("月", "-", $str_s);
                                            $str_s = str_replace("日", "", $str_s);
                                            //结束时间
                                            $str_e = $ex[1];
                                            $str_e = str_replace("年", "-", $str_e);
                                            $str_e = str_replace("月", "-", $str_e);
                                            $str_e = str_replace("日", "", $str_e);

                                            $Store->store_license_stime = $str_s;
                                            $Store->store_license_time = $str_e;
                                            $Store->save();
                                        }
                                    }

                                    if ($v['item'] == '经营范围') {
                                        //经营范围

                                        $blicScope = $v['itemstring'];
                                    }

                                } else {
                                    continue;
                                }
                            } else {
                                break;
                            }
                        }

                    }
                } catch
                (\Exception $exception) {

                }
            }

            //营业执照时间
            $store_license_time = $Store->store_license_time;
            $store_license_stime = $Store->store_license_stime;
            if ($store_license_time == "") {
                $store_license_time = '长期';//长期
            }

            if ($store_license_stime == "") {
                $store_license_stime = '长期';//长期
            }

            $data = [
                //资料必须
                'hybPhone' => $phone,//登录手机号
                'bankAccName' => $StoreBank->store_bank_name,//入账人姓名
                'accNum' => $Store->head_sfz_no,//店主身份证号
                'bankAccNo' => $StoreBank->store_bank_no,//结算银行卡号
                'bankBranch' => $StoreBank->bank_name,//开户行
                'bankSubbranch' => $StoreBank->sub_bank_name,//开户支行
                'payBankId' => $StoreBank->bank_no,//系统行号
                'accType' => $accType,//开户类型：对公/私1 对公2 对私
                'saleId' => $saleId,//销售ID
                'rname' => $Store->store_name,//店主姓名+"(个人商户)"
                'areaType' => $areaType,//商户类型；4企业；5个体工商户6个人说明：accType为1时，areaType必须为4
                'baddr' => $Store->province_name . $Store->city_name . $Store->area_name . $Store->store_address,//经营地址 baddr = raddr
                'raddr' => $Store->province_name . $Store->city_name . $Store->area_name . $Store->store_address,//详细经营地址 baddr = raddr
                'localCode' => $Store->area_code,//门店所在地地区代码
                'settleType' => $settleType,//结算类型: T：T+1结算D：D+1结算
                'legalPerson' => $Store->head_name,//法人姓名
                'legalNum' => $Store->head_sfz_no,//法人身份证号
                'remarks' => '3',//注册来源,固定值3
                'contactPerson' => $Store->head_name,//联系人= bankAccName
                'contactPhone' => $phone,//联系手机号 =hybPhone
                'businessScope' => '全行业',//行业范围
                'isForeign' => '0',//是否开通储值卡（默认传0）
                'isHighQualityMer' => $isHighQualityMer,//否是优质客户  0是，1否
                'scanRate' => $scanRate,//优质商户固定为0.0038 非优质商户0.0025~0.006
                'industryId' => $MyBankCategory->h_mcc,//所属行业（详情见所属行业描述）
                'jh_mid' => $jh_mid,//修改才有
                //资料非 必须
                'bno' => $Store->store_license_no,//营业执照号
                'shortName' => $Store->store_name,//营业执照注册名称
                'licenceExp' => $this->time_action($store_license_stime, '2010-01-01') . ',' . $this->time_action($store_license_time, '2050-01-01'),//营业执照有效期
                'idNumExp' => $this->time_action($bank_sfz_stime, '2010-01-01') . ',' . $this->time_action($bank_sfz_time, '2050-01-01'),//入账人身份证有效期
                'legalIdExp' => $this->time_action($Store->head_sfz_stime, '2010-01-01') . ',' . $this->time_action($Store->head_sfz_time, '2050-01-01'),//法人身份证有效期
                'businessType' => '0',//业务类型固定值0
                //图片
                'legalUploadFile' => $this->img_content($StoreImg->head_sfz_img_b, 'head_sfz_img_b', '法人身份证国徽'),//法人身份证国徽
                'bupLoadFile' => $this->img_content($StoreImg->store_license_img, 'store_license_img', '营业执照'),//营业执照
                'registryUpLoadFile' => $this->img_content($StoreImg->store_logo_img, 'store_logo_img', '门头照'),//门头照
                'photoUpLoadFile' => $this->img_content($StoreImg->store_img_b, 'store_img_b', '内部经营照片'),//内部经营照片
                'materialUpLoadFile' => $this->img_content($StoreImg->head_sfz_img_a, 'head_sfz_img_a', '法人身份证人像面'),//法人身份证人像面
                'materialUpLoad1File' => $this->img_content($StoreImg->store_auth_bank_img, 'store_auth_bank_img', '入账授权书'),//入账授权书
                'materialUpLoad2File' => $this->img_content($StoreImg->bank_img_a, 'bank_img_a', '结算银行卡正面照'),//结算银行卡正面照
                'materialUpLoad3File' => $this->img_content($StoreImg->head_sfz_img_b, 'head_sfz_img_b', '店主身份证国徽面'),//店主身份证国徽面
                'materialUpLoad4File' => $this->img_content($StoreImg->head_sfz_img_a, 'head_sfz_img_a', '店主身份证人像面'),//店主身份证人像面
                'materialUpLoad5File' => $this->img_content($StoreImg->bank_sc_img?$StoreImg->bank_sc_img:$StoreImg->head_sc_img, 'bank_sc_img', '入账人手持身份证正面'),//入账人手持身份证正面
                'materialUpLoad7File' => $this->img_content($StoreImg->store_img_a, 'store_img_a', '有桌牌的收银台照片'),//有桌牌的收银台照片
                'materialUpLoad8File' => $this->img_content($StoreImg->head_sfz_img_a, 'head_sfz_img_a', '门店租赁合同照片'),//门店租赁合同照片
                'rupLoadFile' => $this->img_content($StoreImg->store_industrylicense_img, 'store_industrylicense_img', '对公账户许可'),//对公账户许可
            ];


            // dd($data);
            //经营性质 1-个体，2-企业，3-个人

            //门店类型-个人
            if ($store_type == 3) {
                $data['bno'] = "";//营业执照号
                $data['shortName'] = "";//营业执照注册名称
                $data['licenceExp'] = "";//营业执照有效期
                $data['bupLoadFile'] = "";//营业执照
            }
            //不是企业不需要传
            if ($store_type != 2) {
                $data['rupLoadFile'] = "";//对公账户许可
            }
            //结算类型 01 对私人 02 对公
            //对公
            if ($store_bank_type == '02') {
                $data['idNumExp'] = "";//入账人身份证有效期
            }

            //清空数组为空到数组
            $data = array_filter($data, function ($v) {
                if ($v == "") {
                    return false;
                } else {
                    return true;
                }
            });

            $return = $this->curl_c($data, $url);
            $return = json_decode($return, true);

            //报错
            if ($return['status'] == "1") {
                return [
                    'status' => 2,
                    'message' => isset($return['msg']) ? $return['msg'] : $return['message'],
                ];
            }


            return $return;


        } catch (\Exception $exception) {
            return [
                'status' => 2,
                'message' => $exception->getMessage(),
            ];
        }

    }


//处理时间格式
    public function time_action($time, $set_time)
    {
        //如果是长期
        if ($time == "") {
            $time = "长期";

        } else {
            //去除中文
            $time = preg_replace('/([\x80-\xff]*)/i', '', $time);

            $is_date = strtotime($time) ? strtotime($time) : false;

            if ($is_date === false) {
                $time = $set_time;//默认时间
            }
            $time = date('Y-m-d', strtotime($time));
        }


        return $time;
    }

    public function img_content($img_url, $name, $name_desc = '')
    {
        try {
            $img_url = explode('/', $img_url);
            $img_url = end($img_url);
            $img = public_path() . '/upload/images/' . $img_url;
            if ($img_url) {
                try {
                    //压缩图片
                    $img_obj = \Intervention\Image\Facades\Image::make($img);
                    $img_obj->resize(500, 400);
                    $img = public_path() . '/upload/s_images/' . $img_url;
                    $img_obj->save($img);

                    //文件流
                    $content = new \CURLFile(realpath($img), 'image/png', $name_desc);

                    return $content;
                } catch (\Exception $exception) {
                    throw new \Exception($name_desc . '存在问题');
                }
            } else {
                return '';
            }


        } catch (\Exception $exception) {
            return '';
        }
    }


    public function curl_a($url, $postFields = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $postBodyString = "";
        $encodeArray = Array();
        $postMultipart = false;


        if (is_array($postFields) && 0 < count($postFields)) {

            foreach ($postFields as $k => $v) {
                if ("@" != substr($v, 0, 1)) //判断是不是文件上传
                {

                    $postBodyString .= "$k=" . urlencode($this->characet($v, $this->postCharset)) . "&";
                    $encodeArray[$k] = $this->characet($v, $this->postCharset);
                } else //文件上传用multipart/form-data，否则用www-form-urlencoded
                {
                    $postMultipart = true;
                    $encodeArray[$k] = new \CURLFile(substr($v, 1));


                }

            }
            unset ($k, $v);
            curl_setopt($ch, CURLOPT_POST, true);
            if ($postMultipart) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $encodeArray);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, -1));
            }


            //dd($encodeArray);
        }

        if ($postMultipart) {

            $headers = array('content-type: multipart/form-data;charset=' . $this->postCharset . ';boundary=' . $this->getMillisecond());
        } else {

            $headers = array('content-type: application/x-www-form-urlencoded;charset=' . $this->postCharset);
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


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
        return $reponse;
    }


    public function exec($requestUrl, $data, $picName, $filename)
    {
        if ($this->checkEmpty($this->postCharset)) {
            $this->postCharset = "UTF-8";
        }
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
            if ("@" == substr($v, 0, 1)) {
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
                $body .= "\r\n";
                //$body .= substr($v, 1);
                $body .= "\r\n";
                $body .= $contentBody;
            }
        }


        $body .= $endBoundary;

        try {
            $resp = $this->curl_b($requestUrl, $https = true, $method = 'post', $body, $BOUNDARY);
            // dump($resp);
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


    public function curl_b($url, $https = true, $method = 'post', $data = null, $BOUNDARY)
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

    /*
      curl发送数据
  */
    public function curl_c($data, $url)
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
}

