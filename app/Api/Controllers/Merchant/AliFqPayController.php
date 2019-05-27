<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/6/22
 * Time: 上午10:27
 */

namespace App\Api\Controllers\Merchant;


use Alipayopen\Sdk\AopClient;
use Alipayopen\Sdk\Request\AlipayTradeCancelRequest;
use Alipayopen\Sdk\Request\AlipayTradeQueryRequest;
use App\Api\Controllers\AlipayOpen\PayController;
use App\Api\Controllers\BaseController;
use App\Api\Controllers\Config\AlipayIsvConfigController;
use App\Common\MerchantFuwu;
use App\Common\PaySuccessAction;
use App\Common\StoreDayMonthOrder;
use App\Common\UserGetMoney;
use App\Models\AlipayAccount;
use App\Models\AlipayAppOauthUsers;
use App\Models\AlipayHbOrder;
use App\Models\AlipayHbrate;
use App\Models\MerchantStore;
use App\Models\Store;
use App\Models\StorePayWay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AliFqPayController extends BaseController
{


    //花呗分期支付
    public function fq_pay(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $merchant_id = $merchant->merchant_id;
            $merchant_name = $merchant->merchant_name;
            $shop_name = $request->get('shop_name', '');
            $total_amount = $request->get('total_amount');//金额
            $total_amount = number_format($total_amount, 2, '.', '');
            $shop_price = $request->get('shop_price', $total_amount);
            $hb_fq_num = (int)$request->get('hb_fq_num');//花呗分期数
            $hb_fq_seller_percent = $request->get('hb_fq_seller_percent');//商家承担手续费传入100，用户承担手续费传入0$hb_fq_seller_percent=0;
            $hb_fq_seller_percent_in = $hb_fq_seller_percent;
            $buyer_user = $request->get('buyer_user', '');
            $buyer_phone = $request->get('buyer_phone', '');
            $ways_source = $request->get('ways_source', 'alipay');//支付通道
            $config_type = '01';
            $config_id = $merchant->config_id;
            $auth_code = $request->get('auth_code', '');
            $store_id= $request->get('store_id', '');
            $total_amount_ali = $shop_price;//传到支付宝的金额
            $total_amount_out = 0.00;
            $notify_url = url('/api/alipayopen/qr_pay_notify');


            $check_data = [
                'total_amount' => '总金额',
                'shop_price' => '商品金额',
                'hb_fq_num' => '分期数',
                'hb_fq_seller_percent' => '手续费承担方',
                'ways_source' => '支付类型',
            ];
            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }

            if ($store_id==""){
                $MerchantStore = MerchantStore::where('merchant_id', $merchant_id)
                    ->orderBy('created_at', 'asc')
                    ->first();
                if ($MerchantStore) {
                    $store_id = $MerchantStore->store_id;
                }

            }

            $store = Store::where('store_id', $store_id)->first();

            $tg_user_id=$store->user_id;
            $store_name = $store->store_name;
            $store_pid = $store->pid;
            $AlipayHbrate = AlipayHbrate::where('store_id', $store_id)->first();

            $trade_pay = '02';
            if ($AlipayHbrate) {
                $trade_pay = $AlipayHbrate->trade_pay;
            }
            //官方支付宝花呗分期
            if (in_array($ways_source, ['alipay'])) {
                $AlipayAppOauthUsers = AlipayAppOauthUsers::where('store_id', $store_id)->first();
                $out_user_id = $AlipayAppOauthUsers->alipay_user_id;
                $alipay_store_id = $AlipayAppOauthUsers->alipay_store_id;
                $trade_pay_rate = number_format($AlipayAppOauthUsers->trade_pay_rate, 2, '.', '');//当面付费率
                //自用型
                if ($AlipayAppOauthUsers->config_type == '02') {
                    $config_type = '02';
                }

                //分成模式 服务商
                if ($AlipayAppOauthUsers->settlement_type != "set_b") {
                    $AlipayAccount = AlipayAccount::where('config_id', $config_id)
                        ->where('config_type', $config_type)
                        ->first();//服务商的
                    if (!$AlipayAccount) {
                        $msg = '服务商未配置请联系服务商';
                        return json_encode([
                            'status' => 2,
                            'message' => $msg
                        ]);
                    }

                    $AlipayAppOauthUsers = $AlipayAccount;
                    $trade_pay_rate = number_format($AlipayAccount->trade_pay_rate, 2, '.', '');//当面付费率
                }


                /*******************商户贴息模式**********************/
                //用户承担服务费   商户贴息模式
                if ($hb_fq_seller_percent == 0) {
                    $hb_fq_seller_percent = 100;//商户承担服务费
                    $fq_rate = $this->xy_rate($store_id, $AlipayHbrate, $hb_fq_num, 100);
                    $total_amount_ali = (($shop_price * ($fq_rate / 100)) + $shop_price);
                    $total_amount_ali = number_format($total_amount_ali, 2, '.', '');
                    $fw = ($shop_price * ($fq_rate / 100));
                    //去除分期手续费 去除费率
                    $gf = $this->gf_hbrate(100, $hb_fq_num);//分期费率
                    $gf_fq = ($gf * $total_amount_ali) / 100;//官方的手续费
                    $gf_fq = number_format($gf_fq, 2, '.', '');
                    //当面付服务费 0.006来算
                    $gf_pay = ($trade_pay_rate / 100) * $total_amount_ali;
                    $gf_pay = number_format($gf_pay, 2, '.', '');
                    $total_amount_out = $fw - $gf_fq;

                    //服务商承担
                    if ($trade_pay == '01') {
                        $total_amount_out = $fw - $gf_fq - $gf_pay;
                    }

                    $total_amount_out = number_format($total_amount_out, 2, '.', '');
                    $total_amount_in = $total_amount_ali;//入库


                    //24期 只支持用户承担手续费
                    if ($hb_fq_num == 24) {
                        $hb_fq_seller_percent = 0;//用户贴息模式
                        //最终传到阿里的金额
                        $total_amount_ali = $this->ali_price($store_id, $AlipayHbrate, $shop_price, $hb_fq_num);
                        $total_amount_ali = number_format($total_amount_ali, 2, '.', '');
                        //需要从商家支付宝那边转出来的金额
                        $pay_rate = number_format((($trade_pay_rate / 100) * $total_amount_ali), 2, ".", "");//支付手续费;
                        $total_amount_out = $total_amount_ali - $shop_price;
                        //服务商承担
                        if ($trade_pay == '01') {
                            $total_amount_out = $total_amount_ali - $shop_price - $pay_rate;
                        }
                        $total_amount_out = number_format($total_amount_out, 2, ".", "");
                        $total_amount_in = $total_amount_ali;
                        $fw = $total_amount_out;//服务费
                    }


                } //商户承担服务费   商户贴息模式
                else {
                    $hb_fq_seller_percent = 100;//商户承担服务费
                    $fq_rate = $this->xy_rate($store->store_id, $AlipayHbrate, $hb_fq_num, 100);
                    $total_amount_ali = $shop_price;//用户不出服务费
                    $total_amount_ali = number_format($total_amount_ali, 2, '.', '');
                    //去除分期手续费 去除费率
                    $xy_fq = $shop_price * $fq_rate / 100;//想用的手续费
                    //当面付服务费 0.006来算
                    $gf_pay = ($trade_pay_rate / 100) * $shop_price;

                    //去除分期手续费 去除费率
                    $gf = $this->gf_hbrate(100, $hb_fq_num);//分期费率
                    $gf_fq = ($gf * $total_amount_ali) / 100;//官方的手续费

                    $total_amount_out = $xy_fq - $gf_fq;//想用手续费-当面付
                    //服务商承担
                    if ($trade_pay == '01') {
                        $total_amount_out = $xy_fq - $gf_pay - $gf_fq;//想用手续费-当面付
                    }

                    $fw = 0.00;
                    $total_amount_in = $total_amount_ali;//入库
                }
                // }
                /*******************商户贴息模式**********************/
                $shop_name = '门店分期购:' . $hb_fq_num . '期' . '-' . $shop_name;
                $desc = '门店分期购:' . $hb_fq_num . '期' . '-' . $shop_name;

                $app_auth_token = $AlipayAppOauthUsers->app_auth_token;
                $auth_shop_name = $store->store_name;


                //请求参数
                $data = [
                    'config_id' => $config_id,
                    'store_id' => $store_id,
                    'merchant_id' => $merchant_id,
                    'total_amount' => $total_amount,
                    'shop_price' => $shop_price,
                    'remark' => '',
                    'device_id' => '',
                    'open_id' => '',
                    'config_type' => '01',
                    'shop_name' => $shop_name,
                    'shop_desc' => '',
                    'store_name' => $store_name,
                    'is_fq' => 1,
                    'is_fq_data' => [
                        'hb_fq_num' => $hb_fq_num,
                        'hb_fq_seller_percent' => $hb_fq_seller_percent
                    ]
                ];

                //入库参数
                $data_insert = [
                    'store_id' => $store_id,
                    'merchant_id' => $merchant_id,
                    'store_name' => $store_name,
                    'merchant_name' => $merchant_name,
                    'buyer_user' => $buyer_user,
                    'buyer_phone' => $buyer_phone,
                    'shop_name' => $shop_name,
                    'shop_desc' => $desc,
                    'total_amount' => $total_amount_in,
                    'shop_price' => $shop_price,
                    'receipt_amount' => $shop_price,
                    'pay_status' => 2,
                    'pay_status_desc' => '等待支付',
                    'hb_fq_num' => $hb_fq_num,
                    'hb_fq_seller_percent' => $hb_fq_seller_percent_in,
                    'hb_fq_sxf' => ($this->xy_rate($store_id, $AlipayHbrate, $hb_fq_num) * $shop_price) / 100, //$this->hb_fq_sxf($hb_fq_num, $total_amount, $hb_fq_seller_percent),
                    'xy_rate' => $this->xy_rate($store_id, $AlipayHbrate, $hb_fq_num),
                    'total_amount_out' => $total_amount_out,
                    'out_status' => 2,
                    'config_id' => $config_id,
                    'pay_sxf' => 0.00,//支付手续费
                ];

                $pay_obj = new PayController();

                //配置
                $isvconfig = new AlipayIsvConfigController();

                $storeInfo = $isvconfig->alipay_auth_info($store_id, $store_pid);
                $out_user_id = $storeInfo->user_id;//商户的id
                $alipay_store_id = $storeInfo->alipay_store_id;

                $config = $isvconfig->AlipayIsvConfig($config_id, $config_type);


                //分成模式 服务商
                if ($storeInfo->settlement_type == "set_a") {
                    if ($storeInfo->config_type == '02') {
                        $config_type = '02';
                    }
                    $storeInfo = AlipayAccount::where('config_id', $config_id)
                        ->where('config_type', $config_type)
                        ->first();//服务商的
                }

                if (!$storeInfo) {
                    $msg = '支付宝授权信息不存在';
                    return [
                        'status' => 2,
                        'message' => $msg
                    ];

                }


                $data['code'] = $auth_code;
                $data['alipay_store_id'] = $alipay_store_id;
                $data['out_user_id'] = $out_user_id;
                $data['app_auth_token'] = $storeInfo->app_auth_token;
                $data['config'] = $config;
                $data['notify_url'] = $notify_url;


                //扫一扫分期
                if ($auth_code) {
                    $out_trade_no = 'fq_scan' . date('YmdHis', time()) . $merchant_id . rand(10000, 99999);

                    $data['out_trade_no'] = $out_trade_no;
                    //入库参数
                    $data_insert['ways_type'] = 1006;
                    $data_insert['ways_type_desc'] = '扫一扫分期';
                    $data_insert['ways_source'] = 'alipay';
                    $data_insert['ways_source_desc'] = '支付宝';
                    $data_insert['out_trade_no'] = $out_trade_no;


                    $insert_re = AlipayHbOrder::create($data_insert);

                    if (!$insert_re) {
                        return json_encode([
                            'status' => 2,
                            'message' => '订单未入库'
                        ]);
                    }


                    $return_array = $pay_obj->scan_pay($data);
                    $type = 1006;//分期
                    if ($return_array['status'] == 1) {
                        AlipayHbOrder::where('out_trade_no', $out_trade_no)->update(
                            [
                                'trade_no' => $return_array['trade_no'],
                                'buyer_id' => $return_array['buyer_id'],
                                'buyer_logon_id' => $return_array['buyer_logon_id'],
                                'pay_status_desc' => '支付成功',
                                'pay_status' => 1,
                            ]);

                       //支付后

                        return json_encode([
                            'status' => 1,
                            'pay_status' => '1',
                            'message' => '支付成功',
                            'data' => [
                                'out_trade_no' => $out_trade_no,
                                'ways_type' => 1006,
                                'total_amount' => $total_amount,
                                'store_id' => $store_id,
                                'store_name' => $store_name,
                                'config_id' => $config_id,
                            ]
                        ]);


                    }

                    if ($return_array['status'] == 3) {

                        return json_encode([
                            'status' => 1,
                            'pay_status' => '2',
                            'message' => '正在支付',
                            'data' => [
                                'out_trade_no' => $out_trade_no,
                                'ways_type' => 1006,
                                'total_amount' => $total_amount,
                                'store_id' => $store_id,
                                'store_name' => $store_name,
                                'config_id' => $config_id,
                            ]
                        ]);
                    }


                    //其他状态
                    return json_encode($return_array);


                } else {
                    $out_trade_no = 'fq_qr' . date('YmdHis', time()) . $merchant_id . rand(10000, 99999);
                    $data['out_trade_no'] = $out_trade_no;
                    $return = $pay_obj->qr_pay($data);
                    $return_aray = json_decode($return, true);
                    if ($return_aray['status'] == 1) {
                        //入库参数
                        //入库参数
                        $data_insert['ways_type'] = 1007;
                        $data_insert['ways_type_desc'] = '固定二维码分期';
                        $data_insert['ways_source'] = 'alipay';
                        $data_insert['ways_source_desc'] = '支付宝';
                        $data_insert['out_trade_no'] = $out_trade_no;


                        $insert_re = AlipayHbOrder::create($data_insert);

                        if (!$insert_re) {
                            return json_encode([
                                'status' => 2,
                                'message' => '订单未入库'
                            ]);
                        }
                    }
                }


                return $return;


            }

        } catch (\Exception $exception) {
            $info = $exception->getMessage();
            return json_encode([
                'status' => 2,
                'message' => $info
            ]);
        }

    }


//方法
    public
    function ways_source(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $merchant_id = $merchant->merchant_id;
            $MerchantStore = MerchantStore::where('merchant_id', $merchant_id)
                ->orderBy('created_at', 'asc')
                ->first();
            $store_id = '';
            if ($MerchantStore) {
                $store_id = $MerchantStore->store_id;

            }


            $StorePayWay = StorePayWay::where('store_id', $store_id)
                ->where('status', 1)
                ->where('ways_type', 3001)
                ->first();

            if ($StorePayWay) {
                $data = [
                    [
                        'ways_source' => 'alipay',
                        'ways_source_desc' => '当面付花呗分期'
                    ],
                    [
                        'ways_source' => 'mybank',
                        'ways_source_desc' => '网商银行花呗分期'
                    ]
                ];
            } else {
                $data = [
                    [
                        'ways_source' => 'alipay',
                        'ways_source_desc' => '当面付花呗分期'
                    ]
                ];
            }

            return json_encode(
                [
                    'status' => 1,
                    'data' => $data
                ]
            );
        } catch (\Exception $exception) {
            return json_encode(['status' => 2, 'message' => $exception->getMessage() . ' - ' . $exception->getLine()]);
        }
    }


    //查询花呗分期的费率
    public function hbrate(Request $request)
    {
        try {
            $user = $this->parseToken();
            $merchant_id = $user->merchant_id;
            $type = $request->get('hb_fq_seller_percent', 100);//客户端已经不传了 默认商户承担
            $hb_fq_num = $request->get('hb_fq_num');
            $MerchantStore = MerchantStore::where('merchant_id', $merchant_id)
                ->orderBy('created_at', 'asc')
                ->first();
            $store_id = '';
            if ($MerchantStore) {
                $store_id = $MerchantStore->store_id;

            }
            $AlipayHbrate = AlipayHbrate::where('store_id', $store_id)->first();
            //商户承担
            if ($type == 100) {
                $hb_fq_num_3 = 0;
                $hb_fq_num_6 = 0;
                $hb_fq_num_12 = 0;
                $hb_fq_num_24 = 0;
                //商户自己设置
//                if ($AlipayHbrate) {
//                    $hb_fq_num_3 = $AlipayHbrate->hb_fq_num_3 / 100;
//                    $hb_fq_num_6 = $AlipayHbrate->hb_fq_num_6 / 100;
//                    $hb_fq_num_12 = $AlipayHbrate->hb_fq_num_12 / 100;
//                    $hb_fq_num_24 = $AlipayHbrate->hb_fq_num_24 / 100;
//                }
//               // 用户承担
            } else {
                $hb_fq_num_3 = 0.023;
                $hb_fq_num_6 = 0.045;
                $hb_fq_num_12 = 0.075;
                $hb_fq_num_24 = 0.125;
                //商户自己设置
                if ($AlipayHbrate) {
                    $hb_fq_num_3 = $AlipayHbrate->hb_fq_num_3 / 100;
                    $hb_fq_num_6 = $AlipayHbrate->hb_fq_num_6 / 100;
                    $hb_fq_num_12 = $AlipayHbrate->hb_fq_num_12 / 100;
                    $hb_fq_num_24 = $AlipayHbrate->hb_fq_num_24 / 100;
                }
            }


            //官方2.30%
            if ((int)$hb_fq_num == 3) {
                return json_encode(['status' => 1, 'data' => [
                    'rate' => $hb_fq_num_3,
                ]]);
            }
            //官方4.5%
            if ((int)$hb_fq_num == 6) {
                return json_encode(['status' => 1, 'data' =>
                    ['rate' => $hb_fq_num_6,
                    ]]);
            }
            // 官方7.5%
            if ((int)$hb_fq_num == 12) {
                return json_encode(['status' => 1, 'data' => [
                    'rate' => $hb_fq_num_12,
                ]]);
            }

            // 官方12.5%
            if ((int)$hb_fq_num == 24) {
                return json_encode(['status' => 1, 'data' => [
                    'rate' => $hb_fq_num_24,
                ]]);
            }

            return json_encode(['status' => 2, 'message' => "参数填写不正确"]);


        } catch (\Exception $exception) {
            return json_encode(['status' => 2, 'message' => $exception->getMessage()]);

        }
    }


//商户花呗分期数查询
    public
    function hb_fq_num(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $merchant_id = $merchant->merchant_id;
            $MerchantStore = MerchantStore::where('merchant_id', $merchant_id)
                ->orderBy('created_at', 'asc')
                ->first();
            $store_id = '';
            if ($MerchantStore) {
                $store_id = $MerchantStore->store_id;

            }
            $AlipayHbrate = AlipayHbrate::where('store_id', $store_id)->first();
            if ($AlipayHbrate) {
                $hb_fq_num_3 = [];
                if ($AlipayHbrate->hb_fq_num_3_status == '01') {
                    $hb_fq_num_3 = [
                        'hb_fq_num' => 3,
                        'hb_fq_rate' => $AlipayHbrate->hb_fq_num_3,
                        'hb_fq_num_desc' => '3期'
                    ];
                }
                $hb_fq_num_6 = [];
                if ($AlipayHbrate->hb_fq_num_6_status == '01') {
                    $hb_fq_num_6 = [
                        'hb_fq_num' => 6,
                        'hb_fq_rate' => $AlipayHbrate->hb_fq_num_6,
                        'hb_fq_num_desc' => '6期'
                    ];
                }
                $hb_fq_num_12 = [];
                if ($AlipayHbrate->hb_fq_num_12_status == '01') {
                    $hb_fq_num_12 = [
                        'hb_fq_num' => 12,
                        'hb_fq_rate' => $AlipayHbrate->hb_fq_num_12,
                        'hb_fq_num_desc' => '12期'
                    ];
                }
                $hb_fq_num_24 = [];
                if ($AlipayHbrate->hb_fq_num_24_status == '01') {
                    $hb_fq_num_24 = [
                        'hb_fq_num' => 24,
                        'hb_fq_rate' => $AlipayHbrate->hb_fq_num_24,
                        'hb_fq_num_desc' => '24期'
                    ];
                }
                $data = [$hb_fq_num_3, $hb_fq_num_6, $hb_fq_num_12, $hb_fq_num_24];
                $data1 = [];

                foreach ($data as $k => $v) {
                    if (count($v)) {
                        $data1[$k] = $v;
                    }
                    continue;
                }
                $data2 = [];
                foreach ($data1 as $k => $v) {
                    $data2[] = $v;
                }

                $data = ['merchant' => array_filter($data2)];

            } else {
                $data = [
                    [
                        'hb_fq_num' => 3,
                        'hb_fq_rate' => 1.8,
                        'hb_fq_num_desc' => '3期'
                    ],
                    [
                        'hb_fq_num' => 6,
                        'hb_fq_rate' => 4.5,
                        'hb_fq_num_desc' => '6期'
                    ],
                    [
                        'hb_fq_num' => 12,
                        'hb_fq_rate' => 7.5,
                        'hb_fq_num_desc' => '12期'
                    ],
                    [
                        'hb_fq_num' => 24,
                        'hb_fq_rate' => 12.5,
                        'hb_fq_num_desc' => '24期'
                    ]

                ];
            }


            return json_encode(['status' => 1, 'data' => $data]);

        } catch (\Exception $exception) {
            return json_encode(['status' => 2, 'message' => $exception->getMessage()]);

        }
    }


//查询官方花呗分期的费率
    public
    function gf_hbrate($type, $hb_fq_num)
    {
        if ($type == 100) {
            $hb_fq_num_3 = 1.8;
        } else {
            $hb_fq_num_3 = 2.3;
        }
        $hb_fq_num_6 = 4.5;
        $hb_fq_num_12 = 7.5;
        $hb_fq_num_24 = 12.5;

        //官方2.30%
        if ((int)$hb_fq_num == 3) {
            $rete = $hb_fq_num_3;
        }
        //官方4.5%
        if ((int)$hb_fq_num == 6) {
            $rete = $hb_fq_num_6;

        }
        // 官方7.5%
        if ((int)$hb_fq_num == 12) {
            $rete = $hb_fq_num_12;

        }
        // 官方12.5%
        if ((int)$hb_fq_num == 24) {
            $rete = $hb_fq_num_24;

        }

        return $rete;

    }

//得到传到支付宝的价格
    public
    function ali_price($store_id, $AlipayHbrate, $shop_price, $hb_fq_num)
    {
        //官方的费率
        $gf_ra_3 = 2.3;
        $gf_ra_6 = 4.5;
        $gf_ra_12 = 7.5;
        $gf_ra_24 = 12.5;

        //默认官方
        $xy_ra_3 = 2.3;
        $xy_ra_6 = 4.5;
        $xy_ra_12 = 7.5;
        $xy_ra_24 = 12.5;
        //商户自己设置
        if ($AlipayHbrate) {
            $xy_ra_3 = $AlipayHbrate->hb_fq_num_3;
            $xy_ra_6 = $AlipayHbrate->hb_fq_num_6;
            $xy_ra_12 = $AlipayHbrate->hb_fq_num_12;
            $xy_ra_24 = $AlipayHbrate->hb_fq_num_24;
        }

        //2.30%
        if ((int)$hb_fq_num == 3) {
            $xy_money = (($xy_ra_3 * $shop_price) / 100) + $shop_price;//想用的收款
            $a = (100 + $gf_ra_3);
            $ali_money = $xy_money * (100 / (100 + $gf_ra_3));
        }
        //4.5%
        if ((int)$hb_fq_num == 6) {
            $xy_money = (($xy_ra_6 * $shop_price) / 100) + $shop_price;//想用的收款
            $ali_money = $xy_money * (100 / (100 + $gf_ra_6));
        }
        // 7.5%
        if ((int)$hb_fq_num == 12) {
            $xy_money = (($xy_ra_12 * $shop_price) / 100) + $shop_price;//想用的收款
            $ali_money = $xy_money * (100 / (100 + $gf_ra_12));
        }

        // 12.5%
        if ((int)$hb_fq_num == 24) {
            $xy_money = (($xy_ra_24 * $shop_price) / 100) + $shop_price;//想用的收款
            $ali_money = $xy_money * (100 / (100 + $gf_ra_24));
        }

        return number_format($ali_money, 2, ".", "");
    }

//得到门店设置想用的费率
    public
    function xy_rate($store_id, $AlipayHbrate, $hb_fq_num, $hb_fq_seller_percent = 0)
    {

        if ($hb_fq_seller_percent == 0) {
            $xy_ra_3 = 2.3;

        } else {
            $xy_ra_3 = 1.8;

        }

        $xy_ra_6 = 4.5;
        $xy_ra_12 = 7.5;
        $xy_ra_24 = 12.5;
        //商户自己设置
        if ($AlipayHbrate) {
            $xy_ra_3 = $AlipayHbrate->hb_fq_num_3;
            $xy_ra_6 = $AlipayHbrate->hb_fq_num_6;
            $xy_ra_12 = $AlipayHbrate->hb_fq_num_12;
            $xy_ra_24 = $AlipayHbrate->hb_fq_num_24;
        }

        //2.30%
        if ((int)$hb_fq_num == 3) {
            $rate = $xy_ra_3;
        }
        //4.5%
        if ((int)$hb_fq_num == 6) {
            $rate = $xy_ra_6;
        }
        // 7.5%
        if ((int)$hb_fq_num == 12) {
            $rate = $xy_ra_12;
        }

        // 12.5%
        if ((int)$hb_fq_num == 24) {
            $rate = $xy_ra_24;
        }
        return $rate;
    }

    //查询支付宝的订单状态
    public function AlipayTradePayQuery($out_trade_no, $app_auth_token, $configs)
    {
        $aop = new AopClient();
        $aop->rsaPrivateKey = $configs->rsa_private_key;
        $aop->appId = $configs->app_id;
        $aop->method = 'alipay.trade.query';

        $aop->signType = "RSA2";//升级算法
        $aop->gatewayUrl = $configs->alipay_gateway;
        $aop->format = "json";
        $aop->charset = "GBK";
        $aop->version = "2.0";
        $requests = new AlipayTradeQueryRequest();
        $requests->setBizContent("{" .
            "    \"out_trade_no\":\"" . $out_trade_no . "\"" .
            "  }");
        $result = $aop->execute($requests, '', $app_auth_token);
        return $result;
    }

    //支付宝取消接口
    public
    function AlipayTradePayCancel($out_trade_no, $app_auth_token, $configs)
    {
        $aop = new AopClient();
        $aop->rsaPrivateKey = $configs->rsa_private_key;
        $aop->appId = $configs->app_id;
        $aop->method = 'alipay.trade.cancel';

        $aop->signType = "RSA2";//升级算法
        $aop->gatewayUrl = $configs->alipay_gateway;
        $aop->format = "json";
        $aop->charset = "GBK";
        $aop->version = "2.0";
        $requests = new AlipayTradeCancelRequest();
        $requests->setBizContent("{" .
            "    \"out_trade_no\":\"" . $out_trade_no . "\"" .
            "  }");
        $result = $aop->execute($requests, '', $app_auth_token);
        return $result;
    }


}