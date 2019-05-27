<?php

namespace App\Api\Controllers\School;
use Illuminate\Support\Facades\DB;

/*
    公告管理
*/
class AnnounceController extends \App\Api\Controllers\BaseController
{ 
    public $cate=[
        1=>'收款通知',
        2=>'公告通知',
    ];
    /*
        列表
    */
    public function cateLst(){

        try{
            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;
 

            $cout=[];

            $this->t=count($this->cate);

            foreach($this->cate as $k=>$v)
            {
                $cout[]=['type'=>$k,'name'=>$v];
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
        添加
    */
    public function add(){

        try{
            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;

            $merchant_id=$loginer->merchant_id;

            $have = \App\Models\Announce::where('store_id',$request->get('store_id'))->where('title',$request->get('title'))->first();

            if(!empty($have))
            {
                $this->message='标题已经存在！';
                return $this->format();
            }

            $cin=[
                'merchant_id'=>$merchant_id,//创建者id
                'store_id'=>$request->get('store_id',''),
                'cate_id'=>$request->get('cate_id',''),
                'title'=>$request->get('stu_class_name',''),
                'content'=>!empty($request->get('stu_class_desc','')) ? $request->get('stu_class_desc','') : '',
            ];
 

              $validate=\Validator::make($cin, [
                        'merchant_id'=>'required',
                        'store_id'=>'required',
                        'cate_id'=>'required',
                        'title'=>'required',
                        'content'=>'required',
                    // 'content'=>'required|exists:goods_cate,id',
              ], [
                  'required' => ':attribute为必填项！',
                  'min' => ':attribute长度不符合要求！',
                  'max' => ':attribute长度不符合要求！',
                  'unique' => ':attribute已经被人占用！',
                  'exists' => ':attribute不存在！'
              ], [
                        'merchant_id'=>'创建者',
                        'store_id'=>'学校编号',
                        'cate_id'=>'分类编号',
                        'title'=>'公告标题',
                        'content'=>'公告内容',
              ]);

          if($validate->fails())
          {
            $this->message=$validate->getMessageBag()->first();
            return $this->format();
          }


          if(in_array($cin['cate_id'], array_keys($this->cate)))
          {
                $this->message='公告类型不支持！';
                return $this->format();

          }

          $grade=\App\Models\Announce::create($cin);


            $this->status=1;
            $this->message='公告创建成功';
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

            $id=$request->get('id');

            $have = \App\Models\Announce::where('id',$id)->first();

            if(empty($have))
            {
                $this->message='要修改的公告不存在！';
                return $this->format();
            }

            $cin=[
                'merchant_id'=>$merchant_id,//创建者id
                'store_id'=>$request->get('store_id',''),
                'cate_id'=>$request->get('cate_id',''),
                'title'=>$request->get('stu_class_name',''),
                'content'=>!empty($request->get('stu_class_desc','')) ? $request->get('stu_class_desc','') : '',
            ];
 

              $validate=\Validator::make($cin, [
                        'merchant_id'=>'required',
                        'store_id'=>'required',
                        'cate_id'=>'required',
                        'title'=>'required',
                        'content'=>'required',
                    // 'content'=>'required|exists:goods_cate,id',
              ], [
                  'required' => ':attribute为必填项！',
                  'min' => ':attribute长度不符合要求！',
                  'max' => ':attribute长度不符合要求！',
                  'unique' => ':attribute已经被人占用！',
                  'exists' => ':attribute不存在！'
              ], [
                        'merchant_id'=>'创建者',
                        'store_id'=>'学校编号',
                        'cate_id'=>'分类编号',
                        'title'=>'公告标题',
                        'content'=>'公告内容',
              ]);

          if($validate->fails())
          {
            $this->message=$validate->getMessageBag()->first();
            return $this->format();
          }




          if(in_array($cin['cate_id'], array_keys($this->cate)))
          {
                $this->message='公告类型不支持！';
                return $this->format();

          }

          $have_other =  \App\Models\Announce::where('id','!=',$id)->where('store_id','!=',$cin['store_id'])->where('title',$cin['title'])->first();
          if(!empty($have_other))
          {
                $this->message='公告标题重复，请更改！';
                return $this->format();
          }

          $ok = $have->update($cin);

          if($ok)
          {

                $this->status=1;
                $this->message='公告修改成功';
                return $this->format();
          }
                $this->message='修改失败，请重试！';
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

            $store_id = $request->get('store_id');

            $obj= new \App\Models\Announce;

            if(!empty($store_id))
            {

                $obj = $obj->where('store_id',$request->get('store_id'));
            }else{

                $get_all_store_id = \App\Models\MerchantStore::where('merchant_id',$loginer->merchant_id)->get();
                $all_store_id=[];
                foreach($get_all_store_id as $v)
                {
                    $all_store_id[]=$v->store_id;
                }


                $obj = $obj->whereIn('store_id',array_unique($all_store_id));

                // var_dump($all_store_id);die;
            }


            $cout=[];

            $this->t=$obj->count();


            $data=$this->page($obj)->get();
            if(!$data->isEmpty())
            {
                $cout=array_map(function($each){
                    return array_merge($each,[
                        'cate_name'=>isset($this->cate[$each['cate_type']]) ? $this->cate[$each['cate_type']] :'',
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

            $id=$request->get('id','');

            $grade = \App\Models\Announce::where('id',$id)->first();

            if(empty($grade))
            {
                $this->message='公告不存在！';
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


            $obj = \App\Models\Announce::where('id',$id)->first();

            if(empty($obj))
            {
                $this->message='公告不存在！';
                return $this->format();
            }

            $ok = $obj->delete();

            $this->message='已成功删除！';

            $this->status=2;

            return $this->format();


        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage().$e->getFile().$e->getLine();
            return $this->format();
        }
    }






}
