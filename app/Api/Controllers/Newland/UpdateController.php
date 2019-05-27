<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/12/30
 * Time: 10:52 AM
 */

namespace App\Api\Controllers\Newland;


use App\Api\Controllers\Config\NewLandConfigController;
use App\Models\MyBankCategory;
use App\Models\NewLandStore;
use App\Models\NewLandStoreItem;
use App\Models\Store;
use App\Models\StoreBank;
use App\Models\StoreImg;
use App\Models\StorePayWay;
use App\Models\UserRate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use TencentYoutuyun\Conf;
use TencentYoutuyun\YouTu;

class UpdateController extends BaseController
{


    //修改门店资料

    public function update_store($data)
    {
        try {
            $store_id = $data['store_id'];
            $type = 8001;
            $email = $data['email'];
            $phone = $data['phone'];
            $update_store = 1;//上否修改资料
            $update_images = 0;//是否修改图片


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

            //公共判断
            $store_type = $Store->store_type;//经营性质 1-个体，2-企业，3-个人
            $config_id = $Store->config_id;
            $store_pid = $Store->pid;
            $store_bank_type = $StoreBank->store_bank_type;//01 对私人 02 对公

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
            }

            //没有邮箱 随机一个
            if ($email == "") {
                $email = '' . $phone . '@139.com';
            }


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
                $store_license_time = '999-12-31';//长期
            }

            //读取识别图片信息
            //法人身份证有效期
            //识别法人身份证反面
            $is_card_b = 1;
            if ($is_card_b && $Store->head_sfz_time == "") {
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
                $bank_sfz_time = $StoreBank->bank_sfz_time;
            }

            $NewLandStore = NewLandStore::where('store_id', $store_id)->first();
            if (!$NewLandStore) {
                return json_encode([
                    'status' => 2,
                    'message' => '新大陆商户号不存在'
                ]);
            }
            $mercId = $NewLandStore->nl_mercId;
            $stoe_id = $NewLandStore->nl_stoe_id;
            $log_no = $NewLandStore->log_no;


            $incom_type = '2';//企业个体
            if ($store_type == 3) {
                //个人
                $incom_type = '1';
            }

            //0-修改申请

            $jj_status = $NewLandStore->jj_status;
            $img_status = $NewLandStore->img_status;
            $tj_status = $NewLandStore->tj_status;
            $check_flag = $NewLandStore->check_flag;
            $check_qm = $NewLandStore->check_qm;


            //如果审核通过了 要修改 需要调审核修改申请
            if ($check_flag == '1') {
                $sign_data_ShenQing = [
                    'mercId' => $mercId,
                ];
                $request_obj = new  \App\Common\XingPOS\Request\XingStoreShangHuXiuGaiShenQing();
                $aop->version = 'V1.0.1';//这个一定要和文档一致
                $request_obj->setBizContent($sign_data_ShenQing, []);
                $return = $aop->executeStore($request_obj);

                Log::info('1');
                Log::info($return);
                //不成功
                if ($return['msg_cd'] != '000000') {
                    return json_encode([
                        'status' => 2,
                        'message' => $return['msg_dat'],
                    ]);
                }

                //申请成功以后 流水号保存下
                $log_no = $return['log_no'];
                $NewLandStore->log_no = $log_no;
                $NewLandStore->save();
            }

            //else {
//                return json_encode([
//                    'status' => 2,
//                    'message' => '新大陆商户状态未通过，请通过过修改'
//                ]);
//            }

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


            //1.进件修改
            if ($NewLandStore && $update_store == 1) {

                $stoe_nm = $Store->province_name . $Store->city_name . $Store->store_name;
                if (mb_strlen($stoe_nm, 'UTF8') > 20) {
                    $stoe_nm = $Store->province_name . $Store->store_name;
                }

                $sign_data = [
                    'mercId' => $mercId,
                    'stoe_id' => $stoe_id,
                    'log_no' => $log_no,
                    "incom_type" => $incom_type,
                    "stl_typ" => '1',//1 T+1 2 D+1（对公账户不能选择D+1）
                    "stl_sign" => $stl_sign,////结算标志 1-对私 0-对 公
                    "stl_oac" => $StoreBank->store_bank_no,//结算账号
                    "bnk_acnm" => $StoreBank->store_bank_name,//户名
                    "wc_lbnk_no" => $StoreBank->bank_no,//联行行号
                    "stoe_nm" => $stoe_nm,//省市区+门店名
                    "stoe_cnt_nm" => $Store->head_name,//门店联系人
                    "stoe_cnt_tel" => $phone,//联系人手机号
                    // "mcc_cd" => $MyBankCategory->mcc,//mcc
                    "stoe_area_cod" => $Store->area_code,//区域码
                    "stoe_adds" => $Store->store_address, //门店详细地址
                    "mailbox" => $email ? $email : 'dmk@umxnt.com',//
                    "alipay_flg" => "Y",
                    "yhkpay_flg" => "Y",
                ];

                $no_sign_data = [
                    "icrp_id_no" => $bank_sfz_no,//结算人身份证号
                    "crp_exp_dt_tmp" => $bank_sfz_time,//结算人身份证有效期
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

                $request_obj = new  \App\Common\XingPOS\Request\XingStoreShangHuZiLiaoXiuGai();
                $aop->version = 'V1.0.5';//这个一定要和文档一致
                $request_obj->setBizContent($sign_data, $no_sign_data);
                $return = $aop->executeStore($request_obj);
                Log::info('2');
                Log::info($return);
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

                $data_insert = [
                    'jj_status' => $jj_status,
                ];
                if ($NewLandStore) {
                    $NewLandStore->update($data_insert);
                    $NewLandStore->save();
                } else {
                    NewLandStore::create($data_insert);
                }
            }


            //2.上传图片

            if ($NewLandStore && $update_images == 1) {
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
                                'imgFile' => $this->new_land_img($StoreImg->head_sc_img, '结算人法人手持身份证')
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
            //  if ($NewLandStore && $NewLandStore->tj_status != 1) {
            $sign_data = [
                'mercId' => $mercId,
                'log_no' => $log_no,
            ];
            $request_obj = new  \App\Common\XingPOS\Request\XingStoreShangHuTiJiao();
            $aop->version = 'V1.0.1';//这个一定要和文档一致
            $request_obj->setBizContent($sign_data, []);
            $return = $aop->executeStore($request_obj);
            Log::info('3');
            Log::info($return);
            //不成功
            if ($return['msg_cd'] != '000000') {
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


            //4. 安心签名 修改不需要
            if (0 && $NewLandStore && $NewLandStore->check_qm != 1) {
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
            return json_encode($return);


        } catch (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'message' => $exception->getMessage() . $exception->getLine()
            ]);
        }


    }


    //修改门店费率
    public function update_store_rate($data)
    {
        try {
            $store_id = $data['store_id'];
            $type = 8001;
            $email = $data['email'];
            $phone = $data['phone'];
            $update_store = 1;//上否修改资料
            $update_images = 0;//是否修改图片
            $rate = $data['rate'];//支付宝微信扫码费率
            $rate_a = $data['rate_a'];//银联二维码费率-1000以下
            $rate_c = $data['rate_c'];//银联二维码费率-1000以上
            $rate_f = $data['rate_f'];//借记卡费率
            $rate_f_top = $data['rate_f_top'];//借记卡封顶
            $rate_e = $data['rate_e'];//贷记卡费率


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

            //公共判断
            $store_type = $Store->store_type;//经营性质 1-个体，2-企业，3-个人
            $config_id = $Store->config_id;
            $store_pid = $Store->pid;
            $store_bank_type = $StoreBank->store_bank_type;//01 对私人 02 对公

            //没有邮箱 随机一个
            if ($email == "") {
                $email = '' . $phone . '@139.com';
            }


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
                $store_license_time = '999-12-31';//长期
            }

            //读取识别图片信息
            //法人身份证有效期
            //识别法人身份证反面
            $is_card_b = 1;
            if ($is_card_b && $Store->head_sfz_time == "") {
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
                $bank_sfz_time = $StoreBank->bank_sfz_time;
            }

            $NewLandStore = NewLandStore::where('store_id', $store_id)->first();
            if (!$NewLandStore) {
                return json_encode([
                    'status' => 2,
                    'message' => '新大陆商户号不存在'
                ]);
            }
            $mercId = $NewLandStore->nl_mercId;
            $stoe_id = $NewLandStore->nl_stoe_id;
            $log_no = $NewLandStore->log_no;


            $incom_type = '2';//企业个体
            if ($store_type == 3) {
                //个人
                $incom_type = '1';
            }

            //0-修改申请

            $jj_status = $NewLandStore->jj_status;
            $img_status = $NewLandStore->img_status;
            $tj_status = $NewLandStore->tj_status;
            $check_flag = $NewLandStore->check_flag;
            $check_qm = $NewLandStore->check_qm;


            //如果审核通过了 要修改 需要调审核修改申请
            if ($check_flag == '1') {
                $sign_data_ShenQing = [
                    'mercId' => $mercId,
                ];
                $request_obj = new  \App\Common\XingPOS\Request\XingStoreShangHuXiuGaiShenQing();
                $aop->version = 'V1.0.1';//这个一定要和文档一致
                $request_obj->setBizContent($sign_data_ShenQing, []);
                $return = $aop->executeStore($request_obj);

                Log::info('1');
                Log::info($return);
                //不成功
                if ($return['msg_cd'] != '000000') {
                    return json_encode([
                        'status' => 2,
                        'message' => $return['msg_dat'],
                    ]);
                }

                //申请成功以后 流水号保存下
                $log_no = $return['log_no'];
                $NewLandStore->log_no = $log_no;
                $NewLandStore->save();
            }

            //else {
//                return json_encode([
//                    'status' => 2,
//                    'message' => '新大陆商户状态未通过，请通过过修改'
//                ]);
//            }

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


            //1.进件修改
            if ($NewLandStore && $update_store == 1) {

                $sign_data = [
                    'mercId' => $mercId,
                    'stoe_id' => $stoe_id,
                    'log_no' => $log_no,
                    "incom_type" => $incom_type,
                    "stl_typ" => '1',//1 T+1 2 D+1（对公账户不能选择D+1）
                    "stl_sign" => $stl_sign,////结算标志 1-对私 0-对 公
                    "stl_oac" => $StoreBank->store_bank_no,//结算账号
                    "bnk_acnm" => $StoreBank->store_bank_name,//户名
                    "wc_lbnk_no" => $StoreBank->bank_no,//联行行号
                    "stoe_nm" => $Store->province_name . $Store->city_name . $Store->store_name,//省市区+门店名
                    "stoe_cnt_nm" => $Store->head_name,//门店联系人
                    "stoe_cnt_tel" => $phone,//联系人手机号
                    // "mcc_cd" => $MyBankCategory->mcc,//mcc
                    "stoe_area_cod" => $Store->area_code,//区域码
                    "stoe_adds" => $Store->store_address, //门店详细地址
                    "mailbox" => $email ? $email : 'dmk@umxnt.com',//
                    "alipay_flg" => "Y",
                    "yhkpay_flg" => "Y",
                ];

                $no_sign_data = [
                    "icrp_id_no" => $bank_sfz_no,//结算人身份证号
                    "crp_exp_dt_tmp" => $bank_sfz_time,//结算人身份证有效期
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

                $request_obj = new  \App\Common\XingPOS\Request\XingStoreShangHuZiLiaoXiuGai();
                $aop->version = 'V1.0.5';//这个一定要和文档一致
                $request_obj->setBizContent($sign_data, $no_sign_data);
                $return = $aop->executeStore($request_obj);
                Log::info('2');
                Log::info($return);
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

                $data_insert = [
                    'jj_status' => $jj_status,
                ];
                if ($NewLandStore) {
                    $NewLandStore->update($data_insert);
                    $NewLandStore->save();
                } else {
                    NewLandStore::create($data_insert);
                }
            }


            //2.上传图片

            if ($NewLandStore && $update_images == 1) {
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
                                'imgFile' => $this->new_land_img($StoreImg->head_sc_img, '结算人法人手持身份证')
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
            //  if ($NewLandStore && $NewLandStore->tj_status != 1) {
            $sign_data = [
                'mercId' => $mercId,
                'log_no' => $log_no,
            ];
            $request_obj = new  \App\Common\XingPOS\Request\XingStoreShangHuTiJiao();
            $aop->version = 'V1.0.1';//这个一定要和文档一致
            $request_obj->setBizContent($sign_data, []);
            $return = $aop->executeStore($request_obj);
            Log::info('3');
            Log::info($return);
            //不成功
            if ($return['msg_cd'] != '000000') {
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


            //4. 安心签名 修改不需要
            if (0 && $NewLandStore && $NewLandStore->check_qm != 1) {
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
            return json_encode($return);


        } catch (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'message' => $exception->getMessage() . $exception->getLine()
            ]);
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
                throw new \Exception($img_name . '存在问题');
            }

        }

        return bin2hex(file_get_contents($img));

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
                        'message' => '通道入库更新失败',
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
                'message' => '通道入库更新失败',
            ];
        }
    }


}