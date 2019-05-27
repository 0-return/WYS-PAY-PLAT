<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/12/18
 * Time: 2:22 PM
 */

namespace App\Api\Controllers\Basequery;


use App\Models\Ad;
use App\Models\User;

class AdSelectController
{


    //查询到ad到信息
    public function ad_select($data)
    {
        try {

            $config_id = $data['config_id'];
            $user_id = $data['user_id'];
            $store_key_id = $data['store_key_id'];
            $ad_p_id = $data['ad_p_id'];
            $ad_data = [];
            $e_time = date('Y-m-d H:i:s', time());

            //配置ID-广告
            $ad = Ad::where('config_id', $config_id)
                ->where('e_time', '>', $e_time)
                ->where('s_time', '<', $e_time)
                ->where('ad_p_id', $ad_p_id)
                ->get();

            //没有广告读取平台
            if ($ad->isEmpty()) {
                $ad = Ad::where('config_id', '1234')
                    ->where('ad_p_id', $ad_p_id)
                    ->where('e_time', '>', $e_time)
                    ->where('s_time', '<', $e_time)
                    ->get();
            }

            //没有广告
            if ($ad->isEmpty()) {
                return [
                    'status' => 0,
                    'message' => '没有广告-01'
                ];

            }

            $i = 0;
            foreach ($ad as $k => $value) {
                $i = $i + $k;
                //范围是否设置门店
                $store_key_ids = $value->store_key_ids;
                if ($store_key_ids) {
                    //设置门店ID
                    //查看此门店ID是否在这个里面
                    $store_key_ids_arr = explode(',', $store_key_ids);
                    if (!in_array($store_key_id, $store_key_ids_arr)) {
                        continue;
                    }
                }

                //范围是否设置代理商
                $user_ids = $value->user_ids;
                //设置代理 针对
                if ($user_ids) {
                    //设置代理ID
                    //查看此代理ID是否在这个里面
                    $user_ids_arr = explode(',', $user_ids);
                    if (!in_array($user_id, $user_ids_arr)) {
                        continue;
                    }
                } //没有设置代理 针对所有
                else {
                    //查看这个广告的创建者ID是谁 获取到他下面到所有代理商ID
                    $created_id = $value->created_id;
                    $user_ids_arr = $this->getSubIds($created_id);
                    if (!in_array($user_id, $user_ids_arr)) {
                        continue;
                    }
                }

                $imgs = json_decode($value->imgs, true);
                foreach ($imgs as $k1 => $v1) {
                    $i = $i + $k1;
                    $ad_data[$i] = [
                        'title' => $value->title,
                        'ad_p_id' => $value->ad_p_id,
                        'copy_content' => $value->copy_content,
                    ];
                    $ad_data[$i]['img_url'] = $v1['img_url'];
                    $ad_data[$i]['click_url'] = $v1['click_url'];
                }
            }

            return [
                'status' => 1,
                'message' => '广告返回成功',
                'data' => $ad_data,
            ];


        } catch (\Exception $exception) {
            return [
                'status' => 0,
                'message' => $exception->getMessage()
            ];
        }
    }

    //循环获取下级的用户id
    function getSubIds($userID, $includeSelf = true, $t1 = "", $t2 = "")
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
}