<?php 
namespace App\Logic\PrimarySchool;
 
use Illuminate\Support\Facades\DB;


/* 
	
    学校信息录入支付宝
*/

class ChildItem
{ 


	public static function parse($str){


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
          $n_can_use_item=[];
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

            $v['item_price'] = floatval($v['item_price']);

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

            $temp=[
                    'item_serial_number'=>$item_serial_number,
                    'item_name'=>$v['item_name'],
                    'item_price'=>$v['item_price'],
                    'item_mandatory'=>$v['item_mandatory'],
                    'item_number'=>$v['item_number'],            
                ];

            if($v['item_mandatory'] == 'Y')
            {
                $can_use_item[]=$temp;
            }
            else
            {
                $n_can_use_item[]=$temp;
            }

            $item_serial_number++;

          }

          $can=array_merge($can_use_item,$n_can_use_item);



          $num=1;
          
          foreach($can as &$v)
          {
            $v['item_serial_number']=$num;
            $num++;
          }

          

          return ['status'=>1,'all_item'=>$can,'amount'=>$amount];


	}






}

