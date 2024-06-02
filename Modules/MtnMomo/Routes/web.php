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

Route::group(['prefix' => 'gateway/mtnmomo', 'namespace' => 'Modules\MtnMomo\Http\Controllers', 'as' => 'mtnmomo.', 'middleware' => ['auth', 'permission', 'web']], function () {
    Route::post('/store', 'MtnMomoController@store')->name('store')->middleware('checkForDemoMode');
    Route::get('/edit', 'MtnMomoController@edit')->name('edit');
});
