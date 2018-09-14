<?php
use Lib\Base;

// actor-specific routes

if (Base::getActor() == 'sdi') {
    // exchange-specific
    Route::post('checkValidity', 'fatturapa\libsdi\InvoicesController@checkValidity');
    Route::get('deliver', 'fatturapa\libsdi\InvoicesController@deliver');
    // TODO: checkExpiration
} else {
    // issuer-specific
    Route::post('upload', 'fatturapa\libsdi\InvoicesController@upload');
    Route::post('transmit', 'fatturapa\libsdi\InvoicesController@transmit');
    // recipient-specific
    Route::post('accept', 'fatturapa\libsdi\InvoicesController@accept');
    // TODO: refuse
}

// common routes

// general simulation control
Route::post('clear', 'fatturapa\libsdi\BaseController@clear');
Route::get('datetime', 'fatturapa\libsdi\BaseController@getdatetime');
Route::post('timestamp', 'fatturapa\libsdi\BaseController@setdatetime');
Route::post('speed', 'fatturapa\libsdi\BaseController@speed');

// notifications-related
Route::get('notifications', 'fatturapa\libsdi\NotificationsController@index');
Route::get('notifications/{udid}', 'fatturapa\libsdi\NotificationsController@notification');
Route::post('dispatch', 'fatturapa\libsdi\NotificationsController@dispatch');

// invoices-related
Route::get('invoices', 'fatturapa\libsdi\InvoicesController@index');
