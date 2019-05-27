<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/6/20
 * Time: 下午3:18
 */

namespace App\Api\Controllers\User;


use App\Api\Controllers\BaseController;
use App\Models\MerchantStore;
use App\Models\MerchantStoreDayOrder;
use App\Models\MerchantStoreMonthOrder;
use App\Models\MyBankStore;
use App\Models\Order;
use App\Models\RefundOrder;
use App\Models\Store;
use App\Models\StoreDayOrder;
use App\Models\StoreMonthOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends BaseController
{
    public function order(Request $request)
    {
        try {
            $user = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $user_id = $request->get('user_id', '');
            $pay_status = $request->get('pay_status', '');
            $ways_source = $request->get('ways_source', '');
            $company = $request->get('company', '');

            $ways_type = $request->get('ways_type', '');
            $time_start = $request->get('time_start',date('Y-m-d 00:00:00',time()));
            $time_end = $request->get('time_end', date('Y-m-d 23:59:59', time()));
            $sort = $request->get('sort', '');
            $out_trade_no = $request->get('out_trade_no', '');
            $trade_no = $request->get('trade_no', '');

            $obj = DB::table('orders');
            $where = [];

            if ($user_id == "") {
                $user_id = $user->user_id;
            }
            $user_ids = $this->getSubIds($user_id);

            if (1) {
                $where[] = ['ways_type', '!=', '2005'];
            }

            if ($pay_status) {
                $where[] = ['pay_status', '=', $pay_status];
            }
            if ($store_id) {
                $where[] = ['store_id', '=', $store_id];
            }
            if ($company) {
                $where[] = ['company', '=', $company];
            }
            if ($ways_source) {
                $where[] = ['ways_source', '=', $ways_source];
            }
            if ($ways_type) {
                $where[] = ['ways_type', '=', $ways_type];
            }
            if ($time_start) {
                $where[] = ['created_at', '>=', $time_start];
            }
            if ($time_end) {
                $where[] = ['created_at', '<=', $time_end];
            }

            if ($out_trade_no) {
                $where[] = ['out_trade_no', 'like', '%' . $out_trade_no . '%'];
            }

            if ($trade_no) {
                $where[] = ['trade_no', 'like', '%' . $trade_no . '%'];
            }

            if ($sort) {
                $obj = $obj->where($where)
                    ->whereIn('user_id', $user_ids)
                    ->orderBy('total_amount', $sort);
            } else {
                $obj = $obj->where($where)
                    ->whereIn('user_id', $user_ids)
                    ->orderBy('updated_at', 'desc');
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

    public function order_info(Request $request)
    {
        try {
            $user = $this->parseToken();
            $out_trade_no = $request->get('out_trade_no', '');
            $data = Order::where('out_trade_no', $out_trade_no)->first();
            if (!$data) {
                $this->status = 2;
                $this->message = '订单号不存在';
                return $this->format();
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


    //对账统计-比较全
    public function order_count(Request $request)
    {
        try {
            $user = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $user_id = $request->get('user_id', '');
            $ways_source = $request->get('ways_source', '');
            $company = $request->get('company', '');
            $time_start = $request->get('time_start', '');
            $time_end = $request->get('time_end', '');
            $return_type = $request->get('return_type', '01');

            $check_data = [
                'time_start' => '开始时间',
                'time_end' => '结束时间',
            ];
            $where = [];
            $whereIn = [];
            $store_ids = [];
            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }
            //条件查询
            if ($time_start) {
                $time_start = date('Y-m-d H:i:s', strtotime($time_start));
                $where[] = ['created_at', '>=', $time_start];
            }
            if ($time_end) {
                $time_end = date('Y-m-d H:i:s', strtotime($time_end));
                $where[] = ['created_at', '<=', $time_end];
            }

            if ($company) {
                $where[] = ['company', $company];
            }

            if ($ways_source) {
                $where[] = ['ways_source', $ways_source];
            }
            if ($store_id) {
                $where[] = ['store_id', $store_id];
            }
            if ($user_id) {

                $user_ids = [
                    [
                        'user_id' => $user_id,
                    ]
                ];

                $user_ids = $this->getSubIds($user_id);

            } else {
                $user_ids = $this->getSubIds($user->user_id);
            }


            //区间
            $e_order = '0.00';


            $order_data = Order::whereIn('user_id', $user_ids)
                ->where($where)
                ->whereIn('pay_status', [1, 6, 3])//成功+退款
                ->select('total_amount', 'refund_amount', 'receipt_amount', 'fee_amount', 'mdiscount_amount');


            $refund_obj = Order::whereIn('user_id', $user_ids)
                ->where($where)
                ->whereIn('pay_status', [6, 3])//退款
                ->select('total_amount');

            //总的
            $total_amount = $order_data->sum('total_amount');//交易金额
            $refund_amount = $refund_obj->sum('total_amount');//退款金额
            $fee_amount = $order_data->sum('fee_amount');//结算服务费/手续费
            $mdiscount_amount = $order_data->sum('mdiscount_amount');//商家优惠金额
            $get_amount = $total_amount - $refund_amount - $mdiscount_amount;//商家实收，交易金额-退款金额
            $receipt_amount = $get_amount - $fee_amount;//实际净额，实收-手续费
            $e_order = '' . $e_order . '';
            $total_count = '' . count($order_data->get()) . '';
            $refund_count = count($refund_obj->get());


            $data = [
                'total_amount' => number_format($total_amount, 2, '.', ''),//交易金额
                'total_count' => '' . $total_count . '',//交易笔数
                'refund_count' => '' . $refund_count . '',//退款金额
                'get_amount' => number_format($get_amount, 2, '.', ''),//商家实收，交易金额-退款金额
                'refund_amount' => number_format($refund_amount, 2, '.', ''),//退款金额
                'receipt_amount' => number_format($receipt_amount, 2, '.', ''),//实际净额，实收-手续费
                'fee_amount' => number_format($fee_amount, 2, '.', ''),//结算服务费/手续费
                'mdiscount_amount' => number_format($mdiscount_amount, 2, '.', ''),
            ];
            //附加流水详情
            if ($return_type == "02") {
                $obj = DB::table('orders');
                $obj = $obj->where($where)
                    ->whereIn('user_id', $user_ids)
                    ->orderBy('updated_at', 'desc');
                $this->t = $obj->count();
                $data['order_list'] = $this->page($obj)->get();
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
}