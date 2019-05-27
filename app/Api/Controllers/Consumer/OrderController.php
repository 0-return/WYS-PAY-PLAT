<?php

namespace App\Api\Controllers\Consumer;
use Illuminate\Support\Facades\DB;

/*
    年级管理
*/
class OrderController extends \App\Api\Controllers\BaseController
{ 
    /*
        添加
    */
    public function add(){

        try{
            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;

            $merchant_id=$loginer->merchant_id;

            $have = \App\Models\StuGrade::where('store_id',$request->get('store_id'))->where('stu_grades_name',$request->get('stu_grades_name'))->first();

            if(!empty($have))
            {
                $this->message='年级已经存在！';
                return $this->format();
            }
            $cin=[
                'merchant_id'=>$merchant_id,//创建者id

                'store_id'=>$request->get('store_id',''),
                'stu_grades_no'=>$this->makeGradeNo(),
                'stu_grades_name'=>$request->get('stu_grades_name',''),
                'stu_grades_desc'=>$request->get('stu_grades_desc',''),
            ];


              $validate=\Validator::make($cin, [
                        'store_id'=>'required',
                        'stu_grades_no'=>'required',
                        'stu_grades_name'=>'required',
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
                        'stu_grades_name'=>'年级名称',
              ]);

          if($validate->fails())
          {
            $this->message=$validate->getMessageBag()->first();
            return $this->format();
          }

          $grade=\App\Models\StuGrade::create($cin);


            $this->status=1;
            $this->message='年级添加成功';
            return $this->format();


        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage().$e->getFile().$e->getLine();
            return $this->format();
        }
    }



    /*
        修改
    */
    public function save(){

        try{
            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;

            $merchant_id=$loginer->merchant_id;

          $grade=\App\Models\StuGrade::where('stu_grades_no',$request->get('stu_grades_no'))->first();

          if(empty($grade))
          {
            $this->message='年级不存在！';
            return  $this->format();
          }

            $cin=[
                'store_id'=>$request->get('store_id',''),
                'stu_grades_name'=>$request->get('stu_grades_name',''),
                'stu_grades_desc'=>$request->get('stu_grades_desc',''),
            ];

          $cin=array_filter($cin);

          if(empty($cin))
          {

            $this->message='请传入要修改的参数！';
            return  $this->format();
          }

          $grade=$grade->update($cin);


            $this->status=1;
            $this->message='修改年级成功';
            return $this->format();


        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage().$e->getFile().$e->getLine();
            return $this->format();
        }
    }


    /*
        列表
    */
    public function lst(){

        try{
            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;


            $get_all_student=\App\Models\StuStudent::where('student_user_mobile',$loginer->phone)->get();
            $all_student=[];
            foreach($get_all_student as $v)
            {
                $all_student[]=$v->student_no;
            }

            if(empty($all_student))
            {
                $this->message='没有您孩子的资料';
                return $this->format();
            }


            $obj = new \App\Models\StuOrder;

            $obj = $obj->whereIn('student_no',$all_student);

            if(!empty($request->get('start_time'))){
                $obj=$obj->where('gmt_start',$request->get('start_time'));
            }

            if(!empty($request->get('end_time'))){
                $obj=$obj->where('gmt_end',$request->get('end_time'));
            }

            if(!empty($request->get('student_name'))){
                $obj=$obj->where('student_name',$request->get('student_name'));
            }

            if(!empty($request->get('pay_status'))){
                $obj=$obj->where('pay_status',$request->get('pay_status'));
            }



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
            $loginer = $this->parseToken($request->get('token'));
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


            $template = \App\Models\StuOrderType::where('stu_order_type_no',$obj->stu_order_type_no)->first();
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


    /*
        删除
    */
    public function del(){

        try{
            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;

/*            $merchant_id=$loginer->merchant_id;




            $this->status=1;
            $this->message='订单创建成功';
            return $this->format();
*/

        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage().$e->getFile().$e->getLine();
            return $this->format();
        }
    }






}
