<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/6/11
 * Time: 下午6:24
 */

namespace App\Api\Controllers\Merchant;


use App\Api\Controllers\Basequery\StorePayWaysController;
use App\Api\Controllers\BaseController;
use App\Api\Controllers\Config\WeixinConfigController;
use App\Api\Controllers\MyBank\MyBankController;
use App\Models\AlipayAppOauthUsers;
use App\Models\Merchant;
use App\Models\MerchantStore;
use App\Models\MyBankCategory;
use App\Models\MyBankStore;
use App\Models\Order;
use App\Models\ProvinceCity;
use App\Models\QrPayInfo;
use App\Models\Store;
use App\Models\StoreBank;
use App\Models\StoreImg;
use App\Models\StorePayWay;
use App\Models\User;
use App\Models\UserRate;
use App\Models\WeixinNotify;
use EasyWeChat\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;


class StoreController extends BaseController
{


    public function check_store(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $merchant_id = $merchant->merchant_id;
            $merchant_store = MerchantStore::where('merchant_id', $merchant_id)
                ->orderBy('created_at', 'asc')
                ->first();
            $store_id = $request->get('store_id', '');
            if ($store_id == "" && $merchant_store) {
                $store_id = $merchant_store->store_id;
            }
            $is_store = 0;
            $is_wx_open_id = 0;
            $is_email = 0;
            $is_pay_ways = 0;

            $Store = Store::where('store_id', $store_id)
                ->select('head_sfz_no')
                ->first();
            //
            if ($Store && $Store->head_sfz_no) {
                $StoreBank = StoreBank::where('store_id', $store_id)
                    ->select('store_bank_no')
                    ->first();
                if ($StoreBank && $StoreBank->store_bank_no) {
                    $is_store = 1;
                }
            }
            $merchants = Merchant::where('id', $merchant->merchant_id)->first();
            if (!$merchants) {
                return json_encode(['status' => 2, 'message' => '商户不存在']);

            }
            if ($merchants->wx_openid) {
                $is_wx_open_id = 1;
            }

            if ($merchants->email) {
                $is_email = 1;
            }

            $StorePayWay = StorePayWay::where('store_id', $store_id)
                ->where('status', 1)
                ->select('id')->first();

            if ($StorePayWay) {
                $is_pay_ways = 1;
            }

            $data = [
                'is_store' => $is_store,
                'is_wx_open_id' => $is_wx_open_id,
                'is_email' => $is_email,
                'is_pay_ways' => $is_pay_ways,
            ];
            $this->status = 1;
            $this->message = '数据返回成功';

            return $this->format($data);

        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage() . '-' . $exception->getLine();
            return $this->format();
        }
    }

    /**门店信息查看
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $merchant_id = $merchant->merchant_id;

            if ($merchant->merchant_type == 2) {
                return json_encode([
                    'status' => 2,
                    'message' => '收银员没有权限'
                ]);
            }

            $merchant_store = MerchantStore::where('merchant_id', $merchant_id)
                ->orderBy('created_at', 'asc')
                ->first();
            $store_id = $request->get('store_id', '');
            $type = $request->get('type', '');

            if ("" . $store_id . "" == "" && $merchant_store) {
                $store_id = $merchant_store->store_id;
            }

            $store = Store::where('store_id', $store_id)->first();
            $store_bank = StoreBank::where('store_id', $store_id)->first();
            $store_img = StoreImg::where('store_id', $store_id)->first();
            if (!$store) {
                return json_encode([
                    'status' => 2,
                    'message' => '门店未认证'
                ]);

            }
            $store_alipay_account = '';
            $AlipayAppOauthUsers = AlipayAppOauthUsers::where('store_id', $store_id)
                ->select('alipay_user_account')
                ->first();

            if ($AlipayAppOauthUsers) {
                $store_alipay_account = $AlipayAppOauthUsers->alipay_user_account;
            }

            if ($type == "") {
                $type = 'head_info,store_info,account_info,license_info';
            }
            $type_array = explode(",", $type);
            foreach ($type_array as $k => $v) {
                if ($v == 'head_info') {
                    $data = [
                        'head_name' => $store->head_name,//法人
                        'head_sfz_no' => $store->head_sfz_no,
                        'head_sfz_img_a' => $store_img->head_sfz_img_a,
                        'head_sfz_img_b' => $store_img->head_sfz_img_b,
                        'people' => $store->people,
                        'people_phone' => $store->people_phone,
                        'head_sfz_time' => $store->head_sfz_time,
                        'head_sfz_stime' => $store->head_sfz_stime,
                    ];

                    $data_r[$v] = $data;
                }

                if ($v == 'store_info') {
                    $data = [
                        'people' => $store->people,
                        'people_phone' => $store->people_phone,
                        'store_name' => $store->store_name,
                        'province_code' => $store->province_code,
                        'city_code' => $store->city_code,
                        'area_code' => $store->area_code,
                        'province_name' => $store->province_name,
                        'city_name' => $store->city_name,
                        'area_name' => $store->area_name,
                        'store_address' => $store->store_address,
                        'store_type' => $store->store_type,
                        'store_type_name' => $store->store_type_name,
                        'category_id' => $store->category_id,
                        'category_name' => $store->category_name,
                        'store_logo_img' => $store_img->store_logo_img,
                        'store_img_a' => $store_img->store_img_a,
                        'store_img_b' => $store_img->store_img_b,
                        'store_img_c' => $store_img->store_img_c,
                    ];

                    $data_r[$v] = $data;
                }


                if ($v == 'account_info') {
                    $data = [
                        'store_alipay_account' => $store_alipay_account,
                        'store_bank_no' => $store_bank->store_bank_no,
                        'store_bank_phone' => $store_bank->store_bank_phone,
                        'store_bank_name' => $store_bank->store_bank_name,
                        'store_bank_type' => $store_bank->store_bank_type,
                        'bank_name' => $store_bank->bank_name,
                        'bank_no' => $store_bank->bank_no,
                        'sub_bank_name' => $store_bank->sub_bank_name,
                        'bank_province_code' => $store_bank->bank_province_code,
                        'bank_city_code' => $store_bank->bank_city_code,
                        'bank_area_code' => $store_bank->bank_area_code,
                        'bank_province_name' => $this->city_name($store_bank->bank_province_code),
                        'bank_city_name' => $this->city_name($store_bank->bank_city_code),
                        'bank_area_name' => $this->city_name($store_bank->bank_area_code),
                        'bank_img_a' => $store_img->bank_img_a,
                        'bank_img_b' => $store_img->bank_img_b,
                        'bank_sfz_img_a' => $store_img->bank_sfz_img_a,
                        'bank_sfz_img_b' => $store_img->bank_sfz_img_b,
                        'bank_sc_img' => $store_img->bank_sc_img,
                        'store_auth_bank_img' => $store_img->store_auth_bank_img,
                        'bank_sfz_time' => $store_bank->bank_sfz_time,
                        'bank_sfz_stime' => $store_bank->bank_sfz_stime,
                        'weixin_name' => $store->weixin_name,
                        'weixin_no' => $store->weixin_no,
                    ];

                    $data_r[$v] = $data;
                }

                if ($v == 'license_info') {
                    $data = [
                        'store_license_no' => $store->store_license_no,
                        'store_license_time' => $store->store_license_time,
                        'store_license_img' => $store_img->store_license_img,
                        'store_other_img_a' => $store_img->store_other_img_a,
                        'store_other_img_b' => $store_img->store_other_img_b,
                        'store_other_img_c' => $store_img->store_other_img_c,
                        'store_industrylicense_img' => $store_img->store_industrylicense_img,
                        'head_sc_img' => $store_img->head_sc_img,
                        'head_store_img' => $store_img->head_store_img,

                    ];

                    $data_r[$v] = $data;
                }
            }

            $this->status = 1;
            $this->message = '成功';
            return $this->format($data_r);

        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage() . '-' . $exception->getLine();
            return $this->format();
        }


    }

    //城市名称
    public function city_name($city_code)
    {
        $city_name = "";
        $data = ProvinceCity::where('area_code', $city_code)->first();
        if ($data) {
            $city_name = $data->area_name;
        }

        return $city_name;

    }

    /**门店认证修改
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function add_store(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $data = $request->except(['token']);
            $merchant_id = $merchant->merchant_id;
            $phone = $merchant->phone;
            $name = $merchant->merchant_name;

            if ($merchant->merchant_type == 2) {
                return json_encode([
                    'status' => 2,
                    'message' => '收银员没有权限'
                ]);
            }
            $merchant_store = MerchantStore::where('merchant_id', $merchant_id)
                ->orderBy('created_at', 'asc')
                ->first();
            $store_id = $merchant->created_store_no;//预创建编号
            if ($merchant_store) {
                $store_id = $merchant_store->store_id;
            }
            //法人信息
            $head_name = $request->get('head_name', '');
            $head_sfz_no = $request->get('head_sfz_no', '');
            $people = $request->get('people', $name);
            $people_phone = $request->get('people_phone', $phone);

            $head_sfz_img_a = $request->get('head_sfz_img_a', '');
            $head_sfz_img_b = $request->get('head_sfz_img_b', '');

            $head_sfz_time = $request->get('head_sfz_time', '');
            $head_sfz_stime = $request->get('head_sfz_stime', '');
            $head_sfz_time = $this->time($head_sfz_time);
            $head_sfz_stime = $this->time($head_sfz_stime);

            $bank_sfz_time = $request->get('bank_sfz_time', '');
            $bank_sfz_stime = $request->get('bank_sfz_stime', '');
            $bank_sfz_time = $this->time($bank_sfz_time);
            $bank_sfz_stime = $this->time($bank_sfz_stime);


            //门店信息
            $store_name = $request->get('store_name', '');
            $province_code = $request->get('province_code', '');
            $city_code = $request->get('city_code', '');
            $area_code = $request->get('area_code', '');
            $store_address = $request->get('store_address', '');
            $store_type = $request->get('store_type', '');
            $store_type_name = $request->get('store_type_name', '');

            $category_id = $request->get('category_id', '');
            $category_name = $request->get('category_name', '');
            $store_logo_img = $request->get('store_logo_img', '');
            $store_img_a = $request->get('store_img_a', '');
            $store_img_b = $request->get('store_img_b', '');
            $store_img_c = $request->get('store_img_c', '');
            $weixin_name = $request->get('weixin_name', '无');
            $weixin_no = $request->get('weixin_no', '无');


            //收款信息
            $store_alipay_account = $request->get('store_alipay_account', '');
            $store_bank_no = $request->get('store_bank_no', '');
            $store_bank_phone = $request->get('store_bank_phone', '');
            $store_bank_name = $request->get('store_bank_name', '');
            $store_bank_type = $request->get('store_bank_type', '');
            $bank_name = $request->get('bank_name', '');
            $bank_no = $request->get('bank_no', '');
            $sub_bank_name = $request->get('sub_bank_name', '');
            $bank_province_code = $request->get('bank_province_code', '');
            $bank_city_code = $request->get('bank_city_code', '');
            $bank_area_code = $request->get('bank_area_code', '');
            $bank_img_a = $request->get('bank_img_a', '');
            $bank_img_b = $request->get('bank_img_b', '');

            $bank_sfz_img_a = $request->get('bank_sfz_img_a', '');
            $bank_sfz_img_b = $request->get('bank_sfz_img_b', '');
            $bank_sc_img = $request->get('bank_sc_img', '');

            $store_auth_bank_img = $request->get('store_auth_bank_img', '');


            //证照信息
            $store_license_no = $request->get('store_license_no', '');
            $store_license_time = $request->get('store_license_time', '');

            $head_sc_img = $request->get('head_sc_img', '');
            $head_store_img = $request->get('head_store_img', '');

            $store_license_img = $request->get('store_license_img', '');
            $store_industrylicense_img = $request->get('store_industrylicense_img', '');
            $store_other_img_a = $request->get('store_other_img_a', '');
            $store_other_img_b = $request->get('store_other_img_b', '');
            $store_other_img_c = $request->get('store_other_img_c', '');

            //拼装门店信息
            $stores = [
                'config_id' => $merchant->config_id,
                'user_id' => $merchant->user_id,
                'merchant_id' => $merchant_id,
                'store_id' => $store_id,
                'store_name' => $store_name,
                'store_type' => $store_type,
                'store_type_name' => $store_type_name,
                'store_email' => '',
                'store_short_name' => $store_name,
                'people' => $people,//负责人
                'people_phone' => $people_phone,
                'province_code' => $province_code,
                'city_code' => $city_code,
                'area_code' => $area_code,
                'store_address' => $store_address,
                'head_name' => $head_name,//法人
                'head_sfz_no' => $head_sfz_no,
                'head_sfz_time' => $head_sfz_time,
                'head_sfz_stime' => $head_sfz_stime,
                'category_id' => $category_id,
                'category_name' => $category_name,
                'store_license_no' => $store_license_no,
                'store_license_time' => $store_license_time,
                'weixin_name' => isset($weixin_name) ? $weixin_name : "",
                'weixin_no' => isset($weixin_no) ? $weixin_no : "",
            ];
            if ($province_code) {
                $stores['province_name'] = $this->city_name($province_code);
            }
            if ($city_code) {
                $stores['city_name'] = $this->city_name($city_code);
            }
            if ($area_code) {
                $stores['area_name'] = $this->city_name($area_code);
            }


            //图片信息
            $store_imgs = [
                'store_id' => $store_id,
                'head_sfz_img_a' => $head_sfz_img_a,
                'head_sfz_img_b' => $head_sfz_img_b,
                'store_license_img' => $store_license_img,
                'store_industrylicense_img' => $store_industrylicense_img,
                'store_logo_img' => $store_logo_img,
                'store_img_a' => $store_img_a,
                'store_img_b' => $store_img_b,
                'store_img_c' => $store_img_c,
                'bank_img_a' => $bank_img_a,
                'bank_img_b' => $bank_img_b,
                'store_other_img_a' => $store_other_img_a,
                'store_other_img_b' => $store_other_img_b,
                'store_other_img_c' => $store_other_img_c,
                'head_sc_img' => $head_sc_img,
                'head_store_img' => $head_store_img,
                'bank_sfz_img_a' => $bank_sfz_img_a,
                'bank_sfz_img_b' => $bank_sfz_img_b,
                'bank_sc_img' => $bank_sc_img,
                'store_auth_bank_img' => $store_auth_bank_img,
            ];

            //银行卡信息
            $store_banks = [
                'store_id' => $store_id,
                'store_bank_no' => $store_bank_no,
                'store_bank_name' => $store_bank_name,
                'store_bank_phone' => $store_bank_phone,
                'store_bank_type' => $store_bank_type,
                'bank_name' => $bank_name,
                'bank_no' => $bank_no,
                'sub_bank_name' => $sub_bank_name,
                'bank_province_code' => $bank_province_code,
                'bank_city_code' => $bank_city_code,
                'bank_area_code' => $bank_area_code,
                'bank_sfz_no' => $head_sfz_no,//持卡人默认是法人
                'bank_sfz_time' => $bank_sfz_time,
                'bank_sfz_stime' => $bank_sfz_stime,
            ];


            $store = Store::where('store_id', $store_id)->first();
            $store_bank = StoreBank::where('store_id', $store_id)->first();
            $store_img = StoreImg::where('store_id', $store_id)->first();

            //开启事务
            try {
                DB::beginTransaction();

                if ($store) {
                    $stores = array_filter($stores);
                    $store->update($stores);
                    $store->save();
                } else {
                    $stores['store_type'] = 1;
                    $stores['store_type_name'] = '个体';
                    Store::create($stores);
                    MerchantStore::create([
                        'store_id' => $store_id,
                        'merchant_id' => $merchant_id
                    ]);
                }
                if ($store_img) {
                    $store_imgs = array_filter($store_imgs);
                    $store_img->update($store_imgs);
                    $store_img->save();
                } else {
                    StoreImg::create($store_imgs);
                }

                //网商银行修改资料
                $StorePayWay = StorePayWay::where('store_id', $store_id)
                    ->where('company', 'mybank')
                    ->select('status')
                    ->first();

                if ($StorePayWay && $StorePayWay->status == 1) {
                    $MyBankStore = MyBankStore::where('OutMerchantId', $store_id)
                        ->select('MerchantId')
                        ->first();

                    if ($MyBankStore && $MyBankStore->MerchantId) {
                        //修改银行资料
                        $obj = new MyBankController();


                        if ($store_bank_name && $store_bank_no && $bank_no) {

                            $BankCardParam = [
                                "config_id" => $merchant->config_id,
                                "MerchantId" => $MyBankStore->MerchantId,
                                "BankCertName" => $store_bank_name,//名称
                                "BankCardNo" => $store_bank_no,//银行卡号
                                "AccountType" => $store_bank_type,//账户类型。可选值：01：对私账 02对公账户
                                "BankCode" => $bank_name,//开户行名称
                                "BranchName" => $sub_bank_name,//开户支行名称
                                "ContactLine" => $bank_no,//联航号
                                "BranchProvince" => $bank_province_code,//省编号
                                "BranchCity" => $bank_city_code,//市编号
                                "CertType" => '01',//持卡人证件类型。可选值： 01：身份证
                                "CertNo" => $store_bank->bank_sfz_no,//持卡人证件号码
                                "CardHolderAddress" => $store->store_address,//持卡人地址
                            ];

                            $re = $obj->up_bank($BankCardParam);


                            if ($re['status'] == 2) {
                                return json_encode($re);
                            }
                            $body = $re['data']['document']['response']['body'];
                            if ($body['RespInfo']['ResultStatus'] != 'S') {
                                return json_encode([
                                    'status' => 2,
                                    'message' => '换卡失败：' . $body['RespInfo']['ResultMsg']
                                ]);
                            }
                        }

                    }

                }


                if ($store_bank) {
                    $store_banks = array_filter($store_banks);
                    $store_bank->update($store_banks);
                    $store_bank->save();
                } else {

                    StoreBank::create($store_banks);
                }


                DB::commit();
            } catch (\Exception $e) {
                Log::info($e);
                DB::rollBack();
                return json_encode(['status' => -1, 'message' => $e->getMessage()]);
            }


            return json_encode([
                'status' => 1,
                'message' => '资料添加成功',
                'data' => [
                    'store_id' => $store_id,
                    'pid' => '0',
                ]
            ]);


        } catch (\Exception $exception) {

            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }

    }

    /**门店类型
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store_type(Request $request)
    {

        $data = [
            [
                'store_type' => 1,
                'store_type_desc' => '门店-个体',
            ], [
                'store_type' => 2,
                'store_type_desc' => '门店-企业',
            ], [
                'store_type' => 3,
                'store_type_desc' => '门店-个人',
            ],
        ];
        $this->status = 1;
        $this->message = '返回数据成功';
        return $this->format($data);

    }

    /**查询门店经营类目
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store_category(Request $request)
    {

        $data = MyBankCategory::select('category_id', 'category_name')->get();
        $this->status = 1;
        $this->message = '返回数据成功';
        return $this->format($data);

    }

    /*支付宝授权
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function alipay_auth(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $merchant_id = $merchant->merchant_id;
            if ($merchant->pid != 0) {
                return json_encode(['status' => 2, 'message' => '你没有权限添加支付宝授权']);
            }
            $MerchantStore = MerchantStore::where('merchant_id', $merchant_id)
                ->orderBy('created_at', 'asc')
                ->first();
            if ($MerchantStore) {
                $store_id = $MerchantStore->store_id;
            } else {
                $store_id = $merchant->created_store_no;//预创建编号
            }

            $data = [
                'redirect_url' => 'alipays://platformapi/startapp?appId=20000067&url=' . url('/merchant/appAlipay?store_id=' . $store_id . '&merchant_id=' . $merchant_id . "&config_id=" . $merchant->config_id),
                'qr_url' => url('/merchant/appAlipay?store_id=' . $store_id . '&merchant_id=' . $merchant_id . "&config_id=" . $merchant->config_id)
            ];
            $this->status = 1;
            $this->message = '数据请求成功';
            return $this->format($data);

        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }
    }

    /**列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store_lists(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $merchant_id = $request->get('merchant_id', '');
            $return_type = $request->get('return_type', '');

            //门店创建者id
            if ($merchant_id == "") {
                $merchant_id = $merchant->merchant_id;
            }
            $MerchantStore = MerchantStore::where('merchant_id', $merchant_id)
                ->select('store_id')
                ->get();

            if ($MerchantStore->isEmpty()) {
                $stores = [];

            } else {
                $stores = $MerchantStore->toArray();
            }

            $obj = DB::table('stores');

            //查询 pid =0列表
            if ($return_type == '0') {
                $obj->whereIn('store_id', $stores)
                    ->where('pid', 0);

            } //分店
            elseif ($return_type == '1') {
                $obj->whereIn('store_id', $stores)
                    ->where('pid', '>', 0);

            } //所有
            else {
                $obj->whereIn('store_id', $stores);
            }


            $this->t = $obj->count();
            $data = $this->page($obj)->orderBy('id', 'asc')->get();

            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }

    }

    /**单个收银员信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function merchant_info(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $merchant_id = $request->get('merchant_id');
            $store_id = $request->get('store_id', '');

            $check_data = [
                'merchant_id' => '收银员',
            ];
            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }


            $data = Merchant::where('id', $merchant_id)->first();
            if (!$data) {
                $this->status = 2;
                $this->message = '收银员不存在';
                return $this->format();
            }


            //门店id存在
            if ($store_id) {
                //收银员的收款聚合码
                $data->pay_qr = url('/qr?store_id=' . $store_id . '&merchant_id=' . $merchant_id . '');
            }

            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }

    }

    /**添加分店
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add_sub_store(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $store_name = $request->get('store_name', '');
            $province_code = $request->get('province_code', '');
            $city_code = $request->get('city_code', '');
            $area_code = $request->get('area_code', '');
            $store_address = $request->get('store_address', '');
            $merchant_id = $merchant->merchant_id;
            $pay_ways_type = $request->get('pay_ways_type', 1);

            $obj = DB::table('stores');
            $obj = $obj->join('merchant_stores', 'stores.store_id', '=', 'merchant_stores.store_id')
                ->where('merchant_stores.merchant_id', $merchant_id)
                ->where('stores.pid', 0)
                ->select('stores.*')
                ->first();

            if (!$obj) {
                $this->status = 2;
                $this->message = '添加分店前必须要认证一家总店';
                return $this->format();
            }

            if ($merchant->merchant_type == 2) {
                $this->status = 2;
                $this->message = '收银员没有权限添加门店';
                return $this->format();
            }

            $pid_store_id = $obj->store_id;

            if ($store_name == "") {
                $this->status = 2;
                $this->message = '店铺名称必须填写';
                return $this->format();
            }

            if ($province_code == "") {
                $province_code = $obj->province_code;
            }
            if ($city_code == "") {
                $city_code = $obj->city_code;
            }
            if ($area_code == "") {
                $area_code = $obj->area_code;
            }

            $store_id = date('YmdHis', time()) . rand(10000, 99999) . $merchant_id;
            $data = [
                'store_id' => $store_id,
                'config_id' => $obj->config_id,
                'merchant_id' => $merchant_id,
                'pid' => $obj->id,
                'store_name' => $store_name,
                'store_short_name' => $store_name,
                'province_code' => $province_code,
                'city_code' => $city_code,
                'area_code' => $area_code,
                'province_name' => $this->city_name($province_code),
                'city_name' => $this->city_name($city_code),
                'area_name' => $this->city_name($area_code),
                'store_address' => $store_address,
                'user_id' => $obj->user_id,
                'user_pid' => $obj->user_pid,
                'store_type' => $obj->store_type,
                'store_type_name' => $obj->store_type_name,
                'category_id' => $obj->category_id,
                'category_name' => $obj->category_name,
                'pay_ways_type' => $pay_ways_type,//走上级通道
            ];


            Store::create($data);
            StoreImg::create(['store_id' => $store_id]);
            StoreBank::create(['store_id' => $store_id]);

            MerchantStore::create([
                'store_id' => $store_id,
                'merchant_id' => $merchant_id
            ]);


            //共享上级通道 暂时未启用
            $pay_ways_type = 0;
            if ($pay_ways_type == 1) {

                $StorePayWay = StorePayWay::where('store_id', $pid_store_id)
                    ->where('status', 1)
                    ->get();


                foreach ($StorePayWay as $k => $v) {
                    $StorePayWay_new = StorePayWay::where('store_id', $store_id)
                        ->where('ways_type', $v->ways_type)
                        ->first();
                    $sp = [
                        'store_id' => $store_id,
                        'status' => $v->status,
                        'rate' => $v->rate,
                        'settlement_type' => $v->settlement_type,
                        'status_desc' => $v->status_desc,
                        'ways_type' => $v->ways_type,
                        'ways_source' => $v->ways_source,
                        'company' => $v->company,
                        'ways_desc' => $v->ways_desc,
                        'pcredit' => $v->pcredit,
                        'credit' => $v->credit,
                        'is_close' => $v->is_close,
                        'is_close_desc' => $v->is_close_desc,
                        'sort' => $v->sort,
                    ];
                    if ($StorePayWay_new) {
                        StorePayWay::where('store_id', $store_id)
                            ->where('ways_type', $v->ways_type)
                            ->update($sp);
                    } else {
                        StorePayWay::create($sp);
                    }

                }

            }

            //共享上级通道结束

            $this->status = 1;
            $this->message = '分店添加成功';
            $data = [
                'store_id' => $store_id,
                'pid' => $obj->id,
            ];
            return $this->format($data);


        } catch (\Exception $exception) {
            Log::info($exception);
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }

    }


    /**修改分店
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function up_sub_store(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $store_id = $request->get('store_id');
            $merchant_id = $merchant->merchant_id;
            $data = $request->except(['token']);
            $province_code = $request->get('province_code', '');
            $city_code = $request->get('city_code', '');
            $area_code = $request->get('area_code', '');


            $store = Store::where('store_id', $store_id)->first();
            if (!$store && $store->pid == 0) {
                return json_encode([
                    'status' => 2,
                    'message' => '门店不存在或者不是分店',
                ]);
            }

            if ($merchant->merchant_type == 2) {
                $this->status = 2;
                $this->message = '收银员没有权限修改门店';
                return $this->format();
            }

            if ($province_code) {
                $data['province_name'] = $this->city_name($province_code);
            }
            if ($city_code) {
                $data['city_name'] = $this->city_name($city_code);
            }
            if ($area_code) {
                $data['area_name'] = $this->city_name($area_code);
            }

            Store::where('store_id', $store_id)->update($data);
            $this->status = 1;
            $this->message = '分店修改成功';


            //共享上级通道
            if ($store->pay_ways_type == 1) {

                $pid_store_id = Store::where('id', $store->pid)->first();
                $StorePayWay = StorePayWay::where('store_id', $pid_store_id)
                    ->where('status', 1)
                    ->get();

                foreach ($StorePayWay as $k => $v) {
                    $StorePayWay_new = StorePayWay::where('store_id', $store_id)
                        ->where('ways_type', $v->ways_type)
                        ->first();
                    $sp = [
                        'store_id' => $store_id,
                        'status' => $v->status,
                        'rate' => $v->rate,
                        'settlement_type' => $v->settlement_type,
                        'status_desc' => $v->status_desc,
                        'ways_type' => $v->ways_type,
                        'ways_source' => $v->ways_source,
                        'company' => $v->company,
                        'ways_desc' => $v->ways_desc,
                        'pcredit' => $v->pcredit,
                        'credit' => $v->credit,
                        'is_close' => $v->is_close,
                        'is_close_desc' => $v->is_close_desc,
                        'sort' => $v->sort,
                    ];
                    if ($StorePayWay_new) {
                        StorePayWay::where('store_id', $store_id)
                            ->where('ways_type', $v->ways_type)
                            ->update($sp);
                    } else {
                        StorePayWay::create($sp);
                    }
                }

            }
            //共享上级通道结束


            $datare = [
                'store_id' => $store_id,
            ];
            return $this->format($datare);


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }

    }


    /**收银员列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function merchant_lists(Request $request)
    {
        try {

            $merchant = $this->parseToken();
            $merchant_id = $merchant->merchant_id;
            $store_id = $request->get('store_id', '');
            $name = $request->get('name', '');


            if ($store_id == "") {
                $MerchantStore = MerchantStore::where('merchant_id', $merchant_id)
                    ->orderBy('created_at', 'asc')
                    ->first();
                if ($MerchantStore) {
                    $store_id = $MerchantStore->store_id;
                }
            }

            $where = [];


            if ($name) {
                $where[] = ['merchants.name', 'like', '%' . $name . '%'];
            }

            //角色是收银员 只返回自己
            if ($merchant->merchant_type == 2) {
                $where[] = ['merchants.id', '=', $merchant_id];

            }

            if ($store_id) {
                $where[] = ['stores.store_id', '=', $store_id];
            }


            $obj = DB::table('merchant_stores')
                ->join('merchants', 'merchant_stores.merchant_id', '=', 'merchants.id')
                ->join('stores', 'merchant_stores.store_id', '=', 'stores.store_id')
                ->where($where)
                ->select('stores.store_name', 'merchants.id as merchant_id', 'merchants.name', 'merchants.phone', 'merchants.type', 'merchants.logo', 'merchants.wx_logo');


            $this->t = $obj->count();
            $data = $this->page($obj)->get();

            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }

    }


    /**添加收银员二维码
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add_merchant_qr(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $data = [
                'add_url' => url('api/merchant/add_wx_merchant_qr')
            ];
            $this->status = 1;
            $this->message = '收银员添加成功';
            return $this->format($data);


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }

    }

    //扫二维码跳转到这里
    public function add_wx_merchant_qr(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $message = '暂时无法添加,请使用其他方式';
            return view('errors.page_errors', compact('message'));

        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }

    }


    /**添加收银员
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add_merchant(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $type = $request->get('type', '2');
            $name = $request->get('name', '');
            $phone = $request->get('phone', '');
            $data = $request->except(['token']);
            $password = $request->get('password', '');
            $stores = Store::where('store_id', $store_id)->first();
            if ($password == "") {
                $password = '000000';
            }
            if ($name == "") {
                $this->status = 2;
                $this->message = '姓名必填';
                return $this->format();
            }

            if ($phone == "") {
                $this->status = 2;
                $this->message = '手机号必填';
                return $this->format();
            }

            if ($merchant->merchant_type == 2) {
                $this->status = 2;
                $this->message = '收银员没有权限添加收银员';
                return $this->format();
            }


            if (!$stores) {
                $this->status = 2;
                $this->message = '门店不存在';
                return $this->format();
            }


            $rules = [
                'phone' => 'required|min:11|max:11',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
                return json_encode([
                    'status' => 2,
                    'message' => '手机号位数不正确',
                ]);
            }


            //验证密码
            if (strlen($password) < 6) {
                return json_encode([
                    'status' => 2,
                    'message' => '密码长度不符合要求'
                ]);
            }

            $rules = [
                'phone' => 'required|unique:merchants',
            ];
            $validator = Validator::make($data, $rules);
            if ($validator->fails()) {
//                $Merchant = Merchant::where('phone', $phone)
//                    ->select('id')
//                    ->first();
//                $merchant_id = $Merchant->id;

                return json_encode([
                    'status' => 2,
                    'message' => '手机号已经注册'
                ]);
            } else {
                $dataIN = [
                    'pid' => $merchant->merchant_id,
                    'type' => $type,
                    'name' => $name,
                    'email' => '',
                    'password' => bcrypt($password),
                    'phone' => $phone,
                    'user_id' => $merchant->user_id,//推广员id
                    'config_id' => $merchant->config_id,
                    'wx_openid' => ''
                ];
                $insert = Merchant::create($dataIN);
                $merchant_id = $insert->id;
            }

            MerchantStore::create([
                'store_id' => $store_id,
                'merchant_id' => $merchant_id,
            ]);


            $this->status = 1;
            $this->message = '收银员添加成功';
            return $this->format();


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }

    }


    /**修改收银员
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function up_merchant(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $merchant_id = $request->get('merchant_id');
            $store_id = $request->get('store_id');
            $name = $request->get('name', '');
            $password = $request->get('password', '');
            $data = $request->except(['token', 'merchant_id', 'store_id', 'password']);


            if ($password == "") {
                $password = '000000';
            }
            if ($name == "") {
                $this->status = 2;
                $this->message = '姓名必填';
                return $this->format();
            }

            if ($password && $password != '000000') {
                //验证密码
                if (strlen($password) < 6) {
                    return json_encode([
                        'status' => 2,
                        'message' => '密码长度不符合要求'
                    ]);
                }

                $data['password'] = bcrypt($password);
            }


            $dataIN = $data;

            Merchant::where('id', $merchant_id)->update($dataIN);

            $this->status = 1;
            $this->message = '收银员修改成功';
            return $this->format();


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }

    }

    /**删除收银员
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function del_merchant(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $merchant_id = $request->get('merchant_id', '');
            $store_id = $request->get('store_id', '');

            $check_data = [
                'merchant_id' => '收银员',
            ];
            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }


            if ($merchant_id == "") {
                $this->status = 2;
                $this->message = '请选择收银员';
                return $this->format();
            }
            $Merchant = Merchant::where('id', $merchant_id)->first();

            if (!$Merchant) {
                $this->status = 2;
                $this->message = '收银员不存在';
                return $this->format();
            }


            //不允许删除创建者
            if ($merchant->merchant_id == $merchant_id) {
                $this->status = 2;
                $this->message = '无法删除自己';
                return $this->format();
            }


            if ($store_id) {
                //删除收银员关联表
                $MerchantStore = MerchantStore::where('merchant_id', $merchant_id)
                    ->where('store_id', $store_id)
                    ->delete();
            } else {
                //删除收银员关联表
                $MerchantStore = MerchantStore::where('merchant_id', $merchant_id)
                    ->delete();
            }


            //如果是收银员就把账户清了
            if ($Merchant->pid != 0) {
                Merchant::where('id', $merchant_id)->delete();
            }


            $this->status = 1;
            $this->message = '收银员删除成功';
            return $this->format();


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }

    }

    //删除门店
    public function del_store(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $store_id = $request->get('store_id', '');


            if ($merchant->pid) {
                $this->status = 2;
                $this->message = '没有权限删除门店';
                return $this->format();
            }


            $Store = Store::where('store_id', $store_id)
                ->first();


            if (!$Store) {
                return json_encode([
                    'status' => 2,
                    'message' => '门店不存在!'
                ]);
            }


            $Order = Order::where('store_id', $store_id)
                ->where('pay_status', 1)
                ->select('id')
                ->first();


            if ($Order) {
                return json_encode([
                    'status' => 2,
                    'message' => '有交易订单的门店不支持删除!'
                ]);
            }

            $MerchantStore = MerchantStore::where('store_id', $store_id)
                ->select('id')
                ->get();

            if (count($MerchantStore) > 1) {
                return json_encode([
                    'status' => 2,
                    'message' => '请先删除收银员'
                ]);
            }


            Store::where('store_id', $store_id)->delete();
            StoreImg::where('store_id', $store_id)->delete();
            StoreBank::where('store_id', $store_id)->delete();
            MerchantStore::where('store_id', $store_id)->delete();


            $this->status = 1;
            $this->message = '门店删除成功';

            $data = [
                'store_id' => $store_id
            ];
            return $this->format($data);


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }

    }


    //所有系统通道
    public function pay_ways_all(Request $request)
    {
        try {
            $user = $this->parseToken();
            $MerchantStore = MerchantStore::where('merchant_id', $user->merchant_id)
                ->orderBy('created_at', 'asc')
                ->first();
            $store_id = '';
            if ($MerchantStore) {
                $store_id = $MerchantStore->store_id;
            }
            $data = [];
            $store_ways_desc = DB::table('store_ways_desc')->get();
            $store_ways_desc = json_decode(json_encode($store_ways_desc), true);
            foreach ($store_ways_desc as $k => $value) {

                $has = StorePayWay::where('store_id', $store_id)->where('ways_type', $value['ways_type'])
                    ->first();
                if ($has) {
                    $data[$k] = $value;
                    $data[$k]['rate'] = $has->rate;
                    $data[$k]['status'] = $has->status;
                    $data[$k]['status_desc'] = $has->status_desc;
                    $data[$k]['icon'] = '';

                    //如果是刷卡费率读取
                    //新大陆刷卡
                    if (in_array($value['ways_type'], [8005, 6005])) {

                        $data[$k]['rate'] = $has->rate_e;
                    }

                    //银联扫码
                    if (in_array($value['ways_type'], [8004, 6004])) {
                        $data[$k]['rate'] = $has->rate_c;
                    }


                    $data = array_values($data);

                } else {
                    //未开通的支付宝微信不显示
                    if (in_array($value['ways_type'], [1001, 2001])) {

                    }
                    $rate = $value['rate'];//默认是系统费率
                    $rate_a = $value['rate_a'];//默认是系统费率
                    $rate_b = $value['rate_b'];//默认是系统费率
                    $rate_e = $value['rate_e'];//默认是系统费率
                    $rate_c = $value['rate_c'];//默认是系统费率
                    $data[$k] = $value;
                    //代理商的费率
                    $user_rate = UserRate::where('user_id', $user->user_id)
                        ->where('ways_type', $value['ways_type'])
                        ->first();
                    if ($user_rate) {
                        $rate = $user_rate->store_all_rate;
                        $rate_c = $user_rate->store_all_rate_c;
                        $rate_e = $user_rate->store_all_rate_e;
                    }


                    $data[$k]['rate'] = $rate; //
                    $data[$k]['status'] = 0;
                    $data[$k]['status_desc'] = '未开通';
                    $data[$k]['icon'] = '';


                    //如果是刷卡费率读取
                    if (in_array($value['ways_type'], [8005, 6005])) {
                        $data[$k]['rate'] = $rate_e;
                    }

                    //银联扫码
                    if (in_array($value['ways_type'], [8004, 6004])) {
                        $data[$k]['rate'] = $rate_c;
                    }

                }

            }
            $help_url = 'https://www.baidu.com';
            return json_encode(['status' => 1, 'help_url' => $help_url, 'data' => $data]);


        } catch (\Exception $exception) {
            return json_encode(['status' => 0, 'message' => $exception->getMessage() . ' - ' . $exception->getLine()]);
        }
    }


    //所有系统通道-new
    public function store_all_pay_way_lists(Request $request)
    {
        try {
            $user = $this->parseToken();
            $store_id = $request->get('store_id');

            $data = [];
            $store_all_pay_way_lists = DB::table('store_all_pay_way_lists')
                ->select('ways_count', 'company', 'company_desc')
                ->get();
            $store_all_pay_way_lists = json_decode(json_encode($store_all_pay_way_lists), true);
            foreach ($store_all_pay_way_lists as $k => $value) {

                $has = StorePayWay::where('store_id', $store_id)
                    ->where('company', $value['company'])
                    ->select('status')
                    ->first();
                $data[$k] = $value;
                $data[$k]['status'] = '0';
                $data[$k]['status_desc'] = '未开通';
                //
                if ($has && $has->status == 1) {
                    $data[$k]['status'] = '1';
                    $data[$k]['status_desc'] = '开通成功';
                }

                if ($has && $has->status == 2) {
                    $data[$k]['status'] = '2';
                    $data[$k]['status_desc'] = '审核中';
                }

                if ($has && $has->status == 3) {
                    $data[$k]['status'] = '3';
                    $data[$k]['status_desc'] = '开通失败';
                }

            }
            return json_encode(['status' => 1, 'data' => $data]);


        } catch (\Exception $exception) {
            return json_encode(['status' => 0, 'message' => $exception->getMessage() . ' - ' . $exception->getLine()]);
        }
    }


    //单个通道信息查询-new
    public function company_pay_ways_info(Request $request)
    {
        try {
            $user = $this->parseToken();
            $store_id = $request->get('store_id');
            $company = $request->get('company');
            $data = [];
            $store_ways_desc = DB::table('store_ways_desc')
                ->where('company', $company)->get();


            $store = Store::where('store_id', $store_id)
                ->select('user_id')
                ->first();

            $ways_desc = "";
            $ways_status = "";
            foreach ($store_ways_desc as $k => $v) {

                $data[$k]['ways_desc'] = $v->ways_desc;
                $data[$k]['ways_source'] = $v->ways_source;
                $data[$k]['settlement_type'] = $v->settlement_type;
                $data[$k]['ways_type'] = $v->ways_type;

                $has = StorePayWay::where('store_id', $store_id)
                    ->where('ways_type', $v->ways_type)
                    ->first();

                $ways_desc = $ways_desc . $data[$k]['ways_desc'] . ',';


                if ($has) {
                    $data[$k]['rate'] = $has->rate;
                    $data[$k]['status'] = $has->status;
                    $data[$k]['status_desc'] = $has->status_desc;

                } else {

                    $rate = $v->rate;//默认是系统费率
                    //代理商的费率
                    $user_rate = UserRate::where('user_id', $store->user_id)
                        ->where('ways_type', $v->ways_type)
                        ->select('store_all_rate')
                        ->first();
                    if ($user_rate) {
                        $rate = $user_rate->store_all_rate;
                    }
                    $data[$k]['rate'] = $rate; //
                    $data[$k]['status'] = 0;
                    $data[$k]['status_desc'] = '未开通';
                }

                $ways_status = $data[$k]['status'];
            }


            return json_encode(['status' => 1, 'message' => '数据返回成功', 'ways_status' => $ways_status, 'ways_desc' => $ways_desc, 'data' => $data]);


        } catch (\Exception $exception) {
            return json_encode(['status' => 0, 'message' => $exception->getMessage() . ' - ' . $exception->getLine()]);
        }
    }

    //申请通道-new
    public function open_company_pay_ways(Request $request)
    {
        try {

            $user = $this->parseToken();
            $company = $request->get('company');
            $store_id = $request->get('store_id');

            $code = $request->get('code', '888888');

            $SettleModeType = $request->get('Settle_mode_type', '01');//结算方式
            $sp = new StorePayWaysController();

            $store_ways_desc = DB::table('store_ways_desc')
                ->where('company', $company)->first();
            $type = $store_ways_desc->ways_type;


            return $sp->base_open_ways($type, $code, $store_id, $SettleModeType, '', '');

        } catch (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'message' => $exception->getMessage() . $exception->getLine(),
            ]);
        }
    }


    public function settle_mode_type(Request $request)
    {

        try {


            $type = $request->get('ways_type', '3001');
            $data = [
                [
                    'settle_mode_type' => '01',
                    'settle_mode_type_desc' => '结算到银行卡',
                    'agreement' => '银行收单结算协议',
                    'url' => url(''),
                ]
            ];
            if ($type == '1000') {
                $data = [
                    [
                        'settle_mode_type' => '01',
                        'settle_mode_type_desc' => '结算到支付宝',
                        'agreement' => '支付宝协议',
                        'url' => url(''),

                    ]
                ];
            }

            if ($type == '2000') {
                $data = [
                    [
                        'settle_mode_type' => '01',
                        'settle_mode_type_desc' => '结算到微信商户号',
                        'agreement' => '微信支付协议',
                        'url' => url(''),
                    ],
                ];
            }

            if ($type == '3001' || $type == '3002') {
                $data = [
                    [
                        'settle_mode_type' => '01',
                        'settle_mode_type_desc' => '结算到银行卡',
                        'agreement' => '网商银行支付协议',
                        'url' => url(''),
                    ]
                ];
            }


            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }

    }


    //申请通道
    public function open_pay_ways(Request $request)
    {
        try {

            $user = $this->parseToken();
            $type = $request->get('ways_type');
            $code = $request->get('code', '888888');
            $merchant_id = $user->merchant_id;
            $MerchantStore = MerchantStore::where('merchant_id', $merchant_id)
                ->select('store_id')
                ->first();
            if (!$MerchantStore) {
                return json_encode(['status' => 2, 'message' => '没有添加店铺']);
            }
            $store_id = $MerchantStore->store_id;
            Log::info($store_id);
            $Merchant = Merchant::where('id', $merchant_id)
                ->select('phone', 'email')
                ->first();
            $phone = $Merchant->phone;
            $email = $Merchant->email;
            $SettleModeType = $request->get('Settle_mode_type', '01');//结算方式
            $sp = new StorePayWaysController();
            return $sp->base_open_ways($type, $code, $store_id, $SettleModeType, $phone, $email);

        } catch (\Exception $exception) {
            return json_encode([
                'status' => 2,
                'message' => $exception->getMessage() . $exception->getLine(),
            ]);
        }
    }


    //店铺通道开通类型
    public function store_pay_ways(Request $request)
    {
        try {
            $merchant = $this->parseToken();//
            $merchant_id = $merchant->merchant_id;
            $MerchantStore = MerchantStore::where('merchant_id', $merchant_id)
                ->orderBy('created_at', 'asc')
                ->first();
            $store_id = $MerchantStore->store_id;
            if ($store_id) {
                $data = DB::table('store_ways_desc')
                    ->join('store_pay_ways', 'store_pay_ways.ways_type', '=', 'store_ways_desc.ways_type')
                    ->where('store_pay_ways.store_id', $store_id)
                    ->where('store_pay_ways.status', 1)
                    ->where('store_pay_ways.is_close', 0)
                    ->select('store_ways_desc.*', 'store_pay_ways.ways_source', 'store_pay_ways.sort', 'store_pay_ways.rate')
                    ->orderBy('store_pay_ways.sort', 'asc')
                    ->get();

                $this->status = 1;
                $this->message = '数据返回成功';
                return $this->format($data);


            } else {
                return json_encode(['status' => 2, 'message' => '没有绑定店铺']);
            }

        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }
    }


    //我的 绑定简称
    public function add_store_short_name(Request $request)
    {

        try {
            $merchant = $this->parseToken();
            $store_short_name = $request->get('store_short_name', '');
            $store_id = $request->get('store_id', '');

            if ($store_id == "") {
                $MerchantStore = MerchantStore::where('merchant_id', $merchant->merchant_id)
                    ->orderBy('created_at', 'asc')
                    ->first();
                $store_id = $MerchantStore->store_id;
            }

            $store = Store::where('store_id', $store_id)->first();

            //添加修改
            if ($store_short_name) {
                $store->update([
                    'store_short_name' => $store_short_name
                ]);
                $store->save();
                $message = "门店简称修改成功";

            } else {
                $store_short_name = '';
                if ($MerchantStore) {
                    $store_short_name = $store->store_short_name;
                }
                $message = "数据返回成功";
            }


            $data = [
                'status' => 1,
                'message' => $message,
                'data' => [
                    'store_short_name' => $store_short_name
                ]
            ];
            return json_encode($data);

        } catch (\Exception $exception) {
            return json_encode(['status' => 2, 'message' => $exception->getMessage()]);
        }
    }


    //获取门店收银员的收款微信公众号提醒二维码
    public function get_wx_notify(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $config_id = $merchant->config_id;
            $store_id = $request->get('store_id', '');
            $merchant_id = $request->get('merchant_id', $merchant->merchant_id);

            $config = new WeixinConfigController();
            $config_obj = $config->weixin_config_obj($config_id);

            if ($store_id == "") {
                $MerchantStore = MerchantStore::where('merchant_id', $merchant_id)->first();
                if ($MerchantStore) {
                    $store_id = $MerchantStore->store_id;
                }
            }

            $config = [
                'app_id' => $config_obj->wx_notify_appid,
                'secret' => $config_obj->wx_notify_secret,
            ];
            $app = Factory::officialAccount($config);

            $key = 'gztx&' . $config_id . '&' . $store_id . '&' . $merchant_id;

            $result = $app->qrcode->temporary($key, 6 * 24 * 3600);
            if (isset($result['url'])) {
                $data = [
                    'status' => 1,
                    'message' => '返回成功',
                    'data' => [
                        'url' => $result['url'],
                        'name' => $config_obj->app_name,
                    ]
                ];
            } else {
                $data = [
                    'status' => 2,
                    'message' => '生成失败',
                ];
            }
            return json_encode($data);


        } catch (\Exception $exception) {
            return json_encode(['status' => 2, 'message' => $exception->getMessage()]);
        }
    }

//检查是否有关注
    public function check_wx_notify(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $config_id = $merchant->config_id;
            $store_id = $request->get('store_id', '');
            $merchant_id = $request->get('merchant_id', $merchant->merchant_id);

            if ($store_id == "") {
                $MerchantStore = MerchantStore::where('merchant_id', $merchant_id)->first();
                if ($MerchantStore) {
                    $store_id = $MerchantStore->store_id;
                }
            }
            $is_wx_notify = '0';
            $WeixinNotify = WeixinNotify::where('store_id', $store_id)
                ->where('merchant_id', $merchant_id)
                ->first();
            if ($WeixinNotify) {
                $is_wx_notify = '1';
            }
            return json_encode([
                'status' => 1,
                'message' => '返回成功',
                'data' => [
                    'is_wx_notify' => $is_wx_notify,
                ]
            ]);
        } catch (\Exception $exception) {
            return json_encode(['status' => 2, 'message' => $exception->getMessage()]);
        }
    }

//关注删除
    public function get_wx_notify_del(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $config_id = $merchant->config_id;
            $store_id = $request->get('store_id', '');
            $merchant_id = $request->get('merchant_id', $merchant->merchant_id);

            if ($store_id == "") {
                $MerchantStore = MerchantStore::where('merchant_id', $merchant_id)->first();
                if ($MerchantStore) {
                    $store_id = $MerchantStore->store_id;
                }
            }
            $WeixinNotify = WeixinNotify::where('store_id', $store_id)
                ->where('merchant_id', $merchant_id)
                ->delete();

            return json_encode([
                'status' => 1,
                'message' => '取消成功',
                'data' => [
                    'is_wx_notify' => '0',
                ]
            ]);
        } catch (\Exception $exception) {
            return json_encode(['status' => 2, 'message' => $exception->getMessage()]);
        }
    }


    public function time($time)
    {
        try {

            //去除中文
            $time = str_replace(".", "-", $time);
            $time = preg_replace('/([\x80-\xff]*)/i', '', $time);

            $is_date = strtotime($time) ? strtotime($time) : false;
            if ($is_date) {
                $time = date('Y-m-d', strtotime($time));
            }


            return $time;

        } catch (\Exception $exception) {

        }

        return $time;
    }

}