<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/12/24
 * Time: 10:48 PM
 */

namespace App\Api\Controllers\User;


use App\Api\Controllers\BaseController;
use App\Models\StorePayWay;
use App\Models\UserStoreSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SetController extends BaseController
{


    //扣款顺序列表
    public function pay_ways_sort(Request $request)
    {
        try {
            $user = $this->parseToken();//
            $store_id = $request->get('store_id', '');

            if ($store_id) {
                $alipay = DB::table('store_pay_ways')
                    ->where('store_id', $store_id)
                    ->where('ways_source', 'alipay')
                    ->select('id as store_pay_ways_id', 'ways_desc', 'ways_type', 'store_id', 'sort', 'ways_source')
                    ->where('status', 1)
                    ->orderBy('store_pay_ways.sort', 'asc')
                    ->get();
                $weixin = DB::table('store_pay_ways')
                    ->where('store_id', $store_id)
                    ->where('ways_source', 'weixin')
                    ->select('id as store_pay_ways_id', 'ways_desc', 'ways_type', 'store_id', 'sort', 'ways_source')
                    ->where('status', 1)
                    ->orderBy('store_pay_ways.sort', 'asc')
                    ->get();


                $jd = DB::table('store_pay_ways')
                    ->where('store_id', $store_id)
                    ->where('ways_source', 'jd')
                    ->select('id as store_pay_ways_id', 'ways_desc', 'ways_type', 'store_id', 'sort', 'ways_source')
                    ->where('status', 1)
                    ->orderBy('store_pay_ways.sort', 'asc')
                    ->get();
                $unionpay = DB::table('store_pay_ways')
                    ->where('store_id', $store_id)
                    ->where('ways_source', 'unionpay')
                    ->select('id as store_pay_ways_id', 'ways_desc', 'ways_type', 'store_id', 'sort', 'ways_source')
                    ->where('status', 1)
                    ->orderBy('store_pay_ways.sort', 'asc')
                    ->get();

                return json_encode(['status' => 1, 'is_open' => 1, 'message' => '数据返回成功', 'data' => [
                    'ailpay' => $alipay,
                    'weixin' => $weixin,
                    'jd' => $jd,
                    'unionpay' => $unionpay
                ]
                ]);

            } else {
                return json_encode(['status' => 2, 'message' => '门店ID必须传']);
            }

        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }
    }


    //扣款顺序初始化
    public function pay_ways_sort_start(Request $request)
    {
        try {
            $user = $this->parseToken();//
            $store_id = $request->get('store_id', '');
            $StorePayWay = StorePayWay::where('store_id', $store_id)
                ->where('status', 1)
                ->select('ways_source', 'sort', 'id')
                ->get();

            $a = 0;
            $b = 0;
            $c = 0;
            $d = 0;

            foreach ($StorePayWay as $k => $v) {
                //支付宝
                if ($v['ways_source'] == "alipay") {
                    $a = $a + 1;
                    StorePayWay::where('id', $v->id)->update(['sort' => $a]);
                }
                //微信
                if ($v['ways_source'] == "weixin") {
                    $b = $b + 1;
                    StorePayWay::where('id', $v->id)->update(['sort' => $b]);


                }

                //jd
                if ($v['ways_source'] == "jd") {

                    $c = $c + 1;
                    StorePayWay::where('id', $v->id)->update(['sort' => $c]);

                }

                //unionpay
                if ($v['ways_source'] == "unionpay") {
                    $d = $d + 1;
                    StorePayWay::where('id', $v->id)->update(['sort' => $d]);
                }

            }

            return json_encode(['status' => 1, 'message' => '初始化成功']);


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }
    }

    //扣款顺序修改
    public function pay_ways_sort_edit(Request $request)
    {
        try {
            $user = $this->parseToken();//
            $store_pay_ways_id = (int)$request->get('store_pay_ways_id');
            $store_id = $request->get('store_id');
            $new_sort = $request->get('new_sort');
            $check_data = [
                'store_pay_ways_id' => '通道类型id',
                'new_sort' => '新位置',
            ];

            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }

            $ch_storePayWay = StorePayWay::where('id', $store_pay_ways_id)->first();

            $store_id = $ch_storePayWay->store_id;
            $ways_source = $ch_storePayWay->ways_source;
            $sort = $ch_storePayWay->sort;//旧的位置


            if ((int)$sort == (int)$new_sort) {
                return json_encode(['status' => 2, 'message' => '位置没有任何改动']);

            }

            $old_StorePayWay = StorePayWay::where('sort', $new_sort)
                ->where('store_id', $store_id)
                ->where('ways_source', $ways_source)
                ->first();

            //开启事务
            try {
                DB::beginTransaction();

                //先零时配置一个
                $old_StorePayWay->update([
                    'sort' => time(),
                ]);

                $ch_storePayWay->update([
                    'sort' => $new_sort,
                ]);
                $ch_storePayWay->save();
                $old_StorePayWay->save();


                //修正
                $old_StorePayWay->update([
                    'sort' => $sort,
                ]);
                $old_StorePayWay->save();


                DB::commit();
            } catch (\Exception $e) {
                DB::rollBack();
                return json_encode(['status' => 2, 'message' => $e->getMessage()]);
            }


            return json_encode(['status' => 1, 'message' => '顺序修改成功']);

        } catch (\Exception $exception) {

            return json_encode(['status' => 0, 'message' => $exception->getMessage()]);
        }
    }

    //查看是否需要审核商户
    public function user_store_set_status(Request $request)
    {
        try {
            $user = $this->parseToken();//
            $user_id = $request->get('user_id', '');
            $status_check = $request->get('status_check', '');
            $admin_status_check = $request->get('admin_status_check', '');

            if ($user_id == "") {
                return json_encode(['status' => 2, 'message' => 'user_id必须传']);
            }

            $UserStoreSet = UserStoreSet::where('user_id', $user_id)->first();

            //查看
            if ($status_check == "") {
                if ($UserStoreSet) {
                    return json_encode(['status' => 1, 'data' => $UserStoreSet]);
                } else {
                    return json_encode(['status' => 1, 'data' => [
                        'user_id' => $user_id,
                        'status_check' => 0,
                        'admin_status_check' => 0,
                    ]
                    ]);
                }
            }

            $data = [
                'user_id' => $user_id,
                'status_check' => $status_check,
                'admin_status_check' => $admin_status_check,
            ];

            if ($UserStoreSet) {
                $UserStoreSet->update($data);
                $UserStoreSet->save();
            } else {
                UserStoreSet::create($data);
            }


            return json_encode(['status' => 1, 'message' => '修改成功']);


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }
    }

}