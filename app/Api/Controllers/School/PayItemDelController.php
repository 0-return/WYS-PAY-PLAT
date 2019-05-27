<?php

namespace App\Api\Controllers\School;
use Illuminate\Support\Facades\DB;

/* 
*/
class PayItemDelController extends \App\Api\Controllers\BaseController
{

    /*
        单条
        
        订单表中pay_status !=1 的订单可以删除，如果发送支付宝了，先去关闭支付宝再删除

    */
    public function del(){

        try{
            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;

            $obj =  new \App\Models\StuOrderBatch;

            $obj =$obj
              ->where('stu_order_batch_no',$request->get('stu_order_batch_no'))
            ;
            $batch =$obj->first();

            if(empty($batch))
            {
                $this->message='缴费项目不存在！';
                return $this->format();
            }

            $get_all_order = \App\Models\StuOrder::where('stu_order_batch_no',$batch->stu_order_batch_no)->get();

            $all_delete=true;//全部已经删除


$all=0;
$del_success=0;
            // 订单表有记录
            if(!$get_all_order->isEmpty())
            {
              // 删除订单---关闭支付宝订单
              foreach($get_all_order as $order)
              {
$all++;                

                // 如果项目缴费成功，则跳过，否则删除订单及子项
                if($order->pay_status==1)
                {
                  $all_delete=false;
                    continue;
                }

                // 如果订单发送支付宝，则关闭支付宝订单
                if($order->pay_alipay_status == 1)
                {
                  $close_ali = $this->closeAliOrder($order);
                  // 关闭失败
                  if($close_ali['status']!=1){
                    $all_delete=false;
                    continue;
                  }
                  
                  \App\Models\StuOrder::where('id',$order->id)->update(['pay_alipay_status'=>2,'pay_alipay_status_desc'=>$close_ali['message']]);

                }

                // 删除总订单
                \App\Models\StuOrder::where('id',$order->id)->delete();

                // 删除子项
                \App\Models\StuOrderItem::where('out_trade_no',$order->out_trade_no)->delete();

$del_success++;
              }
            }


$del_fail=abs($all-$del_success);

            // 订单记录已全部删除则删除项目  否则  不删除项目
            if($all_delete)
            {
                $batch->delete();
                $this->status=1;
                $this->message='项目已经删除！';
                return $this->format();
            }else{
                // 共20条缴费记录 成功删除10条，失败10条
                $this->status=2;
                $this->message="共{$all}条缴费记录 成功删除{$del_success}条，失败{$del_fail}条";
                return $this->format();
            }

        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage().$e->getFile().$e->getLine();
            return $this->format();
        }
    }


    /*
      关闭支付宝订单
    */
    public function closeAliOrder($order){

              $ali_config = \App\Models\AlipayIsvConfig::where('config_id',$school->config_id)->first();
              //pid
              $check = \App\Logic\CheckField\CheckAlipayIsvConfigs::schoolconfig($ali_config);

              if($check!=true)
              {
                return ['status'=>2,'message'=>'支付宝配置不全！'];
              }

              return \App\Logic\PrimarySchool\SyncOrder::closeAliOrder($ali_config,$order);
    }







}
