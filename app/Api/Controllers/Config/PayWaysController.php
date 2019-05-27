<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/12/20
 * Time: 2:10 PM
 */

namespace App\Api\Controllers\Config;


use App\Models\Store;
use App\Models\StorePayWay;

class PayWaysController
{


    //支付用到 分店没有直接公用总店的 通过ways_source查询
    public function ways_source($ways_source, $store_id, $srore_id_pid)
    {
        $ways = StorePayWay::where('ways_source', $ways_source)
            ->where('store_id', $store_id)
            ->where('status', 1)
            ->orderBy('sort', 'asc')
            ->first();

        if (!$ways) {
            $store_id = "";
            $srore_id_pid = Store::where('id', $srore_id_pid)
                ->select('store_id')
                ->first();

            if ($srore_id_pid) {
                $store_id = $srore_id_pid->store_id;

            }

            $ways = StorePayWay::where('ways_source', $ways_source)
                ->where('store_id', $store_id)
                ->where('status', 1)
                ->orderBy('sort', 'asc')
                ->first();
        }

        return $ways;
    }


    //支付用到 分店没有直接公用总店的 通过ways_source查询 只返回银联扫码
    public function ways_source_un_qr($ways_source, $store_id, $srore_id_pid)
    {
        $ways = StorePayWay::where('ways_source', $ways_source)
            ->where('store_id', $store_id)
            ->whereIn('ways_type',[6004,8004])
            ->where('status', 1)
            ->orderBy('sort', 'asc')
            ->first();

        if (!$ways) {
            $store_id = "";
            $srore_id_pid = Store::where('id', $srore_id_pid)
                ->select('store_id')
                ->first();

            if ($srore_id_pid) {
                $store_id = $srore_id_pid->store_id;

            }

            $ways = StorePayWay::where('ways_source', $ways_source)
                ->where('store_id', $store_id)
                ->whereIn('ways_type',[6004,8004])
                ->where('status', 1)
                ->orderBy('sort', 'asc')
                ->first();
        }

        return $ways;
    }


    //支付用到 分店没有直接公用总店的 通过ways_type查询
    public function ways_type($ways_type, $store_id, $srore_id_pid)
    {
        $ways = StorePayWay::where('ways_type', $ways_type)
            ->where('store_id', $store_id)
            ->where('status', 1)
            ->first();

        if (!$ways) {
            $store_id = "";
            $srore_id_pid = Store::where('id', $srore_id_pid)
                ->select('store_id')
                ->first();

            if ($srore_id_pid) {
                $store_id = $srore_id_pid->store_id;

            }

            $ways = StorePayWay::where('ways_type', $ways_type)
                ->where('store_id', $store_id)
                ->where('status', 1)
                ->first();
        }

        return $ways;
    }

    //支付用到 分店没有直接公用总店的 通过company,ways_source查询
    public function company_ways_source($company, $ways_source, $store_id, $srore_id_pid)
    {
        $ways = StorePayWay::where('ways_source', $ways_source)
            ->where('company', $company)
            ->where('status', 1)
            ->where('store_id', $store_id)
            ->first();

        if (!$ways) {
            $store_id = "";
            $srore_id_pid = Store::where('id', $srore_id_pid)
                ->select('store_id')
                ->first();

            if ($srore_id_pid) {
                $store_id = $srore_id_pid->store_id;

            }

            $ways = StorePayWay::where('ways_source', $ways_source)
                ->where('company', $company)
                ->where('status', 1)
                ->where('store_id', $store_id)
                ->first();
        }

        return $ways;
    }

}