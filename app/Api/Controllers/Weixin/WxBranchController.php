<?php
/**
 * Created by PhpStorm.
 * User: dmk
 * Date: 2017/3/30
 * Time: 11:05
 */

namespace App\Http\Controllers\Weixin;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\WeixinShopList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WxBranchController extends Controller
{


    public function BranchIndex(Request $request)
    {
        $auth = Auth::user()->can('wxBranch');
        if (!$auth) {
            echo '你没有权限操作！';
            die;
        }
        $pid = $request->get('pid');
        $datapage = WeixinShopList::where('pid', $pid)->where('is_delete', 0)->paginate(8);
        $store_name = WeixinShopList::where('id', $pid)->first()->store_name;
        return view('admin.weixin.branch.index', compact('datapage', 'store_name','pid'));
    }

    public function BranchAdd()
    {
        return view('admin.weixin.branch.add');
    }

    public function BranchAddPost(Request $request)
    {
        $data = $request->all();
        $rules = [
            'store_name' => 'required|unique:weixin_shop_lists',
            'service_phone' => 'required|min:11|max:11',
        ];
        $messages = [
            'required' => '不能为空',
            'unique' => '店名已经存在',
            'min' => '手机号码长度不够'
        ];
        $validator = Validator::make($data, $rules, $messages);
        if ($validator->fails()) {
            return back()->withErrors($validator);  //返回一次性错误
        }

        try {
            $store = WeixinShopList::where('id', $request->get('pid'))->first();
            $store = $store->toArray();
            $store['store_id'] = $data['store_id'];
            $store['pid'] = $data['pid'];
            $store['store_name'] = $data['store_name'];
            $store['contact_name'] = $data['contact_name'];
            $store['service_phone'] = $data['service_phone'];
            $s = WeixinShopList::where('store_id', $data['store_id'])->first();
            if ($s) {
                WeixinShopList::where('store_id', $data['store_id'])->updated($store);
            } else {
                WeixinShopList::create($store);
            }
        } catch (\Exception $exception) {
            return back()->with('errors', '添加失败');
        }
        return redirect('/admin/weixin/BranchIndex?pid=' . $data['pid']);
    }
    //加载还原页
    public function BwRestore(Request $request){
        $pid = $request->get('pid');
        $data = WeixinShopList::where('pid', $pid)->where('is_delete', 1)->paginate(8);
        return view('admin.weixin.branch.BwRestore', compact('data'));
    }
    //执行删除
    public function deleteBw(Request $request){
        $auth = Auth::user()->can('delete');
        if (!$auth) {
            return json_encode(['success' => 0]);
            die;
        }
        if (Auth::user()->hasRole('admin')) {
            $id = $request->get("id");
            try {
                if (DB::table("weixin_shop_lists")->where("id", $id)->delete()) {
                    return json_encode(['success' => 1]);
                }
            } catch (\Exception $exception) {
                return json_encode(['success' => 0]);
            }
        } else {
            return json_encode(['success' => 0]);
        }
    }
}