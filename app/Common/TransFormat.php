<?php
namespace App\Common;

class TransFormat
{
	/*
		通讯数据加密
	*/
	public static function encode($data)
	{
		return $data=base64_encode(json_encode((array)$data));
	}

	/*
		通讯数据解密
	*/
	public static function decode($data)
	{
		return json_decode(base64_decode((string)$data),true);
	}

}