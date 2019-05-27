<?php
namespace App\Common\XingPOS\Request;
/*
	


3.1.2  扫码支付- 客户主扫（ sdkBarcodePosPay ）
		
		http://sandbox.starpos.com.cn/adpweb/ehpspos3/sdkBarcodePay.json
		http://gateway.starpos.com.cn/adpweb/ehpspos3/sdkBarcodePay.json

		$request_obj = \App\Common\XingPOS\Request\XingPaySaoMaZhiFuKeHuZhuSao($data)



*/
class XingPaySaoMaZhiFuKeHuZhuSao
{
	public $pdf_type=2;
	public $url_sandbox='http://sandbox.starpos.com.cn/adpweb/ehpspos3/sdkBarcodePosPay.json';
	public $url='http://gateway.starpos.com.cn/adpweb/ehpspos3/sdkBarcodePosPay.json';

	public $content=[];

	public $request_sign_field=[
		'amount',
		'total_amount',
		'payChannel'
	];


	public $back_sign_field=[
		'logNo',
		'result',
		'orderNo',
		'total_amount'
	];


	public function setBizContent($data=[]){
		$this->content += $data;
		// $this->content=$data;
	}

	public function getBizContent(){
		return $this->content;
	}

}