<?php

namespace App\Api\Controllers\School\Agent;
use Illuminate\Support\Facades\DB;

/*
    学校  增删查改
*/
class OrderController extends \App\Api\Controllers\BaseController
{


    /*
        列表
    */
    public function lst(){

        try{
            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;


            $obj =  new \App\Models\StuOrder;


            //获取登陆者id
            $loginer_id = $loginer->user_id;


                $all_store_id=[];

            if(!empty($request->get('user_id')))
            {
                if(!empty($request->get('store_id')))
                {
                    $all_store_id=[$request->get('store_id')];
         
                }else{

                    $all_store_id=[];
                    $get_all_store = \App\Models\StuStore::where('user_id',$request->get('user_id'))->get();
                    foreach($get_all_store as $v)
                    {
                        $all_store_id[]=$v->store_id;
                    }

                }
            }
            else
            {


                if(!empty($request->get('store_id')))
                {
                    $all_store_id=[$request->get('store_id')];

         
                }
                else
                {

                    // 获取下级以及自己
                    $all_user_id = $this->getSubIds($loginer_id);

                    // 查找学校store_id
                    $get_all_store = \App\Models\StuStore::whereIn('user_id',$all_user_id)->get();

                    foreach($get_all_store as $v)
                    {
                        $all_store_id[]=$v->store_id;
                    }
                }

       
            }



            $obj=$obj->whereIn('store_id',$all_store_id);



            if(!empty($request->get('stu_grades_no')))
            {
                $obj=$obj->where('stu_grades_no',$request->get('stu_grades_no'));
            }
  

            if(!empty($request->get('stu_class_no')))
            {
                $obj=$obj->where('stu_class_no',$request->get('stu_class_no'));
            }
 

            if(!empty($request->get('student_name')))
            {
                $obj=$obj->where('student_name','like','%'.$request->get('student_name').'%');
            }
 

            if(!empty($request->get('stu_order_batch_no'))){
                $obj=$obj->where('stu_order_batch_no',$request->get('stu_order_batch_no'));
            }


            if(!empty($request->get('student_no'))){
                $obj=$obj->where('student_no',$request->get('student_no'));
            }

            if(!empty($request->get('school_name'))){
                $obj=$obj->where('school_name','like','%'.$request->get('school_name').'%');
            }


            if(!empty($request->get('out_trade_no'))){
                $obj=$obj->where('out_trade_no',$request->get('out_trade_no'));
            }

            if(!empty($request->get('pay_type'))){
                $obj=$obj->where('pay_type',$request->get('pay_type'));//支付类型，1000-官方支付宝扫码，1005-支付宝行业缴费，2000-微信缴费，2005-微信支付缴费
            }


            if(!empty($request->get('pay_status'))){
                $obj=$obj->where('pay_status',$request->get('pay_status'));//1 支付成功 ，2 等待支付，3 支付失败，4 关闭，5 退款中，6 已退款 7 有退款
            }


            if(!empty($request->get('gmt_start'))){
                $obj=$obj->where('gmt_start','>=',$request->get('gmt_start'));//
            }


            if(!empty($request->get('gmt_end'))){
                $obj=$obj->where('gmt_end','<=',$request->get('gmt_end'));//
            }




            $this->t=$obj->count();

            $data=$this->page($obj)->orderBy('id','desc')->get();


            foreach($data as &$v)
            {
                switch($v->pay_status){
                    case 1:
                        $v->pay_status_desc = $v->pay_status_desc='支付成功';
                        break;
                    case 2:
                        $v->pay_status_desc = $v->pay_status_desc='等待支付';
                        break;
                    case 3:
                        $v->pay_status_desc = $v->pay_status_desc='支付失败';
                        break;
                    case 4:
                        $v->pay_status_desc = $v->pay_status_desc='关闭';
                        break;
                    case 5:
                        $v->pay_status_desc = $v->pay_status_desc='退款中';
                        break;
                    case 6:
                        $v->pay_status_desc = $v->pay_status_desc='已退款';
                        break;
                    case 7:
                        $v->pay_status_desc = $v->pay_status_desc='有退款';
                        break;
                }
            }

// 1 支付成功 ，2  等待支付 ，3  支付失败 ，4 关闭，5 退款中，6 已退款 7 有退款

            $this->status=1;
            $this->message='ok';
            return $this->format($data);


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
/*$str="{" .
                "      \"users\":[{" .
                "        \"user_mobile\":\"17314822364\"," .
                "\"user_name\":\"张三\"," .
                "\"user_relation\":\"1\"," .
                "\"user_change_mobile\":\"17002582596\"" .
                "        }]," .

                "\"school_pid\":\"2088102175631431\"," .//学校授权pid
                "\"school_no\":\"36010300000008\"," .//支付宝学校编号
                "\"child_name\":\"张晓晓\"," .
                "\"grade\":\"高一\"," .
                "\"class_in\":\"3班\"," .
                "\"student_code\":\"888888\"," .
                "\"student_identify\":\"321084199103042611\"," .
                "\"out_trade_no\":\"20160121236564523\"," .
                "\"charge_bill_title\":\"有梦想科技大学培训费\"," .
                "\"charge_type\":\"M\"," .
                "      \"charge_item\":[{" .
                "        \"item_name\":\"c++培训\"," .
                "\"item_price\":1.2," .
                "\"item_serial_number\":1," .
                "\"item_maximum\":1," .
                "\"item_mandatory\":\"N\"" .
                "        }]," .
                "\"amount\":1.2," .
                "\"gmt_end\":\"2018-06-09 13:13:13\"," .
                "\"end_enable\":\"Y\"," .
                "\"partner_id\":\"2088102175631431\"" .
                "  }";


         $d=json_decode($str,true);
echo var_export($d,true);
die;
 print_r($d,true);

         die;
*/
        try{
            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;



            $obj =  new \App\Models\StuOrder;

            $obj =$obj
            ->where('out_trade_no',$request->get('out_trade_no'))
            ;


            $data =$obj->first();

            if(empty($data))
            {
                $this->message='订单不存在！';
                return $this->format();
            }

            $all_item = \App\Models\StuOrderItem::where('out_trade_no',$data->out_trade_no)->get();


            foreach($all_item as &$v)
            {
                switch($v->status){
                    case 1:
                        $v->status_desc = $v->status_desc='支付成功';
                        break;
                    case 2:
                        $v->status_desc = $v->status_desc='等待支付';
                        break;
                    case 3:
                        $v->status_desc = $v->status_desc='支付失败';
                        break;
                    case 4:
                        $v->status_desc = $v->status_desc='关闭';
                        break;
                    case 5:
                        $v->status_desc = $v->status_desc='退款中';
                        break;
                    case 6:
                        $v->status_desc = $v->status_desc='已退款';
                        break;
                    case 7:
                        $v->status_desc = $v->status_desc='有退款';
                        break;
                }
            }



            $data->all_item=$all_item;

            $this->status=1;
            $this->message='ok';
            return $this->format($data);


        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage().$e->getFile().$e->getLine();
            return $this->format();
        }
    }






}
