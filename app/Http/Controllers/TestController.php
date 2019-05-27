<?php

namespace App\Http\Controllers; 
class TestController 
{

    public function index()
    
    { 

// $back = \App\Logic\PrimarySchool\SchoolInfo::start();
$back = \App\Logic\PrimarySchool\SendOrder::start();



var_dump($back);
    	

	}
}