<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/12/4
 * Time: 10:53 AM
 */

namespace App\Api\Controllers\Huodong;


use App\Api\Controllers\BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SelectController extends BaseController
{


    //拉新活动列表
    public function hd_list(Request $request)
    {
        try {
            $public = $this->parseToken();
            $config_id = $public->config_id;
            $user_type = $public->type;
            $source = $request->get('source', '');
            $time_start = date("Y-m-01 00:00:00", time());//这个月第一天
            $time_end = date("Y-m-d H:i:s", time());//
            $month = date("Ym", time());
            $user_id = '';
            $phone=$public->phone;

            if ($user_type == "merchant") {
                $user_id = $public->merchant_id;
                //  $user = Merchant::where('id', $user_id)->first();
            }

            if ($user_type == "user") {
                $user_id = $public->user_id;
                //  $user = User::where('id', $user_id)->first();
            }

            //返回活动列表
            if ($source) {
                $hd_list = DB::table('hd_list')->where('source', $source)
                    ->where('config_id', $config_id)
                    ->first();

                if (!$hd_list) {
                    $config_id = '1234';//读取系统平台的配置ID
                    $hd_list = DB::table('hd_list')->where('source', $source)
                        ->where('config_id', $config_id)
                        ->first();
                }

                //带上推广者的推广二维码
                $hd_list->code=$hd_list->code.$phone;

                //收益详细
                $hd_profit_list_obj = DB::table('hd_profit_list')
                    ->where('user_type', $user_type)
                    ->where('user_id', $user_id)
                    ->where('source', $source)
                    ->where('updated_at', '>=', $time_start)
                    ->where('updated_at', '<=', $time_end);

                $money = $hd_profit_list_obj->sum('money');
                $number = $hd_profit_list_obj->sum('number');

                $data = $hd_list;
                $data->month = $month;
                $data->money = '' . $money . '';
                $data->number = '' . $number . '';


            } else {
                $data = DB::table('hd_list')
                    ->where('config_id', $config_id)
                    ->select('name', 'title', 'desc', 'source')
                    ->get();
                if ($data->isEmpty()) {
                    $data = DB::table('hd_list')
                        ->where('config_id', '1234')
                        ->select('name', 'title', 'desc', 'source')
                        ->get();
                }
            }


            return json_encode(['status' => 1, 'message' => '数据返回成功', 'data' => $data]);


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }
    }


    //收益历史列表
    public function old_hd_list(Request $request)
    {

        try {
            $public = $this->parseToken();
            $user_type = $public->type;
            $source = $request->get('source', '');
            $time_start = date("Y-m-01 00:00:00", time());//这个月第一天
            $time_end = date("Y-m-d H:i:s", time());//
            $month = date("Ym", time());
            $user_id = '';

            if ($user_type == "merchant") {
                $user_id = $public->merchant_id;
                //  $user = Merchant::where('id', $user_id)->first();
            }

            if ($user_type == "user") {
                $user_id = $public->user_id;
                //  $user = User::where('id', $user_id)->first();
            }
            $hd_profit_list_obj = DB::table('hd_profit_list')
                ->where('user_type', $user_type)
                ->where('user_id', $user_id)
                ->where('source', $source)
                ->select('month', 'money', 'number');


            $this->t = $hd_profit_list_obj->count();
            $data = $this->page($hd_profit_list_obj)->get();
            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }


    //
    public function get_info(Request $request)
    {

        try {
            $public = $this->parseToken();
            $user_type = $public->type;
            $source = $request->get('source', '');
            $get_info = [
                [
                    'info' => '1、累计奖励=开户人数x奖励金额',
                ],
                [
                    'info' => '2、30元/户，日均达到100人以上，将按照40/户计算',
                ],
                [
                    'info' => '3、实际结算收益金额按照京东后台数据不定期更新,可在赏金功能中提现',
                ],
            ];
            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($get_info);


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }
    }
}