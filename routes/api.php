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

Route::group(['middleware' => '\App\Http\Middleware\AuthBasicOnceSatis'], function () {
    Route::post('/webhook/satis/{repository}', 'WebhookController@satis')->name('webhook.satis');
});

Route::group(['middleware' => '\App\Http\Middleware\AuthApiGitlab'], function () {
    Route::post('/webhook/gitlab/{repository}', 'WebhookController@gitlab')->name('webhook.gitlab');
});
