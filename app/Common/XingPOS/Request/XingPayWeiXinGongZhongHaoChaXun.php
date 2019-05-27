<?php
namespace App\Common\XingPOS\Request;
/*
	

3.1.5  微信公众号查询（ pubSigQry ）
		http://sandbox.starpos.com.cn/adpweb/ehpspos3/sdkBarcodePay.json
		http://gateway.starpos.com.cn/adpweb/ehpspos3/sdkBarcodePay.json

		$request_obj = \App\Common\XingPOS\Request\XingPayWeiXinGongZhongHaoChaXun($data)



*/
class XingPayWeiXinGongZhongHaoChaXun
{
	public $pdf_type=2;
	public $url_sandbox='http://sandbox.starpos.com.cn/adpweb/ehpspos3/pubSigQry.json';
	public $url='http://gateway.starpos.com.cn/adpweb/ehpspos3/pubSigQry.json';

	public $content=[];

	public $request_sign_field=[
		'orgNo',
		'mercId',
		'trmNo',
		'txnTime',
		'signType',
		'version'

	];

	public $back_sign_field=[
		'returnCode',
		'sysTime',
		'message',
		'mercId',
		'appId',
		'appIdKey'
	];

	public function setBizContent($data=[]){
		$this->content += $data;
		// $this->content=$data;
	}

	public function getBizContent(){
		return $this->content;
	}

}