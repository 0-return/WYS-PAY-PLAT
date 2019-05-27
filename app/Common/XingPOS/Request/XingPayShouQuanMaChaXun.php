<?php
namespace App\Common\XingPOS\Request;
/*
	



3.1.9  授权码查询 openID （ qryAuthorizationcode
		http://sandbox.starpos.com.cn/adpweb/ehpspos3/sdkBarcodePay.json
		http://gateway.starpos.com.cn/adpweb/ehpspos3/sdkBarcodePay.json

		$request_obj = \App\Common\XingPOS\Request\XingPayShouQuanMaChaXun($data)


*/
class XingPayShouQuanMaChaXun
{
	public $pdf_type=2;
	public $url_sandbox='http://sandbox.starpos.com.cn/adpweb/ehpspos3/qryAuthorizationcode.json';
	public $url='http://gateway.starpos.com.cn/adpweb/ehpspos3/qryAuthorizationcode.json';

	public $content=[];

	public $request_sign_field=[
		'mercId',
		'trmNo',
		'txnTime',
		'userData',
		'signType',
		'version'
	];


	public $back_sign_field=[
		'returnCode',
		'sysTime',
		'OpenId',
		'message',
		'mercId'

	];


	public function setBizContent($data=[]){
		$this->content += $data;
		// $this->content=$data;
	}

	public function getBizContent(){
		return $this->content;
	}

}