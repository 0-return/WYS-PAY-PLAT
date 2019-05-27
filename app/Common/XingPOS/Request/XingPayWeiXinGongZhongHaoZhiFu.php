<?php
namespace App\Common\XingPOS\Request;
/*
	

3.1.6 微信公众 微信公众 号支付( pubSigPay)
		http://sandbox.starpos.com.cn/adpweb/ehpspos3/sdkBarcodePay.json
		http://gateway.starpos.com.cn/adpweb/ehpspos3/sdkBarcodePay.json

		$request_obj = \App\Common\XingPOS\Request\XingPayWeiXinGongZhongHaoZhiFu($data)


*/
class XingPayWeiXinGongZhongHaoZhiFu
{
	public $pdf_type=2;
	public $url_sandbox='http://sandbox.starpos.com.cn/adpweb/ehpspos3/pubSigPay.json';
	public $url='http://gateway.starpos.com.cn/adpweb/ehpspos3/pubSigPay.json';

	public $content=[];

	public $request_sign_field=[
		'orgNo',
		'mercId',
		'trmNo',
		'txnTime',
		'version',
		'code',
		'amount',
		'total_amount'
	];

	public $back_sign_field=[

		'returnCode',
		'sysTime',
		'message',
		'mercId',
		'LogNo',
		'orderNo',
		'amount',
		'total_amount',
		'PrepayId',
		'apiAppid',
		'apiTimestamp',
		'apiNoncestr',
		'apiPackage',
		'apiSigntype'
		// 'apiPaysign'
	];

	public function setBizContent($data=[]){
		$this->content += $data;
		// $this->content=$data;
	}

	public function getBizContent(){
		return $this->content;
	}

}