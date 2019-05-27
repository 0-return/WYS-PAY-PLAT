<?php

namespace App\Api\Controllers;

use App\Http\Controllers\Controller;
use App\Models\MerchantStore;
use App\Models\Store;
use App\Models\User;
use Dingo\Api\Routing\Helpers;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;


class BaseController extends Controller
{
    use Helpers;//为了让所有继承这个的控制器都使用dingoApi

    /*
        解析token

        返回用户信息，数组格式返回
    */
    public function parseToken($token = '')
    {
        try {
            JWTAuth::setToken(JWTAuth::getToken());
            $data = JWTAuth::getPayload();//数组
            return (object)$data['sub'];
        } catch (\Exception $e) {
            return false;
        }
    }

    public $needpage = false;//默认不要分页/

    public $l = 15;
    public $p = 1;
    public $t = 0;

    public $status = 1;
    public $message = 'ok';

    /*
        接收app的请求
    */
    /*    public function receive($request = '')
        {
            if (empty($request))
                $request = app('request');

            $this->p = abs(trim($request->get('p', 1)));
            $this->l = abs(trim($request->get('l', 15)));
        }
    */
    /*
        定义返回格式
    */
    public function format($cin = [])
    {
        $data = [
            /*            'l' => $this->l,//每页显示多少条
                        'p' => $this->p,//当前页
                        't' => $this->t,//当前页*/

            'status' => $this->status,
            'message' => $this->message,
            'data' => $cin
        ];
        if ($this->needpage) {

            $data['l'] = $this->l;
            $data['p'] = $this->p;
            $data['t'] = $this->t;

        }

        return response()->json($data);
    }

    /*
        返回分页数据
    */
    public function page($obj, $request = '')
    {

        if (empty($request))
            $request = app('request');

        $this->p = abs(trim($request->get('p', 1)));
        $this->l = abs(trim($request->get('l', 15)));

        $this->needpage = true;

        $start = abs(($this->p - 1) * $this->l);
        return $obj->offset($start)->limit($this->l);
    }

    /**
     * 校验必填字段
     */
    public function check_required($check, $data)
    {
        $rules = [];
        $attributes = [];
        foreach ($data as $k => $v) {
            $rules[$k] = 'required';
            $attributes[$k] = $v;
        }
        $messages = [
            'required' => ':attribute不能为空',
        ];
        $validator = Validator::make($check, $rules,
            $messages, $attributes);
        $message = $validator->getMessageBag();
        return $message->first();
    }

    //循环获取下级的用户id
    function getSubIds($userID, $includeSelf = true, $t1 = "", $t2="")
    {
        $userIDs = [$userID];
        $where = [];

        if ($t2 == "") {
            $t2 = date('Y-m-d H:i:s', time());

        }

        if ($t1) {
            $where[] = ['created_at', '>=', $t1];
            $where[] = ['created_at', '<=', $t2];

        }
        while (true) {
            $subIDs = User::whereIn('pid', $userIDs)
                ->where($where)
                ->select('id')->get()
                ->toArray();
            $subIDs = array_column($subIDs, 'id');
            $userCount = count($userIDs);
            $userIDs = array_unique(array_merge($userIDs, $subIDs));
            if ($userCount == count($userIDs)) {
                break;
            }
        }
        if (!$includeSelf) {
            for ($i = 0; $i < count($userIDs); ++$i) {
                if ($userIDs[$i] == $userID) {
                    array_splice($userIDs, $i, 1);
                    break;
                }
            }
        }
        return $userIDs;
    }

    /**查询门店所有ID
     * @return string
     */
    public
    function getStore_id($store_id, $id)
    {

        $store_ids = [];

        $store = Store::orWhere('pid', $id)
            ->orWhere('store_id', $store_id)
            ->select('store_id')
            ->get();

        if (!$store->isEmpty()) {
            $store_ids = $store->toArray();
        }

        return $store_ids;

    }
}