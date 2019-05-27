<?php 
namespace App\Logic\PrimarySchool;
 
use Illuminate\Support\Facades\DB;


/* 


	
*/

class OrderCanPay
{ 


    /*
        功能：检测大单号可以生成订单
        
        \App\Logic\PrimarySchool\OrderCanPay::check('out_trade_no编号')
        
        入参：大单号

        返回： 
        ['status'=>1,'message'=>'可以下单']
        ['status'=>2,'message'=>'不可以下单的原因']
    */
    public static function check($out_trade_no){
        try{

            $order = \App\Models\StuOrder::where('out_trade_no',$out_trade_no)->first();
            if(empty($order))
            {
                return ['status'=>2,'message'=>'订单不存在，不可以下单！'];
            }

            if($order->pay_status !=2)
            {
                return ['status'=>2,'message'=>'该订单不可以下单！'];
            }

            return ['status'=>1,'message'=>'可以下单'];

        }
        catch(\Exception $e)
        {
            return ['status'=>2,'message'=>'下单检测异常：'.$e->getMessage()];
        }
    }





    /*
        功能：检测大单号+小项编号数组 可以生成订单
        
        \App\Logic\PrimarySchool\OrderCanPay::check('out_trade_no编号',['小项编号1','小项编号2',....])
        
        入参：大单号+小项编号数组

        返回： 
        ['status'=>1,'message'=>'可以下单']
        ['status'=>2,'message'=>'不可以下单的原因']
    */
	public static function checkBak($out_trade_no,$item){
        try{
            $item=(array)$item;

            if(empty($item))
            {
                return ['status'=>2,'message'=>'缴费小项不能为空！'];
            }

            $order = \App\Models\StuOrder::where('out_trade_no',$out_trade_no)->first();
            if(empty($order))
            {
                return ['status'=>2,'message'=>'订单不存在！'];
            }

            $get_all_item = \App\Models\StuOrderItem::where('out_trade_no',$out_trade_no)->get();
            $all_item=[];
            foreach($get_all_item as $v)
            {
                $all_item[$v->item_serial_number] = $v;
            }

            foreach($item as $v)
            {
                if(!isset($all_item[$v]))
                {
                    return ['status'=>2,'message'=>'该小项编号['.$v.']不存在！'];
                }

                if(!in_array([2,3], $all_item[$v]->status))
                {
                    return ['status'=>2,'message'=>'该小项['.$all_item[$v]->item_name.']不可以下单'];
                }
            }
            return ['status'=>1,'message'=>'可以下单'];

        }
        catch(\Exception $e)
        {
            return ['status'=>2,'message'=>'下单检测异常：'.$e->getMessage()];
        }
	}



}

