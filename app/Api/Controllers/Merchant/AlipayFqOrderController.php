<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/6/22
 * Time: 下午5:09
 */

namespace App\Api\Controllers\Merchant;


use App\Api\Controllers\BaseController;
use App\Models\AlipayHbOrder;
use App\Models\MerchantStore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AlipayFqOrderController extends BaseController
{


    public function order(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $merchant_id = $request->get('merchant_id', '');
            $pay_status = $request->get('pay_status', '');
            $time_start = $request->get('time_start', '');
            $time_end = $request->get('time_end', '');

            $hb_fq_num = $request->get('hb_fq_num', '');
            $out_trade_no = $request->get('out_trade_no', '');

            $obj = DB::table('alipay_hb_orders');
            $where = [];

            //没有传入收银员id 角色是收银员 只返回自己
            if ($merchant_id== '') {
                $merchant_id = $merchant->merchant_id;
            }

            if ($store_id == "") {
                $MyBankStore = MerchantStore::where('merchant_id', $merchant_id)
                    ->orderBy('created_at', 'asc')
                    ->first();
                $store_id = $MyBankStore->store_id;
            }


            if ($out_trade_no) {
                $where[] = ['out_trade_no', 'like', '%' . $out_trade_no . '%'];
            }
            if ($hb_fq_num) {
                $where[] = ['hb_fq_num', '=', $hb_fq_num];
            }

            //收银员
            if ($merchant->merchant_type == 2) {
                $where[] = ['merchant_id', '=', $merchant->merchant_id];
            }

            //是否传收银员ID
            if ($request->get('merchant_id', '')) {
                $where[] = ['merchant_id', '=', $request->get('merchant_id', '')];
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

    //花呗分期退款
    public function refund(Request $request)
    {

        try {
            $merchant = $this->parseToken();
            $out_trade_no = $request->get('out_trade_no', '');
            $refund_amount = $request->get('refund_amount', '');

            $Order = AlipayHbOrder::where('out_trade_no', $out_trade_no)->first();
            if (!$Order) {
                $this->status = 2;
                $this->message = '订单号不存在';
                return $this->format();
            }

            $update = [
                'pay_status' => 6,
                'pay_status_desc' => '已退款'
            ];
            $Order->update($update);
            $Order->save();


            $data = [
                'refund_amount' => $refund_amount,
                'out_trade_no' => $out_trade_no,
            ];


            $this->status = 1;
            $this->message = '退款成功';

            return $this->format($data);
        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }
    }

}