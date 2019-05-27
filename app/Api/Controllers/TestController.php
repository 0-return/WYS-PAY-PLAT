<?php

namespace App\Api\Controllers; 



class TestController extends BaseController
{

	/*
		客户端请求服务器示例
	*/
    public function index()
    
    { 
    	try{

	    	$request=app('request');



	    	$this->status=2;

	    	$name =  $request->get('name','获取姓名');

	    	if(empty($name))
	    	{
	    		$this->message='参数不能为空！';
	    		return $this->format();
	    	}

	    	$order=new Order;
	    	$order = $order->where('id','>',1);


	    	$this->t=$order->count();
	    	$data = $this->page($order,$request)->get();

	    	$this->status=1;
	    	return $this->format($data);


    	}catch(\Exception $e){
    		$this->status= -1 ;
    		$this->message='系统错误'.$e->getMessage();
    		return $this->format();
    	}


	}
}