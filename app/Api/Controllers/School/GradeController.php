<?php

namespace App\Api\Controllers\School;
use Illuminate\Support\Facades\DB;

/*
    年级管理
*/
class GradeController extends \App\Api\Controllers\BaseController
{
    private function makeGradeNo(){
        return '3'.str_random(7);
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
                'stu_grades_desc'=>!empty($request->get('stu_grades_desc','')) ? $request->get('stu_grades_desc','') : '',
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

            $merchant_id=$loginer->merchant_id;

            $get_all_school = \App\Models\StuStore::get();
            $all_school=[];
            foreach($get_all_school as $v)
            {
                $all_school[$v->store_id]=$v->school_name;
            }

            $grade =  new \App\Models\StuGrade;


            if(!empty($request->get('store_id')))
            {
                $grade = $grade->where('store_id',$request->get('store_id'));
            }

            $grade = $grade->where('merchant_id',$loginer->merchant_id);

            $this->t=$grade->count();

            $data=$this->page($grade)->get();

            $cout=[];

            if(!$data->isEmpty())
            {
                    
                $cout=array_map(function($each) use ($all_school){
                    return array_merge($each,['school_name'=>isset($all_school[$each['store_id']]) ? $all_school[$each['store_id']] : ''  ]);

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

            $stu_grades_no=$request->get('stu_grades_no','');
            $grade = \App\Models\StuGrade::where('stu_grades_no',$stu_grades_no)->first();

            if(empty($grade))
            {
                $this->message='年级不存在！';
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
