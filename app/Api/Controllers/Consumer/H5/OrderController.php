<?php

namespace App\Api\Controllers\Consumer\H5;
use Illuminate\Support\Facades\DB;

/*
    年级管理
*/
class OrderController extends \App\Api\Controllers\BaseController
{ 

    /*
        列表
    */
    public function lst(){

        try{
            $request=app('request');
            $this->status=2;

            $student_id=$request->get('student_id');
            if(empty($student_id))
            {
                $this->message='学生编号不能为空';
                return $this->format();
            }

            $student=\App\Models\StuStudent::where('id',$student_id)->first();

            if(empty($student))
            {
                $this->message='学生不存在！';
                return $this->format();

            }
 


            $obj = new \App\Models\StuOrder;

            $obj = $obj->where('student_no',$student->student_no)->where('store_id',$student->store_id)->where('stu_grades_no',$student->stu_grades_no)->where('stu_class_no',$student->stu_class_no)->where('pay_status','=','2');


            $get_all_template = \App\Models\StuOrderType::get();
            $all_template=[];
            foreach($get_all_template as $v)
            {
                $all_template[$v->stu_order_type_no]=$v;
            }

            $this->t=$obj->count();
            $data=$this->page($obj,$request)->get();
            $cout=[];
            if(!$data->isEmpty())
            {
                $cout=array_map(function($each) use ($all_template) {
                    return array_merge($each,[
                            'charge_name'=>isset($all_template[$each['stu_order_type_no']]) ? $all_template[$each['stu_order_type_no']]->charge_name:''
                        ]);


                }, $data->toArray());
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


    /*
        单条
    */
    public function show(){

        try{
            $request=app('request');
            $this->status=2;

            $out_trade_no=$request->get('out_trade_no','');
            if(empty($out_trade_no))
            {
                $this->message='单号不能为空！';
                return $this->format();
            }
            $obj = \App\Models\StuOrder::where('out_trade_no',$out_trade_no)->first();

            if(empty($obj))
            {
                $this->message='订单不存在！';
                return $this->format();
            }

            // 查找缴费模板
            $item = \App\Models\StuOrderItem::where('out_trade_no',$obj->out_trade_no)->get();


            // 查找学生信息
            $school = \App\Models\StuStore::where('store_id',$obj->store_id)->first();
            $grade = \App\Models\StuGrade::where('stu_grades_no',$obj->stu_grades_no)->first();
            $class = \App\Models\StuClass::where('stu_class_no',$obj->stu_class_no)->first();

            $student = \App\Models\StuStudent::where('store_id',$obj->store_id)
            ->where('stu_grades_no',$obj->stu_grades_no)
            ->where('stu_class_no',$obj->stu_class_no)
            ->where('student_no',$obj->student_no)
            ->first();





            $obj->student_user_mobile= $student->student_user_mobile;


            $template = \App\Models\StuOrderType::where('stu_order_type_no',$obj->stu_order_type_no)->first();

            $obj->charge_name='';
            if(!empty($template))
            $obj->charge_name=$template->charge_name;


            $obj->all_item=$item;

            $obj->school=$school;
            $obj->grade=$grade;
            $obj->class=$class;


            $this->status=1;
            $this->message='ok';
            return $this->format($obj);


        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage().$e->getFile().$e->getLine();
            return $this->format();
        }
    }










}
