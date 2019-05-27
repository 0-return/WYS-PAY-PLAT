<?php

namespace App\Api\Controllers\Consumer;
use Illuminate\Support\Facades\DB;

/*
    年级管理
*/
class StudentController extends \App\Api\Controllers\BaseController
{ 

    /*
        列表
    */
    public function lst(){

        try{
            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;






            // 查找学生信息
            $get_all_school = \App\Models\StuStore::get();
            $get_all_grade = \App\Models\StuGrade::get();
            $get_all_class = \App\Models\StuClass::get();

            $all_school=[];
            foreach($get_all_school as $v)
            {
                $all_school[$v->store_id]=$v;
            }

            $all_grade=[];
            foreach($get_all_grade as $v)
            {
                $all_grade[$v->stu_grades_no]=$v;
            }

            $all_class=[];
            foreach($get_all_class as $v)
            {
                $all_class[$v->stu_class_no]=$v;
            }

            $get_all_student=\App\Models\StuStudent::where('student_user_mobile',$loginer->phone)->get();

            $cout=[];
            if(!$get_all_student->isEmpty())
            {
                $cout=array_map(function($each) use ($all_school,$all_grade,$all_class){
                    return array_merge($each,[
                        'school_name'=>isset($all_school[$each['store_id']])?$all_school[$each['store_id']]->school_name:'',
                        'grade_name'=>isset($all_grade[$each['stu_grades_no']])?$all_grade[$each['stu_grades_no']]->stu_grades_name:'',
                        'class_name'=>isset($all_class[$each['stu_class_no']])?$all_class[$each['stu_class_no']]->stu_class_name:'',
                        ]);
                },$get_all_student->toArray());
            }


            $this->status=1;
            $this->message='ok';
            return $this->format($cout);


        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage().$e->getFile().$e->getLine();
            return $this->format();
        }
    }





}
