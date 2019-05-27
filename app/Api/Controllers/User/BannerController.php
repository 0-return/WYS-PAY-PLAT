<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/7/30
 * Time: 下午6:14
 */

namespace App\Api\Controllers\User;


use App\Api\Controllers\BaseController;
use App\Models\Banner;
use App\Models\NoticeNew;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BannerController extends BaseController
{


    public function banners(Request $request)
    {

        try {
            $user = $this->parseToken();
            $user_id = $user->user_id;
            $banner_time_s = $request->get('banner_time_s', '');
            $banner_time_e = $request->get('banner_time_e', '');
            $type = $request->get('type', '');

            if ($type) {

            }

            $obj = DB::table('banners');

            $where[] = ['user_id', '=', $user_id];

            if ($banner_time_s) {
                $where[] = ['banner_time_s', '>=', $banner_time_s];
            }

            if ($banner_time_e) {
                $where[] = ['banner_time_e', '<=', $banner_time_e];
            }
            if ($type) {
                $where[] = ['type', '=', $type];

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


    public function add_banners(Request $request)
    {

        try {
            $user = $this->parseToken();
            $data = $request->except(['token']);
            $type = $request->get('type');
            $check_data = [
                'type' => '位置',
                'type_desc' => '位置说明',
                'title' => '标题',
                'img_url' => '图片链接',
                'sort' => '排序',
                'action_url' => '跳转地址',
                'status' => '状态',
                'banner_desc' => '描述',
                'banner_time_s' => '开始时间',
                'banner_time_e' => '结束时间',
            ];
            $check = $this->check_required($data, $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }

            $type_arr = explode(',', $type);
            foreach ($type_arr as $k => $v) {
                $data['type'] = $v;
                if ($v == "merchant") {
                    $data['type_desc'] = '商户app';
                } else {
                    $data['type_desc'] = '服务商app';

                }
                $data['config_id'] = $user->config_id;
                $data['user_id'] = $user->user_id;

                Banner::create($data);
            }


            $this->status = 1;
            $this->message = '添加成功';
            return $this->format();

        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }
    }


    public function del_banners(Request $request)
    {

        try {
            $user = $this->parseToken();
            $id = $request->get('id');
            Banner::where('id', $id)->delete();
            $this->status = 1;
            $this->message = '删除成功';
            return $this->format();

        } catch (\Exception $exception) {
            $this->status = -1;
            $this->message = $exception->getMessage();
            return $this->format();
        }
    }

    //banner 位置
    public function banner_type(Request $request)
    {

        try {
            $data = [
                [
                    'type' => 'merchant',
                    'type_desc' => '商户app',
                ], [
                    'type' => 'user',
                    'type_desc' => '服务商app',
                ]
            ];
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