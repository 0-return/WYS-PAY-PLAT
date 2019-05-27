<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/7/20
 * Time: 上午10:35
 */

namespace App\Api\Controllers\User;


use App\Api\Controllers\BaseController;
use App\Api\Controllers\Basequery\StorePayWaysController;
use App\Api\Controllers\Config\FuiouConfigController;
use App\Api\Controllers\Config\JdConfigController;
use App\Api\Controllers\Config\NewLandConfigController;
use App\Api\Controllers\MyBank\MyBankController;
use App\Api\Controllers\Newland\UpdateController;
use App\Models\AlipayAppOauthUsers;
use App\Models\FuiouStore;
use App\Models\HStore;
use App\Models\JdStore;
use App\Models\LtfStore;
use App\Models\Merchant;
use App\Models\MerchantStore;
use App\Models\MyBankStore;
use App\Models\NewLandConfig;
use App\Models\NewLandStore;
use App\Models\Order;
use App\Models\ProvinceCity;
use App\Models\Store;
use App\Models\StoreBank;
use App\Models\StoreImg;
use App\Models\StorePayWay;
use App\Models\User;
use App\Models\UserRate;
use App\Models\WeixinStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StoreController extends BaseController
{


    /**列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store_lists(Request $request)
    {
        try {
            $user = $this->parseToken();
            $user_id = $request->get('user_id', $user->user_id);
            $time_start = $request->get('time_start', '');//关键词
            $time_end = $request->get('time_end', '');//关键词
            $store_name = $request->get('store_name', '');//关键词
            $province_code = $request->get('province_code');//状态
            $city_code = $request->get('city_code');//状态
            $area_code = $request->get('area_code');//状态
            $status = $request->get('status');//状态
            $pid = $request->get('pid', 0);//主店ID
            $is_delete = $request->get('is_delete', 0);//
            $is_close = $request->get('is_close', 0);//


            $where = [];

            $where[] = ['stores.pid', '=', $pid];
            $where[] = ['stores.is_delete', '=', $is_delete];
            $where[] = ['stores.is_close', '=', $is_close];

            if ($status) {
                $where[] = ['stores.status', '=', $status];
            }
            if ($province_code) {
                $where[] = ['stores.province_code', '=', $province_code];
            }
            if ($city_code) {
                $where[] = ['stores.city_code', '=', $city_code];
            }
            if ($area_code) {
                $where[] = ['stores.area_code', '=', $area_code];
            }

            if ($time_start) {
                $time_start = date('Y-m-d 00:00:00', strtotime($time_start));
                $where[] = ['stores.created_at', '>=', $time_start];
            }
            if ($time_end) {
                $time_end = date('Y-m-d 23:59:59', strtotime($time_end));
                $where[] = ['stores.created_at', '<=', $time_end];
            }


            $obj = DB::table('stores');
            $obj = $obj->join('users', 'stores.user_id', 'users.id');

            if ($store_name) {
                if (is_numeric($store_name)) {
                    $where1[] = ['stores.store_id', 'like', '%' . $store_name . '%'];
                } else {
                    $where1[] = ['stores.store_name', 'like', '%' . $store_name . '%'];
                }
                $obj->where($where1)
                    ->whereIn('stores.user_id', $this->getSubIds($user_id))
                    ->select('stores.*', 'users.name as user_name')
                    // ->orderBy('stores.updated_at', 'desc')
                    ->get();

            } else {
                $obj->where($where)
                    ->whereIn('stores.user_id', $this->getSubIds($user_id))
                    ->select('stores.*', 'users.name as user_name')
                    // ->orderBy('stores.updated_at', 'desc')
                    ->get();
            }


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


    /**列表 根据门店ID 获取所有门店名称
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store_all_lists(Request $request)
    {
        try {
            $user = $this->parseToken();
            $store_id = $request->get('store_id', '');//
            $id = $request->get('id', '');//


            $obj = DB::table('stores')
                ->orWhere('id', $id)
                ->orWhere('pid', $id)
                ->select('store_id', 'store_short_name');

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


    /**列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store_pc_lists(Request $request)
    {
        try {
            $user = $this->parseToken();
            $user_id = $request->get('user_id', $user->user_id);
            $time_start = $request->get('time_start', '');//关键词
            $time_end = $request->get('time_end', '');//关键词
            $store_name = $request->get('store_name', '');//关键词
            $province_code = $request->get('province_code');//状态
            $city_code = $request->get('city_code');//状态
            $area_code = $request->get('area_code');//状态
            $status = $request->get('status');//状态
            $pid = $request->get('pid', 0);//主店ID
            $is_delete = $request->get('is_delete', 0);//
            $is_close = $request->get('is_close', 0);//


            $where = [];

            $where[] = ['stores.pid', '=', $pid];
            $where[] = ['stores.is_delete', '=', $is_delete];
            $where[] = ['stores.is_close', '=', $is_close];

            if ($status) {
                $where[] = ['stores.status', '=', $status];
            }
            if ($province_code) {
                $where[] = ['stores.province_code', '=', $province_code];
            }
            if ($city_code) {
                $where[] = ['stores.city_code', '=', $city_code];
            }
            if ($area_code) {
                $where[] = ['stores.area_code', '=', $area_code];
            }

            if ($time_start) {
                $time_start = date('Y-m-d 00:00:00', strtotime($time_start));
                $where[] = ['stores.created_at', '>=', $time_start];
            }
            if ($time_end) {
                $time_end = date('Y-m-d 23:59:59', strtotime($time_end));
                $where[] = ['stores.created_at', '<=', $time_end];
            }


            $obj = DB::table('stores');
            $obj = $obj->join('users', 'stores.user_id', 'users.id');

            if ($store_name) {
                if (is_numeric($store_name)) {
                    $where1[] = ['stores.store_id', 'like', '%' . $store_name . '%'];
                } else {
                    $where1[] = ['stores.store_name', 'like', '%' . $store_name . '%'];
                }

                $obj->where($where1)
                    ->whereIn('stores.user_id', $this->getSubIds($user_id))
                    ->orderBy('stores.created_at', 'desc')
                    ->select('stores.*', 'users.name as user_name')
                    ->get();

            } else {
                $obj->where($where)
                    ->whereIn('stores.user_id', $this->getSubIds($user_id))
                    ->orderBy('stores.created_at', 'desc')
                    ->select('stores.*', 'users.name as user_name')
                    ->get();
            }


            $this->t = $obj->count();
            $data = $this->page($obj)->get();

            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);


        } catch (\Exception $exception) {
            Log::info($exception);
            $this->status = -1;
            $this->message = $exception->getMessage();
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
            $user = $this->parseToken();
            $store_id = $request->get('store_id', '');
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
            $AlipayAppOauthUsers = AlipayAppOauthUsers::where('store_id', $store_id)->first();

            if ($AlipayAppOauthUsers) {
                $store_alipay_account = $AlipayAppOauthUsers->alipay_user_account;
            }

            $type = $request->get('type');
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
                        'store_email' => $store->store_email,
                        'store_name' => $store->store_name,
                        'store_short_name' => $store->store_short_name,
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
                        'status' => $store->status,
                        'status_desc' => $store->status_desc,

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
                        'store_license_stime' => $store->store_license_stime,
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

    /**门店认证修改
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function up_store(Request $request)
    {
        try {

            $user = $this->parseToken();
            $data = $request->except(['token']);

            $store_id = date('Ymdhis', time()) . rand(1000, 9999);//随机
            $store_id = $request->get('store_id', $store_id);
            $config_id = $user->config_id;
            $user_id = $user->user_id;
            $user_id = $request->get('user_id', $user_id);

            //法人信息
            $people_phone = $request->get('people_phone', '');
            $store_email = $request->get('store_email', '');
            $head_name = $request->get('head_name', '');
            $head_sfz_no = $request->get('head_sfz_no', '');
            $people = $request->get('people', $head_name);

            $head_sfz_img_a = $request->get('head_sfz_img_a', '');
            $head_sfz_img_b = $request->get('head_sfz_img_b', '');

            $head_sfz_time = $request->get('head_sfz_time', '');
            $head_sfz_stime = $request->get('head_sfz_stime', '');
            $head_sfz_time = $this->time($head_sfz_time);
            $head_sfz_stime = $this->time($head_sfz_stime);


            //门店信息
            $store_name = $request->get('store_name', '');
            $store_short_name = $request->get('store_short_name', $store_name);
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
            $weixin_name = $request->get('weixin_name', '');
            $weixin_no = $request->get('weixin_no', '');


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

            $bank_sfz_time = $request->get('bank_sfz_time', '');
            $bank_sfz_stime = $request->get('bank_sfz_stime', '');
            $bank_sfz_time = $this->time($bank_sfz_time);
            $bank_sfz_stime = $this->time($bank_sfz_stime);

            //证照信息
            $store_license_no = $request->get('store_license_no', '');
            $store_license_time = $request->get('store_license_time', '');
            $store_license_stime = $request->get('store_license_stime', '');

            $head_sc_img = $request->get('head_sc_img', '');
            $head_store_img = $request->get('head_store_img', '');

            $store_license_img = $request->get('store_license_img', '');
            $store_industrylicense_img = $request->get('store_industrylicense_img', '');
            $store_other_img_a = $request->get('store_other_img_a', '');
            $store_other_img_b = $request->get('store_other_img_b', '');
            $store_other_img_c = $request->get('store_other_img_c', '');


            //拼装门店信息
            $stores = [
                'config_id' => $config_id,
                'user_id' => $user_id,
                'merchant_id' => '',
                'store_id' => $store_id,
                'store_name' => $store_name,
                'store_type' => $store_type,
                'store_type_name' => $store_type_name,
                'store_email' => $store_email,
                'store_short_name' => $store_short_name,
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
                'store_license_stime' => $store_license_stime,
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


            if ($store) {
                $is_store = 1;
            } else {
                $is_store = 0;
            }

            $is_merchant = 0;
            //判断手机号是否被注册
            if ($people_phone) {
                //验证手机号
                if (!preg_match("/^1[3456789]{1}\d{9}$/", $people_phone)) {
                    return json_encode([
                        'status' => 2,
                        'message' => '手机号码格式不正确'
                    ]);
                }

                $Merchant = Merchant::where('phone', $people_phone)
                    ->select('id')->first();

                if ($Merchant) {
                    $is_merchant = 1;
                }

                if ($is_merchant == 1 && $is_store == 0) {
                    return json_encode([
                        'status' => 2,
                        'message' => '手机号已被注册,请更换'
                    ]);
                }
            }


            //提交的新商户必须填写
            if (!$store) {
                $check_data = [
                    'store_id' => '门店ID',
                ];
                $check = $this->check_required($request->except(['token']), $check_data);
                if ($check) {
                    return json_encode([
                        'status' => 2,
                        'message' => $check
                    ]);
                }
            }
            //开启事务
            try {
                DB::beginTransaction();

                //入库账户
                if ($people_phone) {
                    //未注册
                    if ($is_merchant == 0) {
                        //注册账户
                        $dataIN = [
                            'pid' => 0,
                            'type' => 1,
                            'name' => $people,
                            'email' => '',
                            'password' => bcrypt('000000'),
                            'phone' => $people_phone,
                            'user_id' => $user_id,//推广员id
                            'config_id' => $config_id,
                            'wx_openid' => ''
                        ];
                        $merchant = Merchant::create($dataIN);
                        $merchant_id = $merchant->id;

                        $stores['merchant_id'] = $merchant_id;
                        MerchantStore::create([
                            'store_id' => $store_id,
                            'merchant_id' => $merchant_id
                        ]);

                    }
                }

                if ($store) {
                    //修改   不修改归属ID
                    $stores['user_id'] = $store->user_id;
                    $stores['config_id'] = $store->config_id;
                    $stores = array_filter($stores, function ($v) {
                        if ($v == "") {
                            return false;
                        } else {
                            return true;
                        }
                    });
                    $store->update($stores);
                    $store->save();
                } else {

                    Store::create($stores);
                }
                if ($store_img) {
                    $store_img->update(array_filter($store_imgs));
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
                                "config_id" => $config_id,
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
                    $store_bank->update(array_filter($store_banks));
                    $store_bank->save();
                } else {
                    $stores['store_type'] = 1;
                    $stores['store_type_name'] = '个体';
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
                    'store_id' => $store_id
                ]
            ]);


        } catch (\Exception $exception) {
            Log::info($exception);
            $this->status = -1;
            $this->message = $exception->getMessage();
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


    //所有系统通道
    public function pay_ways_all(Request $request)
    {
        try {
            $user = $this->parseToken();
            $store_id = $request->get('store_id');

            $store = Store::where('store_id', $store_id)
                ->select('user_id')
                ->first();

            if (!$store) {
                return json_encode(['status' => 2, 'message' => '门店不存在', 'data' => []]);
            }
            $data = [];
            $store_ways_desc = DB::table('store_ways_desc')
                ->select()
                ->get();
            $store_ways_desc = json_decode(json_encode($store_ways_desc), true);
            foreach ($store_ways_desc as $k => $value) {
                $has = StorePayWay::where('store_id', $store_id)
                    ->where('ways_type', $value['ways_type'])
                    ->first();
                if ($has) {
                    $data[$k] = $value;
                    $data[$k]['rate'] = $has->rate;
                    $data[$k]['status'] = $has->status;
                    $data[$k]['status_desc'] = $has->status_desc;
                    $data[$k]['icon'] = '';
                    $data[$k]['rate_a'] = $has->rate_a;
                    $data[$k]['rate_b'] = $has->rate_b;
                    $data[$k]['rate_b_top'] = $has->rate_b_top;
                    $data[$k]['rate_c'] = $has->rate_c;
                    $data[$k]['rate_d'] = $has->rate_d;
                    $data[$k]['rate_d_top'] = $has->rate_d_top;
                    $data[$k]['rate_e'] = $has->rate_e;
                    $data[$k]['rate_f'] = $has->rate_f;
                    $data[$k]['rate_f_top'] = $has->rate_f_top;
                    //如果是刷卡费率读取
                    //新大陆刷卡
                    if (in_array($value['ways_type'], [8005, 6005])) {
                        $data[$k]['rate'] = '贷记卡=' . $has->rate_e . '%,借记卡=' . $has->rate_f;
                    }

                    //银联扫码
                    if (in_array($value['ways_type'], [8004, 6004])) {
                        $data[$k]['rate'] = $has->rate_c;
                    }

                    $data = array_values($data);

                } else {
                    $rate = $value['rate'];//默认是系统费率
                    $rate_a = $value['rate_a'];//默认是系统费率
                    $rate_b = $value['rate_b'];//默认是系统费率
                    $rate_b_top = $value['rate_b_top'];//默认是系统费率
                    $rate_e = $value['rate_e'];//默认是系统费率
                    $rate_c = $value['rate_c'];//默认是系统费率
                    $rate_d = $value['rate_d'];//默认是系统费率
                    $rate_d_top = $value['rate_d_top'];//默认是系统费率
                    $rate_f = $value['rate_f'];//默认是系统费率
                    $rate_f_top = $value['rate_f_top'];//默认是系统费率
                    $data[$k] = $value;
                    //代理商的费率
                    $user_rate = UserRate::where('user_id', $store->user_id)
                        ->where('ways_type', $value['ways_type'])
                        ->first();
                    if ($user_rate) {
                        $rate = $user_rate->store_all_rate;//显示代理商的默认费率
                        $rate_a = $user_rate->store_all_rate_a;//默认是系统费率
                        $rate_b = $user_rate->store_all_rate_b;//默认是系统费率
                        $rate_c = $user_rate->store_all_rate_c;//默认是系统费率
                        $rate_b_top = $user_rate->store_all_rate_b_top;//默认是系统费率
                        $rate_d = $user_rate->store_all_rate_d;//默认是系统费率
                        $rate_d_top = $user_rate->store_all_rate_d_top;//默认是系统费率
                        $rate_e = $user_rate->store_all_rate_e;//默认是系统费率
                        $rate_f = $user_rate->store_all_rate_f;//默认是系统费率
                        $rate_f_top = $user_rate->store_all_rate_f_top;//默认是系统费率
                    }

                    $data[$k]['rate'] = $rate; //
                    $data[$k]['status'] = 0;
                    $data[$k]['status_desc'] = '未开通';
                    $data[$k]['icon'] = '';
                    $data[$k]['rate_a'] = $rate_a;
                    $data[$k]['rate_b'] = $rate_b;
                    $data[$k]['rate_c'] = $rate_c;
                    $data[$k]['rate_d'] = $rate_d;
                    $data[$k]['rate_d_top'] = $rate_d_top;
                    $data[$k]['rate_b_top'] = $rate_b_top;
                    $data[$k]['rate_e'] = $rate_e;
                    $data[$k]['rate_f'] = $rate_f;
                    $data[$k]['rate_f_top'] = $rate_f_top;
                    //如果是刷卡费率读取
                    //新大陆刷卡
                    if (in_array($value['ways_type'], [8005, 6005])) {
                        $data[$k]['rate'] = '贷记卡=' . $rate_e . '%,借记卡=' . $rate_f;
                    }

                    //银联扫码
                    if (in_array($value['ways_type'], [8004, 6004])) {
                        $data[$k]['rate'] = $rate_c;
                    }

                }

            }

            return json_encode(['status' => 1, 'data' => $data]);


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

    //查询单个通道详细
    public function pay_ways_info(Request $request)
    {
        try {
            $user = $this->parseToken();
            $store_id = $request->get('store_id');
            $ways_type = $request->get('ways_type');

            $store = Store::where('store_id', $store_id)
                ->select('user_id')
                ->first();

            if (!$store) {
                return json_encode(['status' => 2, 'message' => '门店不存在', 'data' => []]);
            }
            $data = [];
            $store_ways_desc = DB::table('store_ways_desc')->where('ways_type', $ways_type)->first();
            $data['ways_desc'] = $store_ways_desc->ways_desc;
            $data['ways_source'] = $store_ways_desc->ways_source;
            $data['settlement_type'] = $store_ways_desc->settlement_type;
            $data['ways_type'] = $store_ways_desc->ways_type;

            $has = StorePayWay::where('store_id', $store_id)->where('ways_type', $ways_type)->first();
            if ($has) {
                $data['rate'] = $has->rate;
                $data['status'] = $has->status;
                $data['status_desc'] = $has->status_desc;

            } else {
                $rate = $store_ways_desc->rate;//默认是系统费率
                //代理商的费率
                $user_rate = UserRate::where('user_id', $store->user_id)
                    ->where('ways_type', $ways_type)
                    ->select('rate')
                    ->first();
                if ($user_rate) {
                    $rate = $user_rate->rate;
                }
                $data['rate'] = $rate; //
                $data['status'] = 0;
                $data['status_desc'] = '未开通';
            }


            return json_encode(['status' => 1, 'data' => $data]);


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage() . ' - ' . $exception->getLine()]);
        }
    }

    //门店设置费率-扫码
    public function edit_store_rate(Request $request)
    {

        try {
            $user = $this->parseToken();
            $store_id = $request->get('store_id');
            $ways_type = $request->get('ways_type');
            $rate = $request->get('rate');

            $store = Store::where('store_id', $store_id)
                ->first();

            if (!$store) {
                return json_encode(['status' => 2, 'message' => '门店不存在', 'data' => []]);
            }

            //共享通道不支持修改费率
            if ($user->level > 0 && $store->pay_ways_type) {
                return json_encode(['status' => 2, 'message' => '共享通道不支持修改费率']);
            }


            //代理商的费率
            $user_rate = UserRate::where('user_id', $store->user_id)
                ->where('ways_type', $ways_type)
                ->select('rate')
                ->first();


            if (!$user_rate) {
                return json_encode(['status' => 2, 'message' => '代理商未设置费率', 'data' => []]);
            }

            //不能大于代理商的成本
            if ($user->level > 0 && $rate < $user_rate->rate) {
                return json_encode(['status' => 2, 'message' => '费率不能低于代理商的费率', 'data' => []]);
            }

            $has = StorePayWay::where('store_id', $store_id)->where('ways_type', $ways_type)
                ->first();


            $all_pay_ways = DB::table('store_ways_desc')
                ->where('ways_type', $ways_type)
                ->select('company')
                ->first();

            $company = $all_pay_ways->company;

            if ($has) {
                $status = $has->status;
                $status_desc = $has->status_desc;

                //如果通道审核中不允许修改费率
                if ($status == 2) {
                    return json_encode(['status' => 2, 'message' => '通道审核中不允许修改费率', 'data' => []]);
                }


                //京东收银通道修改费率
                if ($has->status == 1 && 5999 < $ways_type && $ways_type < 6999) {
                    return json_encode(['status' => 2, 'message' => '京东通道开通后不支持软件修改', 'data' => $request->except(['token'])]);
                }


                //网商银行同步平台费率
                if ($has->status == 1 && 2999 < $ways_type && $ways_type < 3999) {
                    $Merchant = MyBankStore::where('OutMerchantId', $store_id)
                        ->select('MerchantId')
                        ->first();

                    if (!$Merchant) {
                        return json_encode(['status' => 2, 'message' => '网商商户信息不存在']);
                    }

                    //暂时全部修改
                    $data = [
                        'ta_rate' => $rate + 0.02,
                        'tb_rate' => $rate,
                        'MerchantId' => $Merchant->MerchantId,
                    ];
                    $obj = new MyBankController();
                    $re = $obj->StoreRate($data);

                    if ($re['status'] == 2) {
                        return json_encode($re);
                    }
                }


                //新大陆同步平台费率
                if ($has->status == 1 && 7999 < $ways_type && $ways_type < 8999) {
                    $OBJ = new  UpdateController();

                    //修改费率
                    $up_data = [
                        'store_id' => $store_id,
                        'email' => $store->people_phone . '@139.com',
                        'phone' => $store->people_phone,
                        'rate' => $rate,
                        'rate_a' => $has->rate_a,
                        'rate_c' => $has->rate_c,
                        'rate_f' => $has->rate_f,
                        'rate_f_top' => $has->rate_f_top,
                        'rate_e' => $has->rate_e,
                    ];

                    // $return = $OBJ->update_store_rate($up_data);
                    //  return $return;
                }

                //和融通
                if ($user->level > 0 && $has->status == 1 && 8999 < $ways_type && $ways_type < 9999) {
                    if ($rate != '0.38') {
                        return json_encode(['status' => 2, 'message' => '和融通费率必须是0.38']);
                    }
                }


            } else {
                $status = 0;
                $status_desc = '未开通';

                //和融通
                if ($user->level > 0 && 8999 < $ways_type && $ways_type < 9999) {
                    if ($rate != '0.38') {
                        return json_encode(['status' => 2, 'message' => '和融通费率必须是0.38']);
                    }
                }
            }


            //查找下级门店共享通道
            if ($store->pid == 0) {
                $sub_store = Store::where('pid', $store->id)
                    ->where('pay_ways_type', 1)
                    ->select('store_id')
                    ->get();

                foreach ($sub_store as $k => $v) {

                    //暂时全部修改
                    $data['store_id'] = $v->store_id;
                    $data['rate'] = $rate;
                    $data['status'] = $status;
                    $data['status_desc'] = $status_desc;
                    $data['company'] = $company;
                    $this->send_ways_data($data);
                }

            }


            //暂时全部修改
            $data['store_id'] = $store_id;
            $data['rate'] = $rate;
            $data['status'] = $status;
            $data['status_desc'] = $status_desc;
            $data['company'] = $company;
            $return = $this->send_ways_data($data);
            return $return;


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage() . ' - ' . $exception->getLine()]);
        }

    }

    //门店设置费率-刷卡
    public function edit_store_un_rate(Request $request)
    {

        try {
            $user = $this->parseToken();
            $store_id = $request->get('store_id');
            $ways_type = $request->get('ways_type');
            $rate_e = $request->get('rate_e');
            $rate_f = $request->get('rate_f');
            $rate_f_top = $request->get('rate_f_top', '20');
            $store = Store::where('store_id', $store_id)
                ->first();

            if (!$store) {
                return json_encode(['status' => 2, 'message' => '门店不存在', 'data' => []]);
            }

            //共享通道不支持修改费率
            if ($store->pay_ways_type) {
                return json_encode(['status' => 2, 'message' => '共享通道不支持修改费率']);
            }


            //代理商的费率
            $user_rate = UserRate::where('user_id', $store->user_id)
                ->where('ways_type', $ways_type)
                ->select('rate_e', 'rate_f')
                ->first();


            if (!$user_rate) {
                return json_encode(['status' => 2, 'message' => '代理商未设置费率', 'data' => []]);
            }

            //不能大于代理商的成本
            if ($rate_e < $user_rate->rate_e) {
                return json_encode(['status' => 2, 'message' => '费率不能低于代理商的费率', 'data' => []]);
            }
            //不能大于代理商的成本
            if ($rate_f < $user_rate->rate_f) {
                return json_encode(['status' => 2, 'message' => '费率不能低于代理商的费率', 'data' => []]);
            }

            $ways = StorePayWay::where('store_id', $store_id)->where('ways_type', $ways_type)
                ->first();

            if (!$ways) {
                return json_encode(['status' => 2, 'message' => '请先设置扫码通道', 'data' => []]);
            } else {
                $status = $ways->status;
                $status_desc = $ways->status_desc;

                //如果通道审核中不允许修改费率
                if ($status == 2) {
                    return json_encode(['status' => 2, 'message' => '通道审核中不允许修改费率', 'data' => []]);
                }

                //新大陆同步平台费率
                if ($ways->status == 1 && 7999 < $ways_type && $ways_type < 8999) {
                    $OBJ = new  UpdateController();
                    //修改费率
                    $up_data = [
                        'store_id' => $store_id,
                        'email' => $store->people_phone . '@139.com',
                        'phone' => $store->people_phone,
                        'rate' => $ways->rate,
                        'rate_a' => $ways->rate_a,
                        'rate_c' => $ways->rate_c,
                        'rate_f' => $rate_f,
                        'rate_f_top' => $rate_f_top,
                        'rate_e' => $rate_e,
                    ];

                    $return = $OBJ->update_store_rate($up_data);
                    return $return;
                }


                //京东收银通道修改费率
                if ($ways->status == 1 && 5999 < $ways_type && $ways_type < 6999) {
                    return json_encode(['status' => 2, 'message' => '京东通道开通后不支持软件修改', 'data' => $request->except(['token'])]);
                }
            }


            //全部修改
            $company = $ways->company;
            StorePayWay::where('store_id', $store_id)
                ->where('company', $company)
                ->update([
                    'rate_e' => $rate_e,
                    'rate_f' => $rate_f,
                    'rate_f_top' => $rate_f_top,
                ]);


            return json_encode(['status' => 1, 'message' => '设置成功', 'data' => []]);


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage() . ' - ' . $exception->getLine()]);
        }

    }

    //门店设置费率-银联扫码
    public function edit_store_unqr_rate(Request $request)
    {

        try {
            $user = $this->parseToken();
            $store_id = $request->get('store_id');
            $ways_type = $request->get('ways_type');
            $rate_a = $request->get('rate_a');
            $rate_b = $request->get('rate_b');
            $rate_b_top = $request->get('rate_b_top', '20');
            $rate_c = $request->get('rate_c');
            $rate_d = $request->get('rate_d');
            $rate_d_top = $request->get('rate_d_top', '20');
            $store = Store::where('store_id', $store_id)
                ->first();

            if (!$store) {
                return json_encode(['status' => 2, 'message' => '门店不存在', 'data' => []]);
            }

            //共享通道不支持修改费率
            if ($store->pay_ways_type) {
                return json_encode(['status' => 2, 'message' => '共享通道不支持修改费率']);
            }


            //代理商的费率
            $user_rate = UserRate::where('user_id', $store->user_id)
                ->where('ways_type', $ways_type)
                ->select('rate_a', 'rate_b')
                ->first();


            if (!$user_rate) {
                return json_encode(['status' => 2, 'message' => '代理商未设置费率', 'data' => []]);
            }

            //不能大于代理商的成本
            if ($rate_a < $user_rate->rate_a) {
                return json_encode(['status' => 2, 'message' => '小于1000贷记卡费率不能低于代理商的费率', 'data' => []]);
            }
            //不能大于代理商的成本
            if ($rate_b < $user_rate->rate_b) {
                return json_encode(['status' => 2, 'message' => '小于1000费率不能低于代理商的费率', 'data' => []]);
            }


            //不能大于代理商的成本
            if ($rate_c < $user_rate->rate_c) {
                return json_encode(['status' => 2, 'message' => '大于1000贷记卡费率不能低于代理商的费率', 'data' => []]);
            }
            //不能大于代理商的成本
            if ($rate_d < $user_rate->rate_d) {
                return json_encode(['status' => 2, 'message' => '大于1000借记卡费率不能低于代理商的费率', 'data' => []]);
            }

            $ways = StorePayWay::where('store_id', $store_id)->where('ways_type', $ways_type)
                ->first();

            if (!$ways) {
                return json_encode(['status' => 2, 'message' => '请先设置扫码通道', 'data' => []]);
            } else {
                $status = $ways->status;
                $status_desc = $ways->status_desc;

                //如果通道审核中不允许修改费率
                if ($status == 2) {
                    return json_encode(['status' => 2, 'message' => '通道审核中不允许修改费率', 'data' => []]);
                }

                //新大陆同步平台费率
                if ($ways->status == 1 && 7999 < $ways_type && $ways_type < 8999) {
                    $OBJ = new  UpdateController();
                    //修改费率
                    $up_data = [
                        'store_id' => $store_id,
                        'email' => $store->people_phone . '@139.com',
                        'phone' => $store->people_phone,
                        'rate' => $ways->rate,
                        'rate_a' => $rate_a,
                        'rate_c' => $rate_c,
                        'rate_f' => $ways->rate_f,
                        'rate_f_top' => $ways->rate_f_top,
                        'rate_e' => $ways->rate_e,
                    ];

                    $return = $OBJ->update_store_rate($up_data);
                    return $return;
                }


                //京东收银通道修改费率
                if ($ways->status == 1 && 5999 < $ways_type && $ways_type < 6999) {
                    return json_encode(['status' => 2, 'message' => '京东通道开通后不支持软件修改', 'data' => $request->except(['token'])]);
                }
            }

            $company = $ways->company;

            //全部设置
            $update_data = [
                'rate_a' => $rate_a,
                'rate_b' => $rate_b,
                'rate_b_top' => $rate_b_top,
                'rate_c' => $rate_c,
                'rate_d' => $rate_d,
                'rate_d_top' => $rate_d_top,
            ];
            StorePayWay::where('store_id', $store_id)
                ->where('company', $company)
                ->update($update_data);


            return json_encode(['status' => 1, 'message' => '设置成功', 'data' => []]);


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage() . ' - ' . $exception->getLine()]);
        }

    }

    //pc端开通通道
    public function open_ways_type(Request $request)
    {
        try {
            $user = $this->parseToken();
            $pay_status = $request->get('pay_status', '1');
            $pay_status_desc = $request->get('pay_status_desc', '开通成功');
            $store_id = $request->get('store_id');
            $ways_type = $request->get('ways_type', '');

            //支付宝
            $alipay_store_id = $request->get('alipay_store_id', $store_id);
            $out_store_id = $request->get('out_store_id', $store_id);

            if ($alipay_store_id == "") {
                $alipay_store_id = "";
            }

            if ($out_store_id == "") {
                $out_store_id = "";
            }

            $user = User::where('id', $user->user_id)->first();
            $hasPermission = $user->hasPermissionTo('通道商户号');
            if (!$hasPermission) {
                return json_encode(['status' => 2, 'message' => '没有权限配置商户号']);
            }


            //微信
            $wx_sub_merchant_id = $request->get('wx_sub_merchant_id', '');
            //京东
            $merchant_no = $request->get('merchant_no', '');
            $md_key = $request->get('md_key', '');
            $des_key = $request->get('des_key', '');

            //网商
            $MerchantId = $request->get('MerchantId', '');


            //新大陆
            $nl_key = $request->get('nl_key', '');
            $nl_mercId = $request->get('nl_mercId', '');
            $trmNo = $request->get('trmNo', '');

            //和融通
            $h_mid = $request->get('h_mid', '');


            //联拓富
            $appId = $request->get('appId', '');
            $ltf_md_key = $request->get('md_key', '');
            $merchantCode = $request->get('merchantCode', '');


            $Store = Store::where('store_id', $store_id)
                ->first();

            if (!$Store) {
                return json_encode([
                    'status' => 2,
                    'message' => '门店不存在',
                ]);
            }
            $config_id = $Store->config_id;
            $store_name = $Store->store_name;
            //费率 默认商户的费率为代理商的费率
            $UserRate = UserRate::where('user_id', $Store->user_id)
                ->where('ways_type', $ways_type)//目前是一样的直接读取支付宝就行
                ->first();


            if (!$UserRate) {
                return json_encode([
                    'status' => 2,
                    'message' => '请联系代理商开启此通道',
                ]);
            }

            $rate = $UserRate->store_all_rate;

            //支付宝
            if ($ways_type == '1000') {
                $storeInfo = AlipayAppOauthUsers::where('store_id', $store_id)->first();
                if ($alipay_store_id == "" && $out_store_id) {
                    if ($storeInfo) {
                        return json_encode([
                            'status' => 1,
                            'data' => [
                                'alipay_store_id' => $storeInfo->alipay_store_id,
                                'out_store_id' => $storeInfo->out_store_id,

                            ]
                        ]);
                    } else {
                        return json_encode([
                            'status' => 1,
                            'data' => []
                        ]);
                    }
                } else {


                    if ($storeInfo) {
                        $storeInfo->alipay_store_id = $alipay_store_id;
                        $storeInfo->out_store_id = $out_store_id;
                        $storeInfo->save();

                    } else {
                        //添加
                        $insert_data = [
                            'alipay_user_id' => '',
                            'alipay_store_id' => $alipay_store_id,
                            'out_store_id' => $out_store_id,
                            'merchant_id' => $Store->merchant_id,
                            'alipay_user_account' => '',
                            'alipay_user_account_name' => '',
                            'config_type' => '01',
                            'pid' => $Store->pid,
                            'store_id' => $store_id,
                            'is_delete' => 0,
                            'auth_app_id' => '',
                            'app_auth_token' => '',
                            'app_refresh_token' => '',
                            'expires_in' => '',
                            're_expires_in' => '',
                            'trade_pay_rate' => 0.6,
                        ];

                        AlipayAppOauthUsers::create($insert_data);
                    }


                }


            }
            //微信官方
            if ($ways_type == '2000') {
                $data = WeixinStore::where('store_id', $store_id)
                    ->select('wx_sub_merchant_id')
                    ->first();
                if ($wx_sub_merchant_id == '') {
                    if ($data) {
                        return json_encode([
                            'status' => 1,
                            'data' => $data
                        ]);
                    } else {
                        return json_encode([
                            'status' => 1,
                            'data' => []
                        ]);
                    }
                } else {

                    if ($data) {
                        WeiXinStore::where('store_id', $store_id)
                            ->update([
                                'config_id' => $config_id,
                                'store_id' => $store_id,
                                'status' => 1,
                                'status_desc' => '成功',
                                'wx_sub_merchant_id' => $wx_sub_merchant_id,
                            ]);


                    } else {
                        $gets2 = StorePayWay::where('store_id', $store_id)->where('ways_source', 'weixin')->get();
                        $ways = StorePayWay::where('store_id', $store_id)->where('ways_type', 2000)->first();
                        $count = count($gets2);

                        $rate = 0.6;
                        $store = Store::where('store_id', $store_id)
                            ->select('user_id')
                            ->first();
                        if (!$store) {
                            return json_encode([
                                'status' => 2,
                                'msg' => '门店不存在'
                            ]);
                        }

                        $UserRate = UserRate::where('user_id', $store->user_id)
                            ->where('ways_type', $ways_type)
                            ->first();
                        if ($UserRate) {
                            $rate = $UserRate->rate;
                        }
                        $data1 = [
                            'store_id' => $store_id,
                            'ways_type' => 2000,
                            'ways_source' => 'weixin',
                            'ways_desc' => '微信支付',
                            'sort' => ($count + 1),
                            'status' => 1,
                            'status_desc' => '成功',
                            'rate' => $rate,
                            'settlement_type' => 'T1',
                            'company' => 'weixin',

                        ];
                        if ($ways) {
                            $ways->update($data1);
                            $ways->save();
                        } else {
                            StorePayWay::create($data1);
                        }
                        WeiXinStore::create([
                            'config_id' => $config_id,
                            'store_id' => $store_id,
                            'status' => 1,
                            'status_desc' => '成功',
                            'wx_sub_merchant_id' => $wx_sub_merchant_id,

                        ]);
                    }
                }
            }
            //富友
            if ($ways_type == "11001" || $ways_type == "11002") {
                $FuiouStore = FuiouStore::where('store_id', $store_id)
                    ->first();

                if ($MerchantId == '') {
                    if ($FuiouStore) {
                        return json_encode([
                            'status' => 1,
                            'data' => [
                                'MerchantId' => $FuiouStore->mchnt_cd
                            ]
                        ]);
                    } else {
                        return json_encode([
                            'status' => 1,
                            'data' => []
                        ]);
                    }
                } else {


                    if ($FuiouStore) {
                        FuiouStore::where('store_id', $store_id)
                            ->update([
                                'store_id' => $store_id,
                                'config_id' => $config_id,
                                'mchnt_cd' => $MerchantId,
                            ]);


                    } else {
                        FuiouStore::create([
                            'store_id' => $store_id,
                            'config_id' => $config_id,
                            'mchnt_cd' => $MerchantId,
                        ]);
                    }
                }


                //费率 默认商户的费率为代理商的费率
                $UserRate = UserRate::where('user_id', $Store->user_id)
                    ->where('ways_type', 11001)//目前是一样的直接读取支付宝就行
                    ->first();

                if (!$UserRate) {
                    return json_encode([
                        'status' => 2,
                        'message' => '请联系代理商开启此通道',
                    ]);
                }

                $rate = $UserRate->store_all_rate;
                //读取商户的费率
                $StorePayWay = StorePayWay::where('ways_type', $ways_type)
                    ->where('store_id', $store_id)
                    ->select('rate')
                    ->first();

                if ($StorePayWay) {
                    $rate = $StorePayWay->rate;
                } else {
                    return json_encode([
                        'status' => 2,
                        'message' => '商户通道费率未设置',
                    ]);
                }
                //默认支付通道未注册成功
                $data['store_id'] = $store_id;
                $data['rate'] = $rate;
                $data['status'] = $pay_status;
                $data['status_desc'] = $pay_status_desc;
                $data['company'] = 'fuiou';
                $return = $this->send_ways_data($data);
                return $return;

            }
            //京东
            if (5999 < $ways_type && $ways_type < 6999) {
                $JdStore = JdStore::where('store_id', $store_id)
                    ->first();

                if ($merchant_no == '') {
                    if ($JdStore) {
                        return json_encode([
                            'status' => 1,
                            'data' => $JdStore
                        ]);
                    } else {
                        return json_encode([
                            'status' => 1,
                            'data' => []
                        ]);
                    }
                } else {

                    $config = new JdConfigController();
                    $jd_config = $config->jd_config($config_id);
                    //更新密钥
                    $store_keys = [
                        'request_url' => 'https://psi.jd.com/merchant/status/queryMerchantKeys',
                        'agentNo' => $jd_config->agentNo,
                        'merchantNo' => $merchant_no,
                        'serialNo' => "" . time() . "",
                        'store_md_key' => $jd_config->store_md_key,
                        'store_des_key' => $jd_config->store_des_key,
                    ];

                    $OBJ = new \App\Api\Controllers\Jd\StoreController();
                    $re = $OBJ->store_keys($store_keys);
                    if ($re['code'] == "0000") {
                        $des_key = $re['data']['desKey'];
                        $md_key = $re['data']['mdKey'];
                    }

                    if ($JdStore) {
                        JdStore::where('store_id', $store_id)
                            ->update([
                                'config_id' => $config_id,
                                'store_id' => $store_id,
                                'merchant_no' => $merchant_no,
                                'md_key' => $md_key,
                                'des_key' => $des_key,
                            ]);


                    } else {
                        JdStore::create([
                            'config_id' => $config_id,
                            'store_id' => $store_id,
                            'merchant_no' => $merchant_no,
                            'md_key' => $md_key,
                            'des_key' => $des_key,
                            'store_true' => 1,
                            'pay_true' => 1
                        ]);
                    }
                }


                //费率 默认商户的费率为代理商的费率
                $UserRate = UserRate::where('user_id', $Store->user_id)
                    ->where('ways_type', 6001)//目前是一样的直接读取支付宝就行
                    ->first();

                if (!$UserRate) {
                    return json_encode([
                        'status' => 2,
                        'message' => '请联系代理商开启此通道',
                    ]);
                }

                $rate = $UserRate->store_all_rate;
                //读取商户的费率
                $StorePayWay = StorePayWay::where('ways_type', $ways_type)
                    ->where('store_id', $store_id)
                    ->select('rate')
                    ->first();

                if ($StorePayWay) {
                    $rate = $StorePayWay->rate;
                } else {
                    return json_encode([
                        'status' => 2,
                        'message' => '商户通道费率未设置',
                    ]);
                }
                //默认支付通道未注册成功
                $data['store_id'] = $store_id;
                $data['rate'] = $rate;
                $data['status'] = $pay_status;
                $data['status_desc'] = $pay_status_desc;
                $data['company'] = 'jdjr';
                $return = $this->send_ways_data($data);
                return $return;

            }
            //联拓富
            if (9999 < $ways_type && $ways_type < 19999) {
                //费率 默认商户的费率为代理商的费率
                $UserRate = UserRate::where('user_id', $Store->user_id)
                    ->where('ways_type', 10001)//目前是一样的直接读取支付宝就行
                    ->first();

                if (!$UserRate) {
                    return json_encode([
                        'status' => 2,
                        'message' => '请联系代理商开启此通道',
                    ]);
                }

                $rate = $UserRate->store_all_rate;

                $LtfStore = LtfStore::where('store_id', $store_id)
                    ->first();

                if ($merchantCode == '') {
                    if ($LtfStore) {
                        return json_encode([
                            'status' => 1,
                            'data' => $LtfStore
                        ]);
                    } else {
                        return json_encode([
                            'status' => 1,
                            'data' => []
                        ]);
                    }
                } else {
                    if ($LtfStore) {
                        LtfStore::where('store_id', $store_id)
                            ->update([
                                'config_id' => $config_id,
                                'store_id' => $store_id,
                                'appId' => $appId,
                                'md_key' => $ltf_md_key,
                                'merchantCode' => $merchantCode
                            ]);


                    } else {
                        LtfStore::create([
                            'config_id' => $config_id,
                            'store_id' => $store_id,
                            'appId' => $appId,
                            'md_key' => $ltf_md_key,
                            'merchantCode' => $merchantCode
                        ]);
                    }
                }


                //读取商户的费率
                $StorePayWay = StorePayWay::where('ways_type', $ways_type)
                    ->where('store_id', $store_id)
                    ->select('rate')
                    ->first();

                if ($StorePayWay) {
                    $rate = $StorePayWay->rate;
                }
                //默认支付通道未注册成功
                $data['store_id'] = $store_id;
                $data['rate'] = $rate;
                $data['status'] = $pay_status;
                $data['status_desc'] = $pay_status_desc;
                $data['company'] = 'ltf';
                $return = $this->send_ways_data($data);
                return $return;

            }
            //网商银行
            if (2999 < $ways_type && $ways_type < 3999) {
                $MyBankStore = MyBankStore::where('OutMerchantId', $store_id)->select('MerchantId')->first();
                if ($MerchantId == '') {
                    if ($MyBankStore) {
                        return json_encode([
                            'status' => 1,
                            'data' => $MyBankStore
                        ]);
                    } else {
                        return json_encode([
                            'status' => 1,
                            'data' => []
                        ]);
                    }
                }


                //费率 默认商户的费率为代理商的费率
                $UserRate = UserRate::where('user_id', $Store->user_id)
                    ->where('company', 'mybank')//目前是一样的直接读取支付宝就行
                    ->first();

                if (!$UserRate) {
                    return json_encode([
                        'status' => 2,
                        'message' => '请联系代理商开启此通道',
                    ]);
                }

                $rate = $UserRate->store_all_rate;
                //读取商户的费率
                $StorePayWay = StorePayWay::where('ways_type', $ways_type)
                    ->where('store_id', $store_id)
                    ->select('rate')
                    ->first();

                if ($StorePayWay) {
                    $rate = $StorePayWay->rate;
                } else {
                    return json_encode([
                        'status' => 2,
                        'message' => '商户通道费率未设置',
                    ]);
                }

                //查询商户资料
                //商户信息查询接口
                $aop = new \App\Api\Controllers\MyBank\BaseController();
                $ao = $aop->aop();
                $ao->url = env("MY_BANK_request2");
                $ao->Function = "ant.mybank.merchantprod.merchant.query";

                $data = [
                    'MerchantId' => $MerchantId,
                ];

                $re = $ao->Request($data);
                if ($re['status'] == 0) {
                    return json_encode([
                        'status' => 2,
                        'message' => $re['message'],
                    ]);
                }
                $body = $re['data']['document']['response']['body'];

                //这个地方代表需要排查
                if ($body['RespInfo']['ResultStatus'] != 'S') {
                    return [
                        'status' => 2,
                        'message' => $body['RespInfo']['ResultMsg']
                    ];
                }
                $FeeParamList = base64_decode($re['data']['document']['response']['body']['FeeParamList']);

                $FeeParamList = json_decode($FeeParamList, true);
                $FeeValue = "";
                foreach ($FeeParamList as $k => $v) {
                    if ($v['FeeType'] == "02") {
                        $FeeValue = $v['FeeValue'];
                        break;
                    } else {
                        continue;
                    }
                }
                if ($rate / 100 != $FeeValue) {
                    return json_encode([
                        'status' => 2,
                        'message' => '请将通道费率设置为' . $FeeValue * 100,
                    ]);
                }

                $in = [
                    'status' => '1',
                    'OrderNo' => '',
                    'config_id' => $config_id,
                    'smid' => "",
                    'RateVersion' => '',
                    'OutMerchantId' => $store_id,
                    'MerchantId' => $MerchantId,
                    'MerchantName' => $store_name,
                    'MerchantType' => '',
                    'DealType' => '',
                    'SupportPrepayment' => '',
                    'SettleMode' => '',
                    'Mcc' => '',
                    'MerchantDetail' => '',
                    'TradeTypeList' => '',
                    'PrincipalCertType' => '',
                    'PayChannelList' => '',
                    'DeniedPayToolList' => '',
                    'FeeParamList' => '',
                    'BankCardParam' => '',
                    'SupportStage' => '',
                    'PartnerType' => '',
                    'wx_Path' => url('/api/mybank/weixin/'),
                    'wx_AppId' => '',
                    'wx_Secret' => '',
                    'wx_SubscribeAppId' => '',
                    'is_yulibao' => '',
                ];

                if ($MyBankStore) {
                    MyBankStore::where('OutMerchantId', $store_id)
                        ->update($in);
                } else {

                    //微信子商户支付配置接口
                    try {
                        $ao->url = env("MY_BANK_request2");
                        $ao->Function = "ant.mybank.merchantprod.merchant.addMerchantConfig";
                        $data = [
                            'MerchantId' => $MerchantId,
                            'Path' => env('APP_URL') . '/api/mybank/weixin/',
                            'OutTradeNo' => date('YmdHis') . time() . rand(10000, 99999),
                        ];
                        $re = $ao->Request($data);

                    } catch (\Exception $exception) {

                    }

                    MyBankStore::create($in);
                }

                //默认支付通道未注册成功
                $data['store_id'] = $store_id;
                $data['rate'] = $rate;
                $data['status'] = $pay_status;
                $data['status_desc'] = $pay_status_desc;
                $data['company'] = 'mybank';
                $return = $this->send_ways_data($data);
                return $return;

            }

            //新大陆
            if (7999 < $ways_type && $ways_type < 8999) {

                $NewLandStore = NewLandStore::where('store_id', $store_id)
                    //->where('nl_mercId', $nl_mercId)
                    ->first();

                if ($nl_mercId == '') {
                    if ($NewLandStore) {
                        return json_encode([
                            'status' => 1,
                            'data' => $NewLandStore
                        ]);
                    } else {
                        return json_encode([
                            'status' => 1,
                            'data' => []
                        ]);
                    }
                } else {
                    //修改先查询信息
                    $config_obj = new NewLandConfigController();
                    $config = $config_obj->new_land_config($config_id);
                    $aop = new \App\Common\XingPOS\Aop();
                    $aop->key = $config->nl_key;
                    $aop->version = 'V1.0.1';
                    $aop->org_no = $config->org_no;
                    $aop->url = 'https://gateway.starpos.com.cn/emercapp';//测试地址

                    $sign_data = [
                        'mercId' => $nl_mercId,
                    ];

                    $request_obj = new  \App\Common\XingPOS\Request\XingStoreShangHuChaXun();
                    $request_obj->setBizContent($sign_data);
                    $return = $aop->executeStore($request_obj);
                    //不成功
                    if ($return['msg_cd'] != '000000') {
                        return json_encode([
                            'status' => 2,
                            'message' => $return['msg_dat'],
                        ]);
                    }

                    //直接通过
                    if ($return['check_flag'] == 1) {
                        $pay_status = 1;
                        $pay_status_desc = '开通成功';
                        $nl_key = $return['key'];
                        // $trmNo = $return['REC'][0]['trmNo'];
                        $nl_stoe_id = $return['REC'][0]['stoe_id'];
                    } else {
                        return json_encode([
                            'status' => 2,
                            'message' => '商户未开通成功',
                        ]);
                    }


                    if ($NewLandStore) {
                        NewLandStore::where('store_id', $store_id)
                            ->where('nl_mercId', $nl_mercId)
                            ->update([
                                'config_id' => $config_id,
                                'store_id' => $store_id,
                                'nl_key' => $nl_key,
                                'trmNo' => $trmNo,
                                'nl_mercId' => $nl_mercId,
                                'nl_stoe_id' => $nl_stoe_id,
                                'jj_status' => 1,
                                'img_status' => 1,
                                'tj_status' => 1,
                                'check_flag' => 1,
                                'check_qm' => 1,
                            ]);


                    } else {
                        NewLandStore::create([
                            'config_id' => $config_id,
                            'store_id' => $store_id,
                            'store_name' => $store_name,
                            'nl_key' => $nl_key,
                            'trmNo' => $trmNo,
                            'nl_mercId' => $nl_mercId,
                            'nl_stoe_id' => $nl_stoe_id,
                            'jj_status' => 1,
                            'img_status' => 1,
                            'tj_status' => 1,
                            'check_flag' => 1,
                            'check_qm' => 1,
                        ]);
                    }
                }


                //费率 默认商户的费率为代理商的费率
                $UserRate = UserRate::where('user_id', $Store->user_id)
                    ->where('ways_type', 8001)//目前是一样的直接读取支付宝就行
                    ->first();

                if (!$UserRate) {
                    return json_encode([
                        'status' => 2,
                        'message' => '请联系代理商开启此通道',
                    ]);
                }

                $rate = $UserRate->store_all_rate;
                //读取商户的费率
                $StorePayWay = StorePayWay::where('ways_type', $ways_type)
                    ->where('store_id', $store_id)
                    ->select('rate')
                    ->first();

                if ($StorePayWay) {
                    $rate = $StorePayWay->rate;
                } else {
                    return json_encode([
                        'status' => 2,
                        'message' => '商户通道费率未设置',
                    ]);
                }
                //默认支付通道未注册成功
                $data['store_id'] = $store_id;
                $data['rate'] = $rate;
                $data['status'] = $pay_status;
                $data['status_desc'] = $pay_status_desc;
                $data['company'] = 'newland';
                $return = $this->send_ways_data($data);
                return $return;

            }

            //和融通
            if (8999 < $ways_type && $ways_type < 9999) {
                $JdStore = HStore::where('store_id', $store_id)
                    ->first();

                if ($h_mid == '') {
                    if ($JdStore) {
                        return json_encode([
                            'status' => 1,
                            'data' => $JdStore
                        ]);
                    } else {
                        return json_encode([
                            'status' => 1,
                            'data' => []
                        ]);
                    }
                } else {
                    if ($JdStore) {
                        HStore::where('store_id', $store_id)
                            ->update([
                                'config_id' => $config_id,
                                'store_id' => $store_id,
                                'h_mid' => $h_mid,
                                'h_status' => $pay_status,
                                'h_status_desc' => $pay_status_desc,
                            ]);


                    } else {
                        HStore::create([
                            'config_id' => $config_id,
                            'store_id' => $store_id,
                            'h_mid' => $h_mid,
                            'h_status' => $pay_status,
                            'h_status_desc' => $pay_status_desc,
                        ]);
                    }
                }


                //费率 默认商户的费率为代理商的费率
                $UserRate = UserRate::where('user_id', $Store->user_id)
                    ->where('ways_type', 9001)//目前是一样的直接读取支付宝就行
                    ->first();

                if (!$UserRate) {
                    return json_encode([
                        'status' => 2,
                        'message' => '请联系代理商开启此通道',
                    ]);
                }

                $rate = $UserRate->store_all_rate;
                //读取商户的费率
                $StorePayWay = StorePayWay::where('ways_type', $ways_type)
                    ->where('store_id', $store_id)
                    ->select('rate')
                    ->first();

                if ($StorePayWay) {
                    $rate = $StorePayWay->rate;
                } else {
                    return json_encode([
                        'status' => 2,
                        'message' => '商户通道费率未设置',
                    ]);
                }
                //默认支付通道未注册成功
                $data['store_id'] = $store_id;
                $data['rate'] = $rate;
                $data['status'] = $pay_status;
                $data['status_desc'] = $pay_status_desc;
                $data['company'] = 'herongtong';
                $return = $this->send_ways_data($data);
                return $return;

            }


            return json_encode([
                'status' => 1,
                'message' => '保存成功'
            ]);


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage() . $exception->getLine()]);
        }
    }

    //修改费率
    public function send_ways_data($data)
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
                                'rate' => $data['rate'],
                                'status_desc' => $data['status_desc']
                            ]);
                        $ways->save();
                    } else {
                        StorePayWay::create($data);
                    }
                    DB::commit();
                } catch (\Exception $e) {
                    dd($e);
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
                'message' => '修改成功',
            ];

        } catch (\Exception $e) {
            dd($e);
            Log::info('入库通道');
            Log::info($e);
            return [
                'status' => 2,
                'message' => '通道入库更新失败',
            ];
        }
    }


    /**添加分店
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function add_sub_store(Request $request)
    {
        try {
            $user = $this->parseToken();
            $store_name = $request->get('store_name', '');
            $province_code = $request->get('province_code', '');
            $city_code = $request->get('city_code', '');
            $area_code = $request->get('area_code', '');
            $store_address = $request->get('store_address', '');
            $pid = $request->get('pid', '');


            if ($store_name == "") {
                $this->status = 2;
                $this->message = '店铺名称必须填写';
                return $this->format();
            }

            if ($area_code == "") {
                $this->status = 2;
                $this->message = '地区编号必须填写';
                return $this->format();
            }
            $pid_store = Store::where('id', $pid)->first();
            if (!$pid_store) {
                return json_encode([
                    'status' => 2,
                    'message' => '总门店不存在'
                ]);
            }

            if ($pid_store->pid) {
                return json_encode([
                    'status' => 2,
                    'message' => '分店暂不允许再创建分店'
                ]);
            }


            $store_id = date('YmdHis', time()) . rand(10000, 99999);
            $data = [
                'store_id' => $store_id,
                'config_id' => $pid_store->config_id,
                'merchant_id' => $pid_store->merchant_id,
                'pid' => $pid_store->id,
                'store_name' => $store_name,
                'store_short_name' => $store_name,
                'province_code' => $province_code,
                'city_code' => $city_code,
                'area_code' => $area_code,
                'province_name' => $this->city_name($province_code),
                'city_name' => $this->city_name($city_code),
                'area_name' => $this->city_name($area_code),
                'store_address' => $store_address,
                'user_id' => $pid_store->user_id,
                'user_pid' => $pid_store->user_pid,
                'store_type' => $pid_store->store_type,
                'store_type_name' => $pid_store->store_type_name,
                'category_id' => $pid_store->category_id,
                'category_name' => $pid_store->category_name,
            ];


            //开启事务
            try {
                DB::beginTransaction();

                Store::create($data);
                MerchantStore::create([
                    'store_id' => $store_id,
                    'merchant_id' => $pid_store->merchant_id
                ]);


                StoreBank::create([
                    'store_id' => $store_id,
                ]);
                StoreImg::create([
                    'store_id' => $store_id,
                ]);

                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
            }

            $this->status = 1;
            $this->message = '分店添加成功';
            $data = [
                'store_id' => $store_id,
            ];
            return $this->format($data);


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }


    }

    public function store_pay_qr(Request $request)
    {

        try {
            $user = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $ways_type = $request->get('ways_type', '');

            $this->status = 1;
            $this->message = '数据返回成功';
            $data = [
                'store_pay_qr' => url('/qr?store_id=' . $store_id),
                'store_id' => $store_id,
                'ways_type' => $ways_type
            ];
            return $this->format($data);


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }

    }


    /*支付宝授权
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|string
     */
    public function alipay_auth(Request $request)
    {
        try {
            $user = $this->parseToken();
            $store_id = $request->get('store_id');
            $store = Store::where('store_id', $store_id)->first();
            if (!$store) {
                return [
                    'status' => 2,
                    'message' => '门店不存在',
                ];
            }

            $data = [
                'redirect_url' => 'alipays://platformapi/startapp?appId=20000067&url=' . url('/merchant/appAlipay?store_id=' . $store_id . '&merchant_id=' . $store->merchant_id . "&config_id=" . $store->config_id),
                'qr_url' => url('/merchant/appAlipay?store_id=' . $store_id . '&merchant_id=' . $store->merchant_id . "&config_id=" . $store->config_id)
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

    //审核门店
    public function check_store(Request $request)
    {

        try {
            $user = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $status = $request->get('status', '1');
            $status_desc = $request->get('status_desc', '');

            $Store = Store::where('store_id', $store_id)->first();
            if (!$Store) {
                return json_encode([
                    'status' => 2,
                    'message' => '门店不存在'
                ]);
            }
            if ($status == '1') {
                $status_desc = "审核成功";
            }
            $Store->status = $status;
            $Store->status_desc = $status_desc;
            if ($user->level == 0) {
                $Store->admin_status = $status;
                $Store->admin_status_desc = $status_desc;
            }

            $Store->save();

            return json_encode([
                'status' => 1,
                'message' => '门店状态更改成功',
                'data' => $request->except(['token'])
            ]);
        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage() . $exception->getFile()
            ]);
        }

    }


    //删除门店
    public function del_store(Request $request)
    {
        try {
            $user = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $store_ids = explode(',', $store_id);

            //目前只有平台可以删除
            if ($user->level > 1) {
                return json_encode([
                    'status' => 2,
                    'message' => '暂时不支持删除门店'
                ]);
            }


            foreach ($store_ids as $k => $v) {
                $Store = Store::where('store_id', $v)->first();
                //门店不存在
                if (!$Store) {
                    continue;
                }
                //有分店不允许删除
                $sub_Store = Store::where('id', $Store->pid)->first();
                if ($sub_Store) {
                    continue;
                }
                //不是自己或者不是下级的商户
                $users = $this->getSubIds($user->user_id);//获取所有下级
                if (!in_array($Store->user_id, $users)) {
                    continue;
                }

                //彻底删除门店
                //开启事务
                try {
                    DB::beginTransaction();
                    Store::where('store_id', $v)->update([
                        'is_delete' => 1,
                    ]);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    continue;
                }

            }
            return json_encode([
                'status' => 1,
                'message' => '门店删除成功',
                'data' => [
                    'store_id' => $store_id,
                ]
            ]);
        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage() . $exception->getFile()
            ]);
        }

    }

    //恢复门店
    public function rec_store(Request $request)
    {
        try {
            $user = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $store_ids = explode(',', $store_id);

            //目前只有平台可以删除
            if ($user->level > 1) {
                return json_encode([
                    'status' => 2,
                    'message' => '暂时不支持恢复门店'
                ]);
            }


            foreach ($store_ids as $k => $v) {
                $Store = Store::where('store_id', $v)->first();
                //门店不存在
                if (!$Store) {
                    continue;
                }
                //有分店不允许删除
                $sub_Store = Store::where('id', $Store->pid)->first();
                if ($sub_Store) {
                    continue;
                }
                //不是自己或者不是下级的商户
                $users = $this->getSubIds($user->user_id);//获取所有下级
                if (!in_array($Store->user_id, $users)) {
                    continue;
                }

                //彻底删除门店
                //开启事务
                try {
                    DB::beginTransaction();
                    Store::where('store_id', $v)->update([
                        'is_delete' => 0,
                    ]);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    continue;
                }

            }
            return json_encode([
                'status' => 1,
                'message' => '门店恢复成功',
                'data' => [
                    'store_id' => $store_id,
                ]
            ]);
        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage() . $exception->getFile()
            ]);
        }

    }


    //直接清除门店
    public function clear_store(Request $request)
    {
        try {
            $user = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $store_ids = explode(',', $store_id);

            //目前只有平台可以删除
            if ($user->level > 0) {
                return json_encode([
                    'status' => 2,
                    'message' => '暂时不支持清理门店'
                ]);
            }


            foreach ($store_ids as $k => $v) {
                $Store = Store::where('store_id', $v)->first();
                //门店不存在
                if (!$Store) {
                    continue;
                }
                //有分店不允许删除
                $sub_Store = Store::where('id', $Store->pid)->first();
                if ($sub_Store) {
                    continue;
                }
                //不是自己或者不是下级的商户
                $users = $this->getSubIds($user->user_id);//获取所有下级
                if (!in_array($Store->user_id, $users)) {
                    continue;
                }

                //彻底删除门店
                //开启事务
                try {
                    DB::beginTransaction();

                    Store::where('store_id', $v)->delete();
                    StorePayWay::where('store_id', $v)->delete();
                    MerchantStore::where('store_id', $v)->delete();
                    StoreImg::where('store_id', $v)->delete();
                    StoreBank::where('store_id', $v)->delete();

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    continue;
                }

            }
            return json_encode([
                'status' => 1,
                'message' => '门店删除成功',
                'data' => [
                    'store_id' => $store_id,
                ]
            ]);
        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage() . $exception->getFile()
            ]);
        }

    }


    //门店关闭接口
    public function col_store(Request $request)
    {
        try {
            $user = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $store_ids = explode(',', $store_id);

            foreach ($store_ids as $k => $v) {
                $Store = Store::where('store_id', $v)->first();
                //门店不存在
                if (!$Store) {
                    continue;
                }
                $Store->is_close = 1;
                $Store->save();

            }
            return json_encode([
                'status' => 1,
                'message' => '门店关闭成功',
                'data' => [
                    'store_id' => $store_id,
                ]
            ]);
        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage() . $exception->getFile()
            ]);
        }

    }

    //门店开启接口
    public function ope_store(Request $request)
    {
        try {
            $user = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $store_ids = explode(',', $store_id);

            //目前只有平台可以删除
            if ($user->level > 0) {
                return json_encode([
                    'status' => 2,
                    'message' => '暂时不支持开启门店'
                ]);
            }

            foreach ($store_ids as $k => $v) {
                $Store = Store::where('store_id', $v)->first();
                //门店不存在
                if (!$Store) {
                    continue;
                }
                $Store->is_close = 0;
                $Store->save();

            }
            return json_encode([
                'status' => 1,
                'message' => '门店开启成功',
                'data' => [
                    'store_id' => $store_id,
                ]
            ]);
        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage() . $exception->getFile()
            ]);
        }

    }


    public function update_user(Request $request)
    {

        try {
            $public = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $user_id = $request->get('user_id', '');

            $store_ids = explode(',', $store_id);
            $user = User::where('id', $user_id)->first();//要转出的人
            if (!$user) {
                return json_encode([
                    'status' => 2,
                    'message' => '用户不存在'
                ]);
            }
            $config_id = $user->config_id;
            foreach ($store_ids as $k => $v) {
                $Store = Store::where('store_id', $v)
                    ->select('user_id', 'config_id', 'id')
                    ->first();

                //门店不存在
                if (!$Store) {
                    return json_encode([
                        'status' => 2,
                        'message' => '门店不存在'
                    ]);
                }

                if ($config_id != $Store->config_id) {
                    return json_encode([
                        'status' => 2,
                        'message' => '门店不支持转移到其他服务商下面-config_id'
                    ]);
                }


                //开启事务
                try {
                    DB::beginTransaction();
                    //分店也转移
                    Store::where('store_id', $v)->update(['user_id' => $user_id]);
                    //分店也转移
                    Store::where('pid', $Store->id)->update(['user_id' => $user_id]);

                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    continue;
                }

            }


            return json_encode([
                'status' => 1,
                'message' => '门店转移成功',
                'data' => [
                    'store_id' => $store_id,
                ]
            ]);
        } catch (\Exception $exception) {
            return json_encode([
                'status' => -1,
                'message' => $exception->getMessage() . $exception->getFile()
            ]);
        }

    }


    public function time($time)
    {
        try {

            //去除中文
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