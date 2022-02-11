<?php


Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:api')->group(function(){

    Route::get('/logout', 'RegisterController@logout');
    Route::get('/my-profile', 'RegisterController@profile');   
});

 Route::post('/register','RegisterController@register');
    Route::post('/login', 'RegisterController@login');

