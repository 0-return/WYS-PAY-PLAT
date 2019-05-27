<?php
namespace App\Common\XingPOS\Request;
/*
	

3.1.3  退款（ sdkRefundBarcodePay ）
		
		http://sandbox.starpos.com.cn/adpweb/ehpspos3/sdkBarcodePay.json
		http://gateway.starpos.com.cn/adpweb/ehpspos3/sdkBarcodePay.json

		$request_obj = \App\Common\XingPOS\Request\XingPayTuiKuan($data)



*/
class XingPayTuiKuan
{
	public $pdf_type=2;
	public $url_sandbox='http://sandbox.starpos.com.cn/adpweb/ehpspos3/sdkRefundBarcodePay.json';
	public $url='http://gateway.starpos.com.cn/adpweb/ehpspos3/sdkRefundBarcodePay.json';
	public $content=[];

	public $request_sign_field=[
		'orderNo'

	];


	public $back_sign_field=[
		'LogNo',
		'result',
		'txnAmt',
		'amount',
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