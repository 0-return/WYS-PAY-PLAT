<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});

$api = app('Dingo\Api\Routing\Router');
$api->version('v1', function ($api) {
//无需token
    $api->group(['namespace' => 'App\Api\Controllers\Consumer', 'prefix' => 'consumer'], function ($api) {
        $api->any('login', 'LoginController@login');

    });


//需要token
    $api->group(['namespace' => 'App\Api\Controllers\Consumer', 'prefix' => 'consumer', 'middleware' => 'consumer.api'], function ($api) {
        $api->any('index', 'IndexController@index');


        $api->any('order/lst', 'OrderController@lst');
        $api->any('order/show', 'OrderController@show');

        $api->any('student/lst', 'StudentController@lst');
    });


// 不要token


    $api->group(['namespace' => 'App\Api\Controllers\Consumer\H5', 'prefix' => 'consumer/h5'], function ($api) {

        $api->any('school/info', 'SchoolController@info');

    	//年级和班级列表
        $api->any('grade/lst', 'GradeController@lst');
        $api->any('class/lst', 'ClassController@lst');

        // 检验页面信息
        $api->any('info/check', 'CheckPageInfoController@index');

        
        $api->any('order/lst', 'OrderController@lst');
        $api->any('order/show', 'OrderController@show');

    });


});