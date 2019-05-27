<?php

namespace App\Api\Controllers\School;
use Illuminate\Support\Facades\DB;

/*
     
*/
class StatPayController extends \App\Api\Controllers\BaseController
{


    /*
        教育缴费情况统计及导出
    */
    public function pay(){

        try{
            $request=app('request');
            $loginer = $this->parseToken($request->get('token'));
            $this->status=2;


            $obj =  new \App\Models\StuOrderBatch;

            $obj =$obj
            // ->leftJoin('stu_order_types','stu_order_types.stu_order_type_no','stu_order_batchs.stu_order_type_no')
            ->leftJoin('stu_orders','stu_orders.stu_order_batch_no','stu_order_batchs.stu_order_batch_no')
            ;

            $obj=$obj->select(DB::raw('stu_orders.batch_name as charge_name,count(stu_orders.id) tot_should_payer,stu_orders.stu_order_batch_no,sum(if(stu_orders.pay_status=1,stu_orders.pay_amount,0)) as have_pay_amount,sum(if(stu_orders.pay_status <> 1,stu_orders.amount,0)) as not_pay_amount,sum(stu_orders.amount) tot_should_pay,sum(if(stu_orders.pay_status=1,1,0)) as have_pay_rs,sum(if(stu_orders.pay_status<>1,1,0)) as not_pay_rs '))
            ->where('stu_order_batchs.status',1)
            ->groupBy('stu_order_batchs.stu_order_batch_no','stu_orders.batch_name');


            if(!empty($request->get('stu_grades_no'))){
                $obj=$obj->where('stu_orders.stu_grades_no',$request->get('stu_grades_no'));
            }

            if(!empty($request->get('store_id'))){
                $obj=$obj->where('stu_orders.store_id',$request->get('store_id'));
            }else{

                $get_all_store_id = \App\Models\MerchantStore::where('merchant_id',$loginer->merchant_id)->get();
                $all_store_id=[];
                foreach($get_all_store_id as $v)
                {
                    $all_store_id[]=$v->store_id;
                }


                $obj = $obj->whereIn('stu_orders.store_id',array_unique($all_store_id));
            }


            if(!empty($request->get('stu_class_no'))){
                $obj=$obj->where('stu_orders.stu_class_no',$request->get('stu_class_no'));
            }

            if(!empty($request->get('stu_order_batch_no'))){
                $obj=$obj->where('stu_orders.stu_order_batch_no',$request->get('stu_order_batch_no'));
            }

            if(!empty($request->get('start_time'))){
                $obj=$obj->where('stu_order_batchs.created_at','>=',$request->get('start_time'));
            }

            if(!empty($request->get('end_time'))){
                $obj=$obj->where('stu_order_batchs.gmt_end','<=',$request->get('end_time'));
            }

/*


select * from stu_orders where stu_order_batch_no='6R0wVM0309Ar'\G
select * from stu_orders where stu_order_batch_no='AJdRFCYFJduP'\G
select * from stu_orders where stu_order_batch_no='1VzihP2rdHls'\G


select * from stu_order_batchs where stu_order_batch_no='6R0wVM0309Ar'\G
select * from stu_order_batchs where stu_order_batch_no='AJdRFCYFJduP'\G
select * from stu_order_batchs where stu_order_batch_no='PHfA90btWjMC'\G

PHfA90btWjMC

select * from stu_order_types where stu_order_type_no='PHfA90btWjMC';
select * from stu_order_types where stu_order_type_no='PHfA90btWjMC';

6R0wVM0309Ar
AJdRFCYFJduP
1VzihP2rdHls

            //项目名称
            $all_template = \App\Models\StuOrderType::get();

            foreach($all_template as $v)
            {
                $all_template[$v->]
            }


*/
            //导出
            if(!empty($request->get('export')) && $request->get('export')==1 ){
                $data = $obj->get();

                $title=[
                    '缴费项目名称',
                    '已缴金额',
                    '应缴金额',
                    '已缴人数',
                    '未缴人数',
                    '未缴金额',
                    '应缴人数'
                ];
                $cout=[];
                foreach($data as $v)
                {
                    $cout[]=[
                        $v->charge_name,
                        number_format($v->have_pay_amount, 2, '.', ''),
                        $v->tot_should_pay,
                        $v->have_pay_rs,
                        $v->not_pay_rs,
                        number_format($v->not_pay_amount, 2, '.', ''),
                        $v->tot_should_payer
                    ];
                }
                $file_name=!empty($request->get('export_name')) ? $request->get('export_name') : '缴费情况统计';

                \App\Common\Excel\Excel::downExcel($title,$cout,$file_name);
            }


            $this->t=$obj->count();

            $data=$this->page($obj)->get();


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
