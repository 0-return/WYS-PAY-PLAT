<?php
namespace App\Common\XingPOS\Request;
/*

2.11  商户新增产品
App\Common\XingPOS\Request\XingStoreShangHuXinZengChanPin($data)

*/
class XingStoreShangHuXinZengChanPin
{

	public $content=[
		'serviceId'=>'6060607'
	];

	public function setBizContent($data=[]){
		$this->content += $data;
		// $this->content=$data;
	}

	public function getBizContent(){
		return $this->content;
	}

}