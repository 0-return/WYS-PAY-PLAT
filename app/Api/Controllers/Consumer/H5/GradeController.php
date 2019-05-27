<?php

namespace App\Api\Controllers\Consumer\H5;
use Illuminate\Support\Facades\DB;

/*
*/
class GradeController extends \App\Api\Controllers\BaseController
{ 

    /*
        列表
    */
    public function lst(){


        try{
            $request=app('request');
            $this->status=2;

            $store_id=$request->get('store_id');

            $get_all_grade = \App\Models\StuGrade::where('store_id',$store_id)->get();

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
