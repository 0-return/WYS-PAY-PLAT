<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/10/25
 * Time: 1:50 PM
 */

namespace App\Api\Controllers\Fuiou;


use App\Models\MyBankCategory;
use App\Models\Store;
use App\Models\StoreBank;
use App\Models\StoreImg;
use Illuminate\Support\Facades\Request;
use TencentYoutuyun\Conf;
use TencentYoutuyun\YouTu;

class StoreController extends BaseController
{
    public $postCharset = 'GBK';
    public $fileCharset = 'GBK';
    public $open_store = "http://www-1.fuiou.com:28090/wmp/wxMchntMng.fuiou?action=wxMchntAdd";
    public $config_weixin = "http://www-1.fuiou.com:28090/wmp/wxMchntMng.fuiou?action=xyWechatConfigSet";

    //进件
    public function open_store($data)
    {
        try {
            $key = $data['key'];
            $url = $this->open_store;
            $obj = new \App\Api\Controllers\Fuiou\BaseController();
            $request = [
                'trace_no' => isset($data['trace_no']) ? $data['trace_no'] : time(),//版本
                'ins_cd' => isset($data['ins_cd']) ? $data['ins_cd'] : '',//机构号
                'mchnt_name' => isset($data['mchnt_name']) ? $data['mchnt_name'] : '',//商户名称
                'mchnt_shortname' => isset($data['mchnt_shortname']) ? $data['mchnt_shortname'] : '',//商户简称
                'real_name' => isset($data['real_name']) ? $data['real_name'] : "",//营业执照名称
                'license_type' => isset($data['license_type']) ? $data['license_type'] : '',//证件类型：0 营业执照，1 三证合一，A 身份证（一证下机）B 个体户
                'license_no' => isset($data['license_no']) ? $data['license_no'] : '',//证件号码，填写方法：1.license_type=0 或1，此处填写营业执照号码。2.license_type=A，此处填写身份证号码3.license_type=B，此处填写个体工商户营业执照号码
                'license_expire_dt' => isset($data['license_expire_dt']) ? $data['license_expire_dt'] : '',//证件到期日（格式yyyyMMdd）格式 长期请填20991231 无有效期请填 19000101 1.license_type=0 或1，此处填写 营业执照到期日。 2.license_type=A 此处填写身份证 的到期日 3.license_type=B，此处填写个体 工商户营业执照号的到期日
                'certif_id' => isset($data['certif_id']) ? $data['certif_id'] : '',//法人身份证号码
                'certif_id_expire_dt' => isset($data['certif_id_expire_dt']) ? $data['certif_id_expire_dt'] : '',//法人身份证到期日（格式YYYYMMDD）
                'contact_person' => isset($data['contact_person']) ? $data['contact_person'] : '',//联系人姓名,
                'contact_phone' => isset($data['contact_phone']) ? $data['contact_phone'] : '',//客服电话,
                'contact_addr' => isset($data['contact_addr']) ? $data['contact_addr'] : '',//联系人地址,
                'contact_mobile' => isset($data['contact_mobile']) ? $data['contact_mobile'] : $data['contact_phone'],//联系人电话,
                'contact_email' => isset($data['contact_email']) ? $data['contact_email'] : $data['contact_email'],//联系人邮箱,
                'business' => isset($data['business']) ? $data['business'] : '',//经营范围代码（新开户则必填）,
                'city_cd' => isset($data['city_cd']) ? $data['city_cd'] : '',//市代码,
                'county_cd' => isset($data['county_cd']) ? $data['county_cd'] : '',//区代码,
                'acnt_type' => isset($data['acnt_type']) ? $data['acnt_type'] : '',//入账卡类型：1：对公；2：对私; 入账卡类型为1 时，对公户户名需 与营业执照名称保持一致（进件若 为双账户时，此处必填2，即对私 结算）,
                'bank_type' => isset($data['bank_type']) ? $data['bank_type'] : '',//行别,（acnt_type=1 必填）(参考 行别对照表) 见附件7 行别对照表,
                'inter_bank_no' => isset($data['inter_bank_no']) ? $data['inter_bank_no'] : '',//入账卡开户行联行号,
                'iss_bank_nm' => isset($data['iss_bank_nm']) ? $data['iss_bank_nm'] : '',//入账卡开户行名称,
                'acnt_nm' => isset($data['acnt_nm']) ? $data['acnt_nm'] : '',//入账卡户名,
                'acnt_no' => isset($data['acnt_no']) ? $data['acnt_no'] : '',//入账卡号（不带长度位）,
                'settle_tp' => isset($data['settle_tp']) ? $data['settle_tp'] : '1',//清算类型,
                'artif_nm' => isset($data['artif_nm']) ? $data['artif_nm'] : '',//法人姓名,
                'acnt_artif_flag' => isset($data['acnt_artif_flag']) ? $data['acnt_artif_flag'] : '',//法人入账标识(0:非法人入账,1:法人入账,
                'acnt_certif_tp' => isset($data['acnt_certif_tp']) ? $data['acnt_certif_tp'] : '',//入账证件类型("0":"身份证,
                'acnt_certif_id' => isset($data['acnt_certif_id']) ? $data['acnt_certif_id'] : '',//入账证件号,
                'acnt_certif_expire_dt' => isset($data['acnt_certif_expire_dt']) ? $data['acnt_certif_expire_dt'] : '',//入账证件到期日,
            ];

            $str = $obj->getSignContentNONULL($request);
            $request['sign'] = $obj->sign($str, $key, 'md5');
            $re = $obj->send($request, $url);


            //成功
            if ($re['ret_code'] == "0000") {
                return [
                    'status' => 1,
                    'message' => '进件成功',
                    'data' => $re,
                ];
            } else {
                return [
                    'status' => 0,
                    'message' => $re['ret_msg'],
                ];
            }

        } catch (\Exception $exception) {
            return [
                'status' => 0,
                'message' => $exception->getMessage(),
            ];
        }


    }


    //config_weixin
    public function config_weixin($data)
    {
        try {
            $key = $data['key'];
            $url = $this->config_weixin;
            $obj = new \App\Api\Controllers\Fuiou\BaseController();
            $request = [
                'traceNo' => isset($data['traceNo']) ? $data['traceNo'] : time(),//版本
                'insCd' => isset($data['insCd']) ? $data['insCd'] : '',//机构号
                'agencyType' => isset($data['agencyType']) ? $data['agencyType'] : '0',//
                "configs" => [
                    'mchntCd' => isset($data['mchntCd']) ? $data['mchntCd'] : '',
                    'jsapiPath' => url('/api/fuiou/weixin/'),
                    "subAppid" => isset($data['subAppid']) ? $data['subAppid'] : 'wx2421b1c4370ec43b',
                    "subscribeAppid" => isset($data['subscribeAppid']) ? $data['subscribeAppid'] : 'wx2421b1c4370ec43b',
                ]
            ];

            $str = $obj->getSignContentNONULL($request);
            $request['sign'] = $obj->sign($str, $key, 'md5');
            $re = $obj->send($request, $url);

            dd($re);
            //成功
            if ($re['ret_code'] == "0000") {
                return [
                    'status' => 1,
                    'message' => '进件成功',
                    'data' => $re,
                ];
            } else {
                return [
                    'status' => 0,
                    'message' => $re['ret_msg'],
                ];
            }

        } catch (\Exception $exception) {
            return [
                'status' => 0,
                'message' => $exception->getMessage(),
            ];
        }


    }

    /**
     * 将含有GBK的中文数组转为utf-8
     *
     * @param array $arr 数组
     * @param string $in_charset 原字符串编码
     * @param string $out_charset 输出的字符串编码
     * @return array
     */
    function array_iconv($arr, $in_charset = "utf-8", $out_charset = "gbk")
    {
        $ret = eval('return ' . iconv($in_charset, $out_charset, var_export($arr, true) . ';'));
        return $ret;
        // 这里转码之后可以输出json
        // return json_encode($ret);
    }


}

