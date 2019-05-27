<?php
namespace App\Common\XingPOS\Request;
/*
	



3.1.7  撤销订单(RevokeBarcodepay)
		http://sandbox.starpos.com.cn/adpweb/ehpspos3/sdkBarcodePay.json
		http://gateway.starpos.com.cn/adpweb/ehpspos3/sdkBarcodePay.json

		$request_obj = \App\Common\XingPOS\Request\XingPayCheXiaoDingDan($data)




*/
class XingPayCheXiaoDingDan
{
	public $pdf_type=2;
	public $url_sandbox='http://sandbox.starpos.com.cn/adpweb/ehpspos3/RevokeBarcodepay.json';
	public $url='http://gateway.starpos.com.cn/adpweb/ehpspos3/RevokeBarcodepay.json';

	public $content=[];

	public $request_sign_field=[
	'qryNo'

	];


	public $back_sign_field=[
		'LogNo',
		'result',
		'payChannel'

	];


	public function setBizContent($data=[]){
		$this->content += $data;
		// $this->content=$data;
	}

	public function getBizContent(){
		return $this->content;
	}

}