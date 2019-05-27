<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/8/24
 * Time: 上午11:52
 */

namespace App\Api\Controllers\Ad;


use App\Api\Controllers\BaseController;
use App\Models\Ad;
use App\Models\Merchant;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdController extends BaseController
{

//广告列表
    public function ad_lists(Request $request)
    {

        try {
            $public = $this->parseToken();
            $user_id = '';
            if ($public->type == "merchant") {
                $user_id = $public->merchant_id;

            }

            if ($public->type == "user") {
                $user_id = $public->user_id;

            }
            $obj = DB::table('ads')
                ->whereIn('created_id', $this->getSubIds($user_id))
                ->where('model_type', $public->type);


            $this->message = '数据返回成功';
            $this->t = $obj->count();
            $data = $this->page($obj)->get();


            return $this->format($data);

        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }


    public function ad_create(Request $request)
    {

        try {
            $public = $this->parseToken();
            $config_id = $public->config_id;
            $user_id = '';
            if ($public->type == "merchant") {
                $user_id = $public->merchant_id;

            }
            if ($public->type == "user") {
                $user_id = $public->user_id;
            }

            $ad_p_id = $request->get('ad_p_id', '');
            $title = $request->get('title', '');
            $ad_p_desc = $request->get('ad_p_desc', '');
            $user_ids = $request->get('user_ids', '');
            $store_key_ids = $request->get('store_key_ids', '');
            $s_time = $request->get('s_time', '');
            $e_time = $request->get('e_time', '');
            $imgs = $request->get('imgs', '');
            $copy_content = $request->get('copy_content', '');
            $ad_p_ids = explode(',', $ad_p_id);

            $check_data = [
                'title' => '标题',
                'ad_p_id' => '位置',
                'imgs' => '图片',
                's_time' => '投放开始时间',
                'e_time' => '投放结束时间',
                'copy_content' => '复制内容',

            ];
            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }


            foreach ($ad_p_ids as $k => $v) {
                $ad_p_desc = "";
                if ($v == "1") {
                    $ad_p_desc = "支付宝成功页";
                }
                if ($v == "2") {
                    $ad_p_desc = "微信支付成功页";
                }
                if ($v == "3") {
                    $ad_p_desc = "支付宝失败页面";
                }
                if ($v == "4") {
                    $ad_p_desc = "微信支付失败页面";
                }

                $data = [
                    'title' => $title,
                    'config_id' => $config_id,
                    'ad_p_id' => $v,
                    'model_type' => $public->type,
                    'created_id' => $user_id,
                    'ad_p_desc' => $ad_p_desc,
                    'user_ids' => $user_ids,
                    'store_key_ids' => $store_key_ids,
                    's_time' => $s_time,
                    'e_time' => $e_time,
                    'imgs' => $imgs,
                    'copy_content' => $copy_content,

                ];
                Ad::create($data);

            }


            return json_encode([
                'status' => 1,
                'message' => '添加成功',
            ]);

        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }
    }

    public function ad_up(Request $request)
    {

        try {
            $public = $this->parseToken();

            if ($public->type == "merchant") {
                $user_id = $public->merchant_id;

            }

            if ($public->type == "user") {
                $user_id = $public->user_id;

            }
            $id = $request->get('id', '');
            $ad_p_id = $request->get('ad_p_id', '');
            $title = $request->get('title', '');
            $ad_p_desc = $request->get('ad_p_desc', '');
            $user_ids = $request->get('user_ids', '');
            $store_key_ids = $request->get('store_key_ids', '');
            $s_time = $request->get('s_time', '');
            $e_time = $request->get('e_time', '');
            $imgs = $request->get('imgs', '');
            $copy_content = $request->get('copy_content', '');
            $ad_p_ids = explode(',', $ad_p_id);

            if (count($ad_p_ids) > 1) {
                return json_encode([
                    'status' => 2,
                    'message' => '修改不支持添加多个类型',
                ]);
            }
            $data = [
                'title' => $title,
                'ad_p_id' => $ad_p_id,
                'model_type' => $public->type,
                'created_id' => $user_id,
                'ad_p_desc' => $ad_p_desc,
                'user_ids' => $user_ids,
                'store_key_ids' => $store_key_ids,
                's_time' => $s_time,
                'e_time' => $e_time,
                'imgs' => $imgs,
                'copy_content' => $copy_content,

            ];

            Ad::where('id', $id)->update($data);

            return json_encode([
                'status' => 1,
                'message' => '修改成功',
            ]);

        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }
    }


    public function ad_del(Request $request)
    {

        try {
            $public = $this->parseToken();


            if ($public->type == "merchant") {
                $user_id = $public->merchant_id;

            }

            if ($public->type == "user") {
                $user_id = $public->user_id;

            }
            $id = $request->get('id', '');


            Ad::where('id', $id)->delete();

            return json_encode([
                'status' => 1,
                'message' => '删除成功',
            ]);

        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }
    }


    public function ad_p_id(Request $request)
    {
        try {
            $public = $this->parseToken();
            $user_id = '';
            if ($public->type == "merchant") {
                $user_id = $public->merchant_id;

            }

            if ($public->type == "user") {
                $user_id = $public->user_id;

            }

            $data = [
                [
                    'ad_p_id' => '1',
                    'ad_p_desc' => '支付宝成功页'
                ],
                [
                    'ad_p_id' => '2',
                    'ad_p_desc' => '微信成功页'
                ], [
                    'ad_p_id' => '3',
                    'ad_p_desc' => '支付宝失败页'
                ],
                [
                    'ad_p_id' => '4',
                    'ad_p_desc' => '微信失败页'
                ]
            ];

            $this->message = '数据返回成功';
            return $this->format($data);

        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }


    public function ad_info(Request $request)
    {
        try {
            $public = $this->parseToken();

            $id = $request->get('id', '');
            $ad = Ad::where('id', $id)->first();

            $user_ids = $ad->user_ids;
            $store_key_ids = $ad->store_key_ids;

            $user_ids_arr = explode(",", $user_ids);
            $store_key_ids_arr = explode(",", $store_key_ids);


            if ($public->type == "merchant") {
                $user_id = $public->merchant_id;
                $user = Merchant::whereIn('id', $user_ids_arr)
                    ->select('name')
                    ->get();
            }

            if ($public->type == "user") {
                $user_id = $public->user_id;
                $user = User::whereIn('id', $user_ids_arr)
                    ->select('name')
                    ->get();
            }

            $Store = Store::whereIn('id', $store_key_ids_arr)
                ->select('store_short_name')
                ->get();

            $user_names = '';
            if (!$user->isEmpty()) {
                foreach ($user as $k => $v) {
                    $user_names = $user_names . $v->name . ',';
                }
            }


            $store_names = '';
            if (!$Store->isEmpty()) {
                foreach ($Store as $k => $v) {
                    $store_names = $store_names . $v->store_short_name . ',';
                }
            }

            $ad->store_names = $store_names;
            $ad->user_names = $user_names;


            $this->message = '数据返回成功';
            return $this->format($ad);

        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }


}