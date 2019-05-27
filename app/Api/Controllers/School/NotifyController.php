<?php

namespace App\Api\Controllers\School;

/*

*/
class NotifyController 
{

    /*
        注意：   支付宝发来的  out_trade_no   是发送支付宝订单时支付宝的单号    我们内部的单号没有返回！！

    */
    public function index(){

// \App\Common\Log::write(app('request')->all(),'school_pay_notify.txt');

        $request = app('request');
        $cin=$request->all();
                // $cin['subject'] = iconv('gbk', 'utf-8', $cin['subject']);
\App\Common\Log::write($cin,'school_pay_notify.txt');
    
        if(!isset($cin['passback_params']))
        {
            return;
        }

        // 小项状态更新
        $item_str = base64_decode($cin['passback_params']);//orderNo=5b7a8eaa7bb90a29b74c81de&isvOrderNo=2018082017301048445&items=1-1|2-1  只能用这个里面的支付宝下单的单号   isvOrderNo 我方订单编号  orderNo支付宝订单编号

        parse_str($item_str,$item_data);

        if(!isset($item_data['isvOrderNo']) || empty($item_data['isvOrderNo']))
        {
            return;//我方订单编号，即订单表的out_trade_no
        }
        $out_trade_no = $item_data['isvOrderNo'];//支付宝订单号

\App\Common\Log::write($cin,'school_pay_notify.txt');



        $order = \App\Models\StuOrder::where('out_trade_no',$out_trade_no)->first();
        if(empty($order))
        return;
    
$sys_order_no=$order->out_trade_no;//服务商订单号

\App\Common\Log::write('222222222','school_pay_notify.txt');

        //支付状态 1-成功-2-等待支付，3-失败，4-关闭，5-退款中，6-已退款
        if($order->pay_status == 1 || $order->pay_status==3 || $order->pay_status== 4 || $order->pay_status==6)
        {
            return;
        }
\App\Common\Log::write('333333333333','school_pay_notify.txt');


        if(isset($cin['subject']))
        {
            $cin['subject']=iconv('gbk', 'utf-8', $cin['subject']);
                // $cin['subject'] = mb_convert_encoding($cin['subject'], 'utf-8', 'gbk');
        }












        $info_school = $cur_school = \App\Models\StuStore::where('store_id',$order->store_id)->first();
        if(empty($cur_school))
        {
            return;
        }
\App\Common\Log::write('4444444444','school_pay_notify.txt');


        //分校
        if(!empty($cur_school->pid))
        {
            /*
                分校一个条件     分校同步一个条件    分校授权一个条件

                如果没有同步到学校 或者么有授权 就是公用主校的
            */
            $cur_ali_app_auth_user = \App\Models\AlipayAppOauthUsers::where('store_id',$cur_school->store_id)->first();
            if($cur_school->alipay_status !=1 || empty($cur_ali_app_auth_user))
            {
                // 使用学校资料
                $info_school = \App\Models\StuStore::where('store_id',$cur_school->pid)->first();
                if(empty($info_school)){
                    return;
                    // $this->message='主校信息不存在！';
                }

            // 其他情况使用自己的学校资料
            }else{
                $info_school = $cur_school;
            }

        }





        $ali_config = \App\Models\AlipayIsvConfig::where('config_id',$info_school->config_id)->first();
\App\Common\Log::write($ali_config,'school_pay_notify.txt');

        //pid
        $check = \App\Logic\CheckField\CheckAlipayIsvConfigs::schoolconfig($ali_config);

        if($check !== true)
        {
            return;
        }

        // alipay_rsa_old_public_key


\App\Common\Log::write('5555555555','school_pay_notify.txt');


/*
    alipay_rsa_old_public_key
    rsa 1
    为空 不要验
    不为空在验
*/
        if(!empty($ali_config->ali_config))
        {
            $aop = \App\Logic\Common\InitAliAop::aop2($ali_config);


            // $aop->alipayrsaPublicKey='MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDDI6d306Q8fIfCOaTXyiUeJHkrIvYISRcc73s3vF1ZT7XN8RNPwJxo8pWaJMmvyTn9N4HQ632qJBVHf8sxHi/fEsraprwCtzvzQETrNRwVxLO5jVmRGi60j8Ue1efIlzPXV9je9mkjzOmdssymZkh2QhUrCmZYI/FCEa3/cNMW0QIDAQAB';

            // $aop->alipayrsaPublicKey=$ali

            // $aop->apiVersion = $cin['version'];
            // $aop->signType = $cin['sign_type'];
            // $aop->postCharset=$cin['charset'];
            // $aop->format='json';

            $check_sign = $aop->rsaCheckV1($cin,null,$cin['sign_type']);



            // $check_sign = $aop->rsaCheckUmxnt($cin, $ali_config->alipay_rsa_public_key);

\App\Common\Log::write('验证签名','school_pay_notify.txt');
            // 签名不正确
            if(!$check_sign)
            {
\App\Common\Log::write('签名错误===>'.$check_sign,'school_pay_notify.txt');
                return;
            }


        }







           //支付成功
         if($cin['trade_status'] == 'TRADE_SUCCESS'){


            $order->pay_status=1;
            $order->pay_status_desc='支付宝缴费成功';

            $order->pay_amount=$cin['total_amount'];
            $order->receipt_amount=$cin['receipt_amount'];
            $order->buyer_id=$cin['buyer_id'];
            $order->buyer_logon_id=$cin['buyer_logon_id'];

            $order->pay_time=date('Y-m-d H:i:s');
            $order->pay_type='1005';//支付类型
            $order->pay_type_desc='支付宝缴费';//支付类型描述
            $order->pay_type_source='alipay';//支付类型
            $order->pay_type_source_desc='支付宝缴费';//支付类型描述
            $ok=$order->update();


            if(!empty($item_str))
            {
\App\Common\Log::write($item_str,'item.txt');
                parse_str($item_str,$arr);
\App\Common\Log::write($arr,'item.txt');
                $items=isset($arr['items']) ? $arr['items'] : '' ;

                if(!empty($items))
                {

                        $all_item=explode('|', $items);

                        foreach($all_item as $item)
                        {
                            $item_serial_number=$item{0};//小项编号
\App\Common\Log::write($item_serial_number,'item.txt');
                            try{
                            // 跟新状态
                            \App\Models\StuOrderItem::where('out_trade_no',$sys_order_no)->where('item_serial_number',$item_serial_number)->update(['status'=>1,'status_desc'=>'支付成功']);
                            }
                            catch(\Exception $e)
                            {
                                
                            }
                        }
                }else{
\App\Common\Log::write('所有子项都缴了','item.txt');
                    \App\Models\StuOrderItem::where('out_trade_no',$sys_order_no)->update(['status'=>1,'status_desc'=>'支付成功']);
                }


            }




            $order = \App\Models\StuOrder::where('out_trade_no',$sys_order_no)->first();
            $sync=\App\Logic\PrimarySchool\SyncOrder::start($ali_config,$order,$info_school);
\App\Common\Log::write($sync,'sync.txt');

            if($sync['status']==1)
            {
                $order->pay_alipay_status=1;
                $order->pay_alipay_status_desc=$sync['message'];
                $order->update();
            }




            if($ok)
            {
\App\Common\Log::write('已通知对方---success----','sync.txt');
                echo 'success';
                exit();
                
            }

         }


         return;
    }

}