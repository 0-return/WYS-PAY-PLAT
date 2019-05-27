<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/6/22
 * Time: 下午5:09
 */

namespace App\Api\Controllers\User;


use App\Api\Controllers\BaseController;
use App\Models\AlipayHbOrder;
use App\Models\MerchantStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlipayFqOrderController extends BaseController
{


    public function order(Request $request)
    {
        try {
            $user = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $user_id = $request->get('user_id', $user->user_id);
            $pay_status = $request->get('pay_status', '');
            $time_start = $request->get('time_start', '');
            $time_end = $request->get('time_end', '');
            $hb_fq_num = $request->get('hb_fq_num', '');
            $out_trade_no = $request->get('out_trade_no', '');


            $obj = DB::table('alipay_hb_orders');
            $where = [];

            if ($out_trade_no) {
                $where[] = ['out_trade_no', 'like', '%' . $out_trade_no . '%'];
            }
            if ($user_id) {
                $where[] = ['user_id', '=', $user_id];
            }

            if ($hb_fq_num) {
                $where[] = ['hb_fq_num', '=', $hb_fq_num];
            }
            if ($pay_status) {
                $where[] = ['pay_status', '=', $pay_status];
            }
            if ($store_id) {
                $where[] = ['store_id', '=', $store_id];
            }

            if ($time_start) {
                $where[] = ['updated_at', '>=', $time_start];
            }
            if ($time_end) {
                $where[] = ['updated_at', '<=', $time_end];
            }

            $obj = $obj->where($where);
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
            $merchant = $this->parseToken();
            $out_trade_no = $request->get('out_trade_no', '');
            $data = AlipayHbOrder::where('out_trade_no', $out_trade_no)->first();
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


}