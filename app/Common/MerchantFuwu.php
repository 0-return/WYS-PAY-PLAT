<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/2/2
 * Time: 下午3:33
 */

namespace App\Common;


class MerchantFuwu
{

    //订单入库
    public static function insert($title, $desc, $store_id, $merchant_id, $total_amount, $out_trade_no)
    {

        try {
            $data = [
                'store_id' => $store_id,
                'merchant_id' => $merchant_id,
                'title' => $title,
                'desc' => $desc,
                'amount' => $total_amount,
                'out_trade_no' => $out_trade_no,
            ];
            \App\Models\MerchantFuwu::create($data);


        } catch (\Exception $exception) {
            \Illuminate\Support\Facades\Log::info($exception);
        }

    }

}