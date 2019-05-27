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


Route::group(['namespace' => 'Deposit','prefix' => 'd'], function () {
    Route::get('login', 'ViewController@login');
    Route::get('forget', 'ViewController@forget');
    Route::get('index', 'ViewController@index');
    Route::get('home', 'ViewController@home');
    Route::get('cashflow', 'ViewController@cashflow');
    Route::get('depositwater', 'ViewController@depositwater');

});
