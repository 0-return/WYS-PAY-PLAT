<?php
namespace App\Common\XingPOS\Request;
/*
	
3.1.1  扫码支付- 商户主扫 ( sdkBarcodePay )
		
		http://sandbox.starpos.com.cn/adpweb/ehpspos3/sdkBarcodePay.json
		http://gateway.starpos.com.cn/adpweb/ehpspos3/sdkBarcodePay.json
		
		$request_obj = \App\Common\XingPOS\Request\XingPaySaoMaZhiFuShangHuZhuSao($data)

*/
class XingPaySaoMaZhiFuShangHuZhuSao
{
	public $pdf_type=2;
	public $url_sandbox='http://sandbox.starpos.com.cn/adpweb/ehpspos3/sdkBarcodePay.json';
	public $url='http://gateway.starpos.com.cn/adpweb/ehpspos3/sdkBarcodePay.json';

	public $content=[];

	public $request_sign_field=[
		'amount',
		'total_amount',
		'authCode',
		'payChannel'
	];

	public $back_sign_field=[
		'LogNo',
		'Result'
	];



	public function setBizContent($data=[]){
		$this->content += $data;
		// $this->content=$data;
	}

	public function getBizContent(){
		return $this->content;
	}

}