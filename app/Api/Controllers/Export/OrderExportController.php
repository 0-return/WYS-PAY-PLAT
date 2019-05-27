<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2019/1/12
 * Time: 1:12 PM
 */

namespace App\Api\Controllers\Export;


use App\Api\Controllers\BaseController;
use App\Models\MerchantStore;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderExportController extends BaseController
{


    //商户导出
    public function MerchantOrderExcelDown(Request $request)
    {

        $merchant = $this->parseToken();
        $store_id = $request->get('store_id', '');
        $merchant_id = $request->get('merchant_id', '');
        $pay_status = $request->get('pay_status', '');
        $ways_source = $request->get('ways_source', '');
        $ways_type = $request->get('ways_type', '');
        $time_start = $request->get('time_start', '');
        $time_end = $request->get('time_end', '');
        $out_trade_no = $request->get('out_trade_no', '');
        $trade_no = $request->get('trade_no', '');
        $where_desc = '';

        $where = [];
        $store_ids = [];
        if ($out_trade_no && $out_trade_no != "undefined") {
            $where[] = ['out_trade_no', 'like', '%' . $out_trade_no . '%'];
            $where_desc = $where_desc . ';订单编号：' . $out_trade_no;
        }

        if ($trade_no && $trade_no != "undefined") {
            $where[] = ['trade_no', 'like', '%' . $trade_no . '%'];
            $where_desc = $where_desc . ';订单编号：' . $trade_no;

        }
        //收银员
        if ($merchant->merchant_type == 2) {
            $where[] = ['merchant_id', '=', $merchant->merchant_id];
            $where_desc = $where_desc . ';收银员ID：' . $merchant->merchant_id;

        }

        //是否传收银员ID
        if ($merchant_id) {
            $where[] = ['merchant_id', '=', $merchant_id];
            $where_desc = $where_desc . ';收银员ID：' . $merchant->merchant_id;

        }
        if (1) {
            $where[] = ['ways_type', '!=', '2005'];
        }
        if ($pay_status) {
            $where[] = ['pay_status', '=', $pay_status];
            $where_desc = $where_desc . ';支付状态：' . $pay_status;

        }
        if ($store_id) {
            $store_ids = [
                [
                    'store_id' => $store_id,
                ]
            ];
        } else {
            $MerchantStore = MerchantStore::where('merchant_id', $merchant->merchant_id)
                ->select('store_id')
                ->get();

            if (!$MerchantStore->isEmpty()) {
                $store_ids = $MerchantStore->toArray();
            }
        }
        if ($ways_source) {
            $where[] = ['ways_source', '=', $ways_source];
            $where_desc = $where_desc . ';支付方式：' . $ways_source;

        }
        if ($ways_type) {
            $where[] = ['ways_type', '=', $ways_type];
            $where_desc = $where_desc . ';支付类型：' . $ways_type;

        }
        if ($time_start) {
            $time_start = date('Y-m-d H:i:s', strtotime($time_start));
            $where[] = ['created_at', '>=', $time_start];
            $where_desc = $where_desc . ';订单开始时间：' . $time_start;

        }
        if ($time_end) {
            $time_end = date('Y-m-d H:i:s', strtotime($time_end));
            $where[] = ['created_at', '<=', $time_end];
            $where_desc = $where_desc . ';订单结束时间：' . $time_end;

        }


        $obj = Order::where($where)
            ->whereIn('store_id', $store_ids)
            ->orderBy('updated_at', 'desc')
            ->select(
                'store_id',
                'store_name',
                'merchant_name',
                'out_trade_no',
                'total_amount',
                'rate',//商户交易时的费率
                'fee_amount',
                'ways_source_desc',
                'pay_status',//系统状态
                'pay_status_desc',
                'pay_time',
                'company',//通道方
                "remark"
            )
            ->get();


        $filename = '订单导出.csv';
        $s_array = ['筛选条件：' . $where_desc];
        $tileArray = ['门店ID', '门店名称', '收银员', '订单号', '订单金额', '费率', '手续费', '支付方式', '支付状态', '支付状态说明', '付款时间', '通道', '备注'];

        $dataArray = $obj->toArray();


        return $this->exportToExcel($filename, $s_array, $tileArray, $dataArray);

    }

    //服务商导出
    public function UserOrderExcelDown(Request $request)
    {
        $user = $this->parseToken();
        $store_id = $request->get('store_id', '');
        $user_id = $request->get('user_id', '');
        $pay_status = $request->get('pay_status', '');
        $ways_source = $request->get('ways_source', '');
        $company = $request->get('company', '');
        $ways_type = $request->get('ways_type', '');
        $time_start = $request->get('time_start', '');
        $time_end = $request->get('time_end', '');
        $out_trade_no = $request->get('out_trade_no', '');
        $trade_no = $request->get('trade_no', '');
        $where_desc = '';
        $where = [];

        if (1) {
            $where[] = ['orders.ways_type', '!=', '2005'];
        }
        if ($user_id == "") {
            $user_id = $user->user_id;
        }
        $user_ids = $this->getSubIds($user_id);

        if ($pay_status) {
            $where[] = ['orders.pay_status', '=', $pay_status];
            $where_desc = $where_desc . ';支付状态：' . $pay_status;

        }
        if ($store_id) {
            $where[] = ['orders.store_id', '=', $store_id];
            $where_desc = $where_desc . ';门店ID：' . $store_id;

        }
        if ($company) {
            $where[] = ['orders.company', '=', $company];
            $where_desc = $where_desc . ';通道：' . $company;

        }
        if ($ways_source) {
            $where[] = ['orders.ways_source', '=', $ways_source];
            $where_desc = $where_desc . ';支付方式：' . $ways_source;

        }
        if ($ways_type) {
            $where[] = ['orders.ways_type', '=', $ways_type];
            $where_desc = $where_desc . ';支付类型：' . $ways_type;

        }
        if ($time_start) {
            $time_start = date('Y-m-d H:i:s', strtotime($time_start));
            $where[] = ['orders.created_at', '>=', $time_start];
            $where_desc = $where_desc . ';订单开始时间：' . $time_start;

        }
        if ($time_end) {
            $time_end = date('Y-m-d H:i:s', strtotime($time_end));
            $where[] = ['orders.created_at', '<=', $time_end];
            $where_desc = $where_desc . ';订单结束时间：' . $time_end;

        }

        if ($out_trade_no && $out_trade_no != "undefined") {
            $where[] = ['orders.out_trade_no', 'like', '%' . $out_trade_no . '%'];
            $where_desc = $where_desc . ';订单编号：' . $out_trade_no;

        }

        if ($trade_no && $out_trade_no != "undefined") {
            $where[] = ['orders.trade_no', 'like', '%' . $trade_no . '%'];
            $where_desc = $where_desc . ';订单编号：' . $out_trade_no;

        }

        $obj = Order::where($where)
            ->join('users', 'orders.user_id', '=', 'users.id')
            ->whereIn('orders.user_id', $user_ids)
            ->orderBy('orders.updated_at', 'desc')
            ->select(
                'users.name',
                'orders.store_id',
                'orders.store_name',
                'orders.merchant_name',
                'orders.out_trade_no',
                'orders.total_amount',
                'orders.rate',//商户交易时的费率
                'orders.fee_amount',
                'orders.ways_source_desc',
                'orders.pay_status',//系统状态
                'orders.pay_status_desc',
                'orders.pay_time',
                'orders.company',//通道方
                "orders.remark"
            )
            ->get();

        $filename = '订单导出.csv';
        $s_array = ['筛选条件：' . $where_desc];
        $tileArray = ['代理商ID', '门店ID', '门店名称', '收银员', '订单号', '订单金额', '费率', '手续费', '支付方式', '支付状态', '支付状态说明', '付款时间', '通道', '备注'];

        $dataArray = $obj->toArray();


        return $this->exportToExcel($filename, $s_array, $tileArray, $dataArray);

    }


    public function exportToExcel($filename, $s_array, $tileArray = [], $dataArray = [])
    {
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 0);
        ob_end_clean();
        ob_start();
        header("Content-Type: text/csv");
        header("Content-Disposition:filename=" . $filename);
        $fp = fopen('php://output', 'w');
        fwrite($fp, chr(0xEF) . chr(0xBB) . chr(0xBF));//转码 防止乱码(比如微信昵称(乱七八糟的))

        fputcsv($fp, $s_array);
        fputcsv($fp, []);
        fputcsv($fp, $tileArray);
        $index = 0;
        foreach ($dataArray as $item) {
            $item['rate'] = $item['rate'] / 100;
            $index++;
            fputcsv($fp, $item);
        }

        ob_flush();
        flush();
        ob_end_clean();
    }

}