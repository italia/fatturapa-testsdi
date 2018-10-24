<?php

if (!class_exists('\FatturaPa\Core\Actors\Base')) {
    return false;
}
        
use FatturaPa\Core\Actors\Base;

// actor-specific routes
if (Base::getActor() == 'sdi') {
    // exchange-specific
    Route::post('checkValidity', 'fatturapa\control\InvoicesController@checkValidity');
    Route::post('deliver', 'fatturapa\control\InvoicesController@deliver');
    Route::post('checkExpiration', 'fatturapa\control\InvoicesController@checkExpiration');
    Route::get('actors', 'fatturapa\control\BaseController@getActors');
    Route::get('issuers', 'fatturapa\control\BaseController@getIssuers');
} else {
    // issuer-specific
    Route::post('upload', 'fatturapa\control\InvoicesController@upload');
    Route::post('transmit', 'fatturapa\control\InvoicesController@transmit');
    // recipient-specific
    Route::post('accept/{udid}', 'fatturapa\control\InvoicesController@accept');
    Route::post('refuse/{udid}', 'fatturapa\control\InvoicesController@refuse');
}
// common routes

// general simulation control
Route::post('resetTime', 'fatturapa\control\BaseController@resetTime');
Route::post('clear', 'fatturapa\control\BaseController@clear');
Route::get('datetime', 'fatturapa\control\BaseController@getdatetime');
Route::post('timestamp', 'fatturapa\control\BaseController@setdatetime');
Route::post('speed', 'fatturapa\control\BaseController@speed');

// notifications-related
Route::get('notifications', 'fatturapa\control\NotificationsController@index');
Route::get('notifications/{udid}', 'fatturapa\control\NotificationsController@notification');
Route::post('dispatch', 'fatturapa\control\NotificationsController@dispatchi');

// invoices-related
Route::get('invoices', 'fatturapa\control\InvoicesController@index');

Route::get('channels', 'fatturapa\control\BaseController@getChannels');
