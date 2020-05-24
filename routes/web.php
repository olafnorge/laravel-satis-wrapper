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

// main entry point
Route::get('/', ['middleware' => 'guest', 'uses' => 'Auth\LoginController@index'])->name('index');

// catch all route in order to pass through composer requests to our repos
Route::group(['middleware' => 'auth.basic.once.satis'], function () {
    Route::get('/storage/{any}', 'SatisRepositoryController@show')
        ->where('any', '.*')
        ->name('satis.repository.show');
});

// authentication routes
Route::group(['prefix' => 'auth'], function () {
    Route::get('{provider}', ['middleware' => 'guest', 'uses' => 'Auth\LoginController@redirect'])
//        ->where('provider', '(github|google|linkedin)')
        ->where('provider', '(google)')
        ->name('auth.redirect');
    Route::get('callback', ['middleware' => 'guest', 'uses' => 'Auth\LoginController@callback'])->name('auth.callback');
    Route::post('logout', ['middleware' => 'auth', 'uses' => 'Auth\LoginController@logout'])->name('auth.logout');
});

// authenticated routes
Route::group(['middleware' => 'auth', 'prefix' => 'admin'], function () {
    // satis configuration routes
    Route::group(['prefix' => 'configurations'], function () {
        Route::get('/', 'SatisConfigurationController@index')->name('satis.configuration.index');
        Route::get('/create', 'SatisConfigurationController@create')->name('satis.configuration.create');
        Route::post('/create', 'SatisConfigurationController@store')->name('satis.configuration.create');
        Route::get('/details/{uuid}', 'SatisConfigurationController@details')->name('satis.configuration.details');
        Route::get('/edit/{uuid}', 'SatisConfigurationController@edit')->name('satis.configuration.edit');
        Route::post('/edit/{uuid}', 'SatisConfigurationController@update')->name('satis.configuration.edit');
        Route::post('/build/{uuid}', 'SatisConfigurationController@build')->name('satis.configuration.build');
        Route::post('/purge/{uuid}', 'SatisConfigurationController@purge')->name('satis.configuration.purge');
    });

    // satis repository routes
    Route::group(['prefix' => 'repositories'], function () {
        Route::get('/create/{uuid}', 'SatisRepositoryController@create')->name('satis.repository.create');
        Route::post('/create/{uuid}', 'SatisRepositoryController@store')->name('satis.repository.create');
        Route::get('/edit/{uuid}/{index}', 'SatisRepositoryController@edit')->name('satis.repository.edit');
        Route::post('/edit/{uuid}/{index}', 'SatisRepositoryController@update')->name('satis.repository.edit');
        Route::delete('/delete/{uuid}/{index}', 'SatisRepositoryController@delete')->name('satis.repository.delete');
    });
});
