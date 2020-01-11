<?php

Route::get('/config', 'Configuration\ConfigurationController@getConfig');

Route::group(['prefix' => 'auth'], function () {
    Route::post('/login', 'Auth\AuthController@authenticate');
    Route::post('/check', 'Auth\AuthController@check');
    Route::post('/password', 'Auth\AuthController@password');
    Route::post('/validate-password-reset', 'Auth\AuthController@validatePasswordReset');
    Route::post('/reset', 'Auth\AuthController@reset');
});

Route::group(['middleware' => ['trt.auth']], function () {
    Route::post('/auth/logout', 'Auth\AuthController@logout');

});
