<?php

namespace App\Api\Controllers\School;
use Illuminate\Support\Facades\DB;

/*
    班级管理
*/
class ClassController extends \App\Api\Controllers\BaseController
{
    private function makeClassNo(){
        return '5'.str_random(7);
    }
    /*
        添加
    */
    public function add(){

        try{
            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;

            $merchant_id=$loginer->merchant_id;

            $have = \App\Models\StuClass::where('store_id',$request->get('store_id'))->where('stu_grades_no',$request->get('stu_grades_no'))->where('stu_class_name',$request->get('stu_class_name'))->first();

            if(!empty($have))
            {
                $this->message='班级已经存在！';
                return $this->format();
            }
            $cin=[
                'merchant_id'=>$merchant_id,//创建者id

                'store_id'=>$request->get('store_id',''),
                'stu_grades_no'=>$request->get('stu_grades_no',''),

                'stu_class_no'=>$this->makeClassNo(),
                'stu_class_name'=>$request->get('stu_class_name',''),
                'stu_class_desc'=>!empty($request->get('stu_class_desc','')) ? $request->get('stu_class_desc','') : '',
            ];
 

              $validate=\Validator::make($cin, [
                        'store_id'=>'required',
                        'stu_grades_no'=>'required',
                        'stu_class_no'=>'required',
                        'stu_class_name'=>'required',
                    // 'cate_id'=>'required|exists:goods_cate,id',
              ], [
                  'required' => ':attribute为必填项！',
                  'min' => ':attribute长度不符合要求！',
                  'max' => ':attribute长度不符合要求！',
                  'unique' => ':attribute已经被人占用！',
                  'exists' => ':attribute不存在！'
              ], [
                        'stu_grades_no'=>'年级编号',
                        'store_id'=>'学校编号',

                        'stu_class_no'=>'年级编号',
                        'stu_class_name'=>'年级名称',
              ]);

          if($validate->fails())
          {
            $this->message=$validate->getMessageBag()->first();
            return $this->format();
          }

          $grade=\App\Models\StuClass::create($cin);


            $this->status=1;
            $this->message='班级创建成功';
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

          $class=\App\Models\StuClass::where('stu_class_no',$request->get('stu_class_no'))->first();

          if(empty($class))
          {
            $this->message='班级不存在！';
            return  $this->format();
          }

            $cin=[
                'store_id'=>$request->get('store_id',''),
                'stu_grades_no'=>$request->get('stu_grades_no',''),
                'stu_class_name'=>$request->get('stu_class_name',''),
                'stu_class_desc'=>$request->get('stu_class_desc',''),
            ];

          $cin=array_filter($cin);

          if(empty($cin))
          {

            $this->message='请传入要修改的参数！';
            return  $this->format();
          }

          $class=$class->update($cin);


            $this->status=1;
            $this->message='修改班级成功';
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


// 学校
            $get_all_school = \App\Models\StuStore::get();
            $all_school=[];
            foreach($get_all_school as $v)
            {
                $all_school[$v->store_id]=$v->school_name;
            }
// 班级
            $get_all_grade = \App\Models\StuGrade::get();
            $all_grade=[];
            foreach($get_all_grade as $v)
            {
                $all_grade[$v->stu_grades_no]=$v->stu_grades_name;
            }

            $grade =  new \App\Models\StuClass;

            if(!empty($request->get('stu_grades_no')))
            {
                $grade = $grade->where('stu_grades_no',$request->get('stu_grades_no'));
                
            }

            if(!empty($request->get('store_id')))
            {
                $grade = $grade->where('store_id',$request->get('store_id'));
                
            }else{

                $get_all_store_id = \App\Models\MerchantStore::where('merchant_id',$loginer->merchant_id)->get();
                $all_store_id=[];
                foreach($get_all_store_id as $v)
                {
                    $all_store_id[]=$v->store_id;
                }


                $grade = $grade->whereIn('store_id',array_unique($all_store_id));
            }

            $cout=[];

            $this->t=$grade->count();


            $data=$this->page($grade)->get();
            if(!$data->isEmpty())
            {
                $cout=array_map(function($each) use ($all_school,$all_grade){
                    return array_merge($each,[
                        'school_name'=>isset($all_school[$each['store_id']]) ? $all_school[$each['store_id']] :'',
                        'grade_no'=>isset($all_grade[$each['stu_grades_no']]) ? $all_grade[$each['stu_grades_no']] :'',
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

            $stu_class_no=$request->get('stu_class_no','');

            $grade = \App\Models\StuClass::where('stu_class_no',$stu_class_no)->first();

            if(empty($grade))
            {
                $this->message='班级不存在！';
                return $this->format();
            }

            $this->status=1;
            $this->message='ok';
            return $this->format($grade);


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
