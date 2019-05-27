<?php
/**
 * Created by PhpStorm.
 * User: daimingkang
 * Date: 2018/6/29
 * Time: 下午2:42
 */

namespace App\Api\Controllers\Merchant;


use App\Api\Controllers\BaseController;
use App\Models\AppOem;
use App\Models\Merchant;
use App\Models\MerchantStore;
use App\Models\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MyController extends BaseController
{


    //app 我的布局返回
    public function my(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            //收银员
            if ($merchant->merchant_type == 2) {
                $data1 = DB::table('app_my_indexs')
                    ->whereNotIn('type', ['mdzl', 'mdgl', 'rzzx', 'sqtd', 'sksx'])
                    ->where('type', '!=', 'url')
                    ->orderBy('sort', 'asc')
                    ->get();
                $arr1 = $data1->toArray();
            } else {
                //店长
                $data1 = DB::table('app_my_indexs')
                    ->where('type', '!=', 'url')
                    ->orderBy('sort', 'asc')->get();
                $arr1 = $data1->toArray();
            }


            $appoem = AppOem::where('config_id', $merchant->config_id)->first();
            if ($appoem) {
                $data2 = DB::table('app_my_indexs')
                    ->where('config_id', $merchant->config_id)
                    ->where('type', '=', 'url')
                    ->orderBy('sort', 'asc')->get();
            } else {
                $data2 = DB::table('app_my_indexs')
                    ->where('config_id', '1234')
                    ->where('type', '=', 'url')
                    ->orderBy('sort', 'asc')
                    ->get();

            }

            $arr2 = $data2->toArray();

            $data = array_merge($arr1, $arr2);
            $data = [
                'status' => 1,
                'message' => '数据返回成功',
                'data' => $data
            ];
            return json_encode($data);

        } catch (\Exception $exception) {
            return json_encode(['status' => 0, 'message' => $exception->getMessage()]);
        }
    }

    //我的 邮箱
    public function add_email(Request $request)
    {

        try {
            $merchant = $this->parseToken();
            $email = $request->get('email', '');
            $merchants = Merchant::where('id', $merchant->merchant_id)->first();

            //添加修改
            if ($email) {
                $merchants->update([
                    'email' => $email
                ]);
                $merchants->save();
                $message = "邮箱修改成功";

            } else {
                $message = "数据返回成功";
                $email = $merchants->email;
            }


            $data = [
                'status' => 1,
                'message' => $message,
                'data' => [
                    'email' => $email
                ]
            ];
            return json_encode($data);

        } catch (\Exception $exception) {
            return json_encode(['status' => 0, 'message' => $exception->getMessage()]);
        }
    }

    //我的 微信
    public function add_weixin(Request $request)
    {

        try {
            $merchant = $this->parseToken();
            $wx_openid = $request->get('wx_openid', '');
            $merchants = Merchant::where('id', $merchant->merchant_id)->first();


            //验证微信
            if ($wx_openid) {
                $user_wx_openid = Merchant::where('wx_openid', $wx_openid)->first();
                if ($user_wx_openid) {
                    return json_encode([
                        'status' => 2,
                        'message' => '此微信号已经绑定过账户了请重新更换'
                    ]);
                }
            }

            //添加修改
            if ($wx_openid) {
                $merchants->update([
                    'wx_openid' => $wx_openid
                ]);
                $merchants->save();
                $message = "微信修改成功";

            } else {
                $message = "数据返回成功";
                $wx_openid = $merchants->wx_openid;
            }


            $data = [
                'status' => 1,
                'message' => $message,
                'data' => [
                    'wx_openid' => $wx_openid
                ]
            ];
            return json_encode($data);

        } catch (\Exception $exception) {
            return json_encode(['status' => 0, 'message' => $exception->getMessage()]);
        }
    }


    //
    public function me(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $merchants = Merchant::where('id', $merchant->merchant_id)
                ->select('name', 'phone', 'logo as merchant_logo', 'pid as merchant_pid', 'type')
                ->first();
            if ($merchants && $merchants->merchant_logo == '') {
                $merchants->merchant_logo = url('/app/img/merchant/my/tx.png');

            }
            $store_name = '';
            $store_short_name = '';
            $pid = '';
            $people = "";
            $store_address = "";

            $MerchantStore = MerchantStore::where('merchant_id', $merchant->merchant_id)
                ->orderBy('created_at', 'asc')
                ->select('store_id')
                ->first();

            if ($MerchantStore) {
                $store = Store::where('store_id', $MerchantStore->store_id)
                    ->select('store_name', 'pid', 'people', 'store_address', 'store_short_name')
                    ->first();

                if ($store) {
                    $store_name = $store->store_name;
                    $store_short_name = $store->store_short_name;
                    $pid = $store->pid;
                    $people = $store->people;
                    $store_address = $store->store_address;

                }
            }

            $store_type_icon = url('/app/img/merchant/my/store_dianzhang.png');
            if ($merchants->type == "2") {
                $store_type_icon = url('/app/img/merchant/my/store_cashier.png');
            }
            $data = [
                'status' => 1,
                'message' => '数据返回成功',
                'data' => [
                    'merchant' => $merchants,
                    'store' => [
                        'store_name' => $store_name,
                        'store_short_name' => $store_short_name,
                        'store_pid' => $pid,
                        'people' => $people,
                        'store_address' => $store_address,
                        'store_type_icon' => $store_type_icon
                    ],
                ]
            ];
            return json_encode($data);

        } catch (\Exception $exception) {
            return json_encode(['status' => 0, 'message' => $exception->getMessage()]);
        }
    }

    public function edit_merchant(Request $request)
    {
        try {
            $merchant = $this->parseToken();
            $logo = $request->get('logo', '');
            Merchant::where('id', $merchant->merchant_id)->update([
                'logo' => $logo
            ]);
            $data = [
                'status' => 1,
                'message' => '修改成功',
                'data' => $request->except(['token'])
            ];
            return json_encode($data);

        } catch (\Exception $exception) {
            return json_encode(['status' => 0, 'message' => $exception->getMessage()]);
        }
    }


}