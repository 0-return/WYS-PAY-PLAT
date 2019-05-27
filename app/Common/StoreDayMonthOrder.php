<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/2/2
 * Time: 下午3:33
 */

namespace App\Common;


class StoreDayMonthOrder
{

    //订单入库
    public static function insert($store_id, $user_id, $merchant_id, $total_amount, $type, $source_type = "")
    {

        try {
            //这个代表店铺收银员每天的金额相加
            $day = date('Ymd', time());
            $StoreDayOrder = \App\Models\MerchantStoreDayOrder::where('day', $day)
                ->where('type', $type)
                ->where('source_type', $source_type)
                ->where('store_id', $store_id)
                ->where('merchant_id', $merchant_id)
                ->first();

            if ($StoreDayOrder && $total_amount) {
                $StoreDayOrder->total_amount = ($total_amount + $StoreDayOrder->total_amount);
                $StoreDayOrder->order_sum = ($StoreDayOrder->order_sum + 1);//笔数
                $StoreDayOrder->save();
            } else {
                \App\Models\MerchantStoreDayOrder::create([
                    'store_id' => $store_id,
                    'merchant_id' => $merchant_id,
                    'day' => $day,
                    'total_amount' => $total_amount,
                    'type' => $type,
                    'source_type' => $source_type,
                    'order_sum' => 1,
                ]);
            }


            //这个代表店铺收银员每月的金额相加
            $month = date('Ym', time());
            $StoreDayOrder = \App\Models\MerchantStoreMonthOrder::where('month', $month)
                ->where('store_id', $store_id)
                ->where('merchant_id', $merchant_id)
                ->where('source_type', $source_type)
                ->where('type', $type)
                ->first();
            if ($StoreDayOrder && $total_amount) {
                $StoreDayOrder->total_amount = ($total_amount + $StoreDayOrder->total_amount);
                $StoreDayOrder->order_sum = ($StoreDayOrder->order_sum + 1);//笔数
                $StoreDayOrder->save();
            } else {
                \App\Models\MerchantStoreMonthOrder::create([
                    'store_id' => $store_id,
                    'merchant_id' => $merchant_id,
                    'month' => $month,
                    'total_amount' => $total_amount,
                    'type' => $type,
                    'source_type' => $source_type,
                    'order_sum' => 1,
                ]);
            }


            //这个代表店铺每天的金额相加
            $day = date('Ymd', time());
            $StoreDayOrder = \App\Models\StoreDayOrder::where('day', $day)
                ->where('store_id', $store_id)
                ->where('type', $type)
                ->where('source_type', $source_type)
                ->first();
            if ($StoreDayOrder && $total_amount) {
                $StoreDayOrder->total_amount = ($total_amount + $StoreDayOrder->total_amount);
                $StoreDayOrder->order_sum = ($StoreDayOrder->order_sum + 1);//笔数
                $StoreDayOrder->save();
            } else {
                \App\Models\StoreDayOrder::create([
                    'store_id' => $store_id,
                    'day' => $day,
                    'total_amount' => $total_amount,
                    'type' => $type,
                    'source_type' => $source_type,
                    'order_sum' => 1,

                ]);
            }


            //这个代表店铺每月的金额相加
            $month = date('Ym', time());
            $StoreDayOrder = \App\Models\StoreMonthOrder::where('month', $month)
                ->where('store_id', $store_id)
                ->where('source_type', $source_type)
                ->where('type', $type)
                ->first();
            if ($StoreDayOrder && $total_amount) {
                $StoreDayOrder->total_amount = ($total_amount + $StoreDayOrder->total_amount);
                $StoreDayOrder->order_sum = ($StoreDayOrder->order_sum + 1);//笔数
                $StoreDayOrder->save();
            } else {
                \App\Models\StoreMonthOrder::create([
                    'store_id' => $store_id,
                    'month' => $month,
                    'total_amount' => $total_amount,
                    'type' => $type,
                    'source_type' => $source_type,
                    'order_sum' => 1,

                ]);
            }


            //这个代代理商铺每天的金额相加
            $day = date('Ymd', time());
            $StoreDayOrder = \App\Models\UserDayOrder::where('day', $day)
                ->where('user_id', $user_id)
                ->where('source_type', $source_type)
                ->where('type', $type)
                ->first();
            if ($StoreDayOrder && $total_amount) {
                $StoreDayOrder->total_amount = ($total_amount + $StoreDayOrder->total_amount);
                $StoreDayOrder->order_sum = ($StoreDayOrder->order_sum + 1);//笔数
                $StoreDayOrder->save();
            } else {
                \App\Models\UserDayOrder::create([
                    'user_id' => $user_id,
                    'day' => $day,
                    'total_amount' => $total_amount,
                    'type' => $type,
                    'source_type' => $source_type,
                    'order_sum' => 1,

                ]);
            }


            //这个代表代理商每月的金额相加
            $month = date('Ym', time());
            $StoreDayOrder = \App\Models\UserMonthOrder::where('month', $month)
                ->where('user_id', $user_id)
                ->where('source_type', $source_type)
                ->where('type', $type)
                ->first();
            if ($StoreDayOrder && $total_amount) {
                $StoreDayOrder->total_amount = ($total_amount + $StoreDayOrder->total_amount);
                $StoreDayOrder->order_sum = ($StoreDayOrder->order_sum + 1);//笔数
                $StoreDayOrder->save();
            } else {
                \App\Models\UserMonthOrder::create([
                    'user_id' => $user_id,
                    'month' => $month,
                    'total_amount' => $total_amount,
                    'source_type' => $source_type,
                    'type' => $type,
                    'order_sum' => 1,

                ]);
            }


        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::info($exception);
        }

    }

}