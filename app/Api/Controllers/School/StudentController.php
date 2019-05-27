<?php

namespace App\Api\Controllers\School;

use Illuminate\Support\Facades\DB;

/*
    班级管理
*/

class StudentController extends \App\Api\Controllers\BaseController
{
    private function makeStudentNo()
    {
        return str_random(12);
    }

    private $student_lst = [];

    public function importExcel()
    {
        $all_success = true;
        $message = '表格已经全部导入成功！';

        try {
            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;


            $stu_class_no = $request->get('stu_class_no');

            $merchant_id = $loginer->merchant_id;

            if (empty($_FILES)) {
                $this->status = 2;
                $this->message = '请上传xlsx表格！';
                return $this->format();
            }

            $file_arr = array_shift($_FILES);

            if ($file_arr['error'] !== 0) {

                $this->status = 2;
                $this->message = '请上传xlsx表格！';
                return $this->format();
            }
            // var_dump($file_arr);die;
            $file = $file_arr['tmp_name'];


            if (empty($stu_class_no)) {

                $this->status = 2;
                $this->message = '班级编号不能为空！';
                return $this->format();
            }

            $have_class = \App\Models\StuClass::where('stu_class_no', $stu_class_no)->first();
            if (empty($have_class)) {
                $this->status = 2;
                $this->message = '班级不存在！';
                return $this->format();
            }


            $excel_data = \App\Common\Excel\Excel::_readExcel($file);

            // $record=0;

            foreach ($excel_data as $k => $v) {

                if ($k == 0) {
                    continue;
                }
                /*
                                // $record++;
                                $cin=[
                                    'store_id'=>trim($v[0]),
                                    'stu_grades_no'=>trim($v[1]),
                                    'stu_class_no'=>trim($v[2]),


                                    'student_name'=>trim($v[3]),
                                    'student_identify'=>trim($v[4]),
                                    'student_no'=>trim($v[5]),
                                    'student_user_name'=>trim($v[6]),
                                    'student_user_mobile'=>trim($v[7]),
                                    'student_user_relation'=>trim($v[8]),
                                    'status'=>'1',
                                    'status_desc'=>'excel导入',
                                ];
                     */

                // $record++;
                $cin = [
                    'store_id' => $have_class->store_id,
                    'stu_grades_no' => $have_class->stu_grades_no,
                    'stu_class_no' => $have_class->stu_class_no,


                    'student_name' => trim($v[0]),
                    'student_identify' => trim($v[1]) ? trim($v[1]) : date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999)),
                    'student_no' => trim($v[2]),
                    'student_user_name' => trim($v[3]),
                    'student_user_mobile' => trim($v[4]),
                    'student_user_relation' => trim($v[5]),
                    'status' => '1',
                    'status_desc' => 'excel导入',
                ];


                $hand = self::addEach($cin);
                if ($hand['status'] == 1) {
                    $this->student_lst[] = $hand['data']->id;

                    // $this->status=$hand['status'];
                    // $this->message='当前表格未导入：'.$hand['message'];
                    // return $this->format();
                } else {
                    $message = '当前表格未导入：' . $hand['message'];
                    $all_success = false;
                    break;
                }
            }

            /*
                        if($record<=0)
                        {
                            $this->status=2;
                            $this->message='excel表格中没有数据！';
                            return $this->format();
                        }
            */


        } catch (\Exception $e) {
            $all_success = false;
            $message = '表格未导入：系统错误：' . $e->getMessage() . $e->getFile() . $e->getLine();
        }


        if ($all_success) {
            $this->status = 1;
            $this->message = $message;
            return $this->format();
        } else {
            //删除成功的记录
            if (!empty($this->student_lst)) {
                try {
                    \App\Models\StuStudent::whereIn('id', $this->student_lst)->delete();
                } catch (\Exception $e) {

                }
            }


            $this->status = 2;
            $this->message = $message;
            return $this->format();
        }


    }


    private static function addEach($cin)
    {


        $validate = \Validator::make($cin, [
            'store_id' => 'required',
            'student_no' => 'required',
            'stu_grades_no' => 'required',
            'stu_class_no' => 'required',
            'student_name' => 'required',
            // 'student_identify'=>'required|min:15|max:18',
            'student_user_mobile' => 'required|min:11|max:11',
            'student_user_name' => 'required',
            'student_user_relation' => 'required',
            'status' => 'required',
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
            'student_no' => '学生学号',
            'stu_grades_no' => '年级编号',
            'stu_class_no' => '班级编号',
            'student_name' => '学生姓名',
            //  'student_identify'=>'学生身份证号',
            'student_user_mobile' => '家长手机号',
            'student_user_name' => '家长姓名',
            'student_user_relation' => '学生与家长关系',
            'status' => '学生入学情况',
            'status_desc' => '备注',
        ]);

        if ($validate->fails()) {
            return ['status' => 2, 'message' => $validate->getMessageBag()->first()];
            // $this->message=$validate->getMessageBag()->first();
            // return $this->format();
        }


        $have_school = \App\Models\StuStore::where('store_id', $cin['store_id'])->first();
        if (empty($have_school)) {
            return ['status' => 2, 'message' => '学校不存在！'];
        }


        $have_grade = \App\Models\StuGrade::where('stu_grades_no', $cin['stu_grades_no'])->first();
        if (empty($have_grade)) {
            return ['status' => 2, 'message' => '年级不存在！'];
        }


        $have_class = \App\Models\StuClass::where('stu_class_no', $cin['stu_class_no'])->first();
        if (empty($have_class)) {
            return ['status' => 2, 'message' => '班级不存在！'];
        }


        $not_match = \App\Models\StuClass::where('stu_class_no', $cin['stu_class_no'])->where('stu_grades_no', $cin['stu_grades_no'])->where('store_id', $cin['store_id'])->first();
        if (empty($not_match)) {
            return ['status' => 2, 'message' => '年级或者班级不存在！'];
        }


        $have = \App\Models\StuStudent::
        where('store_id', $cin['store_id'])
            ->where('stu_grades_no', $cin['stu_grades_no'])
            ->where('stu_class_no', $cin['stu_class_no'])
            ->where('student_no', $cin['student_no'])
            ->where('student_name', $cin['student_name'])
            ->first();

        if ($have) {
            return ['status' => 2, 'message' => '学生已经存在！'];
        }

        $ok = \App\Models\StuStudent::create($cin);

        return ['status' => 1, 'message' => 'ok', 'data' => $ok];
    }


    /*
        添加
    */
    public function add()
    {

        try {
            $request = app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status = 2;
            $student_identify = $request->get('student_identify', '');
            if ($student_identify == "" || $student_identify == "NULL") {
                $student_identify = date('YmdHis', time()) . substr(microtime(), 2, 6) . sprintf('%03d', rand(0, 999));;
            }
            $merchant_id = $loginer->merchant_id;


            /*

                        $have = \App\Models\StuStudent::
                        where('store_id',$request->get('store_id'))
                        ->where('stu_grades_no',$request->get('stu_grades_no'))
                        ->where('stu_class_no',$request->get('stu_class_no'))
                        ->where('student_identify',$request->get('student_identify'))
                        ->first();

                        if(!empty($have))
                        {
                            $this->message='学生已经存在！';
                            return $this->format();
                        }
            */

            $cin = [
                'store_id' => $request->get('store_id', ''),
                'stu_grades_no' => $request->get('stu_grades_no', ''),
                'stu_class_no' => $request->get('stu_class_no', ''),

                'student_no' => $request->get('student_no', ''),
                'student_name' => $request->get('student_name', ''),
                'student_identify' => $student_identify,
                'student_user_mobile' => $request->get('student_user_mobile', ''),
                'student_user_name' => $request->get('student_user_name', ''),
                'student_user_relation' => $request->get('student_user_relation', ''),
                'status' => $request->get('status', ''),
                'status_desc' => $request->get('status_desc', ''),
            ];


            /*


                          $validate=\Validator::make($cin, [
                                    'store_id'=>'required',
                                    'stu_grades_no'=>'required',
                                    'stu_class_no'=>'required',
                                    'student_name'=>'required',
                                    'student_identify'=>'required|min:15|max:18',
                                    'student_user_mobile'=>'required',
                                    'student_user_name'=>'required',
                                    'student_user_relation'=>'required',
                                    'status'=>'required',
                                    // 'status_desc'=>'required',
                                // 'cate_id'=>'required|exists:goods_cate,id',
                          ], [
                              'required' => ':attribute为必填项！',
                              'min' => ':attribute长度不符合要求！',
                              'max' => ':attribute长度不符合要求！',
                              'unique' => ':attribute已经被人占用！',
                              'exists' => ':attribute不存在！'
                          ], [
                            'store_id'=>'学校编号',
                            'stu_grades_no'=>'年级编号',
                            'stu_class_no'=>'班级编号',
                            'student_name'=>'学生姓名',
                            'student_identify'=>'学生身份证号',
                            'student_user_mobile'=>'家长手机号',
                            'student_user_name'=>'家长姓名',
                            'student_user_relation'=>'学生与家长关系',
                            'status'=>'学生入学情况',
                            'status_desc'=>'备注',
                          ]);

                      if($validate->fails())
                      {
                        $this->message=$validate->getMessageBag()->first();
                        return $this->format();
                      }

                      $grade=\App\Models\StuStudent::create($cin);
            */
            $hand = self::addEach($cin);
            if ($hand['status'] != 1) {
                $this->status = $hand['status'];
                $this->message = $hand['message'];
                return $this->format();
            }


            $this->status = 1;
            $this->message = '学生添加成功';
            return $this->format();


        } catch (\Exception $e) {
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


            $student = \App\Models\StuStudent::
            where('id', $request->get('student_id'))
                ->first();

            if (empty($student)) {
                $this->message = '学生不存在！';
                return $this->format();
            }


            $cin = [
                'student_name' => $request->get('student_name', ''),
                'student_no' => $request->get('student_no', ''),
                // 'student_identify'=>$request->get('student_identify',''),
                // 'student_user_mobile'=>$request->get('student_user_mobile',''),
                'student_user_name' => $request->get('student_user_name', ''),
                'student_user_relation' => $request->get('student_user_relation', ''),
                'status' => $request->get('status', ''),
                'status_desc' => $request->get('status_desc', ''),
            ];

            $student_user_mobile = $request->get('student_user_mobile');

            if (!empty($student_user_mobile)) {
                if (strlen($student_user_mobile) != 11) {
                    $this->message = '手机号码格式不正确！';
                    return $this->format();
                }
                $cin['student_user_mobile'] = $student_user_mobile;
            }

            $student_identify = $request->get('student_identify');
//
//        if(!empty($student_identify))
//        {
//            $cin['student_identify']=$student_identify;
//
//            $have = \App\Models\StuStudent::where('student_identify',$student_identify)->where('id','!=',$student->id)->first();
//            if(!empty($have))
//            {
//                $this->message='身份证号已经被别人占用！';
//                return  $this->format();
//
//            }
//
//
//        }


            $stu_grades_no = $request->get('stu_grades_no');

            if (!empty($stu_grades_no)) {
                $cin['stu_grades_no'] = $stu_grades_no;


                $not_match = \App\Models\StuGrade::where('stu_grades_no', $stu_grades_no)->where('store_id', $student->store_id)->first();
                if (empty($not_match)) {
                    return ['status' => 2, 'message' => '年级不存在！'];
                }


            }

            $stu_class_no = $request->get('stu_class_no');

            if (!empty($stu_class_no)) {

                $not_match = \App\Models\StuClass::where('stu_class_no', $stu_class_no)->where('stu_grades_no', $student->stu_grades_no)->where('store_id', $student->store_id)->first();
                if (empty($not_match)) {
                    return ['status' => 2, 'message' => '班级不存在！'];
                }

                $cin['stu_class_no'] = $stu_class_no;


            }


            $cin = array_filter($cin);

            if (empty($cin)) {

                $this->message = '请传入要修改的参数！';
                return $this->format();
            }

            $student = $student->update($cin);


            $this->status = 1;
            $this->message = '修改学生成功';
            return $this->format();


        } catch (\Exception $e) {
            $this->status = -1;
            $this->message = '系统错误' . $e->getMessage() . $e->getFile() . $e->getLine();
            return $this->format();
        }
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


            $obj = new \App\Models\StuStudent;


            $store_id = $request->get('store_id');
            $stu_grades_no = $request->get('stu_grades_no');
            $stu_class_no = $request->get('stu_class_no');
            $student_name = $request->get('student_name');


            if (!empty($store_id)) {
                $obj = $obj->where('store_id', $store_id);
            } else {
                $get_all_store_id = \App\Models\MerchantStore::where('merchant_id', $loginer->merchant_id)->get();
                $all_store_id = [];
                foreach ($get_all_store_id as $v) {
                    $all_store_id[] = $v->store_id;
                }


                $obj = $obj->whereIn('store_id', array_unique($all_store_id));
            }

            if (!empty($stu_grades_no)) {
                $obj = $obj->where('stu_grades_no', $stu_grades_no);
            }
            if (!empty($stu_class_no)) {
                $obj = $obj->where('stu_class_no', $stu_class_no);
            }

            if (!empty($request->get('status'))) {
                $obj = $obj->where('status', $request->get('status'));
            }

            if (!empty($student_name)) {
                $obj = $obj->where('student_name', 'like', '%' . $student_name . '%');
            }

            $school = \App\Models\StuStore::get();
            $grade = \App\Models\StuGrade::get();
            $class = \App\Models\StuClass::get();

            foreach ($school as $v) {
                $all_school[$v->store_id] = $v;
            }

            foreach ($grade as $v) {
                $all_grade[$v->stu_grades_no] = $v;
            }

            foreach ($class as $v) {
                $all_class[$v->stu_class_no] = $v;
            }

// echo $loginer->merchant_id;die;


            $this->t = $obj->count();

            $data = $this->page($obj)->get();

            $cout = [];
            if (!$data->isEmpty()) {
                $cout = array_map(function ($each) use ($all_school, $all_grade, $all_class) {

                    return array_merge($each, [

                            'school_name' => isset($all_school[$each['store_id']]) ? $all_school[$each['store_id']]->school_name : '',
                            'grade_name' => isset($all_grade[$each['stu_grades_no']]) ? $all_grade[$each['stu_grades_no']]->stu_grades_name : '',
                            'class_name' => isset($all_class[$each['stu_class_no']]) ? $all_class[$each['stu_class_no']]->stu_class_name : '',

                            // 'school_name'=>isset($school->school_name) ? $school->school_name : '',
                            // 'grade_name'=>isset($grade->stu_grade_name) ? $grade->stu_grade_name : '',
                            // 'class_name'=>isset($class->stu_class_name) ? $class->stu_class_name : '',
                        ]
                    );

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


            $obj = new \App\Models\StuStudent;

            $obj = $obj
                ->where('student_no', $request->get('student_no'))
                ->where('student_identify', $request->get('student_identify'));


            $grade = $obj->first();

            if (empty($grade)) {
                $this->message = '学生不存在！';
                return $this->format();
            }

            $this->status = 1;
            $this->message = 'ok';
            return $this->format($grade);


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

            $id = $request->get('id');

            if (empty($id)) {
                $this->message = '学生id不能为空！';
                return $this->format();
            }


            $have = \App\Models\StuStudent::where('id', $id)->first();;
            if (empty($have)) {

                $this->message = '您要删除的学生不存在！';
                return $this->format();
            }

            $ok = $have->delete();
            if (!$ok) {

                $this->message = '删除失败，请重试！';
                return $this->format();
            }


            $this->status = 1;
            $this->message = '删除成功！';
            return $this->format();


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


}
