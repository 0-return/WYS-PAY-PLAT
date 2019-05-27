<?php

namespace App\Api\Controllers\Consumer\H5;
use Illuminate\Support\Facades\DB;

/*
*/
class CheckPageInfoController extends \App\Api\Controllers\BaseController
{ 

    /*
    */
    public function index(){

        try{
            $request=app('request');
            $this->status=2;

            $store_id=$request->get('store_id');
            $stu_grades_no=$request->get('stu_grades_no');
            $stu_class_no=$request->get('stu_class_no');
            $student_name=$request->get('student_name');
            $student_user_mobile=$request->get('student_user_mobile');

            if(empty($store_id))
            {
                $this->message='请传入学校';
                return $this->format();
            }


            if(empty($stu_grades_no))
            {
                $this->message='请传入年级';
                return $this->format();
            }


            if(empty($stu_class_no))
            {
                $this->message='请传入班级';
                return $this->format();
            }


            if(empty($student_name))
            {
                $this->message='请传入学生姓名';
                return $this->format();
            }


            if(empty($student_user_mobile))
            {
                $this->message='请传入学生家长手机号';
                return $this->format();
            }


            $student=\App\Models\StuStudent::where('store_id',$store_id)
            ->where('stu_grades_no',$stu_grades_no)
            ->where('stu_class_no',$stu_class_no)
            ->where('student_name',$student_name)
            ->where('student_user_mobile',$student_user_mobile)
            ->first();

            if(empty($student))
            {
                $this->message='学生信息或家长手机号有误';
                return $this->format();
            }





// $url = url('/school/waitforpay?store_id=' . $student->store_id . '&stu_grades_no=' . $student->stu_grades_no . '&stu_class_no=' . $student->stu_class_no.'&student_id='$student->id);

$url = url('/school/waitforpay?store_id=%s&stu_grades_no=%s&stu_class_no=%s&student_id=%s');
$url=sprintf($url,$student->store_id,$student->stu_grades_no,$student->stu_class_no,$student->id);




            $this->status=1;
            $this->message='验证通过！';
            return $this->format(['url'=>$url]);


        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage().$e->getFile().$e->getLine();
            return $this->format();
        }
    }





}
