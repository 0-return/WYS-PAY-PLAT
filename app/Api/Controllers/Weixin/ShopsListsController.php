<?php
/**
 * Created by PhpStorm.
 * User: dmk
 * Date: 2017/1/10
 * Time: 15:00
 */

namespace App\Http\Controllers\Weixin;

use App\Http\Controllers\Controller;
use App\Merchant;
use App\Models\WeixinShopList;
use App\Models\WxPayOrder;
use EasyWeChat\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;

class ShopsListsController extends BaseController
{

    public function index(Request $request)
    {
        //
        $data = WeixinShopList::where('user_id', Auth::user()->id)->where("is_delete",0)->where('pid',0)->orderBy('created_at', 'desc')->get();
        if (Auth::user()->hasRole('admin')) {
            $data = WeixinShopList::where('pid',0)->where("is_delete",0)->orderBy('created_at', 'desc')->get();
        }

        if ($data->isEmpty()) {
            $paginator = "";
            $datapage = "";
        } else {
            $data = $data->toArray();
            //下一个版本去掉
            foreach ($data as $v) {
                if ($v['store_id']==""){
                    WeixinShopList::where('mch_id', $v['mch_id'])->update([
                        'store_id'=>'w'.$v['mch_id']
                    ]);
                }
            }

            //非数据库模型自定义分页
            $perPage = 9;//每页数量
            if ($request->has('page')) {
                $current_page = $request->input('page');
                $current_page = $current_page <= 0 ? 1 : $current_page;
            } else {
                $current_page = 1;
            }
            $item = array_slice($data, ($current_page - 1) * $perPage, $perPage); //注释1
            $total = count($data);
            $paginator = new LengthAwarePaginator($item, $total, $perPage, $current_page, [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]);
            $datapage = $paginator->toArray()['data'];
        }
        return view('admin.weixin.shoplist', compact('datapage', 'paginator'));
    }

    public function WxAddShop()
    {
        $auth = Auth::user()->can('wxAddShop');
        if (!$auth) {
            echo '你没有权限操作！';
            die;
        }
        return view('admin.weixin.add');

    }

    public function WxEditShop(Request $request)
    {
        $auth = Auth::user()->can('wxEditShop');
        if (!$auth) {
            echo '你没有权限操作！';
            die;
        }
        $id = $request->get('id');
        $Shop = WeixinShopList::where('id', $id)->first();
        return view('admin.weixin.edit', compact('Shop'));
    }

    public function WxShopPost(Request $request)
    {
        $data = $request->except(['_token']);
        $rules = [
            'store_name' => 'required',
            'mch_id' => 'required',
        ];
        $messages = [
            'required' => '必填项',
        ];
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            return back()->withErrors($validator);  //返回一次性错误
        }
        $data['store_id'] = 'w' . $request->get('mch_id');
        $data['user_id'] = Auth::user()->id;
        WeixinShopList::create($data);
        return redirect(route('WxShopList'));
    }

    public function WxEditShopPost(Request $request)
    {
        $data = $request->except(['_token', 'id']);
        $rules = [
            'store_name' => 'required',
            'mch_id' => 'required',
        ];
        $messages = [
            'required' => '必填项',
        ];
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            return back()->withErrors($validator);  //返回一次性错误
        }
        WeixinShopList::where('id', $request->get('id'))->update($data);
        return back();
    }

    public function WxPayQr(Request $request)
    {

        $store_id = $request->get('store_id');
        $merchant_id=$request->get('merchant_id');
        $merchant_name='';
        if ($merchant_id){
            $merchant_name=Merchant::where('id',$merchant_id)->first()->name;
        }
        $shop = WeixinShopList::where('store_id', $store_id)->first();
        $code_url = url('admin/weixin/oauth?sub_info=pay_' . $store_id.'_'.$merchant_id);
        return view('admin.weixin.wxpayqr', compact('code_url', 'shop','merchant_name'));
    }

//收单列表
    public function WxOrder(Request $request)
    {
        $array=[201,202,203,];
        //所有收银员
        $cashier=DB::table("merchants")->select("name","id")->get();
        foreach($cashier as $v){
            $cashier[$v->id]=$v->name;
        }
        if (Auth::user()->hasRole('admin')) {
            //微信扫码枪
            $data = DB::table('orders')
                ->join('weixin_shop_lists', 'orders.store_id', '=', 'weixin_shop_lists.store_id')
                ->select("orders.remark","orders.out_trade_no","weixin_shop_lists.store_id",'weixin_shop_lists.store_name','orders.created_at',"orders.updated_at","orders.total_amount","orders.type","orders.pay_status","orders.merchant_id")
                ->whereIn("orders.type",$array)
                ->orderBy('orders.updated_at', 'desc')
                ->get()
                ->toArray();
        } else {
            //微信扫码枪
            $data = DB::table('orders')
                ->join('weixin_shop_lists', 'orders.store_id', '=', 'weixin_shop_lists.store_id')
                ->select("orders.remark","orders.out_trade_no","weixin_shop_lists.store_id",'weixin_shop_lists.store_name','orders.created_at',"orders.updated_at","orders.total_amount","orders.type","orders.pay_status","orders.merchant_id")
                ->whereIn("orders.type",$array)
                ->where("weixin_shop_lists.user_id", auth()->user()->id)
                ->orderBy('orders.updated_at', 'desc')
                ->get()
                ->toArray();

        }
        //非数据库模型自定义分页
        $perPage = 8;//每页数量
        if ($request->has('page')) {
            $current_page = $request->input('page');
            $current_page = $current_page <= 0 ? 1 : $current_page;
        } else {
            $current_page = 1;
        }
        $item = array_slice($data, ($current_page - 1) * $perPage, $perPage); //注释1
        $total = count($data);
        //dd($total);
        $paginator = new LengthAwarePaginator($item, $total, $perPage, $current_page, [
            'path' => Paginator::resolveCurrentPath(),
            'pageName' => 'page',
        ]);
        $datapage = $paginator->toArray()['data'];
       // dd($paginator);
        return view('admin.weixin.wxorder', compact('datapage', 'paginator',"cashier"));

    }

    public function WxOrder1(Request $request)
    {
        $wxorder = WxPayOrder::all();
        if ($wxorder->isEmpty()) {
            $paginator = "";
            $datapage = "";
        } else {
            $wxorder = $wxorder->toArray();
            foreach ($wxorder as $v) {
                $options = $this->Options();
                $options['payment']['sub_merchant_id'] = $v['mch_id'];
                $app = new Application($options);
                $payment = $app->payment;
                $orderNo = $v['out_trade_no'];
                $query = $payment->query($orderNo);
                if ($query->return_code == "SUCCESS") {
                    $data[] = array_merge($query->toArray(), $v);
                }
            }
            //非数据库模型自定义分页
            $perPage = 9;//每页数量
            if ($request->has('page')) {
                $current_page = $request->input('page');
                $current_page = $current_page <= 0 ? 1 : $current_page;
            } else {
                $current_page = 1;
            }
            $item = array_slice($data, ($current_page - 1) * $perPage, $perPage); //注释1
            $total = count($data);
            $paginator = new LengthAwarePaginator($item, $total, $perPage, $current_page, [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]);
            $datapage = $paginator->toArray()['data'];
        }
        return view('admin.weixin.wxorder', compact('paginator', 'datapage'));
        /*  dd($data);    0 => array:18 [▼
         "return_code" => "SUCCESS"
         "return_msg" => "OK"
         "appid" => "wx789fb035be0b7481"
         "mch_id" => "1419589702"
         "sub_mch_id" => "1419589702"
         "nonce_str" => "zDLnKNcxd6wxpdSp"
         "sign" => "A14999AFD736B9486250F98E701DB663"
         "result_code" => "SUCCESS"
         "out_trade_no" => "20170114030819888820170114030819"
         "trade_state" => "NOTPAY"
         "trade_state_desc" => "订单未支付"
         "id" => 43
         "transaction_id" => ""
         "total_fee" => "0.10"
         "open_id" => "opnT0s8Pltziuu2qATK3o8bKAWbA"
         "status" => ""
         "created_at" => "2017-01-14 15:08:19"
         "updated_at" => "2017-01-14 15:08:19"
       ]
       1 => array:25 [▼
         "return_code" => "SUCCESS"
         "return_msg" => "OK"
         "appid" => "wx789fb035be0b7481"
         "mch_id" => "1419589702"
         "sub_mch_id" => "1419589702"
         "nonce_str" => "Tmz4kk49sUft6SFi"
         "sign" => "9CEF6F088CC60A40F1173C9F2728F492"
         "result_code" => "SUCCESS"
         "openid" => "opnT0s8Pltziuu2qATK3o8bKAWbA"
         "is_subscribe" => "Y"
         "trade_type" => "JSAPI"
         "bank_type" => "CFT"
         "total_fee" => "0.01"
         "fee_type" => "CNY"
         "transaction_id" => ""
         "out_trade_no" => "20170114030827888820170114030827"
         "attach" => null
         "time_end" => "20170114150831"
         "trade_state" => "SUCCESS"
         "cash_fee" => "1"
         "id" => 44
         "open_id" => "opnT0s8Pltziuu2qATK3o8bKAWbA"
         "status" => ""
         "created_at" => "2017-01-14 15:08:27"
         "updated_at" => "2017-01-14 15:08:27"
       ]
     ]*/
    }
   public function wxChangeStatus(Request $request){
       $auth = Auth::user()->can('wxChangeStatus');
       if (!$auth) {
           echo '你没有权限操作！';
           die;
       }
       $id=$request->get("id");
       $data['is_delete']=1;
       try{
           DB::table("weixin_shop_lists")->where("id",$id)->update($data);
       }catch(Exception $e){
         return $e->getMessage();
       }
       return back();
   }
   //还原页
    public function wxRestore(Request $request){
        $auth = Auth::user()->can('wxShopRestore');
        if (!$auth) {
            echo '你没有权限操作！';
            die;
        }

        $data = WeixinShopList::where('user_id', Auth::user()->id)->where("is_delete",1)->where('pid',0)->orderBy('created_at', 'desc')->paginate(8);
        if (Auth::user()->hasRole('admin')) {
            $data = WeixinShopList::where('pid',0)->where("is_delete",1)->orderBy('created_at', 'desc')->paginate(8);
        }
        return view('admin.weixin.wxRestore', compact('data'));
    }
    //多个还原
    public function wxRestoree(Request $request){
        $s=$request->get("data");
        //dd($s);
        $data['is_delete']=0;
        foreach($s as $v){
            DB::table("weixin_shop_lists")->where("id",$v)->update($data);
        }
        return redirect("/admin/weixin/wxRestore");
    }
    //单个还原
    public function wxRestoreee(Request $request){
        $id=$request->id;
        $data['is_delete']=0;
        if(DB::table("weixin_shop_lists")->where("id",$id)->update($data)){
            return redirect("/admin/weixin/wxRestore");
        }
    }
    //彻底删除
    public function deleteWx(Request $request){
        $auth = Auth::user()->can('wxShopDelete');
        if (!$auth) {
            return json_encode(['success' => 0]);
        }else{
            $id = $request->get("id");
            try {
                if (DB::table("weixin_shop_lists")->where("id", $id)->orwhere("pid",$id)->delete()) {
                    return json_encode(['success' => 1]);
                }
            } catch (\Exception $exception) {
                return json_encode(['success' => 0]);
            }
        }
    }
    //搜索
    public function searchWx(Request $request){
        $sp=$request->input("shopname");
        $data = WeixinShopList::where('user_id', Auth::user()->id)->where("store_name","like","%".$sp."%")->where("is_delete",0)->where('pid',0)->orderBy('created_at', 'desc')->get();
        if (Auth::user()->hasRole('admin')) {
            $data = WeixinShopList::where('pid',0)->where("store_name","like","%".$sp."%")->where("is_delete",0)->orderBy('created_at', 'desc')->get();
        }

        if ($data->isEmpty()) {
            $paginator = "";
            $datapage = "";
        } else {
            $data = $data->toArray();
            //下一个版本去掉
            foreach ($data as $v) {
                if ($v['store_id']==""){
                    WeixinShopList::where('mch_id', $v['mch_id'])->update([
                        'store_id'=>'w'.$v['mch_id']
                    ]);
                }
            }

            //非数据库模型自定义分页
            $perPage = 9;//每页数量
            if ($request->has('page')) {
                $current_page = $request->input('page');
                $current_page = $current_page <= 0 ? 1 : $current_page;
            } else {
                $current_page = 1;
            }
            $item = array_slice($data, ($current_page - 1) * $perPage, $perPage); //注释1
            $total = count($data);
            $paginator = new LengthAwarePaginator($item, $total, $perPage, $current_page, [
                'path' => Paginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]);
            $datapage = $paginator->toArray()['data'];
        }
        return view('admin.weixin.shoplist', compact('datapage', 'paginator'));
    }
    //执行还原搜索
    public function searchW(Request $request){
        $sp=$request->input("shopname");
        $data = WeixinShopList::where('user_id', Auth::user()->id)->where("store_name","like","%".$sp."%")->where("is_delete",1)->where('pid',0)->orderBy('created_at', 'desc')->paginate(8);
        return view('admin.weixin.wxRestore', compact('data'));
    }
}