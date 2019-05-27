<?php 
namespace App\Logic\PrimarySchool;
 
use Illuminate\Support\Facades\DB;


/* 
	
    学校信息录入支付宝
*/

class SchoolInfo
{ 


	/*
		同步学校信息到支付宝

		肯定返回  school_no

		直接成功的返回  school_no支付宝学校编号
		对于之前失败的同步，成功返回学校信息

	*/
	public static function sync($ali_config,$send_ali_data,$ali_app_auth_user){

        if(!empty($ali_config))
        {
\App\Common\Log::write($ali_config->toArray(),'school_info_sync.txt');

        }

        $ali_school_type=[1,2,3,4,5];
        $school_type=str_split($send_ali_data['school_type']);
        $school_type = array_unique($school_type);
        $can_use_school_type =array_intersect($school_type, $ali_school_type);

        if(empty($can_use_school_type))
        {

            return ['status'=>2,'message'=>'学校类型不能为空！'];
        }

        $send_ali_data['school_type']=implode('', $can_use_school_type);




/*
            $send_ali_data=[
                    'school_name'=>$cin['school_name'],
                    // 'school_icon'=>$cin['school_icon'],
                    // 'school_icon_type'=>$img_type,
                    'school_stdcode'=>$cin['school_stdcode'],
                    'school_type'=>$cin['school_type'],
                    'province_code'=>$cin['province_code'],
                    'province_name'=>$cin['province_name'],
                    'city_code'=>$cin['city_code'],
                    'city_name'=>$cin['city_name'],
                    'district_code'=>$cin['district_code'],
                    'district_name'=>$cin['district_name'],

                    'isv_name'=>$ali_config_msg->app_name,
                    'isv_notify_url'=>$pay_notify_url,
                    'isv_pid'=>$ali_config->alipay_pid,
                    'isv_phone'=>$ali_config_msg->app_phone,
                    'school_pid'=>$ali_app_auth_user->alipay_user_id,
            ];*/
            //支付宝接口创建
            try{

\App\Common\Log::write($send_ali_data,'school_info_sync.txt');
                $aop = \App\Logic\Common\InitAliAop::aop($ali_config);

                $aop->method = 'alipay.eco.edu.kt.billing.query';

                $ali_request = new \Alipayopen\Sdk\Request\AlipayEcoEduKtSchoolinfoModifyRequest ();
                $ali_request->setBizContent(json_encode($send_ali_data)/*"{" .
                "\"school_name\":\"杭州市西湖第一实验学校\"," .
                "\"school_icon\":\"http://m.umxnt.com/user/img/logo.png\"," .
                "\"school_icon_type\":\"png\"," .
                "\"school_stdcode\":\"3133005132\"," .  //  学校(机构)标识码（由教育部按照国家标准及编码规则编制，可以在教育局官网查询）
                "\"school_type\":\"4\"," .
                "\"province_code\":\"330000\"," .
                "\"province_name\":\"浙江省\"," .
                "\"city_code\":\"330100\"," .
                "\"city_name\":\"杭州市\"," .
                "\"district_code\":\"330106\"," .
                "\"district_name\":\"西湖区\"," .

                // "\"isv_no\":\"201600129391238873\"," . //pid  
                "\"isv_name\":\"杭州少年宫\"," .   //app_name
                "\"isv_notify_url\":\"https://isv.com/xxx\"," .   //    此通知地址是为了保持教育缴费平台与ISV商户支付状态一致性。用户支付成功后，支付宝会根据本isv_notify_url，通过POST请求的形式将支付结果作为参数通知到商户系统，ISV商户可以根据返回的参数更新账单状态。
                "\"isv_pid\":\"2088121212121212\"," .  //填写已经签约教育缴费的isv的支付宝PID
                "\"isv_phone\":\"13300000000\"," .  //  ISV联系电话,用于账单详情页面显示

                "\"school_pid\":\"20880012939123234423\"," .  //  alipay_user_id  学校用来签约支付宝教育缴费的支付宝PID

                // "\"bankcard_no\":\"P0004\"," .
                // "\"bank_uid\":\"20000293230232\"," .
                // "\"bank_notify_url\":\"https://www.xxx.xxx/xx\"," .
                // "\"bank_partner_id\":\"200002924334\"," .
                // "\"white_channel_code\":\"TESTBANK10301\"" .


                "  }"*/);
                $result = $aop->execute ( $ali_request,null,$ali_app_auth_user->app_auth_token); 
\App\Common\Log::write($result,'school_info_sync.txt');

// \App\Common\Log::write($result,'stu_add.txt');

                $responseNode = str_replace(".", "_", $ali_request->getApiMethodName()) . "_response";
                $resultCode = $result->$responseNode->code;

                //同步成功
                if(!empty($resultCode)&&$resultCode == 10000 && $result->$responseNode->status=='Y'){
                    // status  Y 表示成功  N  表示失败
                    // school_no  学校在支付宝的编号

                    $third_data =  ['status'=>1,'school_no'=>isset($result->$responseNode->school_no) ? $result->$responseNode->school_no : '','message'=>'学校入驻支付宝成功！'];
                    // echo "成功";

                    // 同步失败
                }else{

                    $msg= isset($result->$responseNode->sub_msg) ? $result->$responseNode->sub_msg : '';
                    $msg .= isset($result->$responseNode->msg) ? $result->$responseNode->msg : '';


                    $third_data =  ['status'=>2,'message'=>$msg];
                }

                // echo "失败";

            }catch(\Exception $e){
                //支付宝接口错误
                // $third_data =  ['status'=>2,'message'=>$e->getMessage().$e->getLine()];
                $third_data =  ['status'=>-1,'message'=>'支付宝接口错误'];
            }

            return $third_data;



	}





	public static function startTest()
	{ 
 		try
 		{
 			
            $aop = new \Alipayopen\Sdk\AopClient();

$aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
$aop->gatewayUrl = 'https://openapi.alipaydev.com/gateway.do';
$aop->appId = '2016080200145744';

// 线上pid
$aop->appId='2018060360256678';

$aop->rsaPrivateKey = 'MIIEogIBAAKCAQEAwR+6padlk1i4LzdvS+iYpUby74IGli9LFKBTGn3aTKmZJJz4v7zTE486Q4N2KuH7+6iPvPvt0RBCH4MpbzaU8X4SMgVKIiurkvXruJGIwSF2sCupryb6+1DXxHIl7nR1VL/Aow93buvXdoeZGz21rxNrbCfdlrIdOhaddjLWTx7h00vCyrh4i3vfsPG7w6J645VruIsG6skAjMVXtOAEViprkzgWhlwlrVSrCo+7Iuy1PQrd5k+MnP4aRfoKfJ5Ivn4mQaAVgEdUTrPpUeMbWy7yvEMXzbArTpes/IZPSMXR1pmhucPhhXdHIkPNCoCIYU/BXX2U4dkd2vFWDfFjPQIDAQABAoIBAB19EcvvlpP8LQuQpF7r4jsCbV/i88yE5ir9HBNkeivQjcDIczcbxwMqkJP0g9uibA6OO3x432RX3jDfnzkLFY0WWgLnSd2T23vyLw8cscwDpxLZZ+yFwDcVrgyh/Wa+w5ewO+LqHquCOYEwzVEaiB52kaWPJMe45LuU7nA47P5hjdqEWSxmrIkSREhlKDhspIoBa0gF/w8nCk3NSiJQnT88Gba3c/79Xtwz2O5P/XxapClHGDS0DsCpjifLr9w5Sa515jpsmUkLv/THaeYsLwkBifElswrpNmEoyWHZ8lsJOKgAYKmUULsAFT131Blssd1bEVd6tvBgZAnY5i8BywECgYEA5hE2o72X/SqxcHpjq8wj4Exd476o46sBmLwaz+CJcsMm/wHo+v6yCVGp1xBzFiLKJ515rZrVIqgCAfe1siS5KpkQhUf/EwZ7a/GTRpdt8TQLlcppwvSgKEKPLIJNQE1jgqSyVKn9fMJKmH112jcCL9itNDN/UztxnVBhjA99SX0CgYEA1uR8OZyldZPVBC+wC5PN/Gz01NA6hY6r6y7lN7Dcu3tKNggBcpmdHnDu+a5QbYedTvmO810L3Kya6cnbrAZPVked2+hxNESCE+QW0gO/jVRt6R32LuTf/KRYQML+2p2pfJJl93CAXhPQZsUCtbrJWFp2BU8jDvGoi/hpq8IhrMECgYAEV6zVWF3HDIg+3ECHXJoMwMRA6Tdc3Lxx+pLy+4T8ooxY4dtY6XfIzz7KbWgOsedo6gMC8No3Bj7LdLZ8P08za6IxMdOxszyfI/corPEJTXcug5yNbnqbZ+4149u7a/qF27/18yNyuGQaDrwru0ASUR+rzZEIrCWP15WPxDcULQKBgBoIFbBY9IY4wU4/hKDyZ7qTbFk3XE9/h/32cVf8udCQT94ZvCsoxqrAXYKrhhyul/TQMGv0spIp6p41kMHXBdda15mjH8uIHQXR1J3eTF8Pgj0CHydxHF0bf4Fg3cSX4scvaOC/pR1AYzd/2CMxnGBynOdpvcJ6rcM+9XYUD0ZBAoGAPbtVarXDtF+myRDRaHPY/CNA6eEW0Jm7CdSFY18/OlJC/MJ8geqch6aKJRh+Z16Bf1pJfmqmWW4/DdHCLPTSFlILUrA2bZHBmhW/32Ux6TnHu9OSrxhWVqY8TEWx2+geMTeA/x+1y1Rc1zK2UqzhtKvstIntL360LZnFfMLlps8=';

// 沙箱支付宝公钥
$aop->alipayrsaPublicKey='MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAtiriuqJ3TmkT/trKHsotCsBgFyJORKmbVdrM7Ghia1QwICbQRxMdtzGv4D4q00HUcafLnfbUy+rrBMqD+RNIU3Za7M7YNSGowcEo6JDbmKV9JiCwLEYW0Aun5SHHlp7liY4OzNRs7xa4yD6ssVItww1PN3+DVR3zeAcJNXt6jheXlRK1hjdNsAf2q4HVxt0v4nyoizpiAI2NYRyhIKHd+L1/SPwm0dkDDgGO0NJhh1Kuwz/FumjfF0958lIoF1Gw4ffHK8bJn5lkbtb+HSEGlXM0yYIiqr8YEzsGeKgL0Op51Wm2Ygo2irjqaH+UCR0wgC37XWkmY7EXzPBiCocm4wIDAQAB';


//线上支付宝公钥
$aop->alipayrsaPublicKey='MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsfrVa2TgpaPZBY5zxXcXrJNyXDZZk9jDk40iQZ6/0W0I95Ag9JTNZAi1F1GCoRUfLvJvTZ4w04HFHDs/619p31QWi4wHpVp3n6eartQPS6cPNHkvB3kImGPUptkcz3wEHN5Db4XH2WGgebK0ks6nVaN0gOTKaaWqyNCuKD+tiDK86aEvqfIv6cMXeWa3zLN9wDWsYf/j0qRCgKhidOd3u5lqysJhZxG5UaxDSiwu23mNT4MukGqo5iKP4xON8jw5k7hdHb0+J6yFWFH4xhn314Nj+DxaC4kW3mMYt88YcBVQzAPh8h1bgyW9Htfwa0Jzdubd6D0WuUDgT4Uu9KAtTwIDAQAB';



$aop->apiVersion = '1.0';
$aop->signType = 'RSA2';
$aop->postCharset='UTF-8';
$aop->format='json';

$request = new \Alipayopen\Sdk\Request\AlipayEcoEduKtSchoolinfoModifyRequest ();
$request->setBizContent("{" .
"\"school_name\":\"杭州市西湖第一实验学校\"," .
"\"school_icon\":\"http://m.umxnt.com/user/img/logo.png\"," .
"\"school_icon_type\":\"png\"," .
"\"school_stdcode\":\"3133005132\"," .
"\"school_type\":\"4\"," .
"\"province_code\":\"330000\"," .
"\"province_name\":\"浙江省\"," .
"\"city_code\":\"330100\"," .
"\"city_name\":\"杭州市\"," .
"\"district_code\":\"330106\"," .
"\"district_name\":\"西湖区\"," .

"\"isv_no\":\"201600129391238873\"," .
"\"isv_name\":\"杭州少年宫\"," .
"\"isv_notify_url\":\"https://isv.com/xxx\"," .
"\"isv_pid\":\"2088121212121212\"," .
"\"isv_phone\":\"13300000000\"," .

"\"school_pid\":\"20880012939123234423\"," .
"\"bankcard_no\":\"P0004\"," .
"\"bank_uid\":\"20000293230232\"," .
"\"bank_notify_url\":\"https://www.xxx.xxx/xx\"," .
"\"bank_partner_id\":\"200002924334\"," .
"\"white_channel_code\":\"TESTBANK10301\"" .
"  }");
$result = $aop->execute ( $request); 

/*
store_id
config_id
pid

school_no
school_name
school_sort_name
school_icon
school_stdcode
school_type
province_code
province_name
city_code
city_name
district_code
district_name
status
status_desc

*/
var_dump($result);

$responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
$resultCode = $result->$responseNode->code;

if(!empty($resultCode)&&$resultCode == 10000 && $result->$responseNode->status=='Y'){
	// status  Y 表示成功  N  表示失败
	// school_no  学校在支付宝的编号

	return ['status'=>1,'school_no'=>$result->$responseNode->school_no];
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











}

