<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/8/24
 * Time: 上午11:52
 */

namespace App\Api\Controllers\Adcate;

use App\Api\Controllers\BaseController;
use App\Models\Adcate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdcateController extends BaseController
{

    /**
     * note 广告分类列表
     * auth YW
     * date 2019-05-27
     * return json
     */
    public function adcate_lists(Request $request)
    {
        try{
            $public = $this->parseToken();

            $obj = DB::table('ads_cate');

            $this->message = '数据返回成功';
            $this->t = $obj->count();
            $data = $this->page($obj)->get();
            return $this->format($data);

        }catch (\Exception $exception){
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }
    }
    /**
     * note 删除广告分类
     * auth YW
     * date 2019-05-27
     * return json
     */
    public function adcate_del(Request $request)
    {
        try{
            $public = $this->parseToken();
            if ($public->type == "merchant")
            {
                $user_id = $public->merchant_id;
            }
            $id = $request->get('id');

            //检查分类下是否有数据
            $res = DB::table('ads')->where('ad_p_id',$id)->get();

            if ($res->count() > 0)
            {
                return json_encode([
                    'status' => 2,
                    'message' => '该分类下还有数据，无法删除',
                ]);
            }else{
                $res = DB::table('ads_cate')->where('id',$id)->delete();

                if ($res)
                {
                    return json_encode([
                        'status' => 1,
                        'message' => '操作成功',
                    ]);
                }else{
                    return json_encode([
                        'status' => 2,
                        'message' => '操作失败',
                    ]);
                }
            }
        }catch (\Exception $exception)
        {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }
    /**
     * note 添加广告分类
     * auth YW
     * date 2019-05-27
     * return json
     */
    public function adcate_add(Request $request)
    {

        try{
            $public = $this->parseToken();

            if ($public->type == "merchant")
            {
                $user_id = $public->user_id;
            }
            if ($public->type == "user") {
                $user_id = $public->user_id;
            }
            $config_id = $public->config_id;
            $check_data = [
                'title' => '标题',
            ];

            $title = $request->post('title');
            $data = [
                'title' => $title,
                'user_id' => $user_id,
                'unique' => time().mt_rand(1000,9999),
                'config_id' => $config_id,
                'status' => '1',
                'add_time' => time(),
            ];

            $res = Adcate::create($data);
            if ($res)
            {
                return json_encode([
                    'status' => 1,
                    'message' => '添加成功',
                ]);
            }

        }catch (\Exception $exception){
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }
    }


    /**
     * note 编辑广告分类
     * auth YW
     * date 2019-05-27
     * return json
     */
    public function adcate_edit(Request $request)
    {

        try{
            $this->parseToken();
            $id = $request->get('id');
            $data = [
                'title' => $request->get('title'),
            ];
            Adcate::where('id', $id)->update($data);
            return json_encode([
                'status' => 1,
                'message' => '修改成功',
            ]);
        }catch (\Exception $exception)
        {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }
    }
    /**
     * note 获取广告分类详情
     * auth YW
     * date 2019-05-27
     * return json
     */
    public function adcate_info(Request $request)
    {
        try {
            $public = $this->parseToken();

            $id = $request->get('id', '');
            $adcate = Adcate::where('id', $id)->first();

            if ($public->type == "merchant") {
                $user_id = $public->merchant_id;
            }

            if ($public->type == "user") {
                $user_id = $public->user_id;

            }

            $this->message = '数据返回成功';
            return $this->format($adcate);

        } catch (\Exception $exception) {
            return json_encode(['status' => -1, 'message' => $exception->getMessage()]);
        }

    }




}