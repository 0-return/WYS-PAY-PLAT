<?php 
namespace App\Logic\CheckField;
 


/*
	检测表记录不能为空

	alipay_isv_configs
*/

class CheckAlipayIsvConfigs
{ 
	/*
		学校记录创建时检测配置  pid
	*/
	public static function schoolconfig($record)
	{ 
		if(empty($record))
		{
			return '支付宝配置不存在！';
		}

		if(empty($record->alipay_pid))
		{
			return '支付宝pid未配置！';
		}
		if(empty($record->app_id))
		{
			return '支付宝app_id未配置！';
		}
		if(empty($record->rsa_private_key))
		{
			return '支付宝应用私钥未配置！';
		}
		if(empty($record->alipay_rsa_public_key))
		{
			return '支付宝应用公钥未配置！';
		}
 
		if(empty($record->alipay_gateway))
		{
			return '支付宝网关未配置！';
		}
 


		return true;
	}




	/*
		学校记录创建时检测配置   app_name  app_phone
	*/
	public static function schoolconfigmsg($record)
	{ 
		if(empty($record))
		{
			return '支付宝配置不存在！';
		}

		if(empty($record->app_name))
		{
			return '支付宝应用名称未配置！';
		}

		if(empty($record->app_phone))
		{
			return '支付宝应用服务电话未配置！';
		}

		return true;
	}




	/*
		学校记录创建时检测配置   ali_user_id
	*/
	public static function schoolauthuser($record)
	{ 
		if(empty($record))
		{
			return '学校未授权！';
		}

		
		if(empty($record->app_auth_token))
		{
			return '商户还没有授权服务商！';//没有授权，服务商将没有返佣
		}
		
		if(empty($record->alipay_user_id))
		{
			return '学校授权user_id不存在！';
		}

		return true;
	}











}

