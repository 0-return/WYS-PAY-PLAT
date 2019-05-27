<?php

namespace App\Api\Controllers\School\Agent;
use Illuminate\Support\Facades\DB;

/*
    学校  增删查改
*/
class SchoolController extends \App\Api\Controllers\BaseController
{


 


    public function typeLst(){

        try{

            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));

            $all = \App\Models\StuType::get();

            $cout=[];


            if(!$all->isEmpty())
            {
                
                $cout=array_map(function($each){
                    return [
                        'name'=>$each['school_type_desc'],
                        'type'=>$each['school_type'],
                    ];

                },$all->toArray());
            }


/*
            $data=[
                ['name'=>'托儿所','type'=>1],
                ['name'=>'幼儿园','type'=>2],
                ['name'=>'小学','type'=>3],
                ['name'=>'初中','type'=>4],
                ['name'=>'高中','type'=>5],

            ];*/

            $this->status=1;
            return $this->format($cout);


        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage();
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


            $obj =  new \App\Models\StuStore;

            if(!empty($request->get('user_id')))
            {
                $rec_id=$request->get('user_id');
            }else{
                $rec_id=$loginer->user_id;
            }

            $all_user_id = $this->getSubIds($rec_id);//返回包括自己的代理商id数组

            $obj = $obj->whereIn('user_id',$all_user_id);

            if(!empty($request->get('status')))
            {
                $obj=$obj->where('status',$request->get('status'));
            }

            if(!empty($request->get('school_name')))
            {
                $obj=$obj->where('school_name','like','%'.$request->get('school_name').'%');
            }


            $this->t=$obj->count();
            $data = $this->page($obj)->get();

 

            $this->status=1;
            return $this->format($data);



        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage().$e->getFile().$e->getLine();
            return $this->format();
        }
    }







}
