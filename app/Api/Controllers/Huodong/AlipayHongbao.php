<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/9/3
 * Time: 上午10:10
 */

namespace App\Api\Controllers\Huodong;


use App\Api\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\AlipayHongba;
use App\Models\Store;
use Illuminate\Http\Request;

class AlipayHongbao extends BaseController
{

    public function add(Request $request)
    {
        try {
            $public = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $store_name = $request->get('store_name', '');
            $content = $request->get('content', '');
            $remark = $request->get('remark', '');

            if ($public->type == "merchant") {
                $user_id = $public->merchant_id;
                $user_name = $public->name;

            }

            if ($public->type == "user") {
                $user_id = $public->user_id;
                $user_name = $public->name;
            }

            $check_data = [
                'content' => '红包内容'
            ];

            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }

            $data = [
                'store_id' => $store_id,
                'store_name' => $store_name,
                'create_user_id' => $user_id,
                'create_user_name' => $user_name,
                'content' => $content,
                'remark' => $remark,
                'model_type' => $public->type
            ];
            $obj = AlipayHongba::create($data);


            $this->status = 1;
            $this->message = '添加成功';
            return $this->format($data);


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }


    }


    public function del(Request $request)
    {
        try {
            $public = $this->parseToken();
            $id = $request->get('id', '');

            $check_data = [
                'id' => '红包id'
            ];

            $check = $this->check_required($request->except(['token']), $check_data);
            if ($check) {
                return json_encode([
                    'status' => 2,
                    'message' => $check
                ]);
            }


            $where[] = ['model_type', '=', $public->type];

            if ($id) {
                $where[] = ['id', '=', $id];
            }

            $obj = AlipayHongba::where($where)->delete();

            $data = [
                'id' => $id
            ];

            $this->status = 1;
            $this->message = '删除成功';
            return $this->format($data);


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }


    }

    public function get_list(Request $request)
    {
        try {
            $public = $this->parseToken();
            $store_id = $request->get('store_id', '');
            $content = $request->get('content', '');
            $where = [];

            if ($public->type == "merchant") {
                $user_id = $public->merchant_id;
            }

            if ($public->type == "user") {
                $user_id = $public->user_id;
            }

            $create_user_id = $request->get('create_user_id', $user_id);


            $where[] = ['model_type', '=', $public->type];

            if ($store_id) {
                $where[] = ['store_id', '=', $store_id];

            }

            if ($create_user_id) {
                $where[] = ['create_user_id', '=', $create_user_id];
            }


            if ($content) {
                $where[] = ['content', '=', $content];
            }


            $obj = AlipayHongba::where($where);
            $this->t = $obj->count();
            $data = $this->page($obj)->get();
            $this->status = 1;
            $this->message = '数据返回成功';
            return $this->format($data);


        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }


    }


}