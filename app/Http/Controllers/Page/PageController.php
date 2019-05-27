<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/7/16
 * Time: 下午8:02
 */

namespace App\Http\Controllers\Page;


use App\Api\Controllers\Basequery\AdSelectController;
use App\Http\Controllers\Controller;
use App\Models\Store;
use Illuminate\Http\Request;

class PageController extends Controller
{


    //支付成功页面
    public function pay_success(Request $request)
    {

        $store_id = $request->get('store_id');
        $ad_p_id = $request->get('ad_p_id');
        $total_amount = $request->get('total_amount');
        $message = $request->get('message', '支付成功');
        $store_key_id = '';
        $config_id = '';
        $user_id = '';
        //广告

        $store = Store::where('store_id', $store_id)
            ->select('id', 'config_id', 'user_id')
            ->first();

        if ($store) {
            $store_key_id = $store->id;
            $config_id = $store->config_id;
            $user_id = $store->user_id;
        }

        $data = [
            'config_id' => $config_id,
            'user_id' => $user_id,
            'store_key_id' => $store_key_id,
            'ad_p_id' => $ad_p_id,
            'total_amount' => $total_amount,

        ];
        $obj = new AdSelectController();
        $ad = $obj->ad_select($data);
        $ad_data = [];
        if ($ad['status'] == 1) {
            $ad_data = $ad['data'];
        }

        return view('success.pay_success', compact('message', 'data', 'ad_data'));

    }


    //支付失败页面
    public function pay_errors(Request $request)
    {

        $message = $request->get('message', '支付失败');
        $store_id = $request->get('store_id');
        $ad_p_id = $request->get('ad_p_id');
        $total_amount = $request->get('total_amount');
        $store_key_id = '';
        $config_id = '';
        $user_id = '';
        //广告
        $store = Store::where('store_id', $store_id)
            ->select('id', 'config_id', 'user_id')
            ->first();

        if ($store) {
            $store_key_id = $store->id;
            $config_id = $store->config_id;
            $user_id = $store->user_id;
        }

        $data = [
            'config_id' => $config_id,
            'user_id' => $user_id,
            'store_key_id' => $store_key_id,
            'ad_p_id' => $ad_p_id,
            'total_amount' => $total_amount,

        ];
        $obj = new AdSelectController();
        $ad = $obj->ad_select($data);
        $ad_data = [];
        if ($ad['status'] == 1) {
            $ad_data = $ad['data'];
        }

        return view('errors.pay_errors', compact('message', 'data', 'ad_data'));

    }


    //简单的成功页面
    public function success(Request $request)
    {

        $message = $request->get('message');

        return view('success.success', compact('message'));

    }


    public function add_merchant(Request $request)
    {

        $message = $request->get('message');

        return view('page.add_merchant', compact('message'));

    }

}