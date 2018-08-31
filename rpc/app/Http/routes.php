<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/devtest',function (){
    return view('devtest');
});
Route::resource('photo', 'PhotoController');
Route::get('photos/popular', 'PhotoController@index');
//Route::get('/test', 'TestController@index');
//Route::get('/soap', 'SoapController@index');
//Route::get('/auth/auth', 'Auth\AuthController@index');
//Route::get('/soap/SdIRiceviFile', 'Soap\SdIRiceviFileController@index');
