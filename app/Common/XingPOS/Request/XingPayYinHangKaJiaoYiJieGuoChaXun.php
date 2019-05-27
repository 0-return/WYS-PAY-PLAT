<?php
namespace App\Common\XingPOS\Request;
/*
	


3.1.8  银行卡交易结果查询（ sdkQryCardPay
		http://sandbox.starpos.com.cn/adpweb/ehpspos3/sdkBarcodePay.json
		http://gateway.starpos.com.cn/adpweb/ehpspos3/sdkBarcodePay.json

		$request_obj = \App\Common\XingPOS\Request\XingPayYinHangKaJiaoYiJieGuoChaXun($data)



*/
class XingPayYinHangKaJiaoYiJieGuoChaXun
{
	public $pdf_type=2;
	public $url_sandbox='http://sandbox.starpos.com.cn/adpweb/ehpspos3/sdkQryCardPay.json';
	public $url='http://gateway.starpos.com.cn/adpweb/ehpspos3/sdkQryCardPay.json';

	public $content=[];

	public $request_sign_field=[

	];

	public $back_sign_field=[
		'LogNo',
		'result',
		'amount',
		'batNo',
		'ctxndt',
		'cseqNo',
		'srefNo',
		'logNo',
		'selOrderNo'
	];


	public function setBizContent($data=[]){
		$this->content += $data;
		// $this->content=$data;
	}

	public function getBizContent(){
		return $this->content;
	}

}