<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::group(['namespace' => 'User', 'prefix' => 'user'], function () {
    Route::get('login', 'ViewController@login');
    Route::get('index', 'ViewController@index');
    Route::get('alipayconfirm', 'ViewController@alipayconfirm');
    Route::get('wechatconfirm', 'ViewController@wechatconfirm');    
    Route::get('schoollist', 'ViewController@schoollist');
    Route::get('agentlist', 'ViewController@agentlist');
    Route::get('waterlist', 'ViewController@waterlist');
    Route::get('qrcode', 'ViewController@qrcode');
    Route::get('seewater', 'ViewController@seewater');
    Route::get('payitem', 'ViewController@payitem');
    Route::get('addagent', 'ViewController@addagent');
    Route::get('examineschool', 'ViewController@examineschool');
    Route::get('editschool', 'ViewController@editschool');
    Route::get('forget', 'ViewController@forget');
    Route::get('storelist', 'ViewController@storelist');
    Route::get('seestore', 'ViewController@seestore');
    Route::get('editstore', 'ViewController@editstore');
    Route::get('devicelist', 'ViewController@devicelist');
    Route::get('adddevice', 'ViewController@adddevice');
    Route::get('editdevice', 'ViewController@editdevice');
    Route::get('appmsg', 'ViewController@appmsg');
    Route::get('bannerlist', 'ViewController@bannerlist');
    Route::get('addappmsg', 'ViewController@addappmsg');
    Route::get('addbanner', 'ViewController@addbanner');
    Route::get('role', 'ViewController@role');
    Route::get('addrole', 'ViewController@addrole');
    Route::get('permissions', 'ViewController@permissions');
    Route::get('power', 'ViewController@power');
    Route::get('addpower', 'ViewController@addpower');
    Route::get('rolelist', 'ViewController@rolelist');
    Route::get('tradelist', 'ViewController@tradelist');
    Route::get('passway', 'ViewController@passway');
    Route::get('ratelist', 'ViewController@ratelist');
    Route::get('flowerlist', 'ViewController@flowerlist');
    Route::get('branchshop', 'ViewController@branchshop');
    Route::get('addbranchdevice', 'ViewController@addbranchdevice');
    Route::get('qrcodemanage', 'ViewController@qrcodemanage');

    Route::get('adcatelist', 'ViewController@adcate_lists');
    Route::get('addadcate', 'ViewController@addad_cate');
    Route::get('editadcate', 'ViewController@editad_cate');

    Route::get('ad', 'ViewController@ad');
    Route::get('addad', 'ViewController@addad');
    Route::get('adsee', 'ViewController@adsee');
    Route::get('editad', 'ViewController@editad');
    Route::get('reward', 'ViewController@reward');
    Route::get('putforward', 'ViewController@putforward');
    Route::get('alipayred', 'ViewController@alipayred');
    Route::get('addalipayred', 'ViewController@addalipayred');
    Route::get('shouyin', 'ViewController@shouyin');
    Route::get('addshouyin', 'ViewController@addshouyin');
    Route::get('jdconfigure', 'ViewController@jdconfigure');
    Route::get('openpassway', 'ViewController@openpassway');
    Route::get('newworld', 'ViewController@newworld');
    Route::get('settlement', 'ViewController@settlement');
    Route::get('jdwhitebar', 'ViewController@jdwhitebar');
    Route::get('storeratelist', 'ViewController@storeratelist');
    Route::get('updata', 'ViewController@updata');
    Route::get('appconfig', 'ViewController@appconfig');
    Route::get('pushconfig', 'ViewController@pushconfig');
    Route::get('msgconfig', 'ViewController@msgconfig');
    Route::get('hrtconfig', 'ViewController@hrtconfig');
    Route::get('passwaysort', 'ViewController@passwaysort');
    Route::get('storeconfig', 'ViewController@storeconfig');
    Route::get('reconciliation', 'ViewController@reconciliation');
    Route::get('devicemanage', 'ViewController@devicemanage');
    Route::get('merchantnumber', 'ViewController@merchantnumber');
    Route::get('withdrawrecord', 'ViewController@withdrawrecord');
    Route::get('deviceconfig', 'ViewController@deviceconfig');
    Route::get('bound', 'ViewController@bound');
    Route::get('unbound', 'ViewController@unbound');
    Route::get('cashset', 'ViewController@cashset');
    Route::get('settlerecord', 'ViewController@settlerecord');
    Route::get('settledetail', 'ViewController@settledetail');
    Route::get('mqtt', 'ViewController@mqtt');
    Route::get('yytong', 'ViewController@yytong');
    Route::get('merchantmanage', 'ViewController@merchantmanage');
    Route::get('addstoretransfer', 'ViewController@addstoretransfer');
    Route::get('transactionlist', 'ViewController@transactionlist');
    Route::get('percode', 'ViewController@percode');
    Route::get('addpercode', 'ViewController@addpercode');
    Route::get('makemoney', 'ViewController@makemoney');
    Route::get('depositwater', 'ViewController@depositwater');
    Route::get('depositacount', 'ViewController@depositacount');
    Route::get('cashwithdrawal', 'ViewController@cashwithdrawal');
    Route::get('fuyoumanage', 'ViewController@fuyoumanage');

});
