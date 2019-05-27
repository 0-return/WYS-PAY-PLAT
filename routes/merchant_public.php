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


Route::group(['namespace' => 'MerchantPublic','prefix' => 'mb'], function () {
    Route::get('login', 'ViewController@login');
    Route::get('forget', 'ViewController@forget');
    Route::get('index', 'ViewController@index');
    Route::get('store', 'ViewController@store');
    Route::get('seestore', 'ViewController@seestore');
    Route::get('cashier', 'ViewController@cashier');
    Route::get('addcashier', 'ViewController@addcashier');
    Route::get('editcashier', 'ViewController@editcashier');
    Route::get('waterlist', 'ViewController@waterlist');
    Route::get('flowerlist', 'ViewController@flowerlist');
    Route::get('home', 'ViewController@home');
    Route::get('merchantnumber', 'ViewController@merchantnumber');
    Route::get('withdrawrecord', 'ViewController@withdrawrecord');

});
