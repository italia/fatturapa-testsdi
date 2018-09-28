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
    // TODO: checkExpiration
} else {
    // issuer-specific
    Route::post('upload', 'fatturapa\control\InvoicesController@upload');
    Route::post('transmit', 'fatturapa\control\InvoicesController@transmit');
    // recipient-specific
    Route::post('accept/{udid}', 'fatturapa\control\InvoicesController@accept');
    // TODO: refuse
}
// common routes

// general simulation control
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
