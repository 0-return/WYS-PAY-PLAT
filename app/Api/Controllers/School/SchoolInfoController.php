<?php

namespace App\Api\Controllers\School;


class SchoolInfoController extends \App\Api\Controllers\BaseController
{


    public function index(){


        try{

            $request=app('request');
            $this->receive($request);

            $this->status=2;

            $name =  $request->get('name','获取姓名');

            if(empty($name))
            {
                $this->message='参数不能为空！';
                return $this->format();
            }

            $data = $this->page(new Order)->where('id','>',1)->get();

            $this->status=1;
            return $this->format($data);


        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage();
            return $this->format();
        }




    }

}
