<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/7/23
 * Time: 下午3:59
 */

namespace App\Api\Controllers\Basequery;


use App\Api\Controllers\BaseController;
use App\Api\Controllers\Merchant\OrderController;
use App\Common\PaySuccessAction;
use App\Models\AlipayIsvConfig;
use App\Models\AppConfigMsg;
use App\Models\AppOem;
use App\Models\AppUpdate;
use App\Models\Bank;
use App\Models\JpushConfig;
use App\Models\Merchant;
use App\Models\MerchantStore;
use App\Models\MyBankCategory;
use App\Models\MyBankStore;
use App\Models\Order;
use App\Models\SmsConfig;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SelectController extends BaseController
{


    /**收银员列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function merchant_lists(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $store_id = $request->get('store_id', '');

            $check_data = [
                'store_id' => '门店id',
            ];
            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }
            $obj = DB::table('merchant_stores');
            $obj->join('merchants', 'merchant_stores.merchant_id', '=', 'merchants.id')
                ->where('merchant_stores.store_id', $store_id)
                ->select('merchants.id as merchant_id', 'merchants.logo', 'merchants.name', 'merchants.phone', 'merchants.type')
                ->get();

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
            ], [
                'store_type' => 4,
                'store_type_desc' => '学校-教育行业',
            ],
        ];
        $this->status = 1;
        $this->message = '返回数据成功';
        return $this->format($data);

    }


    /**支付宝isv信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function alipay_isv_info(Request $request)
    {

        $store_id = $request->store_id;
        $config_id = '1234';
        $store = Store::where('store_id', $store_id)->select('config_id')->first();
        if ($store) {
            $config_id = $store->config_id;
        }
        $data = AlipayIsvConfig::where('config_id', $config_id)
            ->select('isv_name', 'isv_phone')
            ->first();
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


    public function bank(Request $request)
    {

        try {
            $keyword = $request->get('bankname');
            $where = [];
            if ($keyword) {
                $where[] = ['bankname', 'like', '%' . $keyword . '%'];
            }
            $data = Bank::select('bankname')
                ->where($where)
                ->get();
            return json_encode(['status' => 1, 'data' => $data]);

        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }


    }

    //查询银行信息
    public function sub_bank(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $bank_name = $request->get('bank_name', '');
            $sub_bank_keyword = $request->get('sub_bank_keyword', '');
            $bank_area_name = $request->get('bank_area_name', '');
            $bank_city_name = $request->get('bank_city_name', '');
            $bank_province_name = $request->get('bank_province_name', '');


            $bank_province_name = str_replace('省', '', $bank_province_name);
            $bank_city_name = str_replace('市', '', $bank_city_name);
            $bank_area_name = str_replace('区', '', $bank_area_name);
            $bank_area_name = str_replace('县','',$bank_area_name);

            $obj = DB::table('bank_info');


            if ($sub_bank_keyword) {
                $obj = $obj
                    ->where('bankName', 'like', '%' . $bank_name . '%' . '%' . $sub_bank_keyword . '%')
                    ->select('instOutCode  as bank_no', 'bankName as sub_bank_name');
            } else {
                $obj = $obj
                    ->orWhere('bankName', 'like', $bank_name . $bank_province_name . '%')
                    ->orWhere('bankName', 'like', $bank_name . $bank_city_name . '%')
                    ->orWhere('bankName', 'like', $bank_name . $bank_area_name . '%')
                    ->orWhere('bankName', 'like', $bank_province_name . $bank_name . '%')
                    ->orWhere('bankName', 'like', $bank_city_name . $bank_name . '%')
                    ->orWhere('bankName', 'like', $bank_area_name . $bank_name . '%')
                    ->orWhere('bankName', 'like', '%' . $bank_name . $bank_province_name . '%')
                    ->orWhere('bankName', 'like', '%' . $bank_name . $bank_city_name . '%')
                    ->orWhere('bankName', 'like', '%' . $bank_name . $bank_area_name . '%')
                    ->orWhere('bankName', 'like', '%' . $bank_province_name . $bank_name . '%')
                    ->orWhere('bankName', 'like', '%' . $bank_city_name . $bank_name . '%')
                    ->orWhere('bankName', 'like', '%' . $bank_area_name . $bank_name . '%')
                    ->orWhere('bankName', 'like', '%' . $bank_name . '%' . $bank_province_name . '%')
                    ->orWhere('bankName', 'like', '%' . $bank_name . '%' . $bank_city_name . '%')
                    ->orWhere('bankName', 'like', '%' . $bank_name . '%' . $bank_area_name . '%')
                    ->orWhere('bankName', 'like', '%' . $bank_province_name . '%' . $bank_name . '%')
                    ->orWhere('bankName', 'like', '%' . $bank_city_name . '%' . $bank_name . '%')
                    ->orWhere('bankName', 'like', '%' . $bank_area_name . '%' . $bank_name . '%')
                    ->select('instOutCode as bank_no', 'bankName as sub_bank_name');


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

    //订单汇总查询
    public function order_query_a(Request $request)
    {

        $public = $this->parseToken();
        $store_id = $request->get('store_id', '');
        $ways_source = $request->get('ways_source', '');
        $ways_type = $request->get('ways_type', '');
        $time_start = $request->get('time_start', '');
        $time_end = $request->get('time_end', '');

        $return_type = $request->get('return_type', '');


    }

    //订单汇总查询
    public function order_query_b(Request $request)
    {

        $public = $this->parseToken();
        $store_id = $request->get('store_id', '');
        $ways_source = $request->get('merchant_id', '');
        $ways_type = $request->get('ways_type', '');
        $time_start = $request->get('time_start', '');
        $time_end = $request->get('time_end', '');

        $total_amount = 12.00;
        $get_amount = 10.00;
        $refund_amount = 11.00;
        $receipt_amount = 120.00;
        $fee_amount = 142.00;
        $mdiscount_amount = 129.90;
        $total_count = 160;
        $refund_count = rand(1, 20);

        $data = [
            [
                'type' => 'all',
                'desc' => '总订单统计',
                'total_amount' => number_format($total_amount, 2, '.', ''),
                'get_amount' => number_format($get_amount, 2, '.', ''),
                'refund_amount' => number_format($refund_amount, 2, '.', ''),
                'receipt_amount' => number_format($receipt_amount, 2, '.', ''),
                'fee_amount' => number_format($fee_amount, 2, '.', ''),
                'mdiscount_amount' => number_format($mdiscount_amount, 2, '.', ''),
                'total_count' => '' . $total_count . '',
                'refund_count' => '' . $refund_count . '',
            ],
            [
                'type' => 'alipay',
                'desc' => '支付宝订单统计',
                'total_amount' => number_format($total_amount, 2, '.', ''),
                'get_amount' => number_format($get_amount, 2, '.', ''),
                'refund_amount' => number_format($refund_amount, 2, '.', ''),
                'receipt_amount' => number_format($receipt_amount, 2, '.', ''),
                'fee_amount' => number_format($fee_amount, 2, '.', ''),
                'mdiscount_amount' => number_format($mdiscount_amount, 2, '.', ''),
                'total_count' => '' . $total_count . '',
                'refund_count' => '' . $refund_count . '',


            ], [
                'type' => 'weixin',
                'desc' => '微信订单统计',
                'total_amount' => number_format($total_amount, 2, '.', ''),
                'get_amount' => number_format($get_amount, 2, '.', ''),
                'refund_amount' => number_format($refund_amount, 2, '.', ''),
                'receipt_amount' => number_format($receipt_amount, 2, '.', ''),
                'fee_amount' => number_format($fee_amount, 2, '.', ''),
                'mdiscount_amount' => number_format($mdiscount_amount, 2, '.', ''),
                'total_count' => '' . $total_count . '',
                'refund_count' => '' . $refund_count . '',


            ],

        ];
        $this->status = 1;
        $this->message = '数据返回成功';
        return $this->format($data);

    }


    //删除这个手机号注册账号和门店信息

    public function del_store(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $code = $request->get('code');
            $phone = $request->get('phone');


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }

    }


    //更新app
    public function appUpdate(Request $request)
    {
        $type = $request->get('type', '');
        $app_id = $request->get('app_id');
        $action = $request->get('action', '1');
        $version = $request->get('version', '');

        $where = [];
        $check_data = [
            'app_id' => '包名ID',
            'type' => '类型',
            'version' => '当前版本号',
        ];
        $check = $this->check_required($request->except(['token']), $check_data);
        if ($check) {
            return json_encode([
                'status' => 2,
                'message' => $check
            ]);
        }


        //
        if ($type) {
            $where[] = ['type', '=', $type];
        }

        if ($app_id) {
            $where[] = ['app_id', '=', $app_id];
        }

        if ($version) {
            $where[] = ['version', '>', $version];
        }

        $app = AppUpdate::where($where)->first();
        $update_info = AppUpdate::where($where)->select('msg')->get();
        if ($app) {
            return json_encode([
                'status' => 1,
                'data' => [
                    'version' => $app->version,
                    'app_url' => $app->UpdateUrl,
                    'update_info' => $update_info,
                ],]);
        } else {
            return json_encode([
                'status' => 2,
                'message' => '没有记录'
            ]);
        }
    }


    //结算方式查询
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


    //app oem平台信息
    public function app_oem_info(Request $request)
    {
        try {
            $public = $this->parseToken();
            $config_id = $public->config_id;
            if ($public->level > 1) {
                return json_encode([
                    'status' => 2,
                    'message' => '你没有权限设置'
                ]);
            }
            $app_id = $request->get('app_id', '');
            $app_oem = AppOem::where('config_id', $config_id)->first();
            $beianhao = $request->get('beianhao', '');
            $phone = $request->get('phone', '');
            $merchant_app_url = $request->get('merchant_app_url', '');
            $name = $request->get('name', '');
            if ($app_id == "") {
                $data = [
                    'status' => 1,
                    'data' => $app_oem,
                ];
                return json_encode($data);
            } else {
                //更新
                $data = [
                    'config_id' => $config_id,
                    'app_id' => $app_id,
                    'name' => $name,
                    'ym' => url('/'),
                    'beianhao' => $beianhao,
                    'phone' => $phone,
                    'merchant_app_url' => $merchant_app_url,
                    'keyword' => $name,
                    'title' => $name,
                    'body' => $name,

                ];

                if ($app_oem) {
                    $app_oem->update($data);
                    $app_oem->save();
                } else {
                    AppOem::create($data);
                }
                $AppConfigMsg = AppConfigMsg::where('config_id', $config_id)
                    ->first();
                $app_config = [
                    'config_id' => $config_id,
                    'app_id' => $app_id,
                    'ym' => url('/'),
                    'app_name' => $name,
                    'app_icon' => $beianhao,
                    'app_phone' => $phone,
                    'company' => $name,
                    'about_title' => $name,
                    'about_body' => $name,
                ];
                if ($AppConfigMsg) {
                    $AppConfigMsg->update($app_config);
                    $AppConfigMsg->save();
                } else {
                    AppConfigMsg::create($app_config);
                }

                return json_encode([
                    'status' => 1,
                    'message' => '保存成功'
                ]);

            }

        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }


    }


    //app 极光配置
    public function j_push_info(Request $request)
    {
        try {
            $public = $this->parseToken();
            $config_id = $public->config_id;
            if ($public->level > 1) {
                return json_encode([
                    'status' => 2,
                    'message' => '你没有权限设置'
                ]);
            }
            $DevKey = $request->get('DevKey', '');
            $API_DevSecret = $request->get('API_DevSecret', '');
            $JpushConfig = JpushConfig::where('config_id', $config_id)->first();
            if ($DevKey == "") {
                $data = [
                    'status' => 1,
                    'data' => $JpushConfig,
                ];
                return json_encode($data);
            } else {
                $data = [
                    'config_id' => $config_id,
                    'DevKey' => $DevKey,
                    'API_DevSecret' => $API_DevSecret,

                ];
                //更新
                if ($JpushConfig) {
                    $JpushConfig->update($data);
                    $JpushConfig->save();
                } else {
                    JpushConfig::create($data);
                }


                return json_encode([
                    'status' => 1,
                    'message' => '保存成功'
                ]);

            }

        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }


    }


    //app 短信信息配置
    public function sms_type(Request $request)
    {
        try {
            $public = $this->parseToken();
            $config_id = $public->config_id;
            if ($public->level > 1) {
                return json_encode([
                    'status' => 2,
                    'message' => '你没有权限设置'
                ]);
            }
            $SmsConfig = SmsConfig::where('config_id', '1234')
                ->select('type', 'type_desc')
                ->get();

            return json_encode([
                'status' => 1,
                'message' => '数据返回成功',
                'data' => $SmsConfig
            ]);


        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }


    }

    //app 短信信息配置
    public function sms_info(Request $request)
    {
        try {
            $public = $this->parseToken();
            $config_id = $public->config_id;
            if ($public->level > 1) {
                return json_encode([
                    'status' => 2,
                    'message' => '你没有权限设置'
                ]);
            }
            $app_key = $request->get('app_key', '');
            $app_secret = $request->get('app_secret', '');
            $type = $request->get('type', '');
            $TemplateCode = $request->get('TemplateCode', '');
            $SignName = $request->get('SignName', '');
            $SmsConfig = SmsConfig::where('config_id', $config_id)
                ->where('type', $type)
                ->first();

            if ($app_key == "") {
                $data = [
                    'status' => 1,
                    'data' => $SmsConfig,
                ];
                return json_encode($data);
            } else {
                $SmsConfig_pub = SmsConfig::where('config_id', '1234')
                    ->where('type', $type)
                    ->select('type_desc')
                    ->first();
                $data = [
                    'config_id' => $config_id,
                    'app_key' => $app_key,
                    'app_secret' => $app_secret,
                    'SignName' => $SignName,
                    'TemplateCode' => $TemplateCode,
                    'type' => $type,
                    'type_desc' => $SmsConfig_pub->type_desc,
                ];

                //更新
                if ($SmsConfig) {
                    $SmsConfig->update($data);
                    $SmsConfig->save();
                } else {
                    SmsConfig::create($data);
                }


                return json_encode([
                    'status' => 1,
                    'message' => '保存成功'
                ]);

            }

        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }


    }


    //同步订单状态状态
    public function update_order(Request $request)
    {
        try {
            $public = $this->parseToken();
            $store_id = $request->get('store_id');
            $out_trade_no = $request->get('out_trade_no');
            $order = Order::where('out_trade_no', $out_trade_no)
                ->where('store_id', $store_id)
                ->first();

            if (!$order) {
                return json_encode(['status' => 2, 'message' => "订单号不存在"]);
            }
            if ($order->pay_status == 6) {
                return json_encode(['status' => 2, 'message' => "退款订单无法同步状态"]);
            }

            $data = [
                'out_trade_no' => $order->out_trade_no,
                'store_id' => $order->store_id,
                'ways_type' => $order->ways_type,
                'config_id' => $order->config_id,
            ];

            $obj = new OrderController();
            $return = $obj->order_foreach_public($data);
            return $return;
        } catch (\Exception $exception) {
            return json_encode(['status' => 2, 'message' => $exception->getLine()]);
        }
    }


}