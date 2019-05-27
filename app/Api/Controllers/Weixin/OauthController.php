<?php

namespace App\Api\Controllers\Weixin;

use App\Models\Store;
use EasyWeChat\Factory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class OauthController extends BaseController
{
    //拼接链接获取
    public function oauth(Request $request)
    {

        $state = $this->decode($request->get('state'));
        $options = $this->Options($state['config_id']);
        $scope_type = $state['scope_type'];//snsapi_base snsapi_userinfo获取的信息不一样
        $sub_info = $request->get('state');
        $config = [
            'app_id' => $options['app_id'],
            'scope' => $scope_type,
            'oauth' => [
                'scopes' => [$scope_type],
                'response_type' => 'code',
                'callback' => url('/api/weixin/callback?sub_info=' . $sub_info),
            ],

        ];

        $app = Factory::officialAccount($config);
        $oauth = $app->oauth;
        return $oauth->redirect();

    }


    public function callback(Request $request)
    {
        $sub_info = $request->get('sub_info');
        $sub_info = $this->decode($sub_info);
        $options = $this->Options($sub_info['config_id']);
        $sub_info['code'] = $request->get('code');
        $users = $this->users($sub_info, $options);

        //微信支付
        if ($sub_info['bank_type'] == "weixin") {
            $sub_info['open_id'] = $users->getId();
            $sub_info = $this->encode($sub_info);
            return redirect('/api/weixin/qr_pay_view?sub_info=' . $sub_info);

        }


        //教育缴费 //微信支付
        if ($sub_info['bank_type'] == "school_weixin") {
            $open_id = $users->getId();
            $store_id = $sub_info['store_id'];
            $stu_grades_no = $sub_info['stu_grades_no'];
            $stu_class_no = $sub_info['stu_class_no'];
            $school_name = $sub_info['school_name'];
            $url = url('/school/payeducation?open_id=' . $open_id . '&store_id=' . $store_id . '&school_name=' . $school_name . '&stu_grades_no=' . $stu_grades_no . '&stu_class_no=' . $stu_class_no);

            return redirect($url);
        }


    }

    //微信支付视图页面
    public function qr_pay_view(Request $request)
    {
        $sub_info = $request->get('sub_info');
        $sub_info = $this->decode($sub_info);
        $store_id = $sub_info['store_id'];//门店id
        $merchant_id = $sub_info['merchant_id'];//收银员id
        $store_name = $sub_info['store_name'];
        $store_address = $sub_info['store_address'];
        $open_id = $sub_info['open_id'];
        $type = $sub_info['bank_type'];
        $data = [
            //'oem_name' => $AppOem->name,
            'store_id' => $store_id,
            'store_name' => $store_name,
            'store_address' => $store_address,
            'open_id' => $open_id,
            'merchant_id' => $merchant_id,
        ];
        if ($type == 'weixin') {
            return view('weixin.create_weixin_order', compact('data'));

        }
    }

    //学校发起支付页面
    public function paydetails()
    {

        return view('school.paydetails');
    }

    //获取users
    public function users($sub_info, $options)
    {
        $code = $sub_info['code'];
        $config = [
            'app_id' => $options['app_id'],
            "secret" => $options['app_secret'],
            "code" => $code,
            "grant_type" => "authorization_code",
        ];
        $app = Factory::officialAccount($config);
        $oauth = $app->oauth;

        // 获取 OAuth 授权结果用户信息
        return $user = $oauth->user();
    }

}
