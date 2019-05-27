<?php 
namespace App\Logic\PrimarySchool;
 
use Illuminate\Support\Facades\DB;


/*
	其他支付渠道账单状态写入数据库----如果支付宝订单已下单则关闭支付宝教育缴费订单



     状态  1支付成功 2退款中  3退款成功 4退款失败
*/

class SyncOrder
{ 

	public static function  test(){
		echo 33;
	}




	// 成功
	public static function start($ali_config,$order,$info_school)
	{ 
 		try
 		{

            if($order->pay_alipay_status == 1)
            {
            	return ['status'=>2,'message'=>'支付成功的状态已经同步过支付宝了'];
            }

            $data =[
            	'out_trade_no'=>$order->trade_no,//支付宝单号
            	'status'=>1,//状态：1:缴费成功，2:关闭账单，3、退费 
            ];


            //ali_user_id
            $ali_app_auth_user = \App\Models\AlipayAppOauthUsers::where('store_id',$info_school->store_id)->first();

            $check = \App\Logic\CheckField\CheckAlipayIsvConfigs::schoolauthuser($ali_app_auth_user);

            if($check !== true)
            {
//                $this->message=$check;
//                return $this->format();

                return ['status'=>2,'message'=>$check];

            }

 
            $aop = \App\Logic\Common\InitAliAop::aop($ali_config);
            $aop->method='alipay.eco.edu.kt.billing.modify';



			$ali_request = new \Alipayopen\Sdk\Request\AlipayEcoEduKtBillingModifyRequest  ();
			$ali_request->setBizContent(json_encode($data));

			$result = $aop->execute ( $ali_request,null,$ali_app_auth_user->app_auth_token); 

			$responseNode = str_replace(".", "_", $ali_request->getApiMethodName()) . "_response";
			$resultCode = $result->$responseNode->code;

			if(!empty($resultCode)&&$resultCode == 10000){
				// order_no  支付宝订单编号

				return ['status'=>1,'message'=>'订单支付成功的状态已经成功同步到支付宝！'];
				echo "成功";
			}
			$msg= isset($result->$responseNode->sub_msg) ? $result->$responseNode->sub_msg : '';
			$msg .= isset($result->$responseNode->msg) ? $result->$responseNode->msg : '';
			return ['status'=>2,'message'=>$msg];

 		}
 		catch(\Exception $e)
 		{
			return ['status'=>-1,'message'=>$e->getMessage().$e->getFile().$e->getLine()]; 			
 		}

	}




	/*
		订单支付成功的状态写入
        \App\Logic\PrimarySchool\SyncOrder::paySuccess($out_trade_no,$mul_item,$cin)


		入参：
			参1 系统订单号   order表的out_trade_no
			参2 子项编号数组   示例 [1,3,4]
			参3 订单明细  示例
				[
					'pay_amount'=>'',//支付金额
					'receipt_amount'=>'',//商家在交易中实际收到的款项，单位为元
					'buyer_id'=>'',//买家支付宝账号对应的支付宝唯一用户号。以2088开头的纯16位数字,或者微信的openid
					'buyer_logon_id'=>'',//买家支付宝账号，或者微信昵称
					'pay_type'=>'',//支付类型，1000-官方支付宝扫码，1005-支付宝行业缴费，2000-微信缴费，2005-微信支付缴费
					'pay_type_desc'=>'',//支付宝扫码，支付宝缴费，微信支付缴费  微信支付扫码
					'pay_type_source'=>'',//支付来源 如 alipay-支付宝，weixin-微信支付
					'pay_type_source_desc'=>'',//官方支付宝
				]
		返回
			return ['status'=>1,'message'=>'订单支付成功状态已经入库！'];
			return ['status'=>2,'message'=>'子项不能为空！'];
							
	*/
	public static function paySuccess($out_trade_no,$mul_item,$cin)
	{

\App\Common\Log::write(func_get_args(),'dmk_sync.txt');
 		try
 		{

 			if(empty($mul_item))
 			{
				return ['status'=>2,'message'=>'子项不能为空！'];
 			}

 			$order = \App\Models\StuOrder::where('out_trade_no',$out_trade_no)->first();

 			if(empty($order))
 			{
				return ['status'=>2,'message'=>'订单不存在，无法更新订单状态！'];
 			}

 			$order->pay_status=1;
         	$order->pay_status_desc='缴费成功';

         	$order->pay_amount=$cin['pay_amount'];
         	$order->receipt_amount=$cin['receipt_amount'];
         	$order->buyer_id=$cin['buyer_id'];
         	$order->buyer_logon_id=$cin['buyer_logon_id'];
            $order->pay_type=$cin['pay_type'];//支付类型
            $order->pay_type_desc=$cin['pay_type_desc'];//支付类型描述
            $order->pay_type_source=$cin['pay_type_source'];//支付类型
            $order->pay_type_source_desc=$cin['pay_type_source_desc'];//支付类型描述

            $order->pay_time=date('Y-m-d H:i:s');
         	$ok=$order->update();

\App\Common\Log::write('000000000000000000000000','dmk_sync.txt');

	        foreach($mul_item as $item_serial_number)
	        {
	            try{
\App\Common\Log::write('1111111111111111111111','dmk_sync.txt');
	            // 跟新状态
	            \App\Models\StuOrderItem::where('out_trade_no',$out_trade_no)->where('item_serial_number',$item_serial_number)->update(['status'=>1,'status_desc'=>'支付成功']);
	            }
	            catch(\Exception $e)
	            {
\App\Common\Log::write($e->getMessage().$e->getFile(),'dmk_sync.txt');
	                
	            }
	        }

	        // 如果已经下单，并且没有同步的情况下，去将支付宝订单的状态同步为关闭
 			$order = \App\Models\StuOrder::where('out_trade_no',$out_trade_no)->first();
	        if($order->alipay_status==1 && $order->pay_alipay_status != 1)
	        {
	        	//读取配置并同步支付宝
				$info_school=$cur_school = \App\Models\StuStore::where('store_id',$order->store_id)->first();
				if(!empty($cur_school))
				{



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
									return ['status'=>1,'message'=>'订单支付成功状态已经入库！支付宝同步失败：主校资料不存在！'];
						            // $this->message='主校信息不存在！';
						        }

						    // 其他情况使用自己的学校资料
						    }else{
						        $info_school = $cur_school;
						    }

						}






			        $ali_config = \App\Models\AlipayIsvConfig::where('config_id',$info_school->config_id)->first();
			        //pid
			        $check = \App\Logic\CheckField\CheckAlipayIsvConfigs::schoolconfig($ali_config);

			        if($check === true)
			        {
		        		$close_ok = self::closeAliOrder($ali_config,$order,$info_school);

\App\Common\Log::write($close_ok,'sync_close.txt');


		        		// 订单关闭成功
		        		if($close_ok['status'] == 1)
		        		{
		        			$order->pay_alipay_status=1;
		        		}else{
		        			$order->pay_alipay_status=2;

		        		}
		        			$order->pay_alipay_status_desc=$close_ok['message'];
		        		$order->update();

			        }else{

\App\Common\Log::write('配置不正确'.$check,'sync_close.txt');
						return ['status'=>2,'message'=>'支付宝订单关闭失败：'.$check];

			        }

				}

				
				// return ['status'=>1,'message'=>'订单支付成功状态已经入库！支付宝同步失败！'];


	        }



			return ['status'=>1,'message'=>'订单支付成功状态已经入库！'];
 		}
 		catch(\Exception $e)
 		{
			return ['status'=>-1,'message'=>$e->getMessage().$e->getFile().$e->getLine()]; 			
 		}

	}
	// 关闭支付宝订单，并更新   支付宝更新状态
	public static function closeAliOrder($ali_config,$order,$info_school)
	{ 
\App\Common\Log::write($order->out_trade_no,'sync_close.txt');
 		try
 		{

            //ali_user_id
            $ali_app_auth_user = \App\Models\AlipayAppOauthUsers::where('store_id',$info_school->store_id)->first();

            $check = \App\Logic\CheckField\CheckAlipayIsvConfigs::schoolauthuser($ali_app_auth_user);

            if($check !== true)
            {
            	return ['status'=>2,'message'=>$check];
            }

            $data =[
            	'out_trade_no'=>$order->trade_no,//支付宝单号
            	'status'=>2,//状态：1:缴费成功，2:关闭账单，3、退费 
            ];
            
            $aop = \App\Logic\Common\InitAliAop::aop($ali_config); 
            $aop->method='alipay.eco.edu.kt.billing.modify';




			$ali_request = new \Alipayopen\Sdk\Request\AlipayEcoEduKtBillingModifyRequest  ();
			$ali_request->setBizContent(json_encode($data));
			$result = $aop->execute ( $ali_request,null , $ali_app_auth_user->app_auth_token); 

			$responseNode = str_replace(".", "_", $ali_request->getApiMethodName()) . "_response";
			$resultCode = $result->$responseNode->code;

			if(!empty($resultCode)&&$resultCode == 10000){
				// order_no  支付宝订单编号

				return ['status'=>1,'message'=>'订单支付关闭的状态已经成功同步到支付宝！'];
				echo "成功";
			}
			$msg= isset($result->$responseNode->sub_msg) ? $result->$responseNode->sub_msg : '';
			$msg .= isset($result->$responseNode->msg) ? $result->$responseNode->msg : '';
			return ['status'=>2,'message'=>'支付宝订单关闭失败：'.$msg];

 		}
 		catch(\Exception $e)
 		{
			return ['status'=>-1,'message'=>$e->getMessage().$e->getFile().$e->getLine()]; 			
 		}

	}




	// 支付成功的订单同步到支付宝
	private static function syncOrderSuccessToAli($ali_config,$order){


           $info_school= $cur_school = \App\Models\StuStore::where('store_id',$order->store_id)->first();

            if(empty($cur_school)){
            	return ['status'=>2,'message'=>'学校信息不存在！'];
            }

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
            	return ['status'=>2,'message'=>'主校信息不存在！'];
        }

    // 其他情况使用自己的学校资料
    }else{
        $info_school = $cur_school;
    }

}



            //ali_user_id
            $ali_app_auth_user = \App\Models\AlipayAppOauthUsers::where('store_id',$info_school->store_id)->first();

            $check = \App\Logic\CheckField\CheckAlipayIsvConfigs::schoolauthuser($ali_app_auth_user);

            if($check !== true)
            {
            	return ['status'=>2,'message'=>$check];
            }

 			$data=[
 				// 'trade_no'=>$order->trade_no,
 				'out_trade_no'=>$order->trade_no,//下单支付宝的单号
 				'status'=>1
 			];

            // $ali_config = \App\Models\AlipayIsvConfig::where('config_id',$loginer->config_id)->first();
            $aop = \App\Logic\Common\InitAliAop::aop($ali_config);
			$request = new \Alipayopen\Sdk\Request\AlipayEcoEduKtBillingModifyRequest ();

			$request->setBizContent(/*"{" .
			"\"trade_no\":\"2014112611001004680073956707\"," .
			"\"out_trade_no\":\"20160101909909354354354\"," .
			"\"status\":\"1\"," .
			"\"fund_change\":\"Y\"," .
			"\"refund_amount\":200.12," .
			"\"refund_reason\":\"正常退款\"," .
			"\"out_request_no\":\"HZ01RF001\"," .
			"\"buyer_logon_id\":\"159****5620\"," .
			"\"gmt_refund\":\"2015-11-27 15:45:57\"," .
			"\"buyer_user_id\":\"2088101117955611\"," .
			"\"refund_detail_item_list\":\"{\\r\" .
			"\\t\\\"fund_channel\\\":\\\"ALIPAYACCOUNT\\\",\\r\" .
			"\\t\\\"amount\\\":12.00,\\r\" .
			"\\t\\\"real_amount\\\":12.00\\r\" .
			"\\t\\r\" .
			"}\"" .
			"  }"*/json_encode($data));
			$result = $aop->execute ( $request,null ,$ali_app_auth_user->app_auth_token); 

\App\Common\Log::write($result,'sync.txt');

			$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
			$resultCode = $result->$responseNode->code;

			if(!empty($resultCode)&&$resultCode == 10000){
				// order_no  支付宝订单编号

				return ['status'=>1,'message'=>'同步成功！'];
				echo "成功";
			}

			$msg= isset($result->$responseNode->sub_msg) ? $result->$responseNode->sub_msg : '';
			$msg .= isset($result->$responseNode->msg) ? $result->$responseNode->msg : '';

			return ['status'=>2,'message'=>$msg];

			echo "失败";

	}






/*




	public static function startTest()
	{ 
 		try
 		{

            $aop = new \Alipayopen\Sdk\AopClient();
// $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
$aop->gatewayUrl = 'https://openapi.alipaydev.com/gateway.do';

$aop->appId = '2016080200145744';

// 线上pid
// $aop->appId='2018060360256678';

$aop->rsaPrivateKey = 'MIIEogIBAAKCAQEAwR+6padlk1i4LzdvS+iYpUby74IGli9LFKBTGn3aTKmZJJz4v7zTE486Q4N2KuH7+6iPvPvt0RBCH4MpbzaU8X4SMgVKIiurkvXruJGIwSF2sCupryb6+1DXxHIl7nR1VL/Aow93buvXdoeZGz21rxNrbCfdlrIdOhaddjLWTx7h00vCyrh4i3vfsPG7w6J645VruIsG6skAjMVXtOAEViprkzgWhlwlrVSrCo+7Iuy1PQrd5k+MnP4aRfoKfJ5Ivn4mQaAVgEdUTrPpUeMbWy7yvEMXzbArTpes/IZPSMXR1pmhucPhhXdHIkPNCoCIYU/BXX2U4dkd2vFWDfFjPQIDAQABAoIBAB19EcvvlpP8LQuQpF7r4jsCbV/i88yE5ir9HBNkeivQjcDIczcbxwMqkJP0g9uibA6OO3x432RX3jDfnzkLFY0WWgLnSd2T23vyLw8cscwDpxLZZ+yFwDcVrgyh/Wa+w5ewO+LqHquCOYEwzVEaiB52kaWPJMe45LuU7nA47P5hjdqEWSxmrIkSREhlKDhspIoBa0gF/w8nCk3NSiJQnT88Gba3c/79Xtwz2O5P/XxapClHGDS0DsCpjifLr9w5Sa515jpsmUkLv/THaeYsLwkBifElswrpNmEoyWHZ8lsJOKgAYKmUULsAFT131Blssd1bEVd6tvBgZAnY5i8BywECgYEA5hE2o72X/SqxcHpjq8wj4Exd476o46sBmLwaz+CJcsMm/wHo+v6yCVGp1xBzFiLKJ515rZrVIqgCAfe1siS5KpkQhUf/EwZ7a/GTRpdt8TQLlcppwvSgKEKPLIJNQE1jgqSyVKn9fMJKmH112jcCL9itNDN/UztxnVBhjA99SX0CgYEA1uR8OZyldZPVBC+wC5PN/Gz01NA6hY6r6y7lN7Dcu3tKNggBcpmdHnDu+a5QbYedTvmO810L3Kya6cnbrAZPVked2+hxNESCE+QW0gO/jVRt6R32LuTf/KRYQML+2p2pfJJl93CAXhPQZsUCtbrJWFp2BU8jDvGoi/hpq8IhrMECgYAEV6zVWF3HDIg+3ECHXJoMwMRA6Tdc3Lxx+pLy+4T8ooxY4dtY6XfIzz7KbWgOsedo6gMC8No3Bj7LdLZ8P08za6IxMdOxszyfI/corPEJTXcug5yNbnqbZ+4149u7a/qF27/18yNyuGQaDrwru0ASUR+rzZEIrCWP15WPxDcULQKBgBoIFbBY9IY4wU4/hKDyZ7qTbFk3XE9/h/32cVf8udCQT94ZvCsoxqrAXYKrhhyul/TQMGv0spIp6p41kMHXBdda15mjH8uIHQXR1J3eTF8Pgj0CHydxHF0bf4Fg3cSX4scvaOC/pR1AYzd/2CMxnGBynOdpvcJ6rcM+9XYUD0ZBAoGAPbtVarXDtF+myRDRaHPY/CNA6eEW0Jm7CdSFY18/OlJC/MJ8geqch6aKJRh+Z16Bf1pJfmqmWW4/DdHCLPTSFlILUrA2bZHBmhW/32Ux6TnHu9OSrxhWVqY8TEWx2+geMTeA/x+1y1Rc1zK2UqzhtKvstIntL360LZnFfMLlps8=';
// 沙箱支付宝公钥
$aop->alipayrsaPublicKey='MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAtiriuqJ3TmkT/trKHsotCsBgFyJORKmbVdrM7Ghia1QwICbQRxMdtzGv4D4q00HUcafLnfbUy+rrBMqD+RNIU3Za7M7YNSGowcEo6JDbmKV9JiCwLEYW0Aun5SHHlp7liY4OzNRs7xa4yD6ssVItww1PN3+DVR3zeAcJNXt6jheXlRK1hjdNsAf2q4HVxt0v4nyoizpiAI2NYRyhIKHd+L1/SPwm0dkDDgGO0NJhh1Kuwz/FumjfF0958lIoF1Gw4ffHK8bJn5lkbtb+HSEGlXM0yYIiqr8YEzsGeKgL0Op51Wm2Ygo2irjqaH+UCR0wgC37XWkmY7EXzPBiCocm4wIDAQAB';


//线上支付宝公钥
// $aop->alipayrsaPublicKey='MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsfrVa2TgpaPZBY5zxXcXrJNyXDZZk9jDk40iQZ6/0W0I95Ag9JTNZAi1F1GCoRUfLvJvTZ4w04HFHDs/619p31QWi4wHpVp3n6eartQPS6cPNHkvB3kImGPUptkcz3wEHN5Db4XH2WGgebK0ks6nVaN0gOTKaaWqyNCuKD+tiDK86aEvqfIv6cMXeWa3zLN9wDWsYf/j0qRCgKhidOd3u5lqysJhZxG5UaxDSiwu23mNT4MukGqo5iKP4xON8jw5k7hdHb0+J6yFWFH4xhn314Nj+DxaC4kW3mMYt88YcBVQzAPh8h1bgyW9Htfwa0Jzdubd6D0WuUDgT4Uu9KAtTwIDAQAB';


$aop->apiVersion = '1.0';
$aop->signType = 'RSA2';
$aop->postCharset='UTF-8';
$aop->format='json';

var_dump(date('Y-m-d H:i:s'));

$request = new \Alipayopen\Sdk\Request\AlipayEcoEduKtBillingSendRequest ();
$request->setBizContent("{" .
"      \"users\":[{" .
"        \"user_mobile\":\"18xxxxxxxxx\"," .
"\"user_name\":\"张三\"," .
"\"user_relation\":\"1\"," .
"\"user_change_mobile\":\"13xxxxxxxxx\"" .
"        }]," .
"\"school_pid\":\"20880012939123234423\"," .
"\"school_no\":\"36010300000008\"," .
"\"child_name\":\"张晓晓\"," .
"\"grade\":\"高一\"," .
"\"class_in\":\"3班\"," .
"\"student_code\":\"2098453900091\"," .
"\"student_identify\":\"310193199905289483\"," .
"\"out_trade_no\":\"20160232343253299453\"," .
"\"charge_bill_title\":\"学生开学收费项\"," .
"\"charge_type\":\"M\"," .
"      \"charge_item\":[{" .
"        \"item_name\":\"校服费\"," .
"\"item_price\":8.88," .
"\"item_serial_number\":1," .
"\"item_maximum\":5," .
"\"item_mandatory\":\"N\"" .
"        }]," .
"\"amount\":88.88," .
"\"gmt_end\":\"2016-01-01 13:13:13\"," .
"\"end_enable\":\"Y\"," .
"\"partner_id\":\"201600129391238873\"" .
"  }");
$result = $aop->execute ( $request); 

var_dump($result);

$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
$resultCode = $result->$responseNode->code;



if(!empty($resultCode)&&$resultCode == 10000){
	// order_no  支付宝订单编号

	return ['status'=>1,'order_no'=>$result->$responseNode->order_no];
	echo "成功";
}

$msg= isset($result->$responseNode->sub_msg) ? $result->$responseNode->sub_msg : '';
$msg .= isset($result->$responseNode->msg) ? $result->$responseNode->msg : '';

return ['status'=>2,'message'=>$msg];

echo "失败";


 












 		}
 		catch(\Exception $e)
 		{
			return ['status'=>-1,'message'=>$e->getMessage().$e->getFile().$e->getLine()]; 			
 		}

	}


*/








}

