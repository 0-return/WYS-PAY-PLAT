<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/10/25
 * Time: 1:50 PM
 */

namespace App\Api\Controllers\Jd;


use App\Models\MyBankCategory;
use App\Models\Store;
use App\Models\StoreBank;
use App\Models\StoreImg;
use Illuminate\Support\Facades\Log;
use Jdjr\Sdk\TDESUtil;
use TencentYoutuyun\Conf;
use TencentYoutuyun\YouTu;

class StoreController extends BaseController
{

    public $postCharset = '';
    public $fileCharset = '';


    //开户
    public function open_store($data)
    {
        try {
            $store_id = $data['store_id'];
            $regPhone = $data['phone'];
            $email = isset($data['email'])&&$data['email']?$data['email']:$regPhone .'@139.com';//;
            $agentNo = $data['agentNo'];
            $buildOrRepair = $data['buildOrRepair'];//入驻标识0：新入驻，1：修改
            $store_md_key = $data['store_md_key'];
            $store_des_key = $data['store_des_key'];
            $serialNo = $data['serialNo'];//流水号
            $url = $data['request_url'];
            $Store = Store::where('store_id', $store_id)->first();
            if (!$Store) {
                return ['code' => 0, 'message' => '门店未认证请认证门店'];
            }
            $MyBankCategory = MyBankCategory::where('category_id', $Store->category_id)->select('jd_mcc')->first();
            if (!$MyBankCategory) {
                return ['code' => 0, 'message' => '请选择正确的门店类目'];
            }
            $StoreBank = StoreBank::where('store_id', $store_id)->first();
            if (!$StoreBank) {
                return ['code' => 0, 'message' => '没有绑定银行卡,请先绑定银行卡'];
            }
            $StoreImg = StoreImg::where('store_id', $store_id)->first();
            if (!$StoreImg) {
                return ['code' => 0, 'message' => '没有商户资料'];
            }

            if (!$email) {
                return ['code' => 0, 'message' => '请绑定常用邮箱'];
            }

            //公共判断
            $companyType = 'P';//企业-E，个体-P，自然人-N
            $blicCardType = "USC";//商户证件类型 企业包含：统一社会信用代码类-USC，普通五证类-BLI，多证合一类-OCI；个体工商户包含：统一社会信用代码类-USC，普通营业执照-BLI 自然人：身份证-ID；
            $store_type = $Store->store_type;//经营性质 1-个体，2-企业，3-个人
            $store_bank_type = $StoreBank->store_bank_type?$StoreBank->store_bank_type:"01";//01 对私人 02 对公
            $priatePublic = '1';//结算户公私标识 对公1，对私0，非法人3
            $blicScope = "";//经营范围


            /**********************图片***********/
            $store_img_data = [
                'blicUrla' => $this->img_content($StoreImg->store_license_img ? $StoreImg->store_license_img : $StoreImg->head_sfz_img_a, 'blicUrla', '营业执照'),//营业执照照片 企业和个体工商户传营业执照照片，自然人传租赁合同照片
                'occUrla' => $this->img_content($StoreImg->store_industrylicense_img, 'occUrla', '银行开户许可证图片'),//银行开户许可证图片,企业商户必传，结算给对公账户必传
                'lepUrla' => $this->img_content($StoreImg->head_sfz_img_a, 'lepUrla', '法人身份证信息正面'),//法人身份证信息正面图片 必传
                'lepUrlb' => $this->img_content($StoreImg->head_sfz_img_b, 'lepUrlb', '法人身份证信息反面'),//法人身份证信息反面图片 必传
                'lepUrlc' => $this->img_content($StoreImg->head_sc_img, 'lepUrlc', '手持法人身份证'),//手持法人身份证照片 自然人必传
                'settleHoldingIDCard' =>$this->img_content($StoreImg->head_sc_img, 'settleHoldingIDCard', '结算人手持身份证'),//结算人手持身份证
                'img' => $this->img_content($StoreImg->store_logo_img, 'img', '经营门店门头'),   //经营门店门头照片 北京地区门店企业和个体工商户必传
                'enterimg' => $this->img_content($StoreImg->store_img_a, 'enterimg', '经营门店出入口'),//经营门店出入口照片 北京地区门店企业和个体工商户必传
                'innerimg' => $this->img_content($StoreImg->store_img_b, 'innerimg', '经营门店'),//经营门店店内照片 北京地区门店企业和个体工商户必传
                'cardPhoto' => $this->img_content($StoreImg->bank_img_a, 'cardPhoto', '结算银行卡正面'),//结算银行卡正面照图片 企业和个体工商户结算给非法人账户必传，自然人必传
                'settleManPhotoFront' => $this->img_content($StoreImg->bank_sfz_img_a, 'settleManPhotoFront', '结算人身份证正面'),//结算人身份证正面照 结算给非法人账户必传
                'settleManPhotoBack' => $this->img_content($StoreImg->bank_sfz_img_b, 'settleManPhotoBack', '结算人身份证背面'),//结算人身份证背面照 结算给非法人账户必传
                'settleProtocol' => $this->img_content($StoreImg->store_auth_bank_img, 'settleProtocol', '结算账户指定书'),//结算账户指定书图片 结算给非法人账户必传
            ];
            //排除
            //开户许可证  结算到对私不传
            if ($store_type != 2) {
                $store_img_data['occUrla'] = '';
            }

            //自然人必传  其他不传
            if ($store_type != 3) {
                $store_img_data['lepUrlc'] = '';
            }

            //结算人身份证正面照   结算人身份证背面照  结算账户指定书图片  结算给非法人账户必传
            if ($Store->head_name == $StoreBank->store_bank_name) {
                $store_img_data['settleManPhotoFront'] = '';
                $store_img_data['settleManPhotoBack'] = '';
                $store_img_data['settleProtocol'] = '';
            }


            //清空数组为空到数组
            $store_img_data = array_filter($store_img_data, function ($v) {
                if ($v == "") {
                    return false;
                } else {
                    return true;
                }
            });


            /**********************图片结束***********/


            //经营类型-企业
            if ($store_type == 2) {
                $companyType = 'E';

            }
            //经营类型-个人
            if ($store_type == 3) {
                $companyType = 'N';
                $blicCardType = 'ID';
            }

            //结算类型-对私
            if ($store_bank_type == '01') {
                //法人结算
                $priatePublic = '0';//结算户公私标识 对公1，对私0，非法人3

                //非法人结算
                if ($Store->head_name != $StoreBank->store_bank_name) {
                    $priatePublic = '3';
                }
            }


            //识别营业执照+法人身份证
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

                            if (isset($valid_date)) {
                                $head_sfz_stime = $valid_date[0];
                                $head_sfz_time = $valid_date[1];
                                $Store->head_sfz_stime = str_replace(".", "-", $head_sfz_stime);
                                $Store->head_sfz_time = str_replace(".", "-", $head_sfz_time);
                            }
                        }
                    }
                } catch (\Exception $exception) {

                }
            }

            //识别营业执照
            $is_yyzz = 1;
            if ($is_yyzz && $store_type != 3) {
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

            /***********门店信息数据***********/
            $store_info_data = [
                'regEmail' => $email,//注册邮箱 是
                'regPhone' => $regPhone,//注册手机号 是
                'companyType' => $companyType,//企业-E，个体-P，自然人-N
                'blicCardType' => $blicCardType,//商户证件类型 企业包含：统一社会信用代码类-USC，普通五证类-BLI，多证合一类-OCI；个体工商户包含：统一社会信用代码类-USC，普通营业执照-BLI 自然人：身份证-ID；
                'blicCompanyName' => $Store->store_name,//商户实体名称
                'blicUscc' => $Store->store_license_no,//营业执照编号
                'blicProvince' => $Store->province_name,//省
                'blicCity' => $Store->city_name,//市
                'blicAddress' => $Store->province_name . $Store->city_name . $Store->area_name . $Store->store_address,//详细地址
                'blicLongTerm' => $Store->store_license_time == "长期" ? 'true' : 'false',//营业证件到期日是否为长期 true为长期，false不是长期，自然人按身份证件填写
                'blicValidityStart' => $this->time_action($Store->store_license_stime, "2018-01-01"),//营业证件起始日 日期格式yyyy-mm-dd，自然人按身份证件填写
                'blicValidityEnd' => $this->time_action($Store->store_license_time, "2050-01-01", $Store->store_license_time == "长期" ? 'true' : 'false'),//营业证件到期日 长期直接写长期 ，不是写日期格式yyyy-mm-dd，自然人按身份证件填写
                'lepCardType' => 'ID',//法人证件类型 身份证-ID，护照-PAS，台胞证-PASTW，港澳居民来往通行证-PASHK【若商户类型为自然人，则法人证件类型必须为身份证-ID】
                'lepName' => $Store->head_name,//法人证件中姓名
                'lepCardNo' => $Store->head_sfz_no,//法人证件中号码
                'lepLongTerm' => $Store->head_sfz_time == "长期" ? 'true' : 'false',//法人证件到期日是否为长期 true为长期，false不是长期
                'lepValidityStart' => $this->time_action($Store->head_sfz_stime, "2018-01-01"),//法人证件起始日 日期格式yyyy-mm-dd
                'lepValidityEnd' => $this->time_action($Store->head_sfz_time, "2050-01-01", $Store->head_sfz_time == "长期" ? 'true' : 'false'),// 法人证件到期日 长期直接写长期 ，不是写日期格式yyyy-mm-dd
                'blicScope' => $this->bic(mb_strlen($blicScope, 'UTF8') < 800 ? $blicScope : substr($blicScope, 0, 800)),//经营范围 企业/个体工商户必传
                'indTwoCode' => $MyBankCategory->jd_mcc,//行业编码
                'contactName' => $Store->head_name,//联系人姓名
                'contactPhone' => $regPhone,//联系人手机号码
                'contactEmail' => $email,//联系人电子邮箱
                'contactProvince' => $Store->province_name,//联系人省
                'contactCity' => $Store->city_name,//联系人市
                'contactAddress' => $Store->province_name . $Store->city_name . $Store->area_name . $Store->store_address,//联系人详细地址
                'storeProvince' => $Store->province_name,//
                'storeCity' => $Store->city_name,//
                'storeAddress' => $Store->province_name . $Store->city_name . $Store->area_name . $Store->store_address,//
                'priatePublic' => $priatePublic,//结算户公私标识 对公1，对私0，非法人3
                'bankName' => $StoreBank->bank_name,//商户结算银行名称
                'subBankCode' => $StoreBank->bank_no,//结算银行支行联行号
                'bankAccountNo' => $StoreBank->store_bank_no,//商户结算银行卡账号
                'bankAccountName' => $StoreBank->store_bank_name,//结算账户名
                'abMerchantName' => $Store->store_short_name,//商户简称
                'settleCardPhone' => $StoreBank->store_bank_phone ? $StoreBank->store_bank_phone : $regPhone,//商户结算银行卡绑定手机号
                'ifPhyStore' => 'true',//是否线下门店
                'settleToCard' => '1',//是否结算到卡
                'buildOrRepair' => $buildOrRepair,//入驻标识0：新入驻，1：修改
            ];
            //去除不需要的参数
            //个人 不需要传
            if ($store_type == 3) {
                $store_info_data['blicUscc'] = $Store->head_sfz_no;//营业执照编号填写法人身份证
                // 个人按身份证件填写
                $store_info_data['blicLongTerm'] = $Store->head_sfz_time == "长期" ? 'true' : 'false';
                $store_info_data['blicValidityStart'] = $this->time_action($Store->head_sfz_stime, "2018-01-01");
                $store_info_data['blicValidityEnd'] = $this->time_action($Store->head_sfz_time, "2050-01-01", $Store->head_sfz_time == "长期" ? 'true' : 'false');

                //经营范围
                $store_info_data['blicScope'] = '';
            }

            //清空数组为空到数组
            $store_info_data = array_filter($store_info_data, function ($v) {
                if ($v == "") {
                    return false;
                } else {
                    return true;
                }
            });
            $entity_data = $store_info_data;
            $entity_data['serialNo'] = $serialNo;//请求流水号
            $entity_data['agentNo'] = $agentNo;


            $sign_data['serialNo'] = $entity_data['serialNo'];
            $sign_data['lepCardNo'] = $entity_data['lepCardNo'];
            $sign_data['bankAccountNo'] = $entity_data['bankAccountNo'];
            $sign_data['settleCardPhone'] = $entity_data['settleCardPhone'];

            //签名
            $this->store_md_key = $store_md_key;
            $this->store_des_key = $store_des_key;

            $sign = $this->sign($sign_data);
            $entity_data['sign'] = $sign;

            //实体转json
            $entity = json_encode($entity_data);
            //3des加密
            $entity = TDESUtil::encrypt2HexStr($this->store_des_key, $entity);
            //组装请求参数
            $request_data = $store_img_data;
            $request_data['agentNo'] = $agentNo;
            $request_data['entity'] = $entity;//$store_info_data实体
            $return = $this->curl($request_data, $url);
            $return = json_decode($return, true);
            if (!$return) {
                $return['code'] = 0;
                $return['message'] = '请求异常,请检查数据是否正确';
            }
            $return['serialNo'] = $serialNo;
            return $return;
            /***********门店信息数据结束***********/
        } catch
        (\Exception $exception) {
            return [
                'code' => 0,
                'message' => $exception->getMessage(),
            ];
        }

    }


    //	3.2	商户开通产品接口
    public function store_open_ways($data)
    {

        try {
            $serialNo = $data['serialNo'];
            $agentNo = $data['agentNo'];
            $store_md_key = $data['store_md_key'];
            $store_des_key = $data['store_des_key'];
            $url = $data['request_url'];
            //entity 实体参数
            $entity_data = [
                'serialNo' => "" . $serialNo . "",
                'agentNo' => $data['agentNo'],
                'merchantNo' => $data['merchantNo'],
                'productId' => $data['productId'],
                'payToolId' => $data['payToolId'],
                'mfeeType' => $data['mfeeType'],
                'mfee' => $data['mfee'],
            ];
            //拼接加签名字符串
            $string = $data['serialNo'] . $data['agentNo'] . $data['merchantNo'] . $data['productId'] . $data['payToolId'];
            $sign = md5($string);
            $entity_data['sign'] = $sign;

            //签名
            $this->store_md_key = $store_md_key;
            $this->store_des_key = $store_des_key;

            //实体转json
            $entity = json_encode($entity_data);
            //3des加密
            $entity = TDESUtil::encrypt2HexStr($this->store_des_key, $entity);

            $request_data['agentNo'] = $agentNo;
            $request_data['entity'] = $entity;//$store_info_data实体
            $return = $this->curl($request_data, $url);
            $return = json_decode($return, true);
            $return['serialNo'] = $serialNo;

            return $return;

        } catch
        (\Exception $exception) {
            return [
                'code' => 0,
                'message' => $exception->getMessage(),
            ];
        }

    }

    //3.3	商户入驻状态查询接口
    public function store_status($data)
    {

        try {

            $store_md_key = $data['store_md_key'];
            $store_des_key = $data['store_des_key'];
            $entity_data = [
                'serialNo' => $data['serialNo'],
                'merchantNo' => $data['merchantNo'],
            ];
            //签名
            $this->store_md_key = $store_md_key;
            $this->store_des_key = $store_des_key;

            $string = $data['serialNo'] . $data['merchantNo'];
            $sign = md5($string);
            $entity_data['sign'] = $sign;
            $entity = json_encode($entity_data);

            $entity = TDESUtil::encrypt2HexStr($this->store_des_key, $entity);


            $request_data['agentNo'] = $data['agentNo'];
            $request_data['entity'] = $entity;//$store_info_data实体

            $return = $this->curl($request_data, $data['request_url']);
            $return = json_decode($return, true);
            $return['serialNo'] = $data['serialNo'];
            return $return;

        } catch
        (\Exception $exception) {
            return [
                'code' => 0,
                'message' => $exception->getMessage(),
            ];
        }

    }


    //3.3	商户入驻状态查询接口获取key
    public function store_keys($data)
    {

        try {

            $store_md_key = $data['store_md_key'];
            $store_des_key = $data['store_des_key'];
            $entity_data = [
                'serialNo' => $data['serialNo'],
                'merchantNo' => $data['merchantNo'],
            ];
            //签名
            $this->store_md_key = $store_md_key;
            $this->store_des_key = $store_des_key;

            $string = $data['serialNo'] . $data['merchantNo'];
            $sign = md5($string);
            $entity_data['sign'] = $sign;
            $entity = json_encode($entity_data);

            $entity = TDESUtil::encrypt2HexStr($this->store_des_key, $entity);


            $request_data['agentNo'] = $data['agentNo'];
            $request_data['entity'] = $entity;//$store_info_data实体

            $return = $this->curl($request_data, $data['request_url']);

            $return = json_decode($return, true);
            $return['serialNo'] = $data['serialNo'];
            return $return;

        } catch
        (\Exception $exception) {
            return [
                'code' => 0,
                'message' => $exception->getMessage(),
            ];
        }

    }

//处理时间格式
    public function time_action($time, $set_time, $is_long = 'false')
    {
        //如果是长期
        if ($is_long == "true") {
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

    public function bic($data)
    {
        if ($data == "") {
            $data = "经营范围没有读取到";
        }

        return $data;
    }
}

