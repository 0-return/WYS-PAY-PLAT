<?php

Route::group(['namespace' => 'Phone', 'prefix' => 'phone'], function () {
    Route::get('/identfirst', 'ViewController@identfirst');
    Route::get('/identsecond', 'ViewController@identsecond');
    Route::get('/bindbank', 'ViewController@bindbank');
    Route::get('/success', 'ViewController@success');
    Route::get('/bankis', 'ViewController@bankis');

});

