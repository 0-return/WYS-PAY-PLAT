<?php
namespace App\Common\XingPOS\Request;
/*

2.13  安心签签署状态查询
App\Common\XingPOS\Request\XingStoreShangHuAnXinQianQianShuZhuangTaiChaXun($data)

*/
class XingStoreShangHuAnXinQianQianShuZhuangTaiChaXun
{

	public $content=[
		'serviceId'=>'6060106'
	];

	public function setBizContent($data=[]){
		$this->content += $data;
		// $this->content=$data;
	}

	public function getBizContent(){
		return $this->content;
	}

}