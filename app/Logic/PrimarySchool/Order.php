<?php 
namespace App\Logic\PrimarySchool;
 
use Illuminate\Support\Facades\DB;


/* 
	
*/

class Order
{ 


	public static function status($pay_status){

                switch($pay_status){
                    case 1:
                        return '支付成功';
                        break;
                    case 2:
                        return '等待支付';
                        break;
                    case 3:
                        return '支付失败';
                        break;
                    case 4:
                        return '关闭';
                        break;
                    case 5:
                        return '退款中';
                        break;
                    case 6:
                        return '已退款';
                        break;
                    case 7:
                        return '有退款';
                        break;
                }
                return '未定义';

	}






}

