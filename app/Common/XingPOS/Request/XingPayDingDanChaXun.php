<?php
namespace App\Common\XingPOS\Request;
/*
	

3.1.4  订单查询（ sdkQryBarcodePay ）
		http://sandbox.starpos.com.cn/adpweb/ehpspos3/sdkBarcodePay.json
		http://gateway.starpos.com.cn/adpweb/ehpspos3/sdkBarcodePay.json

		$request_obj = \App\Common\XingPOS\Request\XingPayDingDanChaXun($data)



*/
class XingPayDingDanChaXun
{
	public $pdf_type=2;
	public $url_sandbox='http://sandbox.starpos.com.cn/adpweb/ehpspos3/sdkQryBarcodePay.json';
	public $url='http://gateway.starpos.com.cn/adpweb/ehpspos3/sdkQryBarcodePay.json';

	public $content=[];

	public $request_sign_field=[
		'qryNo'

	];


	public $back_sign_field=[

		'LogNo',
		'result',
		'payChannel',
		'refAmt'
	];


	public function setBizContent($data=[]){
		$this->content += $data;
		// $this->content=$data;
	}

	public function getBizContent(){
		return $this->content;
	}

}