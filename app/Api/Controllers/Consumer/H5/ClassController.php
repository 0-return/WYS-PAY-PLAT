<?php

namespace App\Api\Controllers\Consumer\H5;
use Illuminate\Support\Facades\DB;

/*
*/
class ClassController extends \App\Api\Controllers\BaseController
{ 

    /*
        列表
    */
    public function lst(){

        try{
            $request=app('request');
            $this->status=2;

            $stu_grades_no=$request->get('stu_grades_no');

            $get_all_grade = \App\Models\StuClass::where('stu_grades_no',$stu_grades_no)->get();

            if($get_all_grade->isEmpty())
            {
                $get_all_grade=[];
            }
 
            $this->status=1;
            $this->message='ok';
            return $this->format($get_all_grade);


        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage().$e->getFile().$e->getLine();
            return $this->format();
        }
    }





}
