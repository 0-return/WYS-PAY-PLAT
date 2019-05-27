<?php 
namespace App\Logic\Common;
 


/*
	初始化工具类-----支付宝aop--  做缓存
*/

class InitAliAop
{ 


	private static $single_sop=[];

	public static function aop($config)
	{ 
		if(!isset(self::$single_sop[$config->app_id]))
		{

            $aop = new \Alipayopen\Sdk\AopClient();

			// $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
			// $aop->gatewayUrl = 'https://openapi.alipaydev.com/gateway.do';

			$aop->gatewayUrl=$config->alipay_gateway;

			// 线上pid
			$aop->appId=$config->app_id;

			$aop->rsaPrivateKey = $config->rsa_private_key;

			// 沙箱支付宝公钥
			$aop->alipayrsaPublicKey=$config->alipay_rsa_public_key;





			$aop->apiVersion = '1.0';
			$aop->signType = 'RSA2';
			$aop->postCharset='UTF-8';
			// $aop->postCharset='GBK';
			$aop->format='json';
			
			self::$single_sop[$config->app_id]=$aop;
		
		}

		return self::$single_sop[$config->app_id];
	}



	private static $single_sop2=[];

	public static function aop2($config)
	{ 
		if(!isset(self::$single_sop2[$config->app_id]))
		{

            $aop = new \App\Common\Third\Ali\aop\AopClient();

			// $aop->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
			// $aop->gatewayUrl = 'https://openapi.alipaydev.com/gateway.do';

			$aop->gatewayUrl=$config->alipay_gateway;

			// 线上pid
			$aop->appId=$config->app_id;

			$aop->rsaPrivateKey = $config->rsa_private_key;

			// 沙箱支付宝公钥
			$aop->alipayrsaPublicKey=$config->alipay_rsa_old_public_key;
			// $aop->alipayrsaPublicKey=$config->alipay_rsa_public_key;
			





			$aop->apiVersion = '1.0';
			$aop->signType = 'RSA';
			// $aop->postCharset='UTF-8';
			$aop->postCharset='GBK';
			$aop->format='json';
			
			self::$single_sop2[$config->app_id]=$aop;
		
		}

		return self::$single_sop2[$config->app_id];
	}



	public static function aopSandbox($config)
	{ 
		if(!isset(self::$single_sop[$config->app_id]))
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
			
			self::$single_sop[$config->app_id]=$aop;
		
		}

		return self::$single_sop[$config->app_id];
	}







}

