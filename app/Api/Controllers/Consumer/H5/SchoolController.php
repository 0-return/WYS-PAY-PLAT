<?php

namespace App\Api\Controllers\Consumer\H5;
use Illuminate\Support\Facades\DB;

/*
*/
class SchoolController extends \App\Api\Controllers\BaseController
{ 

    /*
        列表
    */
    public function info(){


        try{
            $request=app('request');
            $this->status=2;

            $store_id=$request->get('store_id');

            $store = \App\Models\StuStore::where('store_id',$store_id)->first();

            if(empty($store))
            {
                $this->message='学校不存在';
                return $this->format();
            } 
            $this->status=1;
            $this->message='ok';
            return $this->format($store);


        }catch(\Exception $e){
            $this->status= -1 ;
            $this->message='系统错误'.$e->getMessage().$e->getFile().$e->getLine();
            return $this->format();
        }
    }





}
