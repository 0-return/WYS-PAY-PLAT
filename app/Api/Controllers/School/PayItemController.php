<?php

namespace App\Api\Controllers\School;

use Illuminate\Support\Facades\DB;

/* 
*/

class PayItemController extends \App\Api\Controllers\BaseController
{
    private function makeNo()
    {
        return str_random(12);
    }

    private function makeOrderNo()
    {
        return date('YmdHis') . mt_rand(10000, 99999);
    }

    public function newAdd()
    {

        try {
            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;
            $merchant_id = $loginer->merchant_id;


            $cin = [
                'store_id' => $request->get('store_id', ''),
                'merchant_id' => $merchant_id,
                'stu_order_batch_no' => $this->makeNo(),
                'stu_grades_no' => $request->get('stu_grades_no'),
                'class_and_rmstu' => $request->get('class_and_rmstu'),

                // 'stu_class_no'=>$request->get('stu_class_no'),
                // 'remove_student_no'=>empty($request->get('remove_student_no','')) ? '' : $request->get('remove_student_no',''),

                'stu_order_type_no' => $request->get('stu_order_type_no'),
                'gmt_end' => $request->get('gmt_end'),
                'batch_name' => $request->get('batch_name'),
                'status' => 2,
                'status_desc' => '等待审核',
            ];

            $have_batch = \App\Models\StuOrderBatch::where('stu_order_batch_no', $cin['stu_order_batch_no'])->first();
            if (!empty($have_batch)) {
                $this->message = '项目编号生成失败，请重试！';
                return $this->format();
            }


            $validate = \Validator::make($cin, [
                'store_id' => 'required|exists:stu_stores,store_id',
                'stu_grades_no' => 'required',
                // 'stu_class_no'=>'required',
                // 'remove_student_no'=>'required',
                'stu_order_type_no' => 'required',
                'gmt_end' => 'required',
                'batch_name' => 'required',
                // 'status_desc'=>'required',
                // 'cate_id'=>'required|exists:goods_cate,id',
            ], [
                'required' => ':attribute为必填项！',
                'min' => ':attribute长度不符合要求！',
                'max' => ':attribute长度不符合要求！',
                'unique' => ':attribute已经被人占用！',
                'exists' => ':attribute不存在！'
            ], [
                'store_id' => '学校编号',
                'stu_grades_no' => '年级编号',
                'stu_class_no' => '班级编号',
                'remove_student_no' => '免收学生编号列表',
                'stu_order_type_no' => '收费模板编号',
                'gmt_end' => '缴费截止时间',
                'batch_name' => '缴费项目名称',
            ]);

            if ($validate->fails()) {
                $this->message = $validate->getMessageBag()->first();
                return $this->format();
            }


            $pay_temp = (new \App\Models\StuOrderType)->where('stu_order_type_no', $request->get('stu_order_type_no'))->first();

            if (empty($pay_temp)) {

                $this->message = '收费模板不存在！';
                return $this->format();
            }


            if ($pay_temp->status != 1) {

                $this->message = '收费模板貌似未审核通过！';
                return $this->format();
            }

            $cin['batch_amount'] = $pay_temp->amount;
            $cin['batch_item'] = $pay_temp->charge_item;

            if (strtotime($cin['gmt_end']) <= time()) {
                $this->message = '缴费截止时间不正确！';
                return $this->format();
            }


            $have = \App\Models\StuOrderBatch::where('batch_name', $cin['batch_name'])->first();
            if (!empty($have)) {
                $this->message = '项目名称已存在，请更换项目名';
                return $this->format();

            }


            // 查找班级存在
            $arr = json_decode($cin['class_and_rmstu'], true);

            if (empty($arr)) {
                $this->message = '请传入要缴费的班级';
                return $this->format();
            }

            $cin_use_class = [];
            foreach ($arr as $v) {
                $cin_use_class[$v['stu_class_no']] = $v['remove_student_no'];
            }

            if (count($cin_use_class) <= 0) {
                $this->message = '请传入要缴费的班级';
                return $this->format();
            }

//            $get_use_class = \App\Models\StuClass::whereIn('stu_class_no', array_keys($cin_use_class))->get();
//
//            if (count($get_use_class) != count($cin_use_class)) {
//                $this->message = '您选择的要缴费的班级部分不存在！';
//                return $this->format();
//            }


            // 整理排除的学生

            $class_no_str = '';
            $rm_student_no_str = '';


            foreach ($cin_use_class as $k => $v) {
                $have_remove_str = self::removeStudentStr($v, $k);

                if ($have_remove_str['status'] != 1) {
                    $this->message = $have_remove_str['message'];
                    return $this->format();
                }

                $cin_use_class[$k] = $have_remove_str['remove_student_no_str'];

                $class_no_str .= $k . ',';
                $rm_student_no_str .= $have_remove_str['remove_student_no_str'] . ',';
            }

            $class_no_str = trim($class_no_str, ',');
            $rm_student_no_str = trim($rm_student_no_str, ',');


            $cin['stu_class_no'] = $class_no_str;
            $cin['remove_student_no'] = $rm_student_no_str;

            // 创建项目
            $ok = \App\Models\StuOrderBatch::create($cin);

            if ($ok) {
                $this->status = 1;
                $this->message = '项目创建成功，请等待审核！';
                return $this->format();
            } else {
                $this->message = '项目创建失败，请重试！';
                return $this->format();
            }

        } catch (\Exception $e) {

            \App\Common\Log::write('系统错误' . $e->getMessage() . $e->getFile() . $e->getLine(), 'add_item.txt');


            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage() . $e->getFile() . $e->getLine();
            return $this->format();
        }


    }


    /*
        修改
    */
    public function save()
    {

        try {
            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;

            $student = \App\Models\StuOrderBatch::
            where('stu_order_batch_no', $request->get('stu_order_batch_no'))
                ->first();

            if (empty($student)) {
                $this->message = '模板不存在！';
                return $this->format();
            }

            if ($student->status == 1) {
                $this->message = '当前状态已经无法修改！';
                return $this->format();
            }


            $cin = [
                'stu_grades_no' => $request->get('stu_grades_no'),
                'class_and_rmstu' => $request->get('class_and_rmstu'),//班级编号和排除学生

                'batch_name' => $request->get('batch_name'),//缴费项目名称
                'stu_order_type_no' => $request->get('stu_order_type_no'),//模板编号
                'gmt_end' => $request->get('gmt_end'),
                'status' => 2,
                'status_desc' => $request->get('status_desc'),
            ];

            $cin = array_filter($cin);

            if (empty($cin)) {
                $this->message = '请传入要修改的参数！';
                return $this->format();
            }


            if (!empty($cin['batch_name'])) {
                $have = \App\Models\StuOrderBatch::where('batch_name', $cin['batch_name'])->where('id', '!=', $student->id)->first();
                if (!empty($have)) {
                    $this->message = '项目名称已存在，请更换项目名！';
                    return $this->format();
                }

            }


            if ($cin['gmt_end'] && strtotime($cin['gmt_end']) <= time()) {
                $this->message = '缴费截止时间不正确！';
                return $this->format();
            }


            if (!empty($request->get('stu_order_type_no'))) {
                $pay_temp = (new \App\Models\StuOrderType)->where('stu_order_type_no', $request->get('stu_order_type_no'))->first();
                if (empty($pay_temp)) {
                    $this->message = '收费模板不存在！';
                    return $this->format();
                }

                if ($pay_temp->status != 1) {
                    $this->message = '收费模板貌似未审核通过！';
                    return $this->format();
                }

                $cin['batch_amount'] = $pay_temp->amount;
                $cin['batch_item'] = $pay_temp->charge_item;

            }


            if (!empty($request->get('class_and_rmstu'))) {
                // 查找班级存在
                $arr = json_decode($cin['class_and_rmstu'], true);

                if (empty($arr)) {
                    $this->message = '请传入要缴费的班级';
                    return $this->format();
                }

                $cin_use_class = [];
                foreach ($arr as $v) {
                    $cin_use_class[$v['stu_class_no']] = $v['remove_student_no'];
                }

                if (count($cin_use_class) <= 0) {
                    $this->message = '请传入要缴费的班级';
                    return $this->format();
                }

                $get_use_class = \App\Models\StuClass::whereIn('stu_class_no', array_keys($cin_use_class))->get();

                if (count($get_use_class) != count($cin_use_class)) {
                    $this->message = '您选择的要缴费的班级不存在！';
                    return $this->format();
                }

                // 整理排除的学生
                $class_no_str = '';
                $rm_student_no_str = '';


                foreach ($cin_use_class as $k => $v) {
                    $have_remove_str = self::removeStudentStr($v, $k);

                    if ($have_remove_str['status'] != 1) {
                        $this->message = $have_remove_str['message'];
                        return $this->format();
                    }

                    $cin_use_class[$k] = $have_remove_str['remove_student_no_str'];

                    $class_no_str .= $k . ',';
                    $rm_student_no_str .= $have_remove_str['remove_student_no_str'] . ',';
                }

                $class_no_str = trim($class_no_str, ',');
                $rm_student_no_str = trim($rm_student_no_str, ',');


                $cin['stu_class_no'] = $class_no_str;
                $cin['remove_student_no'] = $rm_student_no_str;
            }

            $student = $student->update($cin);


            $this->status = 1;
            $this->message = '修改缴费成功，请等待审核！';
            return $this->format();


        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage() . $e->getFile() . $e->getLine();
            return $this->format();
        }
    }


    /*
        添加
    */
    public function add_bak()
    {

        try {
            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;

            $merchant_id = $loginer->merchant_id;


            $cin = [
                'store_id' => $request->get('store_id', ''),
                'merchant_id' => $merchant_id,

                'stu_order_batch_no' => $this->makeNo(),

                'stu_grades_no' => $request->get('stu_grades_no'),
                'stu_class_no' => $request->get('stu_class_no'),
                'remove_student_no' => empty($request->get('remove_student_no', '')) ? '' : $request->get('remove_student_no', ''),
                'stu_order_type_no' => $request->get('stu_order_type_no'),
                'gmt_end' => $request->get('gmt_end'),
                'batch_name' => $request->get('batch_name'),
                'status' => 2,
                'status_desc' => '等待审核',
            ];

            // $have = \App\Models\StuStore::where()->first();


            $pay_temp = (new \App\Models\StuOrderType)->where('stu_order_type_no', $request->get('stu_order_type_no'))->first();

            if (empty($pay_temp)) {

                $this->message = '收费模板不存在！';
                return $this->format();
            }

            if ($pay_temp->status != 1) {

                $this->message = '收费模板貌似未审核通过！';
                return $this->format();
            }


            $validate = \Validator::make($cin, [
                'store_id' => 'required|exists:stu_stores,store_id',
                'stu_grades_no' => 'required',
                'stu_class_no' => 'required',
                // 'remove_student_no'=>'required',
                'stu_order_type_no' => 'required',
                'gmt_end' => 'required',
                'batch_name' => 'required',
                // 'status_desc'=>'required',
                // 'cate_id'=>'required|exists:goods_cate,id',
            ], [
                'required' => ':attribute为必填项！',
                'min' => ':attribute长度不符合要求！',
                'max' => ':attribute长度不符合要求！',
                'unique' => ':attribute已经被人占用！',
                'exists' => ':attribute不存在！'
            ], [
                'store_id' => '学校编号',
                'stu_grades_no' => '年级编号',
                'stu_class_no' => '班级编号',
                'remove_student_no' => '免收学生编号列表',
                'stu_order_type_no' => '收费模板编号',
                'gmt_end' => '缴费截止时间',
                'batch_name' => '缴费项目名称',
            ]);

            if ($validate->fails()) {
                $this->message = $validate->getMessageBag()->first();
                return $this->format();
            }

            if (strtotime($cin['gmt_end']) <= time()) {
                $this->message = '缴费截止时间不正确！';
                return $this->format();
            }

            $class = \App\Models\StuClass::where('stu_class_no', $cin['stu_class_no'])->first();
            if (empty($class)) {
                $this->message = '班级不存在！';
                return $this->format();
            }


            if (!empty($cin['remove_student_no'])) {

                $have_remove_str = self::removeStudentStr($cin['remove_student_no'], $class);

                if ($have_remove_str['status'] != 1) {
                    $this->message = $have_remove_str['message'];
                    return $this->format();
                }
                $cin['remove_student_no'] = $have_remove_str['remove_student_no_str'];


            }


            // var_dump($cin);die;


            $have = \App\Models\StuOrderBatch::where('batch_name', $cin['batch_name'])->first();
            if (!empty($have)) {
                $this->message = '项目名称已存在，请更换项目名';
                return $this->format();

            }

            $grade = \App\Models\StuOrderBatch::create($cin);


            $this->status = 1;
            $this->message = '收费项目创建成功，请等待审核！';
            return $this->format();


        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage() . $e->getFile() . $e->getLine();
            return $this->format();
        }
    }

    private static function removeStudentStr($str, $stu_class_no)
    {

        $remove_student_no_arr = explode(',', $str);

        if (empty($remove_student_no_arr)) {
            return ['status' => 2, 'message' => '不需要缴费的学生入参不正确！'];
        }
        $get_all_student = \App\Models\StuStudent::where('stu_class_no', $stu_class_no)->get();

        $all_student = [];
        foreach ($get_all_student as $v) {
            $all_student[] = $v->student_no;
        }

        $can_remove = [];
        foreach ($remove_student_no_arr as $v) {
            if (in_array($v, $all_student)) {
                $can_remove[] = $v;
            }
        }
        /*
                    if(empty($can_remove))
                    {
                        return ['status'=>2,'message'=>'您要排除缴费的学生在当前班级不存在！'];
                    }*/

        $remove_student_no_str = implode(',', $can_remove);

        return ['status' => 1, 'remove_student_no_str' => $remove_student_no_str];
    }

    private static function removeStudentStr_bak($str, $class)
    {

        $remove_student_no_arr = explode(',', $str);

        if (empty($remove_student_no_arr)) {
            return ['status' => 2, 'message' => '不需要缴费的学生入参不正确！'];
        }
        $get_all_student = \App\Models\StuStudent::where('stu_class_no', $class->stu_class_no)->get();

        $all_student = [];
        foreach ($get_all_student as $v) {
            $all_student[] = $v->student_no;
        }

        $can_remove = [];
        foreach ($remove_student_no_arr as $v) {
            if (in_array($v, $all_student)) {
                $can_remove[] = $v;
            }
        }

        if (empty($can_remove)) {
            return ['status' => 2, 'message' => '您要排除缴费的学生在当前班级不存在！'];
        }

        $remove_student_no_str = implode(',', $can_remove);

        return ['status' => 1, 'remove_student_no_str' => $remove_student_no_str];
    }

    /*
        列表
    */
    public function lst()
    {

        try {
            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;


            $all_store_id = [];

            if (!empty($request->get('store_id'))) {
                $all_store_id = [$request->get('store_id')];

            } else {

                // 所有学校
                $get_all_store_id = \App\Models\MerchantStore::where('merchant_id', $loginer->merchant_id)->get();

                if ($get_all_store_id->isEmpty()) {
                    $this->message = '没有数据！';
                    return $this->format();
                }


                foreach ($get_all_store_id as $v) {
                    $all_store_id[] = $v->store_id;
                }
            }
            $obj = new \App\Models\StuOrderBatch;

            $obj = $obj->
            whereIn('store_id', $all_store_id);

            if (!empty($request->get('str'))) {
                $obj = $obj->where('str', $request->get('str'));
            }


            if (!empty($request->get('stu_grades_no'))) {
                $obj = $obj->where('stu_grades_no', $request->get('stu_grades_no'));
            }


            if (!empty($request->get('stu_class_no'))) {
                $obj = $obj->where('stu_class_no', $request->get('stu_class_no'));
            }

            if (!empty($request->get('status'))) {
                $obj = $obj->where('status', $request->get('status'));
            }


            if (!empty($request->get('start_time'))) {
                $obj = $obj->where('created_at', '>=', $request->get('start_time'));
            }
            if (!empty($request->get('end_time'))) {
                $obj = $obj->where('gmt_end', '>=', $request->get('end_time'));
            }

            $obj = $obj->orderBy('id', 'desc');


            //班级  年级  学校  缴费模板名称  缴费总金额

            $get_all_school = \App\Models\StuStore::where('store_id', $all_store_id)->get();
            $all_school = [];
            foreach ($get_all_school as $v) {
                $all_school[$v->store_id] = $v;
            }


            $get_all_grade = \App\Models\StuGrade::get();
            $all_grade = [];
            foreach ($get_all_grade as $v) {
                $all_grade[$v->stu_grades_no] = $v;
            }


            $get_all_class = \App\Models\StuClass::get();
            $all_class = [];
            foreach ($get_all_class as $v) {
                $all_class[$v->stu_class_no] = $v;
            }

            $get_all_template = \App\Models\StuOrderType::where('store_id', $all_store_id)->get();
            $all_template = [];
            foreach ($get_all_template as $v) {
                $all_template[$v->stu_order_type_no] = $v;
            }


            $this->t = $obj->count();

            $data = $this->page($obj)->orderBy('id', 'desc')->get();

            $cout = [];

            if (!$data->isEmpty()) {
                $cout = array_map(function ($each) use ($all_school, $all_grade, $all_class, $all_template) {

                    $all_class_no = explode(',', $each['stu_class_no']);
                    $class_name = '';
                    foreach ($all_class_no as $class) {
                        $class_name .= isset($all_class[$class]) ? $all_class[$class]->stu_class_name . ',' : '';
                    }

                    $class_name = trim($class_name, ',');


                    return array_merge($each, [
                        'school_name' => isset($all_school[$each['store_id']]) ? $all_school[$each['store_id']]->school_name : '',
                        'grade_name' => isset($all_grade[$each['stu_grades_no']]) ? $all_grade[$each['stu_grades_no']]->stu_grades_name : '',
                        'class_name' => $class_name,

                        'template_name' => $each['batch_name'],
                        'amount' => $each['batch_amount'],
                    ]);

                }, $data->toArray());
            }


            $this->status = 1;
            $this->message = 'ok';
            return $this->format($cout);


        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage() . $e->getFile() . $e->getLine();
            return $this->format();
        }
    }


    /*
        单条
    */
    public function show()
    {

        try {
            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;


            $obj = new \App\Models\StuOrderBatch;

            $obj = $obj
                ->where('stu_order_batch_no', $request->get('stu_order_batch_no'));


            $data = $obj->first();

            if (empty($data)) {
                $this->message = '缴费不存在！';
                return $this->format();
            }


            //班级  年级  学校  缴费模板名称  缴费总金额

            $school = \App\Models\StuStore::where('store_id', $data->store_id)->first();
            $grade = \App\Models\StuGrade::where('stu_grades_no', $data->stu_grades_no)->first();
            $class = \App\Models\StuClass::where('stu_class_no', $data->stu_class_no)->first();
            // $template = \App\Models\StuOrderType::where('stu_order_type_no',$data->stu_order_type_no)->first();

            $data->school_name = isset($school->school_name) ? $school->school_name : '';
            $data->grade_name = isset($grade->stu_grades_name) ? $grade->stu_grades_name : '';
            // $data->class_name=isset($class->stu_class_name) ? $class->stu_class_name : '';
            // $data->template_name=isset($template->charge_name) ? $template->charge_name : '';
            $data->template_name = $data->batch_name;//batch_amount
            $data->amount = $data->batch_amount;//batch_amount
            // $data->amount=isset($template->amount) ? $template->amount : '';


            $get_all_class = \App\Models\StuClass::get();
            $all_class = [];
            foreach ($get_all_class as $v) {
                $all_class[$v->stu_class_no] = $v;
            }


            $all_class_no = explode(',', $data->stu_class_no);
            $class_name = '';
            foreach ($all_class_no as $class) {
                $class_name .= isset($all_class[$class]) ? $all_class[$class]->stu_class_name . ',' : '';
            }

            $class_name = trim($class_name, ',');

            $data->class_name = $class_name;


            $this->status = 1;
            $this->message = 'ok';
            return $this->format($data);


        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage() . $e->getFile() . $e->getLine();
            return $this->format();
        }
    }

    /*
        审核
    */
    public function check()
    {

        try {
            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;

            if ($loginer->merchant_type != 1) {

                $this->message = '您没有权限审核';
                return $this->format();

            }


            $merchant_id = $loginer->merchant_id;

            $obj = new \App\Models\StuOrderBatch;

            $obj = $obj->where('stu_order_batch_no', $request->get('stu_order_batch_no'));


            $obj = $obj->first();

            if (empty($obj)) {
                $this->message = '收费项目不存在！';
                return $this->format();
            }

            if ($obj->status != 2) {
                $this->message = '收费项目状态已经不在审核状态！';
                return $this->format();
            }

            $status = $request->get('status', '');
            $status_desc = $request->get('status_desc', '');


            if (!in_array($status, [1, 3])) {
                $this->message = '收费项目审核状态不正确！';
                return $this->format();
            }


            $all_order = 0;
            $msg = '';
            // 创建所有订单--------要么全部成功，要么审核失败
            if ($obj->status != 1 && $status == 1) {


                DB::beginTransaction();

                try {
                    $all_class = explode(',', $obj->stu_class_no);
                    $all_student = \App\Models\StuStudent::whereIn('stu_class_no', $all_class)->get();

                    $remove_student_except = explode(',', $obj->remove_student_no);

                    /*
                                        $stu_type = \App\Models\StuOrderType::where('stu_order_type_no',$obj->stu_order_type_no)->first();

                                        if(empty($stu_type))
                                        {
                                            $this->message='缴费项目模板不存在';
                                            return $this->format();
                                        }
                    */
                    $store = \App\Models\StuStore::where('store_id', $obj->store_id)->first();

                    if (empty($store)) {
                        $this->message = '学校不存在，无法通过审核！';
                        return $this->format();
                    }

                    // 子项目
                    $parseChild = \App\Logic\PrimarySchool\ChildItem::parse($obj->batch_item);

                    $i = 0;

                    foreach ($all_student as $student) {


                        $i++;
                        // 跳过不缴费的学生
                        if (in_array($student->student_no, $remove_student_except)) {
                            continue;
                        }
                        $grade = \App\Models\StuGrade::where('stu_grades_no', $student->stu_grades_no)->first();
                        $class = \App\Models\StuClass::where('stu_class_no', $student->stu_class_no)->first();

                        $all_order++;
                        $dbdata = [
                            'user_id' => $store->user_id,//店铺推广员id
                            'school_name' => $store->school_name,//
                            'batch_name' => $obj->batch_name,//
                            'stu_grades_name' => $grade->stu_grades_name,//
                            'stu_class_name' => $class->stu_class_name,//
                            'gmt_start' => $obj->created_at,
                            // 'pay_time'=>$pay_time,

                            'trade_no' => '',
                            'pay_type_source' => '',
                            'pay_type_source_desc' => '',

                            'pay_type' => '',//支付类型
                            'pay_type_desc' => '',//支付类型描述

                            'out_trade_no' => $this->makeOrderNo(),
                            'store_id' => $student->store_id,
                            'student_user_name' => $student->student_user_name,
                            'student_user_mobile' => $student->student_user_mobile,
                            'merchant_id' => $merchant_id,
                            'stu_grades_no' => $student->stu_grades_no,
                            'stu_class_no' => $student->stu_class_no,
                            'student_no' => $student->student_no,
                            'student_name' => $student->student_name,
                            'stu_order_batch_no' => $obj->stu_order_batch_no,
                            'stu_order_type_no' => $obj->stu_order_type_no,
                            'amount' => $parseChild['amount'],
                            'pay_amount' => 0,
                            'gmt_end' => $obj->gmt_end,
                            'pay_status' => 2,
                            'pay_status_desc' => '未支付'

                        ];


                        $ok = \App\Models\StuOrder::create($dbdata);

                        // 添加子项
                        foreach ($parseChild['all_item'] as $v) {
                            $v['out_trade_no'] = $dbdata['out_trade_no'];
                            $v['status'] = '2';
                            $ok_item = \App\Models\StuOrderItem::create($v);
                        }


                    }


                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();

                    $this->message = '审核失败：' . $e->getMessage() . $e->getFile() . $e->getLine();
                    return $this->format();
                    // echo $e->getMessage();die;

                }
                $msg = "一共创建{$all_order}条订单！";


            }
            /*
            echo count($all_student);echo '--';

            echo $all_order;die;
            */

            $obj->status = $status;
            $obj->status_desc = !empty($status_desc) ? $status_desc : '审核通过';
            $obj->update();


            $this->status = 1;
            $this->message = '操作成功！' . $msg;
            return $this->format();


        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage() . $e->getFile() . $e->getLine();
            return $this->format();
        }
    }


    /*
        删除
    */
    public function del()
    {

        try {
            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;

            /*            $merchant_id=$loginer->merchant_id;




                        $this->status=1;
                        $this->message='订单创建成功';
                        return $this->format();
            */

        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage() . $e->getFile() . $e->getLine();
            return $this->format();
        }
    }


    /*
        短信催收
    */
    public function remind()
    {

        try {
            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;

            $batch_no = $request->get('stu_order_batch_no');

            if (empty($batch_no)) {
                $this->message = '项目编号不能为空！';
                return $this->format();
            }

            $have = \App\Models\StuOrderBatch::where('stu_order_batch_no', $batch_no)->first();
            if (empty($have)) {
                $this->message = '缴费项目不存在，无法催收！';
                return $this->format();
            }

            if ($have->status != 1) {
                $this->message = '缴费项目尚在审核中，无法催收！';
                return $this->format();
            }

            $store = \App\Models\StuStore::where('store_id', $have->store_id)->first();
            if (empty($store)) {
                $this->message = '学校配置找不到，无法催收！';
                return $this->format();
            }

            $get_all_order = \App\Models\StuOrder::where('stu_order_batch_no', $batch_no)->get();

            $success_send = 0;
            $fail_send = 0;
            try {
                $config_id = $store->config_id;//服务商配置ID
                $config = \App\Models\SmsConfig::where('type', '9')->where('config_id', $config_id)->first();
                if (!$config) {
                    $config = \App\Models\SmsConfig::where('type', '9')->where('config_id', '1234')->first();
                }
                $TemplateCode = $config->TemplateCode;
                $app_key = $config->app_key;
                $app_secret = $config->app_secret;
                $SignName = $config->SignName;
                $demo = new \Aliyun\AliSms($app_key, $app_secret);

                foreach ($get_all_order as $v) {/*
                      $student = \App\Models\StuStudent::where('store_id',$v->store_id)->where('stu_grades_no',$v->stu_grades_no)->where('stu_class_no',$v->stu_class_no)->where('student_no',$v->student_no)->first();
                      if(empty($student))
                      {
                        $fail_send++;
                        continue;
                      }
*/
                    $phone = $v->student_user_mobile;//家长手机号

                    $batch_name = mb_substr($v->batch_name, 0, 12);
                    $data = [
                        "school_name" => $v->school_name,//学校名称
                        "item_name" => '【' . $batch_name . '】'//项目名称（截取前12个字）
                    ];

                    $response = $demo->sendSms(
                        $SignName, // 短信签名
                        $TemplateCode, // 短信模板编号
                        $phone, // 短信接收者
                        $data
                    /* Array(  // 短信模板中字段的值
                     "code"=>$code,
                     )*/
                    );


                    // 成功
                    if ($response->Code == 'OK') {

                        \App\Models\StuOrder::where('id', $v->id)->increment('remind', 1);
                        $success_send++;

                        // 失败
                    } else {
                        $fail_send++;

                    }


                }

                // dd($response);

            } catch (\Exception $e) {
                $fail_send++;

            }


            /*            $merchant_id=$loginer->merchant_id;




                        $this->status=1;
                        $this->message='订单创建成功';
                        return $this->format();
            */

            $this->status = 1;
            $this->message = "催收成功！已通知{$success_send}人！";
            return $this->format();


        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage() . $e->getFile() . $e->getLine();
            return $this->format();
        }
    }


    /*
        单条  短信催收
    */
    public function remindOne()
    {

        try {
            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;

            $out_trade_no = $request->get('out_trade_no');//系统订单号

            if (empty($out_trade_no)) {
                $this->message = '请传入订单编号。';
                return $this->format();
            }

            $order = \App\Models\StuOrder::where('out_trade_no', $out_trade_no)->first();
            if (empty($order)) {
                $this->message = '订单不存在，无法催收！';
                return $this->format();
            }


            $have = \App\Models\StuOrderBatch::where('stu_order_batch_no', $order->stu_order_batch_no)->first();
            if (empty($have)) {
                $this->message = '缴费项目不存在，无法催收！';
                return $this->format();
            }

            if ($have->status != 1) {
                $this->message = '缴费项目尚在审核中，无法催收！';
                return $this->format();
            }

            $store = \App\Models\StuStore::where('store_id', $have->store_id)->first();
            if (empty($store)) {
                $this->message = '学校配置找不到，无法催收！';
                return $this->format();
            }


            $message = '催收失败！';

            try {
                $config_id = $store->config_id;//服务商配置ID
                $config = \App\Models\SmsConfig::where('type', '9')->where('config_id', $config_id)->first();
                if (!$config) {
                    $config = \App\Models\SmsConfig::where('type', '9')->where('config_id', '1234')->first();

                }
                $TemplateCode = $config->TemplateCode;
                $app_key = $config->app_key;
                $app_secret = $config->app_secret;
                $SignName = $config->SignName;
                $demo = new \Aliyun\AliSms($app_key, $app_secret);


                $phone = $order->student_user_mobile;//家长手机号

                $batch_name = mb_substr($order->batch_name, 0, 12);
                $data = [
                    "school_name" => $order->school_name,//学校名称
                    "item_name" => '【' . $batch_name . '】'//项目名称（截取前12个字）
                ];

                $response = $demo->sendSms(
                    $SignName, // 短信签名
                    $TemplateCode, // 短信模板编号
                    $phone, // 短信接收者
                    $data
                /* Array(  // 短信模板中字段的值
                 "code"=>$code,
                 )*/
                );


                // 成功
                if ($response->Code == 'OK') {

                    \App\Models\StuOrder::where('id', $order->id)->increment('remind', 1);
                    // 失败


                    $this->status = 1;
                    $this->message = "催收成功！";
                    return $this->format();


                }
                $message = '短信接口错误！';

            } catch (\Exception $e) {
                $message = '系统错误：' . $e->getMessage();
            }


            $this->status = 2;
            $this->message = $message;
            return $this->format();


        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage() . $e->getFile() . $e->getLine();
            return $this->format();
        }
    }


}
