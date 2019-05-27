<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/6/4
 * Time: 下午2:11
 */

namespace App\Api\Controllers\Merchant;


use Alipayopen\Sdk\AopClient;
use Aliyun\AliSms;
use App\Api\Controllers\AlipayOpen\OauthController;
use App\Api\Controllers\BaseController;
use App\Models\AlipayIsvConfig;
use App\Models\AppMerchantIndex;
use App\Models\AppOem;
use App\Models\Banner;
use App\Models\MerchantFuwu;
use App\Models\MerchantStore;
use App\Models\MerchantStoreDayOrder;
use App\Models\Order;
use App\Models\QrList;
use App\Models\QrListInfo;
use App\Models\QrPayInfo;
use App\Models\RefundOrder;
use App\Models\SmsConfig;
use App\Models\StuOrder;
use App\Models\StuOrderType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class IndexController extends BaseController
{

    /** 首页布局
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {

        $data = [];
        $data_sid = [];
        try {
            $merchant = $this->parseToken($request->get('token'));
            $config_id = $merchant->config_id;
            $type = $request->get('type');
            if ($type == "") {
                $type = 'one,two,third,fourth,fourthtg,five';
            }
            $type_array = explode(",", $type);

            foreach ($type_array as $k => $v) {
                //查询出pid=0的栏目
                $data_pid = AppMerchantIndex::where('pid', 0)
                    ->where('type', $v)
                    ->select('id')
                    ->first();
                if (!$data_pid) {
                    $this->status = 2;
                    $this->message = $v . '-数据传入有误';
                    return $this->format();
                }

                //100043 -想用服务
                if ($v == 'one' || $v == 'two') {

                    //查询此pid 下的子项
                    $data_sid = AppMerchantIndex::where('pid', $data_pid->id)
                        ->orderBy('sort', 'ASC')->get();
                }
                //100043 -想用服务
                if ($v == 'third') {
                    $MerchantFuwu = MerchantFuwu::where('merchant_id', $merchant->merchant_id)
                        ->orderBy('updated_at', 'desc')
                        ->take(2)//返回两条数据
                        ->get();

                    $data_sid = $MerchantFuwu;
                }
                //100044 -广告banner
                if ($v == 'fourth') {
                    $AppOem = AppOem::where('config_id', $config_id)->first();
                    if (!$AppOem) {
                        $config_id = '1234';//平台
                    }

                    $value = Banner::where('config_id', $config_id)
                        ->orderBy('sort', 'asc')
                        ->where('type', 'merchant')
                        ->where('banner_time_s', '<', date('Y-m-d H:i:s', time()))
                        ->where('banner_time_e', '>', date('Y-m-d H:i:s', time()))
                        ->where('status', '1')
                        ->get();


                    $data_sid = $value;
                }

                //任务活动
                if ($v == 'fourthtg') {
                    $data_sid = [
                        'url' => 'https://www.qq.com/?fromdefault',
                        'list' => [
                            [
                                'logo' => 'https://pay.umxnt.com/app/img/merchant/index/jingdongbaitiao.png',
                                'name' => '京东拉新',
                                'source' => 'jdlx'
                            ], [
                                'logo' => 'https://pay.umxnt.com/app/img/merchant/index/zhifubaolaxin.png',
                                'name' => '支付宝拉新',
                                'source' => 'alilx'
                            ],
                        ]
                    ];
                }
                //100045 -经营简报
                if ($v == 'five') {

                    //今日
                    $day = date('Ymd', time());
                    $beginToday = date("Y-m-d 00:00:00", time());
                    $endToday = date("Y-m-d H:i:s", time());

                    //针对单个收银员

//                    $day_order_data = MerchantStoreDayOrder::where('day', $day)
//                        ->where('merchant_id', $merchant->merchant_id)
//                        ->select('total_amount', 'order_sum');
//
//
//                    $refund_order_data = RefundOrder::whereBetween('created_at', [$beginToday, $endToday])
//                        ->where('merchant_id', $merchant->merchant_id)
//                        ->select('refund_amount');


                    //整个门店ID
                    $MerchantStore = MerchantStore::where('merchant_id', $merchant->merchant_id)
                        ->select('store_id')
                        ->get();

                    if ($MerchantStore->isEmpty()) {
                        $store_ids = [];
                    } else {
                        $store_ids = $MerchantStore->toArray();
                    }


                    $time_start = date('Y-m-d 00:00:00', time());

                    $time_end = date('Y-m-d H:i:s', time());

                    $day_order_data = Order::whereIn('store_id', $store_ids)
                        ->where('created_at', '>=', $time_start)
                        ->where('created_at', '<=', $time_end)
                        ->whereIn('pay_status', [1, 3, 6])
                        ->select('total_amount', 'id');

                    $refund_order_data = RefundOrder::whereBetween('created_at', [$beginToday, $endToday])
                        ->whereIn('store_id', $store_ids)
                        ->select('refund_amount');


                    $day_order = $day_order_data->sum('total_amount');
                    $day_order = '' . $day_order . '';
                    $day_order_count = '' . $day_order_data->count('id') . '';

                    $refund_day_order = $refund_order_data->sum('refund_amount');
                    $refund_day_order = '' . $refund_day_order . '';
                    $refund_day_order_count = '' . count($refund_order_data->get()) . '';

                    if ($refund_day_order_count) {
                        $title = "今日收款总金额" . $day_order . "元,成功" . $day_order_count . "笔";
                        $desc = '有' . $refund_day_order_count . '笔订单退款,金额' . $refund_day_order . '元';

                    } else {
                        $title = "今日收款总金额" . $day_order . "元";
                        $desc = "交易笔数为" . $day_order_count . "笔";

                    }
                        $title = "";//"今日收款总金额" . $day_order . "元,成功" . $day_order_count . "笔";
                        $desc = "";//'有' . $refund_day_order_count . '笔订单退款,金额' . $refund_day_order . '元';


                    $data_sid = [
                        [
                            'title' => $title,
                            'desc' => $desc,
                            'created_at' => date('Y-m-d H:i:s', time())
                        ]
                    ];
                }
                $data[$v] = $data_sid;
                $data['thirdicon'] = url('app/img/merchant/index/fw.png');

            }

            $this->status = 1;
            $this->message = '数据返回成功';


        } catch
        (\Exception $exception) {
            $this->status = -1;
            $this->message = '系统异常-' . $exception->getLine();

        }


        return $this->format($data);


    }

    //扫一扫
    public function scan(Request $request)
    {
        $merchant = $this->parseToken();
        $config_id = $merchant->config_id;
        $merchant_id = $merchant->merchant_id;
        $code = $request->get('code');
        $store_id = $request->get('store_id', '');
        $is_o = substr($code, 0, 1);
        $type = substr($code, 0, 2);
        $is_qr = substr($code, 0, 4);

        if ($store_id == "") {
            $merchant_store = MerchantStore::where('merchant_id', $merchant_id)->first();
            if ($merchant_store) {
                $store_id = $merchant_store->store_id;
            } else {
                return json_encode(['status' => 2, 'message' => '参数有误']);

            }
        }

        //绑定空码
        if ($is_qr == "http") {
            $url = basename($code);//获取链接
            $data = $this->getParams($url);
            $code = $data['no'];
            $QrListInfo = QrListInfo::where('code_number', $code)->first();

            //空码存在
            if ($QrListInfo) {

                //已经绑定支付码
                if ($QrListInfo->code_type) {
                    return json_encode(['status' => 2, 'message' => '二维码已经被其他店铺绑定！请更换']);
                } else {
                    if ($store_id == "") {
                        return json_encode(['status' => 2, 'message' => '请先开通门店']);
                    }


                    //未绑定
                    $datainfo = $QrListInfo->toArray();
                    $datainfo['store_id'] = $store_id;
                    $datainfo['code_type'] = 1;
                    $datainfo['merchant_id'] = $merchant_id;

                    //开启事务
                    try {
                        DB::beginTransaction();
                        QrPayInfo::create($datainfo);
                        $QrListInfo->update(
                            [
                                'code_type' => 1,
                                'store_id' => $store_id,

                            ]
                        );
                        $QrListInfo->save();

                        //已经使用加 1
                        $QrList = QrList::where('cno', $QrListInfo->cno)->first();
                        $s_num = $QrList->s_num;
                        $QrList->s_num = $s_num + 1;
                        $QrList->save();

                        DB::commit();
                    } catch (\Exception $e) {
                        DB::rollBack();
                    }
                    return json_encode(['status' => 1, 'message' => '绑定收款二维码成功']);


                }
            } else {
                //空码不存在
                if ($store_id == "") {
                    return json_encode(['status' => 2, 'message' => '请先开通门店']);
                }


                //未绑定
                $datainfo = [
                    'user_id' => '1',
                    'code_number' => $code,
                    'code_type' => 1,
                    'store_id' => $store_id,
                    'cno' => '1',
                ];
                //开启事务
                try {
                    DB::beginTransaction();
                    QrListInfo::create($datainfo);
                    $datainfo['merchant_id'] = $merchant_id;
                    QrPayInfo::create($datainfo);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                }
                return json_encode(['status' => 1, 'message' => '绑定收款二维码成功']);
            }

        }

        return json_encode(['status' => 2, 'message' => '不识别的二维码']);
    }

    //核销
    public function discount(Request $request)
    {

        $code = $request->get('code');
        if ($code) {
            $data = [
                'status' => 1,
                'message' => '处理成功',
                'data' => [
                    'code' => $code,
                    'discount_amount' => '98.02'
                ]
            ];
        } else {
            $data = [
                'status' => 2,
                'message' => '请让顾客出示正确的优惠码',
                'data' => [
                    'code' => $code,
                ]
            ];
        }

        return json_encode($data);

    }

    //主页统计数据
    public function get_data(Request $request)
    {

        $merchant = $this->parseToken();

        $type = $request->get('type', '');
        $store_ids = [];
        $MerchantStore = MerchantStore::where('merchant_id', $merchant->merchant_id)
            ->select('store_id')
            ->get();

        if (!$MerchantStore->isEmpty()) {
            $store_ids = $MerchantStore->toArray();
        }

        //昨日流水
        $old_day = date("Ymd", time() - 24 * 60 * 60);
        $beginYesterday = date("Y-m-d 00:00:00", strtotime("-1 day"));
        $endYesterday = date("Y-m-d 23:59:59", strtotime("-1 day"));

        $old_day_order = StuOrder::whereIn('store_id', $store_ids)
            ->where('pay_status', 1)
            ->where('created_at', '>', $beginYesterday)
            ->where('created_at', '<', $endYesterday)
            ->select('amount')
            ->sum('amount');

        $old_day_order = '' . $old_day_order . '';

        //笔数

        //今日流水
        $day = date('Ymd', time());
        $beginToday = date("Y-m-d 00:00:00", time());
        $endToday = date("Y-m-d H:i:s", time());
        $day_order = '0.00';
        $day_order = StuOrder::whereIn('store_id', $store_ids)
            ->where('pay_status', 1)
            ->where('created_at', '>', $beginYesterday)
            ->where('created_at', '<', $endYesterday)
            ->select('amount')
            ->sum('amount');


        $day_order = '' . $day_order . '';

        //最近7天
        $beginYesterday = date("Y-m-d 00:00:00", strtotime("-7 day"));
        $endYesterday = date("Y-m-d 23:59:59", strtotime("-7 day"));

        $week_order = StuOrder::whereIn('store_id', $store_ids)
            ->where('pay_status', 1)
            ->where('created_at', '>', $beginYesterday)
            ->where('created_at', '<', $endYesterday)
            ->select('amount')
            ->sum('amount');

        $week_order = '' . $week_order . '';
        //笔数

        //今日流水
        $day = date('Ymd', time());
        $beginToday = date("Y-m-d 00:00:00", time());
        $endToday = date("Y-m-d H:i:s", time());
        $day_order = '0.00';
        $day_order = StuOrder::whereIn('store_id', $store_ids)
            ->where('pay_status', 1)
            ->where('created_at', '>', $beginToday)
            ->where('created_at', '<', $endToday)
            ->select('amount')
            ->sum('amount');


        $day_order = '' . $day_order . '';


        //上个月

        //上个月
        //得到系统的年月
        $tmp_date = date("Ym", time());
        //切割出年份
        $tmp_year = substr($tmp_date, 0, 4);
        //切割出月份
        $tmp_mon = substr($tmp_date, 4, 2);
        $tmp_forwardmonth = mktime(0, 0, 0, $tmp_mon - 1, 1, $tmp_year);
        $fm_forward_month = date("Ym", $tmp_forwardmonth);


        $begin_time = date('Y-m-01 00:00:00', strtotime('-1 month'));
        $end_time = date("Y-m-d 23:59:59", strtotime(-date('d') . 'day'));

        $old_month_order = StuOrder::whereIn('store_id', $store_ids)
            ->where('pay_status', 1)
            ->where('created_at', '>', $begin_time)
            ->where('created_at', '<', $end_time)
            ->select('amount')
            ->sum('amount');


        $old_month_order = '' . $old_month_order . '';


        //本月
        $tmonth = date('Ym', time());
        $begin_time = date("Y-m-d H:i:s", mktime(0, 0, 0, date("m"), 1, date("Y")));
        $end_time = date("Y-m-d H:i:s", mktime(23, 59, 59, date("m"), date("t"), date("Y")));
        //取缓存
        $month_order = StuOrder::whereIn('store_id', $store_ids)
            ->where('pay_status', 1)
            ->where('created_at', '>', $begin_time)
            ->where('created_at', '<', $end_time)
            ->select('amount')
            ->sum('amount');
        $month_order = '' . $month_order . '';


        $data = [
            'status' => 1,
            'message' => '数据返回',
            'data' => [
                'today_total_amount' => $day_order,
                'yesterday_total_amount' => $old_day_order,
                'week_total_amount' => $week_order,
                'month_total_amount' => $month_order,
                'detail' => [
                    'date' => ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'],
                    'alipay' => ['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
                    'weixin' => ['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
                    'total' => ['0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0', '0'],
                ]
                ,
                'week_increase' => '0%',
                'month_increase' => '0%',
                'year_increase' => '0%',
            ]
        ];
        return json_encode($data);
    }

    public function getParams($url)
    {

        $refer_url = parse_url($url);

        $params = $refer_url['query'];

        $arr = array();
        if (!empty($params)) {
            $paramsArr = explode('&', $params);

            foreach ($paramsArr as $k => $v) {
                $a = explode('=', $v);
                $arr[$a[0]] = $a[1];
            }
        }
        return $arr;
    }

}