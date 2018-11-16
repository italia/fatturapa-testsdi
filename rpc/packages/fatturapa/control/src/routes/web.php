<?php

if (!class_exists('\FatturaPa\Core\Actors\Base')) {
    return false;
}
        
use FatturaPa\Core\Actors\Base;

Route::group(array('prefix' => config('app.url_suffix')), function() { 
    // actor-specific routes
    if (Base::getActor() == 'sdi') {
        // exchange-specific
        Route::post('checkValidity', 'FatturaPa\Control\InvoicesController@checkValidity');
        Route::post('deliver', 'FatturaPa\Control\InvoicesController@deliver');
        Route::post('checkExpiration', 'FatturaPa\Control\InvoicesController@checkExpiration');
        Route::get('actors', 'FatturaPa\Control\BaseController@getActors');
        Route::get('issuers', 'FatturaPa\Control\BaseController@getIssuers');
    } else {
        // issuer-specific
        Route::post('upload', 'FatturaPa\Control\InvoicesController@upload');
        Route::post('transmit', 'FatturaPa\Control\InvoicesController@transmit');
        // recipient-specific
        Route::post('accept/{udid}', 'FatturaPa\Control\InvoicesController@accept');
        Route::post('refuse/{udid}', 'FatturaPa\Control\InvoicesController@refuse');
    }
    // common routes
    
    // general simulation control
    Route::post('resetTime', 'FatturaPa\Control\BaseController@resetTime');
    Route::post('clear', 'FatturaPa\Control\BaseController@clear');
    Route::get('datetime', 'FatturaPa\Control\BaseController@getdatetime');
    Route::post('timestamp', 'FatturaPa\Control\BaseController@setdatetime');
    Route::post('speed', 'FatturaPa\Control\BaseController@speed');
    
    // notifications-related
    Route::get('notifications', 'FatturaPa\Control\NotificationsController@index');
    Route::get('notifications/{udid}', 'FatturaPa\Control\NotificationsController@notification');
    Route::post('dispatch', 'FatturaPa\Control\NotificationsController@dispatchi');
    
    // invoices-related
    Route::get('invoices', 'FatturaPa\Control\InvoicesController@index');
    Route::get('actorsgroup', 'FatturaPa\Control\BaseController@actorsGroup');
    
    // channels-related
    Route::resource('channels', 'FatturaPa\Control\ChannelsController');
    // actors-related
    Route::resource('actors', 'FatturaPa\Control\ActorsController');
});
