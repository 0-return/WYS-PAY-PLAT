<?php

namespace App\Api\Controllers\School;
use Illuminate\Support\Facades\DB;

/*
    缴费模板
*/
class PayTemplateController extends \App\Api\Controllers\BaseController
{
    private function makeNo(){
        return str_random(12);
    }




/*
        导入缴费模板

        只要一个失败，全部撤销  

        缴费名称 不能为空
*/
    public function importExcel(){
        $have_ok_id=[];
        try{

            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;

            $merchant_id=$loginer->merchant_id;



            $this->status=1;
            $this->message='缴费模板已经全部导入！';
            return $this->format();


        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='导入全部失败：'.$e->getMessage().$e->getFile().$e->getLine();
        }

        try{
            if(!empty($have_ok_id))
            {
                \App\Models\StuOrderType::whereIn('id',$have_ok_id)->delete();
            }
        }catch(\Exception $e)
        {

        }
    
        return $this->format();

    }


    /*
        抽取公共添加的方法

    */
    private static function addEach($cin,$merchant_id){

        $validate=\Validator::make($cin, [
                'store_id'=>'required',
                'stu_order_type_no'=>'required',
                'charge_name'=>'required',
                'charge_desc'=>'required',
                // 'charge_item'=>'required',
                'amount'=>'required',
                // 'status_desc'=>'required',
            // 'cate_id'=>'required|exists:goods_cate,id',
        ], [
          'required' => ':attribute为必填项！',
          'min' => ':attribute长度不符合要求！',
          'max' => ':attribute长度不符合要求！',
          'unique' => ':attribute已经被人占用！',
          'exists' => ':attribute不存在！'
        ], [
        'store_id'=>'学校编号',
        'stu_order_type_no'=>'缴费模板编号',
        'charge_name'=>'名称',
        'charge_desc'=>'描述',
        'charge_item'=>'具体收费项目',
        'amount'=>'总收费金额',
        ]);

        if($validate->fails())
        {
            return ['status'=>2,'message'=>$validate->getMessageBag()->first()];
        }


        //检查是否有重复模板
        $have = \App\Models\StuOrderType::
        where('store_id',$cin['store_id'])
        ->where('charge_name',$cin['charge_name'])
        ->first();

        if(!empty($have))
        {
            return ['status'=>2,'message'=>'缴费模板已经存在！'];
        }

        $cin['status_desc']='未审核';
        $cin['status']=2;

        $add=\App\Models\StuOrderType::create($cin);

        return ['status'=>1,'message'=>'ok','data'=>$add];

    }


    /*
        添加
    */
    public function addnew(){

        try{
            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;

            $merchant_id=$loginer->merchant_id;


/*

            $have = \App\Models\StuOrderType::
            where('store_id',$request->get('store_id'))
            ->where('charge_name',$request->get('charge_name'))
            ->first();
            if(!empty($have))
            {
                $this->message='缴费模板已经存在！';
                return $this->format();
            }

*/

            $child=$request->get('child','');

            $cin=[
                'store_id'=>$request->get('store_id',''),

                'merchant_id'=>$merchant_id,
                'stu_order_type_no'=>$this->makeNo(),
                'charge_name'=>$request->get('charge_name',''),
                'charge_desc'=>$request->get('charge_desc',''),
                'charge_item'=>$child,
                'amount'=>$request->get('amount',''),
                'status'=>2,
                'status_desc'=>'模板审核中',
            ];
 


/*

              $validate=\Validator::make($cin, [
                        'store_id'=>'required',
                        'stu_order_type_no'=>'required',
                        'charge_name'=>'required',
                        'charge_desc'=>'required',
                        // 'charge_item'=>'required',
                        'amount'=>'required',
                        // 'status_desc'=>'required',
                    // 'cate_id'=>'required|exists:goods_cate,id',
              ], [
                  'required' => ':attribute为必填项！',
                  'min' => ':attribute长度不符合要求！',
                  'max' => ':attribute长度不符合要求！',
                  'unique' => ':attribute已经被人占用！',
                  'exists' => ':attribute不存在！'
              ], [
                'store_id'=>'学校编号',
                'stu_order_type_no'=>'缴费模板编号',
                'charge_name'=>'名称',
                'charge_desc'=>'描述',
                'charge_item'=>'具体收费项目',
                'amount'=>'总收费金额',
              ]);

          if($validate->fails())
          {
            $this->message=$validate->getMessageBag()->first();
            return $this->format();
          }

          $grade=\App\Models\StuOrderType::create($cin);

*/

              $ok = self::addEach($cin,$merchant_id);
              if($ok['status']!=1)
              {
                $this->status=$ok['status'];
                $this->message=$cin['message'];
                return $this->format();
              }

            $this->status=1;
            $this->message='缴费模板已经添加，请等待审核。';
            return $this->format();


        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage().$e->getFile().$e->getLine();
            return $this->format();
        }
    }


/*
    子项验证并处理

*/

    private function HandleItem($str){


          $child_item=json_decode($str,true);
          if(empty($child_item))
          {
            return ['status'=>2,'message'=>'子项不能为空！'];
          }


/*
 charge_item
json字符串示例

项目列表json格式包含 

item_serial_number 缴费序列号           
item_name 缴费项目名称       
item_price 缴费项目金额       
item_mandatory 缴费是否为必填 
item_number 件数

*/
          $can_use_item=[];
          $amount=0;
          $item_serial_number=1;
          foreach($child_item as $v)
          {
            if(empty($v))
            {
                return ['status'=>2,'message'=>'子项设置错误！'];
            }

            if(empty($v['item_price']))
            {
                return ['status'=>2,'message'=>'子项金额没有设置！'];
            }

            $v['item_price'] = number_format($v['item_price'],2);
            if($v['item_price']<=0)
            {
                return ['status'=>2,'message'=>'子项金额不正确！'];
            }

            if(empty($v['item_number']))
            {
                return ['status'=>2,'message'=>'子项件数没有设置！'];
            }
            $v['item_number']=(int)$v['item_number'];
            if($v['item_number']<1)
            {
                return ['status'=>2,'message'=>'子项件数不正确！'];
            }

            if(empty($v['item_name']))
            {
                return ['status'=>2,'message'=>'子项名称没有设置！'];
            }

            if(empty($v['item_mandatory']))
            {
                return ['status'=>2,'message'=>'子项未设置是否必交！'];
            }

            
            if(!in_array($v['item_mandatory'], ['Y','N']))
            {
                return ['status'=>2,'message'=>'子项设置必交值错误(Y/N)！'];
            }



            $amount += $v['item_price']*$v['item_number'];


            $can_use_item[]=[
                'item_serial_number'=>$item_serial_number,
                'item_name'=>$v['item_name'],
                'item_price'=>$v['item_price'],
                'item_mandatory'=>$v['item_mandatory'],
                'item_number'=>$v['item_number'],            
            ];


            $item_serial_number++;

          }

          $num=1;
          
          foreach($can_use_item as &$v)
          {
            $v['item_serial_number']=$num;
            $num++;
          }



          return ['status'=>1,'all_item'=>$can_use_item,'amount'=>$amount];

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

            $have = \App\Models\StuOrderType::
            where('store_id',$request->get('store_id'))
            ->where('charge_name',$request->get('charge_name'))
            ->first();
            if(!empty($have))
            {
                $this->message='缴费模板已经存在！';
                return $this->format();
            }



            $child=$request->get('charge_item','');

            $cin=[
                'store_id'=>$request->get('store_id',''),

                'merchant_id'=>$merchant_id,
                'stu_order_type_no'=>$this->makeNo(),
                'charge_name'=>$request->get('charge_name',''),
                'charge_desc'=>$request->get('charge_desc',''),
                // 'charge_item'=>$child,
                'amount'=>$request->get('amount',''),
                'status'=>2,
                'status_desc'=>'模板审核中',
            ];








              $validate=\Validator::make($cin, [
                        'store_id'=>'required',
                        'stu_order_type_no'=>'required',
                        'charge_name'=>'required',
                        // 'charge_desc'=>'required',
                        // 'charge_item'=>'required',
                        // 'amount'=>'required',
                        // 'status_desc'=>'required',
                    // 'cate_id'=>'required|exists:goods_cate,id',
              ], [
                  'required' => ':attribute为必填项！',
                  'min' => ':attribute长度不符合要求！',
                  'max' => ':attribute长度不符合要求！',
                  'unique' => ':attribute已经被人占用！',
                  'exists' => ':attribute不存在！'
              ], [
                'store_id'=>'学校编号',
                'stu_order_type_no'=>'缴费模板编号',
                'charge_name'=>'名称',
                'charge_desc'=>'描述',
                'charge_item'=>'收费子项',
                'amount'=>'总收费金额',
              ]);

          if($validate->fails())
          {
            $this->message=$validate->getMessageBag()->first();
            return $this->format();
          }


          $parseChild=\App\Logic\PrimarySchool\ChildItem::parse($child);
          if($parseChild['status']!=1)
          {
            $this->message=$parseChild['message'];
            return $this->format();
          }

          $cin['charge_item']=json_encode($parseChild['all_item']);
          $cin['amount']=$parseChild['amount'];



          $grade=\App\Models\StuOrderType::create($cin);


            $this->status=1;
            $this->message='缴费模板已经添加，请等待审核。';
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


            $obj = \App\Models\StuOrderType::
            where('stu_order_type_no',$request->get('stu_order_type_no'))
            ->first();

            if(empty($obj))
            {
                $this->message='模板不存在！';
                return $this->format();
            }

            if($obj->status==1)
            {
                $this->message='模板已经审核通过了，无法修改！';
                return $this->format();
            }




            $child=$request->get('charge_item','');

            $cin=[
                'charge_name'=>$request->get('charge_name',''),
                'charge_desc'=>$request->get('charge_desc',''),
                'charge_item'=>$child,
                'amount'=>$request->get('amount',''),
                'status'=>2,
                'status_desc'=>'未审核',
            ];
    

          $cin=array_filter($cin);

          if(!empty($child))
          {
              $parseChild=\App\Logic\PrimarySchool\ChildItem::parse($child);
              if($parseChild['status']!=1)
              {
                $this->message=$parseChild['message'];
                return $this->format();
              }

              $cin['charge_item']=json_encode($parseChild['all_item']);
              $cin['amount']=$parseChild['amount'];

          }

          if(empty($cin))
          {

            $this->message='请传入要修改的参数！';
            return  $this->format();
          }

          $obj=$obj->update($cin);


            $this->status=1;
            $this->message='修改缴费模板成功';
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
//merchant_name   school_name
        try{
            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;


            $obj =  new \App\Models\StuOrderType;

            // 所有学校
            $get_all_store_id = \App\Models\MerchantStore::where('merchant_id',$loginer->merchant_id)->get();

            if($get_all_store_id->isEmpty())
            {
                $this->message='没有数据！';
                return $this->format();
            }

            $all_store_id=[];
            foreach($get_all_store_id as $v)
            {
                $all_store_id[]=$v->store_id;
            }
 

            if(!empty($request->get('store_id')))
            {
                $all_store_id[]=$request->get('store_id');
            }

            $obj=$obj->whereIn('store_id',$all_store_id);



            if(!empty($request->get('status')))
            {
                $obj =$obj->
                where('status',$request->get('status'));
            }

            if(!empty($request->get('start_time')))
            {
                $obj =$obj->
                where('created_at','>=',$request->get('start_time'));
                
            }
            if(!empty($request->get('end_time')))
            {
                $obj =$obj->
                where('created_at','<=',$request->get('end_time'));
                
            }

            $obj=$obj->orderBy('id','desc');


            $cout=[];

            $get_all_store = \App\Models\StuStore::whereIn('store_id',$all_store_id)->get();
            $all_store=[];
            foreach($get_all_store as $v)
            {
                $all_store[$v->store_id] = $v;

            }
 

            $get_all_merchant = \App\Models\Merchant::get();
            $all_merchant=[];
            foreach($get_all_merchant as $v)
            {
                $all_merchant[$v->id] = $v;
            }
 

            $this->t=$obj->count();

            $data=$this->page($obj)->get();
 
            if(!$data->isEmpty())
            {
                $cout=array_map(function($each) use ($all_store,$all_merchant){
                    return array_merge($each,[
                        'school_name'=>isset($all_store[$each['store_id']]) ? $all_store[$each['store_id']]->school_name : '',
                        'merchant_name'=>isset($all_merchant[$each['merchant_id']]) ? $all_merchant[$each['merchant_id']]->name : '',
                        'charge_item'=>json_decode($each['charge_item'],true)

                        ]);

                },$data->toArray());
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



            $obj =  new \App\Models\StuOrderType;

            $obj =$obj
            ->where('stu_order_type_no',$request->get('stu_order_type_no'))
            ;


            $grade =$obj->first();



            if(empty($grade))
            {
                $this->message='缴费模板不存在！';
                return $this->format();
            }


            $store = \App\Models\StuStore::where('store_id',$grade->store_id)->first();

            $merchant = \App\Models\Merchant::where('id',$grade->merchant_id)->first(); 
            
            $obj->school_name='学校';
            $obj->merchant_name='创建人';

            $grade->school_name=isset($store->school_name) ? $store->school_name : '';
            $grade->merchant_name=isset($merchant->name) ? $merchant->name : ''; 

            $grade->charge_item=json_decode($grade->charge_item,true);
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
        审核
    */
    public function check(){

        try{
            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;


            if($loginer->merchant_type !=1)
            {

                $this->message='您没有权限审核';
                return $this->format();

            }



            $obj =  new \App\Models\StuOrderType;

            $obj =$obj
            ->where('stu_order_type_no',$request->get('stu_order_type_no'))
            ;


            $obj =$obj->first();

            if(empty($obj))
            {
                $this->message='缴费模板不存在！';
                return $this->format();
            }

            if($obj->status!=2)
            {

                $this->message='缴费模板状态已经不在审核状态！';
                return $this->format();
            }

            $status=$request->get('status','');
            $status_desc=$request->get('status_desc','') ? $request->get('status_desc','') : '';


            if(!in_array($status,[1,3]))
            {


                $this->message='缴费模板审核状态不正确！';
                return $this->format();
            }

            $obj->status=$status;
 
            if($status==1)
            {
                $obj->status_desc= empty($status_desc) ? '审核成功' : $status_desc;

            }else{
                $obj->status_desc= empty($status_desc) ? '审核失败' : $status_desc;
                
            }


            $obj->update();




            $this->status=1;
            $this->message='操作成功！';
            return $this->format();


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
