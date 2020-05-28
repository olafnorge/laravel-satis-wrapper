<?php

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


Route::group(['middleware' => 'auth.basic.once.satis'], function () {
    Route::post('/webhook/satis/{repository}', 'Api\WebhookController@satis')->name('webhook.satis');
});

Route::group(['middleware' => 'auth.api.gitlab'], function () {
    Route::post('/webhook/gitlab/{repository}', 'Api\WebhookController@gitlab')->name('webhook.gitlab');
});

Route::group(['middleware' => 'auth.api.token'], function () {
    Route::group(['prefix' => 'configuration'], function () {
        Route::get('/', 'Api\SatisConfigurationController@index')->name('api.configuration.index');
        Route::post('/', 'Api\SatisConfigurationController@store')->name('api.configuration.store');
        Route::get('/{id}', 'Api\SatisConfigurationController@show')->name('api.configuration.show');
        Route::patch('/{id}', 'Api\SatisConfigurationController@update')->name('api.configuration.update');
    });
});
