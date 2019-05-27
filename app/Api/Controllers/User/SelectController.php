<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/12/21
 * Time: 2:34 PM
 */

namespace App\Api\Controllers\user;


use App\Api\Controllers\BaseController;
use App\Models\Store;
use App\Models\StoreMonthOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SelectController extends BaseController
{


    public function s_code_url(Request $request)
    {
        $message = '暂时无法添加,请使用其他方式';
        return view('errors.page_errors', compact('message'));

    }

    public function sub_code_url(Request $request)
    {
        $message = '暂时无法添加,请使用其他方式';
        return view('errors.page_errors', compact('message'));

    }


    //交易排行榜
    public function ranking(Request $request)
    {

        try {
            $user = $this->parseToken();//
            $type = $request->get('type', '1');//1 交易笔数 由大到小
            $month = $request->get('month', '');
            $obj = DB::table('stores');

            //1 交易笔数
            if ($type == "1" || $type == "2") {
                $sort = 'desc';
                if ($type == "2") {
                    $sort = 'asc';
                }

                $obj = $obj->join('store_month_counts', 'stores.store_id', 'store_month_counts.store_id')
                    ->where('store_month_counts.month', $month)
                    ->orderBy('store_month_counts.total_count', $sort)
                    ->select('store_month_counts.*', 'stores.store_name');
            } else {
                $sort = 'desc';
                if ($type == "4") {
                    $sort = 'asc';
                }
                $obj = $obj->join('store_month_counts', 'stores.store_id', 'store_month_counts.store_id')
                    ->where('store_month_counts.month', $month)
                    ->orderBy('store_month_counts.total_amount', $sort)
                    ->select('store_month_counts.*', 'stores.store_name');

            }


            $this->t = $obj->count();
            $data = $this->page($obj)->get();
            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }


    //打款查询
    public function dk_select(Request $request)
    {

        try {
            $user = $this->parseToken();//
            $store_id = $request->get('store_id', '');//1 交易笔数 由大到小
            $time = $request->get('time', '');
            $company = $request->get('company', '');

            $check_data = [
                'company' => '通道类型',
                'time' => '打款时间',
                'store_id' => '门店ID',

            ];

            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }

            $store = Store::where('store_id', $store_id)
                ->select('store_name')
                ->first();
            if (!$store) {
                return json_encode(['status' => 2, 'message' => '门店ID不存在']);

            }
            $store_name = $store->store_name;

            return json_encode([
                'status' => 1,
                'message' => '查询成功',
                'data' => [
                    'store_name' => $store_name,
                    'time' => $time,
                    'total_amount' => '0.01',
                    'dk_time' => $time,
                    'dk_desc' => '打款成功',

                ]
            ]);


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }


}