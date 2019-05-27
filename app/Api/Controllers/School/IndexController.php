<?php

namespace App\Api\Controllers\School;

use App\Api\Controllers\Config\AlipayIsvConfigController;
use Illuminate\Support\Facades\DB;

/*
    学校  增删查改
*/

class IndexController extends \App\Api\Controllers\BaseController
{

    public function aliAuth()
    {
        try {
            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $mid = $loginer->merchant_id;
            $config_id = $loginer->config_id;

            $store_id = $request->get('store_id');


            $this->status = 2;

            if (empty($store_id)) {
                $this->message = '请传入学校编号';
                return $this->format();
            }
            /*
                        object(stdClass)#628 (10) {
              ["type"]=>
              string(8) "merchant"
              ["merchant_id"]=>
              int(1)
              ["merchant_name"]=>
              string(12) "教育账户"
              ["config_id"]=>
              string(4) "1234"
              ["pid"]=>
              int(0)
              ["phone"]=>
              string(11) "17895009556"
              ["imei"]=>
              NULL
              ["user_id"]=>
              int(1)
              ["merchant_type"]=>
              string(1) "1"
              ["created_store_no"]=>
              string(15) "800178950095561"
            }

            var_dump($loginer);die;*/
            $url = url('/merchant/appAlipay?store_id=' . $store_id . '&merchant_id=' . $mid . '&config_id=' . $config_id . '&auth_type=03');
            $this->status = 1;
            $this->message = '操作成功！';

            return $this->format(['url' => $url]);
        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage();
            return $this->format();
        }

    }


    /*
        系统审核
    */
    public function check()
    {
        try {

            $request = app('request');
            $this->status = 2;
            $this->message = '教师端没有审核接口！';
            return $this->format();

            $store_id = $request->get('store_id');
            $school = \App\Models\StuStore::where('store_id', $store_id)->first();
            if (empty($school)) {
                $this->message = '学校不存在！';
                return $this->format();
            }
            if ($school->status != 2) {
                $this->message = '学校已经不在审核状态！';
                return $this->format();

            }
            $status = $request->get('status');
            $status_desc = !empty($request->get('status_desc', '')) ? $request->get('status_desc', '') : '';

            if (!in_array($status, [1, 3])) {
                $this->message = '审核状态不正确！';
                return $this->format();
            }

            $school->status = $status;

            if ($status == 1) {
                $school->status_desc = empty($status_desc) ? '审核成功' : $status_desc;

            } else {
                $school->status_desc = empty($status_desc) ? '审核失败' : $status_desc;

            }


            $school->update();

            $this->status = 1;
            $this->message = '操作成功！';
            return $this->format();
        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage();
            return $this->format();
        }
    }


    public function typeLst()
    {

        try {

            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));

            $all = \App\Models\StuType::get();

            $cout = [];


            if (!$all->isEmpty()) {

                $cout = array_map(function ($each) {
                    return [
                        'name' => $each['school_type_desc'],
                        'type' => $each['school_type'],
                    ];

                }, $all->toArray());
            }


            /*
                        $data=[
                            ['name'=>'托儿所','type'=>1],
                            ['name'=>'幼儿园','type'=>2],
                            ['name'=>'小学','type'=>3],
                            ['name'=>'初中','type'=>4],
                            ['name'=>'高中','type'=>5],

                        ];*/

            $this->status = 1;
            return $this->format($cout);


        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage();
            return $this->format();
        }

    }


    public function lst()
    {

        try {

            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;

            $merchant_id = $loginer->merchant_id;
            $config_id = $loginer->config_id;


            $obj = new \App\Models\StuStore();
            // $obj=$obj->whereIn('store_id',$all_store_id);


            // 分校查询
            $search_son = $request->get('search_son', '');


            // 分校查询
            // search_son  1查询主校，2查询分校，空为默认查询所有
            if ($search_son == 1) {
                if (empty($request->get('store_id'))) {
                    $get_all_store_id = \App\Models\MerchantStore::where('merchant_id', $merchant_id)->get();

                    $all_store_id = [];
                    foreach ($get_all_store_id as $v) {
                        $all_store_id[] = $v->store_id;
                    }

                } else {
                    $all_store_id = [$request->get('store_id')];
                }

                $obj = $obj->whereIn('store_id', $all_store_id);
                $obj = $obj->where('pid', '');
            } elseif ($search_son == 2) {
                $obj = $obj->where('pid', $request->get('store_id'));
                // 2018061205492993166  主校
                // $obj = $obj->where('pid','!=','');
            } //普通查询
            else {

                if (empty($request->get('store_id'))) {
                    $get_all_store_id = \App\Models\MerchantStore::where('merchant_id', $merchant_id)->get();

                    $all_store_id = [];
                    foreach ($get_all_store_id as $v) {
                        $all_store_id[] = $v->store_id;
                    }

                } else {
                    $all_store_id = [$request->get('store_id')];
                }

                $obj = $obj->whereIn('store_id', $all_store_id);
            }
            $this->t = $obj->count();
            $data = $this->page($obj)->get();

            $this->status = 1;
            return $this->format($data);


        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage() . $e->getFile() . $e->getLine();
            return $this->format();
        }

    }


    public function lstnew()
    {

        try {

            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;

            $merchant_id = $loginer->merchant_id;
            //获取所有的store_id
            // echo $merchant_id;die;

            // echo $merchant_id;die;


//查出绑定的所有的学校
            if (empty($request->get('store_id'))) {
                $get_all_store_id = \App\Models\MerchantStore::where('merchant_id', $merchant_id)->get();

                $all_store_id = [];
                foreach ($get_all_store_id as $v) {
                    $all_store_id[] = $v->store_id;
                }

            } else {
                $all_store_id = [$request->get('store_id')];
            }


            // var_dump($all_store_id);die;


            $obj = new \App\Models\StuStore();
            // $obj=$obj->whereIn('store_id',$all_store_id);


            $search_son = $request->get('search_son', '');
            if (!empty($request->get('search_son'))) {

                $obj = $obj->where('store_id', $request->get('store_id'));

                if ($search_son == 1) {
                    // search_son  1查询主校，2查询分校，空为默认查询所有
                    $obj = $obj->where('pid', '');
                } elseif ($search_son == 2) {
                    // 2018061205492993166  主校
                    $obj = $obj->where('pid', '!=', '');
                }
            } else {

                $obj = $obj->whereIn('store_id', $all_store_id);


            }


            $this->t = $obj->count();
            $data = $this->page($obj)->get();

            $this->status = 1;
            return $this->format($data);


        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage() . $e->getFile() . $e->getLine();
            return $this->format();
        }

    }


    /*
        显示一条学校
    */
    public function show()
    {
        try {

            $request = app('request');
            $this->status = 2;

            $store_id = $request->get('store_id');
            $school = \App\Models\StuStore::where('store_id', $store_id)->first();
            if (empty($school)) {
                $this->message = '学校资料不存在！';
                return $this->format();
            }

            $this->status = 1;
            return $this->format($school);
        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage();
            return $this->format();
        }
    }



    // public $pay_notify_url=url('api/school/pay/notify');//支付结果异步通知地址

    /*

        这个方法是没有考虑子校的情况
    */
    public function add_back_0713()
    {

        try {

            $pay_notify_url = url('api/school/pay/notify');//支付结果异步通知地址

            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;


            $merchant_id = $loginer->merchant_id;
            $get_can_all_store_id = \App\Models\MerchantStore::select(DB::raw('distinct merchant_stores.store_id'))->LeftJoin('stu_stores', 'stu_stores.store_id', '!=', 'merchant_stores.store_id')->where('merchant_stores.merchant_id', $merchant_id)->get();

            $can_all_store_id = [];
            if (!$get_can_all_store_id->isEmpty()) {
                $can_all_store_id = array_map(function ($value) {
                    return $value['store_id'];
                }, $get_can_all_store_id->toArray());
            }

            if (empty($can_all_store_id)) {
                $this->message = '没有可用的store_id来创建学校';
                return $this->format();
            }

            $get_have_store_id = \App\Models\StuStore::where('merchant_id', $merchant_id)->get();
            $have_store_id = [];
            foreach ($get_have_store_id as $v) {
                $have_store_id[] = $v->store_id;
            }

            $can_use_store_id = array_diff($can_all_store_id, $have_store_id);

            if (empty($can_use_store_id)) {
                $this->message = '可用的store_id能创建的学校都已经创建好了';
                return $this->format();
            }

            $choose_store_id = array_shift($can_use_store_id);


            $school_stdcode = $request->get('school_stdcode');
            if (empty($school_stdcode)) {
                $this->message = '学校机构编号不能为空！';
                return $this->format();
            }

            $have = \App\Models\StuStore::where('store_id', $choose_store_id)->first();

            if (!empty($have)) {
                $this->message = '学校已经创建，请去学校列表中查看！';
                return $this->format();
            }

            // 数据库字段
            $cin = [
                'store_id' => $choose_store_id,
                'user_id' => $loginer->user_id,
                'merchant_id' => $merchant_id,
                'config_id' => $loginer->config_id,
                'pid' => !empty($request->get('parent_store_id')) ? $request->get('parent_store_id') : 0,//上级id
                'school_no' => '',

                'school_name' => $request->get('school_name'),
                'school_sort_name' => $request->get('school_sort_name'),
                'school_icon' => $request->get('school_icon'),//图片宽度 高度  必须是  108*108  不大于20kb
                'school_stdcode' => $request->get('school_stdcode'),
                'school_type' => $request->get('school_type'),
                'province_code' => $request->get('province_code'),
                'province_name' => $request->get('province_name'),
                'city_code' => $request->get('city_code'),
                'city_name' => $request->get('city_name'),
                'district_code' => $request->get('district_code'),
                'district_name' => $request->get('district_name'),
                'su_store_address' => $request->get('su_store_address'),

                'status' => '2',
                'status_desc' => '未审核',//未审核

                'alipay_status' => 2,
                'alipay_status_desc' => '未同步'
            ];


            $validate = \Validator::make($cin, [
                'su_store_address' => 'required',
                'school_name' => 'required',
                'school_sort_name' => 'required',
                'school_icon' => 'required',
                // 'school_stdcode'=>'required',
                'school_type' => 'required',
                'province_code' => 'required',
                'province_name' => 'required',
                'city_code' => 'required',
                'city_name' => 'required',
                'district_code' => 'required',
                'district_name' => 'required',
                // 'cate_id'=>'required|exists:goods_cate,id',
            ], [
                'required' => ':attribute为必填项！',
                'min' => ':attribute长度不符合要求！',
                'max' => ':attribute长度不符合要求！',
                'unique' => ':attribute已经被人占用！',
                'exists' => ':attribute不存在！'
            ], [
                'su_store_address' => '学校详细地址',
                'school_name' => '学校名称',
                'school_sort_name' => '学校简称',
                'school_icon' => '学校图标',
                'school_stdcode' => '学校机构编号',
                'school_type' => '学校类型',
                'province_code' => '省编码',
                'province_name' => '省名称',
                'city_code' => '市编码',
                'city_name' => '市名称',
                'district_code' => '区编码',
                'district_name' => '区名称',
            ]);

            if ($validate->fails()) {
                $this->message = $validate->getMessageBag()->first();
                return $this->format();
            }


            $logo = $this->logoCheck($cin['school_icon']);

            if ($logo['status'] != 1) {
                $this->message = $logo['message'];
                return $this->format();
            }


            $school = \App\Models\StuStore::create($cin);

            $this->status = 1;
            $this->message = '学校已创建，请等待审核！';
            return $this->format();


        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage() . $e->getFile() . $e->getLine();
            return $this->format();
        }

    }




    // public $pay_notify_url=url('api/school/pay/notify');//支付结果异步通知地址

    /*
        store_id 要唯一     客户端传过来的parent_store_id是父级的store_id
    */
    public function add()
    {

        try {

            $pay_notify_url = url('api/school/pay/notify');//支付结果异步通知地址

            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;


            $merchant_id = $loginer->merchant_id;


            // 数据库字段
            $cin = [
                'store_id' => $request->get('store_id'),
                'user_id' => $loginer->user_id,
                'merchant_id' => $merchant_id,
                'config_id' => $loginer->config_id,
                'pid' => !empty($request->get('parent_store_id')) ? $request->get('parent_store_id') : '',//上级id
                'school_no' => '',

                'school_name' => $request->get('school_name'),
                'school_sort_name' => $request->get('school_sort_name'),
                'school_icon' => $request->get('school_icon'),//图片宽度 高度  必须是  108*108  不大于20kb
                'school_stdcode' => empty($request->get('school_stdcode')) ? '' : $request->get('school_stdcode'),
                'school_type' => $request->get('school_type'),
                'province_code' => $request->get('province_code'),
                'province_name' => $request->get('province_name'),
                'city_code' => $request->get('city_code'),
                'city_name' => $request->get('city_name'),
                'district_code' => $request->get('district_code'),
                'district_name' => $request->get('district_name'),
                'su_store_address' => $request->get('su_store_address'),

                'status' => '2',
                'status_desc' => '未审核',//未审核

                'alipay_status' => 2,
                'alipay_status_desc' => '未同步'
            ];


            // 创建主校
            // store_id 在  merchant_store中
            // store_id不在stu_store中
            if (empty($request->get('parent_store_id'))) {

                if (empty($cin['store_id'])) {
                    $this->message = '学校编号不存在！';
                    return $this->format();
                }


                $store = \App\Models\StuStore::where('store_id', $cin['store_id'])->first();
                if (!empty($store)) {
                    $this->message = '该学校编号已经创建了学校，请不要重复创建！';
                    return $this->format();
                }

                $can_create_store = \App\Models\MerchantStore::where('store_id', $cin['store_id'])->first();
                if (empty($can_create_store)) {
                    $this->message = '请传入要创建的学校编号';
                    return $this->format();
                }


                // 创建子校
                // 创建子校判断parent_store_id在stu_store中  并且随机生成store_id


            } else {

                $store = \App\Models\StuStore::where('store_id', $cin['pid'])->first();
                if (empty($store)) {
                    $this->message = '主校不存在，无法创建子校！';
                    return $this->format();
                }


                $can_create_store = \App\Models\MerchantStore::where('store_id', $cin['store_id'])->first();
                if (empty($can_create_store)) {
                    $this->message = '请传入要创建的学校编号';
                    return $this->format();
                }


                if (!empty($store->pid)) {
                    $this->message = '非主校，无法创建子校！';
                    return $this->format();
                }

                $have_son = \App\Models\StuStore::where('pid', $cin['pid'])->where('store_id', $cin['store_id'])->first();
                if (!empty($have_son)) {
                    $this->message = '子校已经存在，无法创建！';
                    return $this->format();
                }

                if ($cin['store_id'] == $cin['pid']) {

                    $this->message = '选择的学校不正确！';
                    return $this->format();
                }

                // var_dump($cin);die;


            }


            $validate = \Validator::make($cin, [
                'su_store_address' => 'required',
                'school_name' => 'required',
                'school_sort_name' => 'required',
                'school_icon' => 'required',
                // 'school_stdcode'=>'required',
                'school_type' => 'required',
                'province_code' => 'required',
                'province_name' => 'required',
                'city_code' => 'required',
                'city_name' => 'required',
                'district_code' => 'required',
                'district_name' => 'required',
                // 'cate_id'=>'required|exists:goods_cate,id',
            ], [
                'required' => ':attribute为必填项！',
                'min' => ':attribute长度不符合要求！',
                'max' => ':attribute长度不符合要求！',
                'unique' => ':attribute已经被人占用！',
                'exists' => ':attribute不存在！'
            ], [
                'su_store_address' => '学校详细地址',
                'school_name' => '学校名称',
                'school_sort_name' => '学校简称',
                'school_icon' => '学校图标',
                'school_stdcode' => '学校机构编号',
                'school_type' => '学校类型',
                'province_code' => '省编码',
                'province_name' => '省名称',
                'city_code' => '市编码',
                'city_name' => '市名称',
                'district_code' => '区编码',
                'district_name' => '区名称',
            ]);

            if ($validate->fails()) {
                $this->message = $validate->getMessageBag()->first();
                return $this->format();
            }


            $logo = $this->logoCheck($cin['school_icon']);

            if ($logo['status'] != 1) {
                $this->message = $logo['message'];
                return $this->format();
            }


            $school = \App\Models\StuStore::create($cin);

            $this->status = 1;
            $this->message = '学校已创建，请等待审核！';
            return $this->format();


        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage() . $e->getFile() . $e->getLine();
            return $this->format();
        }

    }


    // public $pay_notify_url=url('api/school/pay/notify');//支付结果异步通知地址

    /*
        store_id 要唯一     客户端传过来的parent_store_id是父级的store_id
    */
    public function add_bak()
    {

        try {

            $pay_notify_url = url('api/school/pay/notify');//支付结果异步通知地址

            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;


            $merchant_id = $loginer->merchant_id;


            // 数据库字段
            $cin = [
                // 'store_id'=>$choose_store_id,
                'user_id' => $loginer->user_id,
                'merchant_id' => $merchant_id,
                'config_id' => $loginer->config_id,
                'pid' => !empty($request->get('parent_store_id')) ? $request->get('parent_store_id') : 0,//上级id
                'school_no' => '',

                'school_name' => $request->get('school_name'),
                'school_sort_name' => $request->get('school_sort_name'),
                'school_icon' => $request->get('school_icon'),//图片宽度 高度  必须是  108*108  不大于20kb
                'school_stdcode' => empty($request->get('school_stdcode')) ? '' : $request->get('school_stdcode'),
                'school_type' => $request->get('school_type'),
                'province_code' => $request->get('province_code'),
                'province_name' => $request->get('province_name'),
                'city_code' => $request->get('city_code'),
                'city_name' => $request->get('city_name'),
                'district_code' => $request->get('district_code'),
                'district_name' => $request->get('district_name'),
                'su_store_address' => $request->get('su_store_address'),

                'status' => '2',
                'status_desc' => '未审核',//未审核

                'alipay_status' => 2,
                'alipay_status_desc' => '未同步'
            ];


            if (empty($request->get('parent_store_id'))) {

                $merchant_id = $loginer->merchant_id;
                $get_can_all_store_id = \App\Models\MerchantStore::select(DB::raw('distinct merchant_stores.store_id'))->LeftJoin('stu_stores', 'stu_stores.store_id', '!=', 'merchant_stores.store_id')->where('merchant_stores.merchant_id', $merchant_id)->get();

                $can_all_store_id = [];
                if (!$get_can_all_store_id->isEmpty()) {
                    $can_all_store_id = array_map(function ($value) {
                        return $value['store_id'];
                    }, $get_can_all_store_id->toArray());
                }

                if (empty($can_all_store_id)) {
                    $this->message = '没有可用的store_id来创建学校';
                    return $this->format();
                }

                $get_have_store_id = \App\Models\StuStore::where('merchant_id', $merchant_id)->get();
                $have_store_id = [];
                foreach ($get_have_store_id as $v) {
                    $have_store_id[] = $v->store_id;
                }

                $can_use_store_id = array_diff($can_all_store_id, $have_store_id);

                if (empty($can_use_store_id)) {
                    $this->message = '可用的store_id能创建的学校都已经创建好了';
                    return $this->format();
                }

                $choose_store_id = array_shift($can_use_store_id);

                /*
                                $school_stdcode=$request->get('school_stdcode');
                                if(empty($school_stdcode))
                                {
                                    $this->message='学校机构编号不能为空！';
                                    return $this->format();
                                }
                */
                $have = \App\Models\StuStore::where('store_id', $choose_store_id)->first();

                if (!empty($have)) {
                    $this->message = '学校已经创建，请去学校列表中查看！';
                    return $this->format();
                }
                $cin['store_id'] = $choose_store_id;
                // 创建子校
            } else {

                $have = \App\Models\StuStore::where('store_id', $request->get('store_id'))->first();
                if (empty($have)) {
                    $this->message = '主校不存在，无法创建子校！';
                    return $this->format();
                }

                $cin['store_id'] = mt_rand(10000, 99999);

                $cin['pid'] = $have->id;


            }


            $validate = \Validator::make($cin, [
                'su_store_address' => 'required',
                'school_name' => 'required',
                'school_sort_name' => 'required',
                'school_icon' => 'required',
                // 'school_stdcode'=>'required',
                'school_type' => 'required',
                'province_code' => 'required',
                'province_name' => 'required',
                'city_code' => 'required',
                'city_name' => 'required',
                'district_code' => 'required',
                'district_name' => 'required',
                // 'cate_id'=>'required|exists:goods_cate,id',
            ], [
                'required' => ':attribute为必填项！',
                'min' => ':attribute长度不符合要求！',
                'max' => ':attribute长度不符合要求！',
                'unique' => ':attribute已经被人占用！',
                'exists' => ':attribute不存在！'
            ], [
                'su_store_address' => '学校详细地址',
                'school_name' => '学校名称',
                'school_sort_name' => '学校简称',
                'school_icon' => '学校图标',
                'school_stdcode' => '学校机构编号',
                'school_type' => '学校类型',
                'province_code' => '省编码',
                'province_name' => '省名称',
                'city_code' => '市编码',
                'city_name' => '市名称',
                'district_code' => '区编码',
                'district_name' => '区名称',
            ]);

            if ($validate->fails()) {
                $this->message = $validate->getMessageBag()->first();
                return $this->format();
            }


            $logo = $this->logoCheck($cin['school_icon']);

            if ($logo['status'] != 1) {
                $this->message = $logo['message'];
                return $this->format();
            }


            $school = \App\Models\StuStore::create($cin);

            $this->status = 1;
            $this->message = '学校已创建，请等待审核！';
            return $this->format();


        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage() . $e->getFile() . $e->getLine();
            return $this->format();
        }

    }

    private function logoCheck($logo)
    {
        return ['status' => 1, 'message' => '通过！'];

        // echo url('/');die;//https://pay.umxnt.com
        // echo $logo;die;//https://pay.umxnt.com/upload/images/store/15305835561687.png
        $relate_pic_path = 'public' . str_replace(url('/'), '', $logo);
        $ab_path = base_path($relate_pic_path);

        if (!file_exists($ab_path)) {
            return ['status' => 2, 'message' => 'logo不存在！'];
        }

        $arr = getimagesize($ab_path);

        // var_dump($arr);die;

        $width = $arr[0];//宽
        $height = $arr[1];//搞
        $type = $arr[2];//1 = GIF，2 = JPG，3 = PNG，

        if ($width != 108 || $height != 108) {
            return ['status' => 2, 'message' => '请上传logo图片尺寸为108*108！'];
        }

        if ($type != 2 && $type != 3) {
            return ['status' => 2, 'message' => 'logo仅支持jpg和png两种格式！'];
        }

        if (filesize($ab_path) > 20 * 1024) {
            return ['status' => 2, 'message' => 'logo不大于20kb！'];

        }


        return ['status' => 1, 'message' => '通过！'];


    }


    /*
        根据store_id  修改学校信息
    */
    public function save()
    {


        try {

            $pay_notify_url = url('api/school/pay/notify');//支付结果异步通知地址
            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;


            $store_id = $request->get('store_id');
            if (empty($store_id)) {
                $this->message = '请传入要修改的store_id！';
                return $this->format();
            }


            $school = \App\Models\StuStore::where('store_id', $store_id)->first();
            if (empty($school)) {
                $this->message = '您要修改的学校不存在！';
                return $this->format();
            }

            if ($school->alipay_status == 1) {
                $this->message = '当前学校状态已不能修改！';
                return $this->format();
            }


            // 数据库字段
            $cin = [
                // 'store_id'=>$choose_store_id,
// 'user_id'=>$loginer->user_id,
                // 'merchant_id'=>$merchant_id,
                // 'config_id'=>$loginer->config_id,
                // 'pid'=>!empty($request->get('parent_store_id')) ? $request->get('parent_store_id') : 0,//上级id
                // 'school_no'=>'',

                'school_name' => $request->get('school_name'),
                'school_sort_name' => $request->get('school_sort_name'),
                'school_icon' => $request->get('school_icon'),//图片宽度 高度  必须是  108*108  不大于20kb
                'school_stdcode' => empty($request->get('school_stdcode')) ? '' : $request->get('school_stdcode'),
                'school_type' => $request->get('school_type'),
                'province_code' => $request->get('province_code'),
                'province_name' => $request->get('province_name'),
                'city_code' => $request->get('city_code'),
                'city_name' => $request->get('city_name'),
                'district_code' => $request->get('district_code'),
                'district_name' => $request->get('district_name'),
                'su_store_address' => $request->get('su_store_address'),

                'status' => '2',
                'status_desc' => '未审核',//未审核

                // 'alipay_status'=>2,
                // 'alipay_status_desc'=>'未同步'
            ];

            $validate = \Validator::make($cin, [
                'su_store_address' => 'required',
                'school_name' => 'required',
                'school_sort_name' => 'required',
                'school_icon' => 'required',
                // 'school_stdcode'=>'required',
                'school_type' => 'required',
                'province_code' => 'required',
                'province_name' => 'required',
                'city_code' => 'required',
                'city_name' => 'required',
                'district_code' => 'required',
                'district_name' => 'required',
                // 'cate_id'=>'required|exists:goods_cate,id',
            ], [
                'required' => ':attribute为必填项！',
                'min' => ':attribute长度不符合要求！',
                'max' => ':attribute长度不符合要求！',
                'unique' => ':attribute已经被人占用！',
                'exists' => ':attribute不存在！'
            ], [
                'su_store_address' => '学校详细地址',
                'school_name' => '学校名称',
                'school_sort_name' => '学校简称',
                'school_icon' => '学校图标',
                'school_stdcode' => '学校机构编号',
                'school_type' => '学校类型',
                'province_code' => '省编码',
                'province_name' => '省名称',
                'city_code' => '市编码',
                'city_name' => '市名称',
                'district_code' => '区编码',
                'district_name' => '区名称',
            ]);

            if ($validate->fails()) {
                $this->message = $validate->getMessageBag()->first();
                return $this->format();
            }

            $ok = $school->update($cin);

            if ($ok) {
                $this->status = 1;
                $this->message = '修改成功！';
                return $this->format();

            } else {
                $this->status = 2;
                $this->message = '修改失败，请重试！';
                return $this->format();
            }


        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage();
            return $this->format();
        }


    }


    /*
        根据store_id  修改学校信息
    */
    public function save_bak()
    {


        try {

            $pay_notify_url = url('api/school/pay/notify');//支付结果异步通知地址
            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;


            $store_id = $request->get('store_id');
            if (empty($store_id)) {
                $this->message = '请传入要修改的store_id！';
                return $this->format();
            }

            /*
                        $have = \App\Models\MerchantStore::where('store_id',$store_id)->where('merchant_id',$loginer->merchant_id)->first();
                        if(empty($have))
                        {
                            $this->message='您无权修改！';
                            return $this->format();
                        }
            */

            $school = \App\Models\StuStore::where('store_id', $store_id)->first();
            if (empty($school)) {
                $this->message = '您要修改的学校不存在！';
                return $this->format();
            }

            if ($school->alipay_status == 1) {

                $this->message = '当前学校状态已不能修改！';
                return $this->format();
            }


            //只能修改   status  school_short_name      异步通知地址只能邮件去修改
            $cin = [];

            if (!empty($request->get('school_name'))) {
                $cin['school_name'] = $request->get('school_name');
            }

            if (!empty($request->get('school_sort_name'))) {
                $cin['school_sort_name'] = $request->get('school_sort_name');
            }

            if (!empty($request->get('school_icon'))) {
                $cin['school_icon'] = $request->get('school_icon');


                $logo = $this->logoCheck($cin['school_icon']);

                if ($logo['status'] != 1) {
                    $this->message = $logo['message'];
                    return $this->format();
                }


            }

            if (!empty($request->get('school_stdcode'))) {
                $cin['school_stdcode'] = $request->get('school_stdcode');
            }

            if (!empty($request->get('school_type'))) {
                $cin['school_type'] = $request->get('school_type');
            }

            if (!empty($request->get('province_code'))) {
                $cin['province_code'] = $request->get('province_code');
            }

            if (!empty($request->get('province_name'))) {
                $cin['province_name'] = $request->get('province_name');
            }

            if (!empty($request->get('city_code'))) {
                $cin['city_code'] = $request->get('city_code');
            }

            if (!empty($request->get('su_store_address'))) {
                $cin['su_store_address'] = $request->get('su_store_address');
            }

            if (!empty($request->get('city_name'))) {
                $cin['city_name'] = $request->get('city_name');
            }

            if (!empty($request->get('district_code'))) {
                $cin['district_code'] = $request->get('district_code');
            }

            if (!empty($request->get('district_name'))) {
                $cin['district_name'] = $request->get('district_name');
            }

            if (empty($cin)) {
                $this->message = '请传入要修改的参数！';
                return $this->format();
            }

            $cin['status'] = 2;//系统审核中
            $cin['status_desc'] = '未审核';


            $ok = $school->update($cin);


            $this->status = 1;
            $this->message = '修改成功！';
            return $this->format();


        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage();
            return $this->format();
        }


    }


    /*
        学校资料同步到支付宝
    */
    public function sync()
    {
        try {

            $request = app('request');
            $this->status = 2;

            $store_id = $request->get('store_id');
            $school = \App\Models\StuStore::where('store_id', $store_id)->first();
            if (empty($school)) {
                $this->message = '学校资料不存在！';
                return $this->format();
            }
            if ($school->alipay_status == 1) {
                $this->message = '学校资料已经同步过了，不能再次同步！';
                return $this->format();
            }

            if ($school->status != 1) {
                $this->message = '请等待系统审核后同步！';
                return $this->format();
            }

            if (!in_array($school->school_type, [1, 2, 3, 4, 5])) {
                $this->message = '学校类型不支持！';
                return $this->format();
            }

            /*
                        if(empty($school->school_stdcode))
                        {
                            $this->message='请填写学校编号后才能同步到支付宝！';
                            return $this->format();
                        }
            */

            $pay_notify_url = url('api/school/pay/notify');//支付结果异步通知地址


// 配置检测--------start
            $ali_config = \App\Models\AlipayIsvConfig::where('config_id', $school->config_id)->first();

            //pid
            $check = \App\Logic\CheckField\CheckAlipayIsvConfigs::schoolconfig($ali_config);

            if ($check !== true) {
                $this->message = $check;
                return $this->format();
            }


            /*
                        // app_name  app_phone
                        $ali_config_msg = \App\Models\AppConfigMsg::where('config_id',$school->config_id)->first();

                        $check = \App\Logic\CheckField\CheckAlipayIsvConfigs::schoolconfigmsg($ali_config_msg);

                        if($check !== true)
                        {
                            $this->message=$check;
                            return $this->format();
                        }


            */


            //ali_user_id
            $ali_app_auth_user = \App\Models\AlipayAppOauthUsers::where('store_id', $school->store_id)->first();

            $check = \App\Logic\CheckField\CheckAlipayIsvConfigs::schoolauthuser($ali_app_auth_user);

            if ($check !== true) {
                $this->message = $check;
                return $this->format();
            }

// 配置检测--------end


            $img_type = substr($school->school_icon, strrpos($school->school_icon, '.') + 1);
            $send_ali_data = [
                'school_name' => $school->school_name,
                // 'school_icon'=>$school->school_icon,
                // 'school_icon_type'=>$img_type,
                'school_stdcode' => $school->school_stdcode,
                'school_type' => implode(',', str_split($school->school_type)),
                'province_code' => $school->province_code,
                'province_name' => $school->province_name,
                'city_code' => $school->city_code,
                'city_name' => $school->city_name,
                'district_code' => $school->district_code,
                'district_name' => $school->district_name,

                'isv_name' => $ali_config->isv_name,

                'isv_notify_url' => $pay_notify_url,
                'isv_pid' => $ali_config->alipay_pid,

                'isv_phone' => $ali_config->isv_phone,
                'school_pid' => $ali_app_auth_user->alipay_user_id,
            ];

            $send_ali_data = array_filter($send_ali_data);


            $api_return = \App\Logic\PrimarySchool\SchoolInfo::sync($ali_config, $send_ali_data, $ali_app_auth_user);

            if ($api_return['status'] == 1) {

                if (!empty($api_return['school_no'])) {
                    $school->school_no = $api_return['school_no'];
                }

                $school->alipay_status = 1;
                $school->alipay_status_desc = '支付宝审核通过';
                $school->update();

                $this->status = 1;
                $this->message = '同步成功！' . $api_return['message'];
                return $this->format();

            } else {

                $school->alipay_status = 3;
                $school->alipay_status_desc = '支付宝审核失败：' . $api_return['message'];
                $school->update();


                $this->message = '同步失败！' . $api_return['message'];
                return $this->format();

            }
        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage();
            return $this->format();
        }
    }


    // public $pay_notify_url=url('api/school/pay/notify');//支付结果异步通知地址


    public function addBefore_wrong()
    {

        try {

            $pay_notify_url = url('api/school/pay/notify');//支付结果异步通知地址


            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;


            // var_dump($loginer);die;


            $merchant_id = $loginer->merchant_id;
            $get_can_all_store_id = \App\Models\MerchantStore::select(DB::raw('distinct merchant_stores.store_id'))->LeftJoin('stu_stores', 'stu_stores.store_id', '!=', 'merchant_stores.store_id')->where('merchant_stores.merchant_id', $merchant_id)->get();

            $can_all_store_id = [];
            if (!$get_can_all_store_id->isEmpty()) {
                $can_all_store_id = array_map(function ($value) {
                    return $value['store_id'];
                }, $get_can_all_store_id->toArray());

            }

            if (empty($can_all_store_id)) {
                $this->message = '没有可用的store_id来创建学校';
                return $this->format();
            }


            $choose_store_id = array_shift($can_all_store_id);


            /*            $school_stdcode=$request->get('school_stdcode');
                        if(empty($school_stdcode))
                        {
                            $this->message='学校机构编号不能为空！';
                            return $this->format();
                        }

                        \App\Models\StuStore::whereIn('store_id',$can_all_store_id)->where()->

            */

// 配置检测--------start
            $ali_config = \App\Models\AlipayIsvConfig::where('config_id', $loginer->config_id)->first();

            //pid
            $check = \App\Logic\CheckField\CheckAlipayIsvConfigs::schoolconfig($ali_config);

            if ($check !== true) {
                $this->message = $check;
                return $this->format();
            }


            // app_name  app_phone
            $ali_config_msg = \App\Models\AppConfigMsg::where('config_id', $loginer->config_id)->first();

            $check = \App\Logic\CheckField\CheckAlipayIsvConfigs::schoolconfigmsg($ali_config_msg);

            if ($check !== true) {
                $this->message = $check;
                return $this->format();
            }


            //ali_user_id
            $ali_app_auth_user = \App\Models\AlipayAppOauthUsers::where('store_id', $choose_store_id)->first();

            $check = \App\Logic\CheckField\CheckAlipayIsvConfigs::schoolauthuser($ali_app_auth_user);

            if ($check !== true) {
                $this->message = $check;
                return $this->format();
            }

// 配置检测--------end


            // 数据库字段
            $cin = [
                'store_id' => $choose_store_id,
                'merchant_id' => $merchant_id,
                'config_id' => $loginer->config_id,
                'pid' => 0,//上级id
                'school_no' => '',

                'school_name' => $request->get('school_name'),
                'school_sort_name' => $request->get('school_sort_name'),
                'school_icon' => $request->get('school_icon'),
                'school_stdcode' => $request->get('school_stdcode'),
                'school_type' => $request->get('school_type'),
                'province_code' => $request->get('province_code'),
                'province_name' => $request->get('province_name'),
                'city_code' => $request->get('city_code'),
                'city_name' => $request->get('city_name'),
                'district_code' => $request->get('district_code'),
                'district_name' => $request->get('district_name'),

                'status' => '2',
                'status_desc' => '正在提交审核',
            ];

            $validate = \Validator::make($cin, [
                'school_name' => 'required',
                'school_sort_name' => 'required',
                'school_icon' => 'required',
                'school_stdcode' => 'required',
                'school_type' => 'required',
                'province_code' => 'required',
                'province_name' => 'required',
                'city_code' => 'required',
                'city_name' => 'required',
                'district_code' => 'required',
                'district_name' => 'required',
                // 'cate_id'=>'required|exists:goods_cate,id',
            ], [
                'required' => ':attribute为必填项！',
                'min' => ':attribute长度不符合要求！',
                'max' => ':attribute长度不符合要求！',
                'unique' => ':attribute已经被人占用！',
                'exists' => ':attribute不存在！'
            ], [
                'school_name' => '学校名称',
                'school_sort_name' => '学校简称',
                'school_icon' => '学校图标',
                'school_stdcode' => '学校机构编号',
                'school_type' => '学校类型',
                'province_code' => '省编码',
                'province_name' => '省名称',
                'city_code' => '市编码',
                'city_name' => '市名称',
                'district_code' => '区编码',
                'district_name' => '区名称',
            ]);

            if ($validate->fails()) {
                $this->message = $validate->getMessageBag()->first();
                return $this->format();
            }


            $school = \App\Models\StuStore::create($cin);

            $img_type = substr($cin['school_icon'], strrpos($cin['school_icon'], '.') + 1, 3);
// echo $img_type;die;

            $send_ali_data = [
                'school_name' => $cin['school_name'],
                // 'school_icon'=>$cin['school_icon'],
                // 'school_icon_type'=>$img_type,
                'school_stdcode' => $cin['school_stdcode'],
                'school_type' => $cin['school_type'],
                'province_code' => $cin['province_code'],
                'province_name' => $cin['province_name'],
                'city_code' => $cin['city_code'],
                'city_name' => $cin['city_name'],
                'district_code' => $cin['district_code'],
                'district_name' => $cin['district_name'],

                'isv_name' => $ali_config_msg->app_name,
                'isv_notify_url' => $pay_notify_url,
                'isv_pid' => $ali_config->alipay_pid,
                'isv_phone' => $ali_config_msg->app_phone,
                'school_pid' => $ali_app_auth_user->alipay_user_id,
            ];
            //支付宝接口创建
            try {

                $aop = \App\Logic\Common\InitAliAop::aop($ali_config);

                $ali_request = new \Alipayopen\Sdk\Request\AlipayEcoEduKtSchoolinfoModifyRequest ();
                $ali_request->setBizContent(json_encode($send_ali_data)/*"{" .
                "\"school_name\":\"杭州市西湖第一实验学校\"," .
                "\"school_icon\":\"http://m.umxnt.com/user/img/logo.png\"," .
                "\"school_icon_type\":\"png\"," .
                "\"school_stdcode\":\"3133005132\"," .  //  学校(机构)标识码（由教育部按照国家标准及编码规则编制，可以在教育局官网查询）
                "\"school_type\":\"4\"," .
                "\"province_code\":\"330000\"," .
                "\"province_name\":\"浙江省\"," .
                "\"city_code\":\"330100\"," .
                "\"city_name\":\"杭州市\"," .
                "\"district_code\":\"330106\"," .
                "\"district_name\":\"西湖区\"," .

                // "\"isv_no\":\"201600129391238873\"," . //pid  
                "\"isv_name\":\"杭州少年宫\"," .   //app_name
                "\"isv_notify_url\":\"https://isv.com/xxx\"," .   //    此通知地址是为了保持教育缴费平台与ISV商户支付状态一致性。用户支付成功后，支付宝会根据本isv_notify_url，通过POST请求的形式将支付结果作为参数通知到商户系统，ISV商户可以根据返回的参数更新账单状态。
                "\"isv_pid\":\"2088121212121212\"," .  //填写已经签约教育缴费的isv的支付宝PID
                "\"isv_phone\":\"13300000000\"," .  //  ISV联系电话,用于账单详情页面显示

                "\"school_pid\":\"20880012939123234423\"," .  //  alipay_user_id  学校用来签约支付宝教育缴费的支付宝PID

                // "\"bankcard_no\":\"P0004\"," .
                // "\"bank_uid\":\"20000293230232\"," .
                // "\"bank_notify_url\":\"https://www.xxx.xxx/xx\"," .
                // "\"bank_partner_id\":\"200002924334\"," .
                // "\"white_channel_code\":\"TESTBANK10301\"" .


                "  }"*/);
                $result = $aop->execute($ali_request);

                \App\Common\Log::write($result, 'stu_add.txt');

                $responseNode = str_replace(".", "_", $ali_request->getApiMethodName()) . "_response";
                $resultCode = $result->$responseNode->code;

                if (!empty($resultCode) && $resultCode == 10000 && $result->$responseNode->status == 'Y') {
                    // status  Y 表示成功  N  表示失败
                    // school_no  学校在支付宝的编号

                    $third_data = ['status' => 1, 'school_no' => $result->$responseNode->school_no];
                    // echo "成功";
                } else {


                    $msg = isset($result->$responseNode->sub_msg) ? $result->$responseNode->sub_msg : '';
                    $msg .= isset($result->$responseNode->msg) ? $result->$responseNode->msg : '';


                    $third_data = ['status' => 2, 'message' => $msg];
                }

                // echo "失败";

            } catch (\Exception $e) {
                //支付宝接口错误
                // $third_data =  ['status'=>2,'message'=>$e->getMessage().$e->getLine()];
                $third_data = ['status' => 2, 'message' => '支付宝接口错误'];
            }


            if ($third_data['status'] != 1) {

                $school->status = '3';
                $school->status_desc = $third_data['message'];
                $school->update();

                $this->message = $third_data['message'];
                return $this->format();
            }

            $third_school_no = $third_data['school_no'];//得到支付宝学校编号

            $school->school_no = $third_school_no;
            $school->status = '1';
            $school->status_desc = '审核通过！';
            $school->update();


            $this->status = 1;
            $this->message = '学校创建成功！';
            return $this->format();


        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage() . $e->getFile() . $e->getLine();
            return $this->format();
        }


    }
































    /*








      `status` varchar(50) NOT NULL DEFAULT '2' COMMENT '状态状态 1 -成功  2-审核中 3-审核失败 4-关闭',
      `status_desc` varchar(255) NOT NULL DEFAULT '审核中' COMMENT '状态说明',
      `alipay_status` varchar(2) NOT NULL DEFAULT '2' COMMENT '状态 1-审核成功，2-未提交，3-失败',
      `alipay_status_desc` varchar(255) NOT NULL DEFAULT '未提交',

    商户添加学校    status=2  alipay_status=2

    代理商不通过  status=3  alipay_status=2
    代理商 和 支付宝通过学校  status=1  alipay_status=1
    代理商通过、支付宝不通过 status=1 alipay_status=3



    */


    /*


    third_status  状态状态 1 -所有审核通过  2-代理商审核中 3-代理商审核失败 4-支付宝审核中 5支付宝审核失败
    third_status_desc  审核状态原因

    status  1学校开启  2关闭
    status_desc  状态说明


    添加学校   status=2（未审核  alipay_status=2（未提交）
    代理商审核通过并提交支付宝  status=1（审核成功）   alipay_status=1或者3（支付宝成功或者失败）  ---  触发同步到支付宝
    代理商审核不通过  status=3（失败）  alipay_status=2(未提交）

    商户修改  只能在alipay_status 不等于1 的时候修改  status=2代理商审核


    */
    /*
        根据store_id  修改学校信息
    */
    public function save2()
    {


        try {

            $pay_notify_url = url('api/school/pay/notify');//支付结果异步通知地址
            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;


            $store_id = $request->get('store_id');
            if (empty($store_id)) {
                $this->message = '请传入要修改的store_id！';
                return $this->format();
            }

            $have = \App\Models\MerchantStore::where('store_id', $store_id)->where('merchant_id', $loginer->merchant_id)->first();
            if (empty($have)) {
                $this->message = '您无权修改！';
                return $this->format();
            }

            $school = \App\Models\StuStore::where('store_id', $store_id)->first();
            if (empty($school)) {
                $this->message = '您要修改的学校还没有创建！';
                return $this->format();
            }


            //只能修改   status  school_short_name      异步通知地址只能邮件去修改
            $cin = [/*
                'status'=>$request->get('status'),
                'school_short_name'=>$request->get('school_short_name')
           */];


            if (!empty($request->get('status'))) {
                $cin['status'] = $request->get('status');

                //
                if (!in_array($cin['status'], [1, 4])) {
                    $this->message = '您要修改的状态不正确！';
                    return $this->format();
                }

                if ($school->status == 2 || $school->status == 3) {
                    $this->message = '当前学校状态在审核或失败中，您不可以更改状态！';
                    return $this->format();

                }
            }


            if (!empty($request->get('school_sort_name'))) {
                $cin['school_sort_name'] = $request->get('school_sort_name');
            }

            if (empty($cin)) {
                $this->message = '请传入要修改的参数！';
                return $this->format();

            }


            $ok = $school->update($cin);


            $this->status = 1;
            return $this->format('修改成功！');


        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage();
            return $this->format();
        }


    }


    /*
        根据store_id
        对已有的学校支付宝审核失败的重新进件审核
    */
    public function haveRecordsave_wrong()
    {


        try {

            $pay_notify_url = url('api/school/pay/notify');//支付结果异步通知地址
            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;


            $store_id = $request->get('store_id');
            if (empty($store_id)) {
                $this->message = '请传入要修改的store_id！';
                return $this->format();
            }

            $have = \App\Models\MerchantStore::where('store_id', $store_id)->where('merchant_id', $loginer->merchant_id)->first();
            if (empty($have)) {
                $this->message = '您无权修改！';
                return $this->format();
            }

            $school = \App\Models\StuStore::where('store_id', $store_id)->first();
            if (empty($school)) {
                $this->message = '您要修改的学校还没有创建！';
                return $this->format();
            }


// 配置检测--------start
            $ali_config = \App\Models\AlipayIsvConfig::where('config_id', $loginer->config_id)->first();

            //pid
            $check = \App\Logic\CheckField\CheckAlipayIsvConfigs::schoolconfig($ali_config);

            if ($check !== true) {
                $this->message = $check;
                return $this->format();
            }


            // app_name  app_phone
            $ali_config_msg = \App\Models\AppConfigMsg::where('config_id', $loginer->config_id)->first();

            $check = \App\Logic\CheckField\CheckAlipayIsvConfigs::schoolconfigmsg($ali_config_msg);

            if ($check !== true) {
                $this->message = $check;
                return $this->format();
            }


            //ali_user_id
            $ali_app_auth_user = \App\Models\AlipayAppOauthUsers::where('store_id', $store_id)->first();

            $check = \App\Logic\CheckField\CheckAlipayIsvConfigs::schoolauthuser($ali_app_auth_user);

            if ($check !== true) {
                $this->message = $check;
                return $this->format();
            }

// 配置检测--------end


            $send_ali_data = [

                'school_name' => !empty($request->get('school_name')) ? $request->get('school_name') : $school->school_name,
                // 'school_icon'=>$cin['school_icon'],
                // 'school_icon_type'=>$img_type,
                'school_stdcode' => !empty($request->get('school_stdcode')) ? $request->get('school_stdcode') : $school->school_stdcode,
                'school_type' => !empty($request->get('school_type')) ? $request->get('school_type') : $school->school_type,
                'province_code' => !empty($request->get('province_code')) ? $request->get('province_code') : $school->province_code,
                'province_name' => !empty($request->get('province_name')) ? $request->get('province_name') : $school->province_name,
                'city_code' => !empty($request->get('city_code')) ? $request->get('city_code') : $school->city_code,
                'city_name' => !empty($request->get('city_name')) ? $request->get('city_name') : $school->city_name,
                'district_code' => !empty($request->get('district_code')) ? $request->get('district_code') : $school->district_code,
                'district_name' => !empty($request->get('district_name')) ? $request->get('district_name') : $school->district_name,


                'isv_name' => $ali_config_msg->app_name,
                'isv_notify_url' => $pay_notify_url,
                'isv_pid' => $ali_config->alipay_pid,
                'isv_phone' => $ali_config_msg->app_phone,
                'school_pid' => $ali_app_auth_user->alipay_user_id,
            ];


            // var_dump($send_ali_data);die;

            $cin = [
                // 'school_no'=>$send_ali_data['school_no'],
                'school_name' => $send_ali_data['school_name'],
                // 'school_icon'=>$send_ali_data['school_icon'],
                'school_stdcode' => $send_ali_data['school_stdcode'],
                'school_type' => $send_ali_data['school_type'],
                'province_code' => $send_ali_data['province_code'],
                'province_name' => $send_ali_data['province_name'],
                'city_code' => $send_ali_data['city_code'],
                'city_name' => $send_ali_data['city_name'],
                'district_code' => $send_ali_data['district_code'],
                'district_name' => $send_ali_data['district_name'],
            ];


            //支付宝接口创建
            try {

                $aop = \App\Logic\Common\InitAliAop::aop($ali_config);

                $ali_request = new \Alipayopen\Sdk\Request\AlipayEcoEduKtSchoolinfoModifyRequest ();
                $ali_request->setBizContent(json_encode($send_ali_data)/*"{" .
                "\"school_name\":\"杭州市西湖第一实验学校\"," .
                "\"school_icon\":\"http://m.umxnt.com/user/img/logo.png\"," .
                "\"school_icon_type\":\"png\"," .
                "\"school_stdcode\":\"3133005132\"," .  //  学校(机构)标识码（由教育部按照国家标准及编码规则编制，可以在教育局官网查询）
                "\"school_type\":\"4\"," .
                "\"province_code\":\"330000\"," .
                "\"province_name\":\"浙江省\"," .
                "\"city_code\":\"330100\"," .
                "\"city_name\":\"杭州市\"," .
                "\"district_code\":\"330106\"," .
                "\"district_name\":\"西湖区\"," .

                // "\"isv_no\":\"201600129391238873\"," . //pid  
                "\"isv_name\":\"杭州少年宫\"," .   //app_name
                "\"isv_notify_url\":\"https://isv.com/xxx\"," .   //    此通知地址是为了保持教育缴费平台与ISV商户支付状态一致性。用户支付成功后，支付宝会根据本isv_notify_url，通过POST请求的形式将支付结果作为参数通知到商户系统，ISV商户可以根据返回的参数更新账单状态。
                "\"isv_pid\":\"2088121212121212\"," .  //填写已经签约教育缴费的isv的支付宝PID
                "\"isv_phone\":\"13300000000\"," .  //  ISV联系电话,用于账单详情页面显示

                "\"school_pid\":\"20880012939123234423\"," .  //  alipay_user_id  学校用来签约支付宝教育缴费的支付宝PID

                // "\"bankcard_no\":\"P0004\"," .
                // "\"bank_uid\":\"20000293230232\"," .
                // "\"bank_notify_url\":\"https://www.xxx.xxx/xx\"," .
                // "\"bank_partner_id\":\"200002924334\"," .
                // "\"white_channel_code\":\"TESTBANK10301\"" .


                "  }"*/);
                $result = $aop->execute($ali_request);

                \App\Common\Log::write($result, 'stu_save.txt');

                $responseNode = str_replace(".", "_", $ali_request->getApiMethodName()) . "_response";
                $resultCode = $result->$responseNode->code;

                if (!empty($resultCode) && $resultCode == 10000 && $result->$responseNode->status == 'Y') {
                    // status  Y 表示成功  N  表示失败
                    // school_no  学校在支付宝的编号

                    $third_data = ['status' => 1, 'school_no' => isset($result->$responseNode->school_no) ? $result->$responseNode->school_no : ''];
                    // echo "成功";
                } else {


                    $msg = isset($result->$responseNode->sub_msg) ? $result->$responseNode->sub_msg : '';
                    $msg .= isset($result->$responseNode->msg) ? $result->$responseNode->msg : '';


                    $third_data = ['status' => 2, 'message' => $msg];
                }

                // echo "失败";

            } catch (\Exception $e) {
                //支付宝接口错误
                // $third_data =  ['status'=>2,'message'=>$e->getMessage().$e->getLine()];
                $third_data = ['status' => 2, 'message' => '支付宝接口错误'];
            }


            if ($third_data['status'] != 1) {

                $school->status = '3';
                $school->status_desc = $third_data['message'];
                $school->update();

                $this->message = $third_data['message'];
                return $this->format();
            }


            if (!empty($third_data['school_no'])) {
                $cin['school_no'] = $third_data['school_no'];
            }
            $ok = $school->update($cin);


            $this->status = 1;
            return $this->format('修改成功！');


        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage();
            return $this->format();
        }


    }


    public function del()
    {


        try {

            $request = app('request');

            $this->status = 2;

            $name = $request->get('name', '获取姓名');

            if (empty($name)) {
                $this->message = '参数不能为空！';
                return $this->format();
            }

            $data = $this->page(new Order)->where('id', '>', 1)->get();

            $this->status = 1;
            return $this->format($data);


        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage();
            return $this->format();
        }


    }


}
