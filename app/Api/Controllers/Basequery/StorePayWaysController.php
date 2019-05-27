<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2017/9/27
 * Time: 上午11:48
 */

namespace App\Api\Controllers\Basequery;


use App\Api\Controllers\BaseController;
use App\Api\Controllers\Config\FuiouConfigController;
use App\Api\Controllers\Config\HConfigController;
use App\Api\Controllers\Config\JdConfigController;
use App\Api\Controllers\Config\MyBankConfigController;
use App\Api\Controllers\Config\NewLandConfigController;
use App\Api\Controllers\Config\WeixinConfigController;
use App\Api\Controllers\Jd\StoreController;
use App\Models\HasOpenWays;
use App\Models\HStore;
use App\Models\JdStore;
use App\Models\JdStoreItem;
use App\Models\Merchant;
use App\Models\MyBankCategory;
use App\Models\MyBankStore;
use App\Models\MyBankStoreTem;
use App\Models\NewLandStore;
use App\Models\NewLandStoreItem;
use App\Models\Store;
use App\Models\StoreBank;
use App\Models\StoreImg;
use App\Models\StorePayWay;
use App\Models\TfStore;
use App\Models\UserRate;
use App\Models\UserStoreSet;
use App\Models\WeixinStoreItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mybank\Mybank;
use MyBank\Sdk\ParamUtil;
use MyBank\Sdk\RSACert;
use MyBank\Tools;
use TencentYoutuyun\Conf;
use TencentYoutuyun\YouTu;

class StorePayWaysController extends BaseController
{

    //申请通道
    public function openways(Request $request)
    {
        try {
            $user = $this->parseToken();
            $phone = '';
            $email = '';
            $type = $request->get('ways_type');
            $code = $request->get('code', '888888');
            $Store_id = $request->get('store_id');

            $store = Store::where('store_id', $Store_id)
                ->select('merchant_id', 'people_phone')
                ->first();

            if ($store) {
                if ($store->people_phone) {
                    $phone = $store->people_phone;
                } else {
                    $merchant = Merchant::where('id', $store->merchant_id)
                        ->select('phone')
                        ->first();
                    if ($merchant) {
                        $phone = $merchant->phone;
                    }
                }
            }

            $SettleModeType = $request->get('SettleModeType', '01');//结算方式
            return $this->base_open_ways($type, $code, $Store_id, $SettleModeType, $phone, $email);

        } catch (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'message' => $exception->getMessage() . $exception->getLine(),
            ]);
        }
    }

    //申请通道共用

    /**
     * @param $type
     * @param $code
     * @param $store_id
     * @param $SettleModeType
     * @param string $other 这个参数来报名参加 各种活动时 开通第二通道使用的
     * @return string
     */
    public function base_open_ways($type, $code, $store_id, $SettleModeType, $phone = '', $email = '', $other = [])
    {
        try {
            $Store = Store::where('store_id', $store_id)->first();
            if (!$Store) {
                return json_encode(['status' => 2, 'message' => '门店未认证请认证门店']);
            }
            $MyBankCategory = MyBankCategory::where('category_id', $Store->category_id)
                ->first();
            if (!$MyBankCategory) {
                return json_encode(['status' => 2, 'message' => '请选择正确的门店类目']);
            }
            $StoreBank = StoreBank::where('store_id', $store_id)->first();
            if (!$StoreBank) {
                return json_encode(['status' => 2, 'message' => '没有绑定银行卡,请先绑定银行卡']);
            }
            $StoreImg = StoreImg::where('store_id', $store_id)->first();
            if (!$StoreImg) {
                return json_encode(['status' => 2, 'message' => '没有商户资料']);
            }


            //检测必要参数
            $store_banks = [
                'store_bank_no' => $StoreBank->store_bank_no,
                'store_bank_name' => $StoreBank->store_bank_name,
                'bank_no' => $StoreBank->bank_no,
            ];
            $check_data = [
                'store_bank_no' => '银行卡号',
                'store_bank_name' => '持卡人名称',
                'bank_no' => '联行号',
            ];

            $check = $this->check_required($store_banks, $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }

            //是否有审核
            $UserStoreSet = UserStoreSet::where('user_id', 0)->first();
            if ($UserStoreSet) {
                //需要服务商审核
                if ($UserStoreSet->status_check == 1 && $Store->status != 1) {
                    $message = "服务商未审核商户";
                    if ($Store->status == 3) {
                        $message = '审核失败：' . $Store->status_desc;
                    }
                    return json_encode([
                        'status' => 2,
                        'message' => $message
                    ]);
                }
                //需要平台审核
                if ($UserStoreSet->admin_status_check == 1 && $Store->admin_status != 1) {
                    $message = "平台未审核商户";
                    if ($Store->status == 3) {
                        $message = '审核失败：' . $Store->admin_status_desc;
                    }
                    return json_encode([
                        'status' => 2,
                        'message' => $message
                    ]);
                }

            } else {
                $UserStoreSet = UserStoreSet::where('user_id', $Store->user_id)->first();
                //需要服务商审核
                if ($UserStoreSet->status_check == 1 && $Store->status != 1) {
                    $message = "服务商未审核商户";
                    if ($Store->status == 3) {
                        $message = '审核失败：' . $Store->status_desc;
                    }
                    return json_encode([
                        'status' => 2,
                        'message' => $message
                    ]);
                }
                //需要平台审核
                if ($UserStoreSet->admin_status_check == 1 && $Store->admin_status != 1) {
                    $message = "平台未审核商户";
                    if ($Store->status == 3) {
                        $message = '审核失败：' . $Store->admin_status_desc;
                    }
                    return json_encode([
                        'status' => 2,
                        'message' => $message
                    ]);
                }
            }


            //读取识别图片信息
            //法人身份证有效期
            //识别法人身份证反面

            if ($Store->head_sfz_time == "" || $Store->head_sfz_time == "") {
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
                            if (isset($valid_date[0])) {
                                $head_sfz_time = $valid_date[0];
                                $Store->head_sfz_stime = str_replace(".", "-", $head_sfz_time);
                                $Store->save();
                            }
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


            //公共判断
            $store_type = $Store->store_type;//经营性质 1-个体，2-企业，3-个人
            $config_id = $Store->config_id;
            $store_name = $Store->store_name;
            $store_bank_type = $StoreBank->store_bank_type ? $StoreBank->store_bank_type : '01';//01 对私人 02 对公
            $store_pid = $Store->pid;//
            $store_email = $Store->store_email ? $Store->store_email : $email;
            $phone = $Store->people_phone ? $Store->people_phone : $phone;

            //费率 默认商户的费率为代理商的费率
            $UserRate = UserRate::where('user_id', $Store->user_id)
                ->where('ways_type', $type)//目前是一样的直接读取支付宝就行
                ->first();

            if (!$UserRate) {
                return json_encode([
                    'status' => 2,
                    'message' => '请联系代理商开启此通道',
                ]);
            }

            $rate = $UserRate->store_all_rate;
            $rate_a = $UserRate->store_all_rate_a;
            $rate_b = $UserRate->store_all_rate_b;
            $rate_b_top = $UserRate->store_all_rate_b_top;
            $rate_c = $UserRate->store_all_rate_c;
            $rate_d = $UserRate->store_all_rate_d;
            $rate_d_top = $UserRate->store_all_rate_d_top;
            $rate_e = $UserRate->store_all_rate_e;
            $rate_f = $UserRate->store_all_rate_f;
            $rate_f_top = $UserRate->store_all_rate_f_top;
            //查找是否有此通道
            $ways1 = StorePayWay::where('store_id', $store_id)
                ->where('ways_type', $type)
                ->first();
            if ($ways1) {
                $rate = $ways1->rate;//如果门店设置走门店扫码费率
                $rate_a = $ways1->rate_a;
                $rate_b = $ways1->rate_b;
                $rate_b_top = $ways1->rate_b_top;
                $rate_c = $ways1->rate_c;
                $rate_d = $ways1->rate_d;
                $rate_d_top = $ways1->rate_d_top;
                $rate_e = $ways1->rate_e;
                $rate_f = $ways1->rate_f;
                $rate_f_top = $ways1->rate_f_top;
                if (in_array($ways1->status, [1, 2])) {
                    return json_encode(['status' => 2, 'message' => '通道已经申请,不需要重复申请']);
                }
            }

            //没有邮箱 随机一个
            if ($store_email == "") {
                $store_email = '' . $phone . '@139.com';
            }


            //新大陆
            if (7999 < $type && $type < 8999) {
                $config = new NewLandConfigController();
                $new_land_config = $config->new_land_config($config_id);
                if (!$new_land_config) {
                    return json_encode([
                        'status' => 2,
                        'message' => '新大陆配置不存在请检查配置'
                    ]);
                }

                $aop = new \App\Common\XingPOS\Aop();
                $aop->key = $new_land_config->nl_key;
                $aop->version = 'V1.0.1';//这个一定要和文档一致
                $aop->org_no = $new_land_config->org_no;

                //整理提交资料

                //银行卡类型
                $stl_sign = '1';
                if ($store_bank_type == '02') {
                    $stl_sign = '0';
                }

                //营业执照时间
                $store_license_time = $Store->store_license_time;
                if ($store_license_time == "") {
                    $store_license_time = '9999-12-31';//长期
                }

                //对私判断结算人和法人是否是一个人
                //结算身份证信息默认都是法人
                $bank_sfz_no = $Store->head_sfz_no;
                $bank_sfz_time = $Store->head_sfz_time;


                //法人名字和结算人名字不一样读取结算人的身份证
                if ($Store->head_name != $StoreBank->store_bank_name) {

                    //读取识别图片信息
                    //法人身份证有效期
                    //识别结算卡身份证正面
                    $is_card_a = 1;
                    if ($is_card_a) {
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
                    if ($is_card_b) {
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
                    $bank_sfz_time = $StoreBank->bank_sfz_time;
                }


                $incom_type = '2';//企业个体
                if ($store_type == 3) {
                    //个人
                    $incom_type = '1';
                }

                $stoe_nm = $Store->province_name . $Store->city_name . $Store->store_name;
                if (mb_strlen($stoe_nm, 'UTF8') > 20) {
                    $stoe_nm = $Store->province_name . $Store->store_name;
                }
                $sign_data = [
                    "incom_type" => $incom_type,
                    "stl_typ" => '1',//1 T+1 2 D+1（对公账户不能选择D+1）
                    "stl_sign" => $stl_sign,////结算标志 1-对私 0-对 公
                    "stl_oac" => $StoreBank->store_bank_no,//结算账号
                    "bnk_acnm" => $StoreBank->store_bank_name,//户名
                    "wc_lbnk_no" => $StoreBank->bank_no,//联行行号
                    "stoe_nm" => $stoe_nm,//省市区+门店名
                    "stoe_cnt_nm" => $Store->head_name,//门店联系人
                    "stoe_cnt_tel" => $phone,//联系人手机号
                    "mcc_cd" => $MyBankCategory->mcc,//mcc
                    "stoe_area_cod" => $Store->area_code,//区域码
                    "stoe_adds" => $Store->store_address, //门店详细地址
                    "mailbox" => $store_email,//
                    "alipay_flg" => "Y",
                    "yhkpay_flg" => "Y",
                ];

                $no_sign_date = [
                    "icrp_id_no" => $bank_sfz_no,//结算人身份证号
                    "crp_exp_dt_tmp" => $this->time_action($bank_sfz_time, '2020-02-02'),//结算人身份证有效期
                    "fee_rat_scan" => $rate,//微信扫码费率
                    "fee_rat3_scan" => $rate,//支付宝扫码费率
                    "fee_rat1_scan" => $rate_a,//银联二维码费率-1000以下
                    "fee_rat2_scan" => $rate_c,//银联二维码费率-1000以上
                    "fee_rat" => $rate_f,//借记卡费率
                    "max_fee_amt" => $rate_f_top,//借记卡封顶
                    "fee_rat1" => $rate_e,//贷记卡费率
                    "tranTyps" => "C1,C2,C3,C4,C5,C6,C7",
                    "suptDbfreeFlg" => "1",
                    "cardTyp" => "00",//
                    "trm_rec" => "1",//pos终端数量
                    "trm_tp" => "1",//台牌终端数量
                    "trm_scan" => "1",//扫码终端数量
                    "bus_lic_no" => $Store->store_license_no ? $Store->store_license_no : $Store->head_sfz_no,//营业执照号为空传身份证号
                    "bse_lice_nm" => $Store->store_name,//营业执照名
                    "crp_nm" => $Store->head_name,//法人
                    "mercAdds" => $Store->province_name . $Store->city_name . $Store->area_name . $Store->store_address,//商户地址
                    "bus_exp_dt" => $this->time_action($store_license_time, '2020-02-02'),//营业执照有效期
                    "crp_id_no" => $Store->head_sfz_no,//法人身份证号
                    "crp_exp_dt" => $this->time_action($Store->head_sfz_time, '2020-02-02'),//法人身份证有效期

                ];

                $NewLandStore = NewLandStore::where('store_id', $store_id)->first();


                //如果已经进件了-修改
                if ($NewLandStore && $NewLandStore->nl_mercId) {
                    $jj_status = $NewLandStore->jj_status;
                    $img_status = $NewLandStore->img_status;
                    $tj_status = $NewLandStore->tj_status;
                    $check_flag = $NewLandStore->check_flag;
                    $check_qm = $NewLandStore->check_qm;

                    //如果审核通过了 要修改 需要调审核修改申请
                    if ($check_flag == '1') {
                        $nl_mercId = $NewLandStore->nl_mercId;
                        $sign_data_ShenQing = [
                            'mercId' => $nl_mercId,
                        ];
                        $request_obj = new  \App\Common\XingPOS\Request\XingStoreShangHuXiuGaiShenQing();
                        $aop->version = 'V1.0.1';//这个一定要和文档一致
                        $request_obj->setBizContent($sign_data_ShenQing, []);
                        $return = $aop->executeStore($request_obj);
                        //不成功
                        if ($return['msg_cd'] != '000000') {
                            return json_encode([
                                'status' => 2,
                                'message' => $return['msg_dat'],
                            ]);
                        }
                    }

                    //全部改为0
                    $jj_status = 0;
                    $img_status = 0;
                    $tj_status = 0;
                    $check_flag = 0;
                    $check_qm = 0;

                    $NewLandStore->jj_status = $jj_status;
                    $NewLandStore->img_status = $img_status;
                    $NewLandStore->tj_status = $tj_status;
                    $NewLandStore->check_flag = $check_flag;//1-通过 2-驳回 3.转人工
                    $NewLandStore->check_qm = $check_qm;
                    $NewLandStore->save();


                }


                //1.进件请求
                //已经成功进件过了
                if ($NewLandStore && $NewLandStore->jj_status == 1) {
                    $mercId = $NewLandStore->nl_mercId;
                    $stoe_id = $NewLandStore->nl_stoe_id;
                    $log_no = $NewLandStore->log_no;

                } else {
                    $request_obj = new  \App\Common\XingPOS\Request\XingStoreShangHuJinJian();
                    $aop->version = 'V1.0.5';//这个一定要和文档一致
                    $request_obj->setBizContent($sign_data, $no_sign_date);
                    $return = $aop->executeStore($request_obj);

                    //不成功
                    if ($return['msg_cd'] != '000000') {
                        return json_encode([
                            'status' => 2,
                            'message' => $return['msg_dat'],
                        ]);
                    }
                    $jj_status = 1;

                    //成功以后
                    $mercId = $return['mercId'];
                    $stoe_id = $return['stoe_id'];
                    $log_no = $return['log_no'];
                    $data_insert = [
                        'store_id' => $store_id,
                        'store_name' => $store_name,
                        'config_id' => $config_id,
                        'jj_status' => $jj_status,
                        'nl_mercId' => $mercId,
                        'nl_stoe_id' => $stoe_id,
                        'log_no' => $log_no,
                    ];
                    $NewLandStore = NewLandStore::where('store_id', $store_id)->first();
                    if ($NewLandStore) {
                        $NewLandStore->update($data_insert);
                        $NewLandStore->save();
                    } else {
                        NewLandStore::create($data_insert);
                    }
                }


                //2.上传图片
                $NewLandStore = NewLandStore::where('store_id', $store_id)->first();
                //未提交商户图片
                if ($NewLandStore && $NewLandStore->img_status != 1) {

                    $data = [
                        "mercId" => $mercId,
                        'log_no' => $log_no,
                        'stoe_id' => $stoe_id,
                    ];

                    $store_type = 2;
                    $img_data = [];
                    //对公类型的商户
                    if ((int)$store_bank_type == '02') {
                        $img_data = [
                            [
                                'imgTyp' => '1', //营业执照
                                'imgNm' => 'yyzz.jpg',
                                'imgFile' => $this->new_land_img($StoreImg->store_license_img ? $StoreImg->store_license_img : $StoreImg->head_sfz_img_a, '营业执照')
                            ],
                            [
                                'imgTyp' => '14', //商户协议
                                'imgNm' => 'xyzp.jpg',
                                'imgFile' => $this->new_land_img($StoreImg->store_license_img ? $StoreImg->store_license_img : $StoreImg->head_sfz_img_a, '商户协议')
                            ], [
                                'imgTyp' => '12', //开户许可证
                                'imgNm' => 'khxk.jpg',
                                'imgFile' => $this->new_land_img($StoreImg->store_industrylicense_img, '开户许可证')
                            ],
                            [
                                'imgTyp' => '4', //法人正面
                                'imgNm' => 'frz.jpg',
                                'imgFile' => $this->new_land_img($StoreImg->head_sfz_img_a, '法人正面')
                            ], [
                                'imgTyp' => '5', //法人反面
                                'imgNm' => 'frf.jpg',
                                'imgFile' => $this->new_land_img($StoreImg->head_sfz_img_b, '法人反面')
                            ],
                            [
                                'imgTyp' => '9', //结算法人正面
                                'imgNm' => 'jsfra.jpg',
                                'imgFile' => $this->new_land_img($StoreImg->head_sfz_img_a, '结算法人正面')
                            ], [
                                'imgTyp' => '10', //结算法人反面
                                'imgNm' => 'jsfrb.jpg',
                                'imgFile' => $this->new_land_img($StoreImg->head_sfz_img_b, '结算法人反面')
                            ],
                            [
                                'imgTyp' => '6', //门头照片
                                'imgNm' => 'mtz.jpg',
                                'imgFile' => $this->new_land_img($StoreImg->store_logo_img, '门头照片')
                            ],
                            [
                                'imgTyp' => '7', //场景照
                                'imgNm' => 'cjz.jpg',
                                'imgFile' => $this->new_land_img($StoreImg->store_img_b, '场景照')
                            ],
                            [
                                'imgTyp' => '8', //收银台
                                'imgNm' => 'syt.jpg',
                                'imgFile' => $this->new_land_img($StoreImg->store_img_a, '收银台')
                            ],
                        ];
                    } else {
                        //对私结算
                        //1.同法人
                        //2.非法人
                        //3.//个人
                        //$store_type 经营性质 1-个体，2-企业，3-个人

                        // 3-个人
                        if ($store_type == 3) {
                            $img_data = [
                                [
                                    'imgTyp' => '14', //商户协议
                                    'imgNm' => 'xyzp.jpg',
                                    'imgFile' => $this->new_land_img($StoreImg->store_license_img ? $StoreImg->store_license_img : $StoreImg->head_sfz_img_a, '商户协议')
                                ],
                                [
                                    'imgTyp' => '4', //法人正面
                                    'imgNm' => 'frz.jpg',
                                    'imgFile' => $this->new_land_img($StoreImg->head_sfz_img_a, '法人正面')
                                ], [
                                    'imgTyp' => '5', //法人反面
                                    'imgNm' => 'frf.jpg',
                                    'imgFile' => $this->new_land_img($StoreImg->head_sfz_img_b, '法人反面')
                                ],
                                [
                                    'imgTyp' => '13', //结算人法人手持身份证
                                    'imgNm' => 'jsrscsfz.jpg',
                                    'imgFile' => $this->new_land_img($StoreImg->bank_sc_img ? $StoreImg->bank_sc_img : $StoreImg->head_sc_img, '结算人法人手持身份证')
                                ],
                                [
                                    'imgTyp' => '6', //结算人站在门口
                                    'imgNm' => 'jsrzzmk.jpg',
                                    'imgFile' => $this->new_land_img($StoreImg->head_store_img, '结算人站在门口')
                                ],
                                [
                                    'imgTyp' => '7', //场景照
                                    'imgNm' => 'cjz.jpg',
                                    'imgFile' => $this->new_land_img($StoreImg->store_img_b, '场景照')
                                ],
                                [
                                    'imgTyp' => '8', //收银台
                                    'imgNm' => 'syt.jpg',
                                    'imgFile' => $this->new_land_img($StoreImg->store_img_a, '收银台')
                                ],
                                [
                                    'imgTyp' => '9', //结算法人正面
                                    'imgNm' => 'jsfra.jpg',
                                    'imgFile' => $this->new_land_img($StoreImg->head_sfz_img_a, '结算法人正面')
                                ], [
                                    'imgTyp' => '10', //结算法人反面
                                    'imgNm' => 'jsfrb.jpg',
                                    'imgFile' => $this->new_land_img($StoreImg->head_sfz_img_b, '结算法人反面')
                                ],
                                [
                                    'imgTyp' => '11', //银行卡正面
                                    'imgNm' => 'yhkzm.jpg',
                                    'imgFile' => $this->new_land_img($StoreImg->bank_img_a, '银行卡正面')
                                ],

                            ];
                        } else {
                            //同法人
                            if ($Store->head_name == $StoreBank->store_bank_name) {
                                $img_data = [
                                    [
                                        'imgTyp' => '1', //营业执照
                                        'imgNm' => 'yyzz.jpg',
                                        'imgFile' => $this->new_land_img($StoreImg->store_license_img ? $StoreImg->store_license_img : $StoreImg->head_sfz_img_a, '营业执照')
                                    ],
                                    [
                                        'imgTyp' => '14', //商户协议
                                        'imgNm' => 'xyzp.jpg',
                                        'imgFile' => $this->new_land_img($StoreImg->store_license_img ? $StoreImg->store_license_img : $StoreImg->head_sfz_img_a, '商户协议')
                                    ],
                                    [
                                        'imgTyp' => '4', //法人正面
                                        'imgNm' => 'frz.jpg',
                                        'imgFile' => $this->new_land_img($StoreImg->head_sfz_img_a, '法人正面')
                                    ], [
                                        'imgTyp' => '5', //法人反面
                                        'imgNm' => 'frf.jpg',
                                        'imgFile' => $this->new_land_img($StoreImg->head_sfz_img_b, '法人反面')
                                    ], [
                                        'imgTyp' => '6', //门头照片
                                        'imgNm' => 'mtz.jpg',
                                        'imgFile' => $this->new_land_img($StoreImg->store_logo_img, '门头照片')
                                    ],
                                    [
                                        'imgTyp' => '7', //场景照
                                        'imgNm' => 'cjz.jpg',
                                        'imgFile' => $this->new_land_img($StoreImg->store_img_b, '场景照')
                                    ],
                                    [
                                        'imgTyp' => '8', //收银台
                                        'imgNm' => 'syt.jpg',
                                        'imgFile' => $this->new_land_img($StoreImg->store_img_a, '收银台')
                                    ], [
                                        'imgTyp' => '9', //结算法人正面
                                        'imgNm' => 'jsfra.jpg',
                                        'imgFile' => $this->new_land_img($StoreImg->head_sfz_img_a, '结算法人正面')
                                    ], [
                                        'imgTyp' => '10', //结算法人反面
                                        'imgNm' => 'jsfrb.jpg',
                                        'imgFile' => $this->new_land_img($StoreImg->head_sfz_img_b, '结算法人反面')
                                    ],
                                    [
                                        'imgTyp' => '11', //银行卡正面
                                        'imgNm' => 'yhkzm.jpg',
                                        'imgFile' => $this->new_land_img($StoreImg->bank_img_a, '银行卡正面')
                                    ],
                                ];
                            } else {
                                //非法人
                                $img_data = [
                                    [
                                        'imgTyp' => '14', //商户协议
                                        'imgNm' => 'xyzp.jpg',
                                        'imgFile' => $this->new_land_img($StoreImg->store_license_img ? $StoreImg->store_license_img : $StoreImg->head_sfz_img_a, '商户协议')
                                    ],
                                    [
                                        'imgTyp' => '1', //营业执照
                                        'imgNm' => 'yyzz.jpg',
                                        'imgFile' => $this->new_land_img($StoreImg->store_license_img ? $StoreImg->store_license_img : $StoreImg->head_sfz_img_a, '营业执照')
                                    ],
                                    [
                                        'imgTyp' => '4', //法人正面
                                        'imgNm' => 'frz.jpg',
                                        'imgFile' => $this->new_land_img($StoreImg->head_sfz_img_a, '法人正面')
                                    ], [
                                        'imgTyp' => '5', //法人反面
                                        'imgNm' => 'frf.jpg',
                                        'imgFile' => $this->new_land_img($StoreImg->head_sfz_img_b, '法人反面')
                                    ], [
                                        'imgTyp' => '6', //门头照片
                                        'imgNm' => 'mtz.jpg',
                                        'imgFile' => $this->new_land_img($StoreImg->store_logo_img, '门头照片')
                                    ],
                                    [
                                        'imgTyp' => '7', //场景照
                                        'imgNm' => 'cjz.jpg',
                                        'imgFile' => $this->new_land_img($StoreImg->store_img_b, '场景照')
                                    ],
                                    [
                                        'imgTyp' => '8', //收银台
                                        'imgNm' => 'syt.jpg',
                                        'imgFile' => $this->new_land_img($StoreImg->store_img_a, '收银台')
                                    ], [
                                        'imgTyp' => '9', //结算法人正面
                                        'imgNm' => 'jsfra.jpg',
                                        'imgFile' => $this->new_land_img($StoreImg->bank_sfz_img_a ? $StoreImg->bank_sfz_img_a : $StoreImg->head_sfz_img_a, '结算法人正面')
                                    ], [
                                        'imgTyp' => '10', //结算法人反面
                                        'imgNm' => 'jsfrb.jpg',
                                        'imgFile' => $this->new_land_img($StoreImg->bank_sfz_img_b ? $StoreImg->bank_sfz_img_b : $StoreImg->head_sfz_img_b, '结算法人反面')
                                    ],
                                    [
                                        'imgTyp' => '11', //银行卡正面
                                        'imgNm' => 'yhkzm.jpg',
                                        'imgFile' => $this->new_land_img($StoreImg->bank_img_a, '银行卡正面')
                                    ],
                                    [
                                        'imgTyp' => '16', //授权结算书
                                        'imgNm' => 'sqjss.jpg',
                                        'imgFile' => $this->new_land_img($StoreImg->store_auth_bank_img ? $StoreImg->store_auth_bank_img : $StoreImg->head_sfz_img_a, '授权结算书')
                                    ],
                                ];
                            }
                        }

                    }
                    foreach ($img_data as $k => $v) {
                        $data['imgTyp'] = $v['imgTyp'];
                        $data['imgNm'] = $v['imgNm'];
                        $data_no_sign['imgFile'] = $v['imgFile'];

                        $request_obj = new  \App\Common\XingPOS\Request\XingStoreTuPianShangChuan();
                        $aop->version = 'V1.0.1';//这个一定要和文档一致
                        $request_obj->setBizContent($data, $data_no_sign);
                        $return = $aop->executeStore($request_obj);

                        //不成功
                        if ($return['msg_cd'] != '000000') {
                            return $return_err = json_encode([
                                'status' => 2,
                                'message' => $return['msg_dat'] . '-' . $v['imgTyp'],
                            ]);
                        }
                    }

                    //图片确认成功
                    $img_status = 1;
                    $NewLandStore->img_status = $img_status;
                    $NewLandStore->save();


                }


                //3.商户提交
                $status = 2;
                $status_desc = '审核中';


                // if ($NewLandStore && $NewLandStore->tj_status != 1) {
                $sign_data = [
                    'mercId' => $mercId,
                    'log_no' => $log_no,
                ];
                $request_obj = new  \App\Common\XingPOS\Request\XingStoreShangHuTiJiao();
                $aop->version = 'V1.0.1';//这个一定要和文档一致
                $request_obj->setBizContent($sign_data, []);
                $return = $aop->executeStore($request_obj);

                //不成功
                if ($return['msg_cd'] != '000000' && $return['msg_cd'] != 'SCM60003') {
                    return $return_err = json_encode([
                        'status' => 2,
                        'message' => $return['msg_dat'],
                    ]);
                }
                //商户确认提交成功
                $tj_status = 1;
                $check_flag = $return['check_flag'];

                $NewLandStore->tj_status = $tj_status;
                $NewLandStore->check_flag = $check_flag;

                //直接通过
                if ($return['check_flag'] == 1) {
                    $status = 1;
                    $status_desc = '开通成功';
                    $NewLandStore->nl_key = $return['key'];
                    $NewLandStore->trmNo = $return['REC'][0]['trmNo'];

                }

                //驳回
                if ($return['check_flag'] == 2) {
                    $status = 3;
                    $status_desc = '开通失败';
                }


                //转人工审核
                if ($return['check_flag'] == 2) {
                    $status = 2;
                    $status_desc = '审核中';
                }


                $NewLandStore->save();

                // }


                //4. 安心签名
                if ($NewLandStore && $NewLandStore->check_qm != 1) {
                    $sign_data = [
                        'mercId' => $mercId,
                    ];

                    $request_obj = new  \App\Common\XingPOS\Request\XingStoreShangHuFaSongQianShuAnXinQianDiZhi();
                    $aop->version = 'V1.0.1';//这个一定要和文档一致
                    $request_obj->setBizContent($sign_data);
                    $return = $aop->executeStore($request_obj);

                    //不成功
                    if ($return['msg_cd'] != '000000') {
                        return $return_err = json_encode([
                            'status' => 2,
                            'message' => $return['msg_dat'],
                        ]);
                    }
                    $check_qm = 1;
                    $NewLandStore->check_qm = $check_qm;
                    $NewLandStore->save();

                }


                //人工审核
                if ($status == 2) {
                    //顺带入临时库等待查询状态
                    $NewLandStoreItem = NewLandStoreItem::where('store_id', $store_id)->first();

                    $item_insert = [
                        'store_id' => $store_id,
                        'config_id' => $config_id,
                        'jj_status' => $jj_status,
                        'img_status' => $img_status,
                        'tj_status' => $tj_status,
                        'nl_mercId' => $mercId,
                        'nl_stoe_id' => $stoe_id,
                        'log_no' => $log_no,
                        'check_flag' => $check_flag,
                        'check_qm' => $check_qm,
                        'nl_key' => '',
                        'trmNo' => '',
                    ];

                    if ($NewLandStoreItem) {
                        $NewLandStoreItem->update($item_insert);
                        $NewLandStoreItem->save();
                    } else {
                        NewLandStoreItem::create($item_insert);
                    }

                }


                $send_ways_data['config_id'] = $config_id;
                $send_ways_data['store_id'] = $store_id;
                $send_ways_data['rate'] = $rate;
                $send_ways_data['status'] = $status;
                $send_ways_data['status_desc'] = $status_desc;
                $send_ways_data['company'] = 'newland';
                $return = $this->send_ways_data($send_ways_data);
                return $return;
            }

            //京东聚合
            if (5999 < $type && $type < 6999) {
                $config = new JdConfigController();
                $jd_config = $config->jd_config($config_id);
                if (!$jd_config) {
                    return json_encode(['status' => 2, 'message' => '没有配置相关通道']);
                }

                //京东聚合必须是5个字
                if (mb_strlen($Store->store_name, 'UTF8') < 5) {
                    return json_encode([
                        'status' => 2,
                        'message' => '门店名称必须大于5个字',
                    ]);
                }

                $jd_store = JdStoreItem::where('store_id', $store_id)->first();
                $buildOrRepair = '0';
                if ($jd_store && $jd_store->store_true) {
                    $buildOrRepair = '1';
                }
                $data['store_id'] = $store_id;
                $data['phone'] = $phone;
                $data['email'] = $store_email;
                $data['buildOrRepair'] = $buildOrRepair;//入驻标识0：新入驻，1：修改
                $data['store_md_key'] = $jd_config->store_md_key;//'8data998mnwepxugnk03-2zirb';//入驻标识0：新入驻，1：修改
                $data['store_des_key'] = $jd_config->store_des_key;// 'XdD4NyO2LG03DThegNm/bhmP6jR6zvg3';//入驻标识0：新入驻，1：修改
                $data['agentNo'] = $jd_config->agentNo;//110770481
                $data['serialNo'] = time() . rand(1000, 9000);
                $data['request_url'] = 'https://psi.jd.com/merchant/enterSingle';//入驻标识0：新入驻，1：修改
                $OBJ = new StoreController();
                $open_store = $OBJ->open_store($data);

                //进件成功
                if ($open_store['code'] === "0000") {
                    //已经入驻过了
                    if ($jd_store && $jd_store->store_true) {
                        //更新门店
                        $jd_store->serialNo = $open_store['serialNo'];
                        $jd_store->merchant_no = $open_store['merchantNo'];
                        $jd_store->save();

                        if ($jd_store->pay_true == 0) {

                            $data1 = [
                                'request_url' => 'https://psi.jd.com/merchant/applySingle',
                                'agentNo' => $jd_config->agentNo,
                                'serialNo' => "" . time() . $phone . "1",
                                'merchantNo' => $open_store['merchantNo'],
                                'store_md_key' => $jd_config->store_md_key,
                                'store_des_key' => $jd_config->store_des_key,
                                'productId' => '35',
                                'payToolId' => '1060',
                                'mfeeType' => '2',
                                'mfee' => $rate,
                            ];

                            //提交申请产品-白条
                            $data1['productId'] = '405';
                            $re = $OBJ->store_open_ways($data1);

                            //提交申请产品-聚合支付
                            $data1['productId'] = '35';
                            $re = $OBJ->store_open_ways($data1);

                            if ($re['code'] == "0000") {
                                JdStoreItem::where('store_id', $store_id)
                                    ->update(
                                        [
                                            'pay_true' => 1
                                        ]);
                            } else {
                                $data['rate'] = $rate;
                                $data['status'] = 3;
                                $data['status_desc'] = $re['message'];
                                $data['company'] = 'jdjr';
                                $this->send_ways_data($data);

                                return json_encode([
                                    'status' => 2,
                                    'message' => $re['message'],
                                ]);
                            }
                        }

                        //默认支付通道未注册成功
                        $data['rate'] = $rate;
                        $data['status'] = 2;
                        $data['status_desc'] = '审核中';
                        $data['company'] = 'jdjr';
                        $return = $this->send_ways_data($data);
                        return json_encode($return);


                    } else {
                        //插入数据库
                        $insert_data = [
                            'pid' => $Store->pid,
                            'store_id' => $Store->store_id,
                            'config_id' => $Store->config_id,
                            'merchant_no' => $open_store['merchantNo'],
                            'store_true' => 1,
                            'pay_true' => 0,
                            'serialNo' => $open_store['serialNo']
                        ];
                        $insert = JdStoreItem::create($insert_data);
                        //提交申请产品
                        $data1 = [
                            'request_url' => 'https://psi.jd.com/merchant/applySingle',
                            'agentNo' => $jd_config->agentNo,
                            'serialNo' => "" . time() . $phone . "2",
                            'merchantNo' => $open_store['merchantNo'],
                            'store_md_key' => $jd_config->store_md_key,
                            'store_des_key' => $jd_config->store_des_key,
                            'productId' => '35',
                            'payToolId' => '1060',
                            'mfeeType' => '2',
                            'mfee' => $rate,
                        ];
                        $re = $OBJ->store_open_ways($data1);
                        if ($re['code'] == "0000") {
                            JdStoreItem::where('store_id', $store_id)
                                ->update(
                                    [
                                        'pay_true' => 1
                                    ]);

                            $data['rate'] = $rate;
                            $data['status'] = 2;
                            $data['status_desc'] = '审核中';
                            $data['company'] = 'jdjr';
                            $return = $this->send_ways_data($data);
                            return json_encode($return);
                        } else {
                            return json_encode([
                                'status' => 2,
                                'message' => $re['message'],
                            ]);
                        }

                    }


                } else {
                    return json_encode([
                        'status' => 2,
                        'message' => $open_store['message'],
                    ]);
                }

            }


            //网商银行
            if (2999 < $type && $type < 3999) {
                try {
                    if (!$store_id || !$code) {
                        return json_encode(['status' => 2, 'message' => '门第ID验证码参数不正确']);
                    }
                    $category_id = $Store->category_id;
                    if ($Store->category_id == "") {
                        if ($Store->category_name) {
                            $ca = MyBankCategory::where('category_name', $Store->category_name)
                                ->select('category_id')
                                ->first();
                            if ($ca) {
                                $category_id = $ca->category_id;
                            } else {
                                return json_encode(['status' => 2, 'message' => '门店分类不正确请重新选择分类']);
                            }

                        } else {
                            return json_encode(['status' => 2, 'message' => '门店分类不正确请重新选择分类']);
                        }
                    }


                    //企业账户不支持结算到余利宝
                    if ($Store->store_type == 2 && $SettleModeType == "02") {
                        return json_encode(['status' => 2, 'message' => '企业账户不支持结算到余利宝']);
                    }

                    //判断是公司还是个人
                    if (!$StoreBank) {
                        return json_encode(['status' => 2, 'message' => '没有绑定银行卡,请先绑定银行卡']);
                    }

                    $bankcard_type = $StoreBank->store_bank_type;//01 对私人 02 对公
                    $aop = new \App\Api\Controllers\MyBank\BaseController();
                    $ao = $aop->aop($config_id);
                    $ao->url = env("MY_BANK_request2");


                    $store_ta_rate = $rate + 0.02;//网商银行不支持t0 默认暂时设置
                    //手续费列表
                    $FeeParamList = [
                        [
                            "channelType" => "01",//支付宝
                            "feeType" => "01",//t0
                            "feeValue" => number_format($store_ta_rate / 100, 4, '.', '')
                        ],
                        [
                            "channelType" => "01",//微信支付
                            "feeType" => "02",//t1
                            "feeValue" => number_format($rate / 100, 4, '.', '')
                        ],
                        [
                            "channelType" => "02",
                            "feeType" => "01",
                            "feeValue" => number_format($store_ta_rate / 100, 4, '.', '')
                        ],
                        [
                            "channelType" => "02",
                            "feeType" => "02",
                            "feeValue" => number_format($rate / 100, 4, '.', '')
                        ]
                    ];


                    //活动
                    if (!empty($other)) {
                        //蓝海行 将支付宝费率设置为0
                        if ($other['activity_type'] == 'RBLUE') {
                            //手续费列表
                            $FeeParamList = [
                                [
                                    "channelType" => "01",//支付宝
                                    "feeType" => "01",//t0
                                    "feeValue" => 0
                                ],
                                [
                                    "channelType" => "01",//支付宝
                                    "feeType" => "02",//t1
                                    "feeValue" => 0
                                ],
                                [
                                    "channelType" => "02",
                                    "feeType" => "01",
                                    "feeValue" => $store_ta_rate / 100
                                ],
                                [
                                    "channelType" => "02",
                                    "feeType" => "02",
                                    "feeValue" => $rate / 100
                                ]
                            ];
                        }


                        $store_id = $store_id . '456';


                    }


                    //结算到他行卡
                    if ($SettleModeType == '01') {
                        if ($Store->store_type == 1) {
                            $ao->Function = "ant.mybank.merchantprod.merchant.register";
                            $MerchantType = '02';
                            $SupportPrepayment = 'N';
                            $SettleMode = '01';
                            $principalPerson = $Store->store_name;
                            $PrincipalMobile = $Store->people_phone;
                        }
                        //企业
                        if ($Store->store_type == 2) {
                            $ao->Function = "ant.mybank.merchantprod.merchant.register";
                            $MerchantType = '03';//企业
                            $SupportPrepayment = 'N';
                            $SettleMode = '01';
                            $principalPerson = $Store->store_name;
                            $PrincipalMobile = $Store->people_phone;
                            $CertOrgCode = $Store->store_license_no;//组织机构代码
                        }

                        //个人
                        if ($Store->store_type == 3) {
                            $ao->Function = "ant.mybank.merchantprod.merchant.register";
                            $MerchantType = '01';
                            $SupportPrepayment = 'N';
                            $SettleMode = '01';
                            $principalPerson = $Store->store_name;
                            $PrincipalMobile = $Store->people_phone;
                        }
                    } //结算到余利宝
                    else {
                        //个体 开通余利宝
                        if ($Store->store_type == 1) {
                            $ao->Function = "ant.mybank.merchantprod.merchant.registerWithAccount";
                            $MerchantType = '02';
                            $SupportPrepayment = 'N';
                            $SettleMode = '02';
                            $principalPerson = $Store->store_name;
                            $PrincipalMobile = $Store->people_phone;
                        }
                        //企业
                        if ($Store->store_type == 2) {
                            $ao->Function = "ant.mybank.merchantprod.merchant.register";
                            $MerchantType = '03';//企业
                            $SupportPrepayment = 'N';
                            $SettleMode = '01';
                            $principalPerson = $Store->store_name;
                            $PrincipalMobile = $Store->people_phone;
                            $CertOrgCode = $Store->store_license_no;//组织机构代码
                        }

                        //个人
                        if ($Store->store_type == 3) {
                            $ao->Function = "ant.mybank.merchantprod.merchant.registerWithAccount";
                            $MerchantType = '01';
                            $SupportPrepayment = 'N';
                            $SettleMode = '02';
                            $principalPerson = $Store->store_name;
                            $PrincipalMobile = $Store->people_phone;
                        }
                    }
                    //默认失败
                    $send_ways_data['rate'] = $rate;
                    $send_ways_data['store_id'] = $store_id;
                    $send_ways_data['status'] = 3;
                    $send_ways_data['company'] = 'mybank';

                    //商户资料
                    $CertPhotoA = $this->images_get($StoreImg->head_sfz_img_a);
                    $CertPhotoB = $this->images_get($StoreImg->head_sfz_img_b);
                    $ShopPhoto = $this->images_get($StoreImg->store_logo_img);
                    $LicensePhoto = $this->images_get($StoreImg->store_license_img);
                    $CheckstandPhoto = $this->images_get($StoreImg->store_img_a);//收银台
                    $ShopEntrancePhoto = $this->images_get($StoreImg->store_img_b);//门店内景

                    $CertPhotoA = $this->uploadIMG('01', $CertPhotoA, $config_id);
                    if ($CertPhotoA['status'] == 0) {
                        $send_ways_data['status_desc'] = $CertPhotoA['message'];
                        $return = $this->send_ways_data($send_ways_data);
                        return json_encode($return);
                    }
                    sleep(2);
                    $CertPhotoB = $this->uploadIMG('02', $CertPhotoB, $config_id);
                    if ($CertPhotoB['status'] == 0) {
                        $send_ways_data['status_desc'] = $CertPhotoB['message'];
                        $return = $this->send_ways_data($send_ways_data);
                        return json_encode($return);
                    }
                    sleep(2);
                    $ShopPhoto = $this->uploadIMG('06', $ShopPhoto, $config_id);
                    if ($ShopPhoto['status'] == 0) {
                        $send_ways_data['status_desc'] = $ShopPhoto['message'];
                        $return = $this->send_ways_data($send_ways_data);
                        return json_encode($return);
                    }
                    sleep(2);

                    $CheckstandPhoto = $this->uploadIMG('08', $CheckstandPhoto, $config_id);
                    if ($CheckstandPhoto['status'] == 0) {
                        $send_ways_data['status_desc'] = $CheckstandPhoto['message'];
                        $return = $this->send_ways_data($send_ways_data);
                        return json_encode($return);
                    }
                    sleep(2);
                    $ShopEntrancePhoto = $this->uploadIMG('09', $ShopEntrancePhoto, $config_id);
                    if ($ShopEntrancePhoto['status'] == 0) {
                        $send_ways_data['status_desc'] = $ShopEntrancePhoto['message'];
                        $return = $this->send_ways_data($send_ways_data);
                        return json_encode($return);
                    }
                    sleep(2);
                    //个人没有营业执照不需要上传
                    if ($Store->store_type != 3) {
                        $LicensePhoto = $this->uploadIMG('03', $LicensePhoto, $config_id);
                        if ($LicensePhoto['status'] == 0) {
                            $send_ways_data['status_desc'] = $LicensePhoto['message'];
                            $return = $this->send_ways_data($send_ways_data);
                            return json_encode($return);
                        }
                        sleep(2);
                    } else {
                        $LicensePhoto = [];
                        $LicensePhoto['url'] = "";
                    }

                    $MerchantDetail = [
                        "alias" => $Store->store_short_name,
                        "contactMobile" => $Store->people_phone,
                        "contactName" => $Store->people,
                        "province" => $Store->province_code,
                        "city" => $Store->city_code,
                        "district" => $Store->area_code,
                        "address" => $Store->store_address,
                        "servicePhoneNo" => $Store->people_phone,
                        // "email" => $Store->people_phone . '@139.com',
                        "principalPerson" => $principalPerson,
                        "principalCertType" => "01",
                        "PrincipalMobile" => $PrincipalMobile,
                        "principalCertNo" => $Store->head_sfz_no,
                        'PrincipalPerson' => $Store->head_name,
                        'CertPhotoA' => $CertPhotoA['url'],//法人身份证
                        'CertPhotoB' => $CertPhotoB['url'],
                        'LicensePhoto' => $LicensePhoto['url'],
                        'ShopPhoto' => $ShopPhoto['url'],//门头
                        'CheckstandPhoto' => $CheckstandPhoto['url'],//门头
                        'ShopEntrancePhoto' => $ShopEntrancePhoto['url'],//门头
                    ];

                    //企业
                    if ($Store->store_type == 2) {
                        $MerchantDetail['LegalPerson'] = $Store->store_name;
                        $MerchantDetail['CertOrgCode'] = $CertOrgCode;

                        //开户许可证
                        $IndustryLicensePhoto = $this->images_get($Store->store_industrylicense_url);
                        $IndustryLicensePhoto = $this->uploadIMG('05', $IndustryLicensePhoto, $config_id);
                        if ($IndustryLicensePhoto['status'] == 0) {
                            $send_ways_data['status_desc'] = $IndustryLicensePhoto['message'];
                            $return = $this->send_ways_data($send_ways_data);
                            return json_encode($return);
                        }
                        $MerchantDetail['IndustryLicensePhoto'] = $IndustryLicensePhoto['url'];//开户许可证
                    }


                    //结算到余利宝
                    if ($SettleModeType == '02') {
                        //个体
                        if ($Store->store_type == 1) {
                            $MerchantDetail['OtherBankCardNo'] = $StoreBank->bankcard_no;
                        }

                        //个人
                        if ($Store->store_type == 3) {
                            $MerchantDetail['OtherBankCardNo'] = $StoreBank->bankcard_no;
                        }
                    }

                    //个人
                    if ($Store->store_type != 3) {
                        $MerchantDetail['BussAuthNum'] = $Store->store_license_no;//营业执照工商注册号。若商户类型为“自然人”无需上送。
                    }

                    /****关注服务商公众号的类型开始****/
                    $PartnerType = '03';//默认是关注有梦想的公众号服务商的关注
                    //网商配置
                    $mbconfig = new MyBankConfigController();


                    //代表有新渠道
                    $is_new_appid = '';
                    $array = [
                        'https://yh.yihoupay.com',
                        'http://yh.yihoupay.com',
                        'https://yb.xiangyongpay.com',
                        'http://yb.xiangyongpay.com',
                        'http://pay.fuya18.com',
                        'https://pay.fuya18.com',
                        "https://x.umxnt.com",
                        "http://x.umxnt.com",
                        "http://ss.tonlot.com",
                        "https://ss.tonlot.com",
                        "https://pay.mynkj.cn",
                        "http://pay.mynkj.cn"
                    ];

                    if (in_array(url(''), $array)) {
                        $is_new_appid = 1;
                    }


                    $MyBankConfig = $mbconfig->MyBankConfig($config_id, $is_new_appid);

                    if ($MyBankConfig) {
                        $Path = $MyBankConfig->Path;
                    } else {
                        return json_encode([
                            'status' => 2,
                            'message' => '网商银行配置信息不存在,请联系服务商',
                        ]);
                    }
//                    //服务商存在
//                    if ($config_id != '123' && $MyBankConfig && $MyBankConfig->SubscribeMerchId && $MyBankConfig->SubscribeAppId && $Store->wx_AppId) {
//                        $PartnerType = '02';//关注服务商的公众号
//                        $MerchantDetail['AppId'] = $MyBankConfig->wx_AppId;//服务商的公众号id
//                        $MerchantDetail['SubscribeMerchId'] = $MyBankConfig->SubscribeMerchId;//服务商在网商银行的商户号
//                        $MerchantDetail['SubscribeAppId'] = $MyBankConfig->SubscribeAppId;//需要关注的服务商的公众号id
//                        $MerchantDetail['Path'] = $MyBankConfig->Path;
//                        $Path = $MerchantDetail['Path'];
//                    }
                    //商户配置就关注商户的
                    if ($Store->wx_SubscribeAppId && $Store->wx_AppId) {
                        $PartnerType = '01';//关注服务商的公众号
                        //  $MerchantDetail['AppId'] = $Store->wx_AppId;//服务商的公众号id
                        $MerchantDetail['SubscribeAppId'] = $Store->wx_SubscribeAppId;//需要关注的服务商的公众号id
                        //  $MerchantDetail['Path'] = $Path;
                    }
                    /****关注服务商公众号的类型结束****/

                    $data = [
                        'MerchantName' => $Store->store_name,
                        'OutMerchantId' => $store_id,
                        'MerchantType' => $MerchantType,
                        'DealType' => '01',//实体
                        'SupportPrepayment' => $SupportPrepayment,//商户清算资金是否支持T+0到账
                        'SettleMode' => $SettleMode,//结算到余利宝
                        'Mcc' => $category_id,
                        'MerchantDetail' => base64_encode(json_encode($MerchantDetail)),
                        'TradeTypeList' => '01,02,06,08',
                        'PayChannelList' => '01,02',
                        'FeeParamList' => base64_encode(json_encode($FeeParamList)),
                        'AuthCode' => $code,
                        //'DeniedPayToolList' => '01',//01：不禁用 02：信用卡 03：花呗（仅支付宝）
                        'SupportStage' => 'Y',
                        'PartnerType' => $PartnerType,
                        'AlipaySource' => $MyBankConfig->ali_pid
                    ];

                    //蓝海行动
                    if ($category_id == "000000") {
                        $data['Mcc'] = '2015050700000000';
                    }

                    //活动
                    if (!empty($other)) {
                        //蓝海行动
                        if ($other['activity_type'] == 'RBLUE') {
                            $data['RateVersion'] = 'RBLUE';
                        }
                    }


                    //结算到他行卡
                    if ($SettleModeType == '01') {
                        //企业
                        if ($Store->store_type == 2) {
                            //银行卡
                            $BankCardParam = [
                                "BankCertName" => $StoreBank->store_bank_name,//名称
                                "BankCardNo" => $StoreBank->store_bank_no,//银行卡号
                                "AccountType" => '02',//账户类型。可选值：01：对私账 02对公账户
                                "ContactLine" => $StoreBank->bank_no,//联航号
                            ];
                            $data['BankCardParam'] = base64_encode(json_encode($BankCardParam));
                        } //个体个人
                        else {
                            //银行卡
                            $BankCardParam = [
                                "BankCertName" => $StoreBank->store_bank_name,//名称
                                "BankCardNo" => $StoreBank->store_bank_no,//银行卡号
                                "AccountType" => '01',//账户类型。可选值：01：对私账 02对公账户
                                //"ContactLine" => $StoreBank->banknum,
                                "BranchName" => $StoreBank->sub_bank_name,
                                "BranchProvince" => $StoreBank->bank_province_code,//省编号
                                "BranchCity" => $StoreBank->bank_city_code,//市编号
                                "CertType" => "01",//持卡人证件类型。可选值： 01：身份证
                                "CertNo" => $StoreBank->bank_sfz_no,//持卡人证件号码
                                "CardHolderAddress" => $Store->store_address,//持卡人地址
                            ];
                            $data['BankCardParam'] = base64_encode(json_encode($BankCardParam));
                        }
                    }

                    $insert = $data;

                    $data['OutTradeNo'] = date('YmdHis') . time() . rand(10000, 99999);
                    $re = $ao->Request($data);
                    if ($re['status'] == 2) {
                        $status = 3;
                        $status_desc = $re['message'];
                        $send_ways_data['status_desc'] = $status_desc;
                        //$return = $this->send_ways_data($send_ways_data);
                        return json_encode($re);
                    }
                    $Result = $re['data']['document']['response']['body']['RespInfo'];
                    $body = $re['data']['document']['response']['body'];
                    //失败
                    if ($Result['ResultStatus'] == 'F') {
                        $status = 3;
                        $status_desc = $Result['ResultMsg'];
                        $send_ways_data['status_desc'] = $status_desc;
                        $return = $this->send_ways_data($send_ways_data);
                        return json_encode($return);
                    } elseif ($Result['ResultStatus'] == 'S') {
                        $status = 2;
                        $status_desc = "审核中";
                        $send_ways_data['status'] = $status;
                        $send_ways_data['status_desc'] = $status_desc;
                        $return = $this->send_ways_data($send_ways_data);
                        //成功
                        $insert['OrderNo'] = $body['OrderNo'];
                        $insert['config_id'] = $config_id;

                        //代表有新渠道
                        if ($is_new_appid) {
                            $insert['wx_AppId'] = $MyBankConfig->wx_AppId;
                            $insert['wx_Secret'] = $MyBankConfig->wx_Secret;
                        }


                        $MyBankStoreTem = MyBankStoreTem::where('OutMerchantId', $store_id)->first();
                        if ($MyBankStoreTem) {
                            $MyBankStoreTem->update($insert);
                            $MyBankStoreTem->save();
                        } else {
                            MyBankStoreTem::create($insert);
                        }
                        return json_encode([
                            'status' => 1,
                            'message' => '提交成功等待审核',
                        ]);

                    } else {
                        return json_encode([
                            'status' => 2,
                            'message' => '未知状态,请重新提交',
                        ]);
                    }

                } catch (\Exception $exception) {
                    Log::info($exception);
                    return json_encode([
                        'status' => 2,
                        'message' => $exception->getMessage(),
                    ]);
                }


            }

            //和融通
            if (8999 < $type && $type < 9999) {
                $config = new HConfigController();
                $h_config = $config->h_config($config_id);
                if (!$h_config) {
                    return json_encode(['status' => 2, 'message' => '没有配置相关通道']);
                }

                if ($rate != '0.38') {
                    return json_encode(['status' => 2, 'message' => '和融通费率必须是0.38']);
                }

                //进件
                $url = "https://merch.hybunion.cn/JHAdminConsole/phone/phoneMicroMerchantInfo_addAggPayMerchantInfo.action";
                $jh_mid = "";

                //修改
                if ($ways1 && $ways1->status == 3) {
                    $url = "https://merch.hybunion.cn/JHAdminConsole/updateMerReportInfo.action";
                    $h_merchant = $config->h_merchant($store_id, $store_pid);
                    if ($h_merchant) {
                        $jh_mid = $h_merchant->h_mid;
                    }
                }

                $data = [
                    'store_id' => $store_id,
                    'phone' => $phone,
                    'email' => $store_email,
                    'request_url' => $url,
                    'isHighQualityMer' => '0',//是否是优质商户
                    'scanRate' => $rate / 100,
                    'settleType' => 'D',
                    'saleId' => $h_config->saleId,
                    'jh_mid' => $jh_mid,
                ];
                $store_obj = new \App\Api\Controllers\Huiyuanbao\StoreController();
                $return = $store_obj->open_store($data);

                //不成功
                if ($return['status'] == 2) {
                    return json_encode([
                        'status' => 2,
                        'message' => $return['message'],
                    ]);
                }


                //第一次进件
                if ($jh_mid == "") {
                    //成功
                    $reportInfo = $return['reportInfo'];
                    $mid = $reportInfo['mid'];

                } else {
                    //修改
                    $mid = $jh_mid;
                }


                $HStore = HStore::where('store_id', $store_id)->first();
                $data_insert = [
                    'store_id' => $store_id,
                    'config_id' => $config_id,
                    'h_mid' => $mid,
                    'h_status' => 2,
                    'h_status_desc' => '审核中',
                ];
                if ($HStore) {
                    $HStore->update($data_insert);
                    $HStore->save();
                } else {
                    HStore::create($data_insert);
                }

                $data['rate'] = $rate;
                $data['status'] = 2;
                $data['status_desc'] = '审核中';
                $data['company'] = 'herongtong';
                $return = $this->send_ways_data($data);

                return $return;
            }

            //微信官方
            if ($type == '2000') {

                $store_type = $Store->store_type;//经营性质 1-个体，2-企业，3-个人

                //暂不支持 企业
                if ($store_type == 2) {
                    return json_encode([
                        'status' => 2,
                        'message' => '暂不支持企业进件',
                    ]);
                }

                $config = new WeixinConfigController();
                $weixin_config = $config->weixin_config_obj($config_id);
                $obj = new \App\Api\Controllers\Weixin\StoreController();
                $product_desc = "其他";

                //超市便利店
                if ($Store->category_id == "2015091000052157") {
                    $product_desc = "线下零售";
                }

                //美容
                if ($Store->category_id == "2015062600004525") {
                    $product_desc = "居民生活服务";

                }  //餐饮
                if ($Store->category_id == "2015050700000000") {
                    $product_desc = "餐饮";


                }  //休闲娱乐
                if ($Store->category_id == "2015063000020189") {
                    $product_desc = "休闲娱乐";


                }  //交通出行
                if ($Store->category_id == "2016062900190124") {
                    $product_desc = "交通出行";

                }


                $data_info = [
                    //参数信息
                    'mch_id' => $weixin_config->wx_merchant_id,
                    'key' => $weixin_config->key,
                    'getcertficates_request_url' => 'https://api.mch.weixin.qq.com/risk/getcertficates',
                    'upload_img_request_url' => 'https://api.mch.weixin.qq.com/secapi/mch/uploadmedia',
                    'submit_request_url' => 'https://api.mch.weixin.qq.com/applyment/micro/submit',
                    'sslCertPath' => public_path() . $weixin_config->cert_path,
                    'sslKeyPath' => public_path() . $weixin_config->key_path,
                    'public_key_path' => public_path() . $weixin_config->key_path,
                    "product_desc" => $product_desc,//售卖商品/提供服务描述
                    "rate" => "" . $rate . "",//费率
                    //门店信息

                    "head_name" => $Store->head_name,//身份证姓名
                    "head_sfz_no" => $Store->head_sfz_no,//身份证号码
                    "head_sfz_stime" => $Store->head_sfz_stime,//身份证有效期
                    "head_sfz_time" => $Store->head_sfz_time,//身份证有效期
                    "store_name" => $Store->head_name,//小微 先走法人名称
                    "city_code" => $Store->city_code,//门店省市编码
                    "store_address" => $Store->store_address,//门店街道名称
                    "store_short_name" => $Store->store_short_name,//商户简称
                    "people_phone" => $Store->people_phone,//客服电话
                    "people" => $Store->people,//联系人姓名

                    //银行信息
                    "store_bank_name" => $StoreBank->store_bank_name,//银行卡户名
                    "bank_name" => $StoreBank->bank_name,//开户银行
                    "bank_city_code" => $StoreBank->bank_city_code,//开户银行省市编码
                    "sub_bank_name" => $StoreBank->sub_bank_name,//开户银行全称（含支行）
                    "store_bank_no" => $StoreBank->store_bank_no,//银行账号

                ];
                //图片信息
                $img_data = [
                    'head_sfz_img_a' => $StoreImg->head_sfz_img_a,
                    'head_sfz_img_b' => $StoreImg->head_sfz_img_b,
                    'store_logo_img' => $StoreImg->store_logo_img,
                    'store_img_b' => $StoreImg->store_img_b,
                ];

                $return = $obj->xw_store($data_info, $img_data);

                //报错
                if ($return['status'] == 2) {
                    return $return;
                }


                //成功
                $applyment_id = $return['data']['applyment_id'];//微信支付分配的申请单号
                $insert = [
                    'config_id' => $config_id,
                    'applyment_id' => $applyment_id,
                    'store_id' => $store_id,
                    'store_type' => $Store->store_type,
                    'xw_status' => 2
                ];
                $MyBankStoreTem = WeixinStoreItem::where('store_id', $store_id)->first();
                if ($MyBankStoreTem) {
                    $MyBankStoreTem->update($insert);
                    $MyBankStoreTem->save();
                } else {
                    WeixinStoreItem::create($insert);
                }

                //入临时库
                $data['store_id'] = $store_id;
                $data['rate'] = $rate;
                $data['status'] = 2;
                $data['status_desc'] = '审核中';
                $data['company'] = 'weixin';
                $return = $this->send_ways_data($data);
                return json_encode($return);


            }

            if ($type == '1000') {
                return json_encode([
                    'status' => 2,
                    'message' => '请在->我的->认证中心->支付宝授权',
                ]);
            }


            //富友聚合
            if (10999 < $type && $type < 11999) {
                $config = new FuiouConfigController();
                $fuiou_config = $config->fuiou_config($config_id);
                if (!$fuiou_config) {
                    return json_encode(['status' => 2, 'message' => '没有配置相关通道']);
                }


                if (mb_strlen($Store->store_name, 'UTF8') < 5) {
                    return json_encode([
                        'status' => 2,
                        'message' => '门店名称必须大于5个字',
                    ]);
                }

                //经营性质 1-个体，2-企业，3-个人
                //如果是个人

                $license_type = "0";
                $license_no = $Store->store_license_no;
                $license_expire_dt = date('Ymd', strtotime($Store->store_license_time));

                //个人
                if ($store_type == 3) {
                    $license_type = 'A';
                    $license_no = $Store->head_sfz_no;
                    $license_expire_dt = date('Ymd', strtotime($Store->head_sfz_time));

                }
                if ($store_type == 1) {
                    $license_type = 'B';
                }

                //需要处理
                $business = "207";//类目
                $bank_type = "0102";
                $inter_bank_no = $StoreBank->bank_no;//入账卡开户行联行号,
                $iss_bank_nm = $StoreBank->sub_bank_name;//入账卡开户行名称
                $settle_tp = "1";///清算类型
                $acnt_certif_id = $StoreBank->bank_sfz_no ? $StoreBank->bank_sfz_no : $Store->head_sfz_no;//入账证件号,
                $acnt_certif_expire_dt = $Store->bank_sfz_time ? $Store->bank_sfz_time : $Store->head_sfz_time;//入账证件到期日,
                $acnt_certif_expire_dt = date('Ymd', strtotime($acnt_certif_expire_dt));

                $acnt_nm = $StoreBank->store_bank_name;//入账卡户名
                $acnt_no = $StoreBank->store_bank_no;//入账卡号（不带长度位）,

                $head_sfz_no = $Store->head_sfz_no;
                $head_sfz_time = $Store->head_sfz_time;
                $head_sfz_time = date('Ymd', strtotime($head_sfz_time));

                $acnt_type = $store_bank_type == "01" ? '2' : "1";

                $request = [
                    'trace_no' => time(),//唯一流水号
                    'ins_cd' => $fuiou_config->ins_cd,//机构号
                    'mchnt_name' => $store_name,//商户名称
                    'mchnt_shortname' => $Store->store_short_name,//商户简称
                    'real_name' => $Store->store_name,//营业执照名称
                    'license_type' => $license_type,//证件类型：0 营业执照，1 三证合一，A 身份证（一证下机）B 个体户
                    'license_no' => $license_no,//证件号码，填写方法：1.license_type=0 或1，此处填写营业执照号码。2.license_type=A，此处填写身份证号码3.license_type=B，此处填写个体工商户营业执照号码
                    'license_expire_dt' => $license_expire_dt,//证件到期日（格式yyyyMMdd）格式 长期请填20991231 无有效期请填 19000101 1.license_type=0 或1，此处填写 营业执照到期日。 2.license_type=A 此处填写身份证 的到期日 3.license_type=B，此处填写个体 工商户营业执照号的到期日
                    'certif_id' => $head_sfz_no,//法人身份证号码
                    'certif_id_expire_dt' => $head_sfz_time,//法人身份证到期日（格式YYYYMMDD）
                    'contact_person' => $Store->people ? $Store->people : $Store->head_name,//联系人姓名,
                    'contact_phone' => $Store->people_phone ? $Store->people_phone : '',//客服电话,
                    'contact_addr' => $Store->store_address,//联系人地址,
                    'contact_mobile' => $Store->people_phone,//联系人电话,
                    'contact_email' => $Store->store_email,//联系人邮箱,
                    'business' => $business,//经营范围代码（新开户则必填）,
                    'city_cd' => $Store->city_code,//市代码,
                    'county_cd' => $Store->area_code,//区代码,
                    'acnt_type' => $acnt_type,//入账卡类型：1：对公；2：对私; 入账卡类型为1 时，对公户户名需 与营业执照名称保持一致（进件若 为双账户时，此处必填2，即对私 结算）,
                    'bank_type' => $bank_type,//行别,（acnt_type=1 必填）(参考 行别对照表) 见附件7 行别对照表,
                    'inter_bank_no' => $inter_bank_no,//入账卡开户行联行号,
                    'iss_bank_nm' => $iss_bank_nm,//入账卡开户行名称,
                    'acnt_nm' => $acnt_nm,//入账卡户名,
                    'acnt_no' => $acnt_no,//入账卡号（不带长度位）,
                    'settle_tp' => $settle_tp,//清算类型,
                    'artif_nm' => $Store->head_name,//法人姓名,
                    'acnt_artif_flag' => $Store->head_name == $StoreBank->store_bank_name ? '1' : '0',//法人入账标识(0:非法人入账,1:法人入账,
                    'acnt_certif_tp' => '0',//入账证件类型("0":"身份证,
                    'acnt_certif_id' => $acnt_certif_id,//入账证件号,
                    'acnt_certif_expire_dt' => $acnt_certif_expire_dt,//入账证件到期日,
                ];

                $request['key'] = $fuiou_config->md_key;
                $open_store = new \App\Api\Controllers\Fuiou\StoreController();
                $open_store->open_store($request);
                dd($open_store);


            }


            //传化支付
            if (11999 < $type && $type < 12999) {

                $store_obj = new \App\Api\Controllers\Tfpay\StoreController();

                $return = $store_obj->open_store($Store, $StoreBank, $StoreImg);

                //不成功
                if ($return['status'] == 2) {
                    return json_encode([
                        'status' => 2,
                        'message' => $return['message'],
                    ]);
                }


                $TfStore = TfStore::where('store_id', $store_id)->first();
                $data_insert = [
                    'store_id' => $store_id,
                    'config_id' => $config_id,
                    'agent_id' => $return['data']['agent_id'],
                    'sub_mch_id' => $return['data']['sub_mch_id'],
                    'status' => $return['data']['status'],
                ];
                if ($TfStore) {
                    $TfStore->update($data_insert);
                    $TfStore->save();
                } else {
                    TfStore::create($data_insert);
                }

                $status_desc = "审核中";
                $status = 2;
                if ($return['data']['status'] == 1) {
                    $status_desc = "开通成功";
                    $status = 1;
                }

                if ($return['data']['status'] == 3) {
                    $status_desc = '进件失败请修改资料重新进件';
                    $status = 3;
                }
                $data['store_id'] = $store_id;
                $data['rate'] = $rate;
                $data['status'] = $status;
                $data['status_desc'] = $status_desc;
                $data['company'] = 'tfpay';
                $return = $this->send_ways_data($data);

                return $return;

            }


            return json_encode([
                'status' => 2,
                'message' => '暂时不支持申请此通道！请更换换其他通道',
            ]);

        } catch (\Exception $exception) {
            return json_encode(
                ['status' => 2,
                    'message' => $exception->getMessage() . $exception->getLine() . $exception->getFile(),]);
        }
    }


    public
    function send_ways_data($data)
    {
        try {
            //开启事务
            $all_pay_ways = DB::table('store_ways_desc')->where('company', $data['company'])->get();
            foreach ($all_pay_ways as $k => $v) {
                $gets = StorePayWay::where('store_id', $data['store_id'])
                    ->where('ways_source', $v->ways_source)
                    ->get();
                $count = count($gets);
                $ways = StorePayWay::where('store_id', $data['store_id'])->where('ways_type', $v->ways_type)->first();
                try {
                    DB::beginTransaction();
                    $data = [
                        'store_id' => $data['store_id'],
                        'ways_type' => $v->ways_type,
                        'company' => $v->company,
                        'ways_source' => $v->ways_source,
                        'ways_desc' => $v->ways_desc,
                        'sort' => ($count + 1),
                        'rate' => $data['rate'],
                        'settlement_type' => $v->settlement_type,
                        'status' => $data['status'],
                        'status_desc' => $data['status_desc'],
                    ];
                    if ($ways) {
                        $ways->update(
                            [
                                'status' => $data['status'],
                                'status_desc' => $data['status_desc']
                            ]);
                        $ways->save();
                    } else {
                        StorePayWay::create($data);
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    Log::info('入库通道');
                    Log::info($e);
                    DB::rollBack();
                    return [
                        'status' => 2,
                        'message' => '通道入库更新失败1',
                    ];
                }
            }


            return [
                'status' => 1,
                'message' => $data['status_desc'],
            ];

        } catch (\Exception $e) {

            return [
                'status' => 2,
                'message' => '通道入库更新失败2',
            ];
        }
    }


    //
    public function new_land_img($img_url, $img_name)
    {
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
                throw new \Exception($img_name . '图片资料存在问题，请重新上传');
            }

        }

        return bin2hex(file_get_contents($img));

    }

    //网商组装图片
    public function images_get($img_url)
    {
        $img_url = explode('/', $img_url);
        $img_url = end($img_url);
        $img = public_path() . '/upload/images/' . $img_url;
        return $img;
    }

    //网商银行上传图片
    public function uploadIMG($type, $filepath, $config)
    {
        try {
            $aop = new \App\Api\Controllers\MyBank\BaseController();
            $ao = $aop->aop($config);
            $partner_private_key = $ao->partner_private_key;
            $url = env("MY_BANK_uploadphoto");
            $data = [
                'IsvOrgId' => $ao->IsvOrgId,
                'PhotoType' => $type,
                'OutTradeNo' => date('YmdHis') . time() . rand(10000, 99999),
                'Function' => 'ant.mybank.merchantprod.merchant.uploadphoto',
                'Version' => '1.0.0',
                'AppId' => $ao->Appid,
                'ReqTime' => date('YmdHis'),
            ];
            $ParamUtil = new ParamUtil();
            //拼接字符串
            $unsign = $ParamUtil->getOrderSignContent($data, "UTF-8");
            $unsign = urlencode($unsign);
            $RSACert = new RSACert();
            $aop = new \Alipayopen\Sdk\AopClient();
            $aop->rsaPrivateKey = $partner_private_key;
            $sign = $RSACert->sign($unsign, null, $partner_private_key, "RSA2");
            $data['Signature'] = $sign;


            $OBJ = new  \App\Api\Controllers\MyBank\BaseController();
            $OBJ->gatewayUrl_photo = $url;
            $picName = 'positive';
            $data["Picture"] = file_get_contents($filepath);
            $return = $OBJ->exec($data, $picName, $filepath);

            $data = Tools::xml_to_array($return);

            return [
                'status' => 1,
                'url' => $data['document']['response']['body']['PhotoUrl'],
            ];
        } catch (\Exception $exception) {

            return [
                'status' => 0,
                'message' => $exception->getMessage() . $exception->getLine(),
            ];
        }

    }

    //处理时间格式
    public function time_action($time, $set_time)
    {

        //去除中文
        $time = preg_replace('/([\x80-\xff]*)/i', '', $time);

        $is_date = strtotime($time) ? strtotime($time) : false;

        if ($is_date === false) {
            $time = $set_time;//默认时间
        } else {

        }

        return date('Y-m-d', strtotime($time));
    }
}