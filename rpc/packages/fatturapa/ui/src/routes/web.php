<?php

use FatturaPa\Core\Actors\Base;

$urlPrefix = Base::getActor()."/".config('app.url_suffix');

Route::group(array('prefix' => $urlPrefix), function() { 
    Route::get('dashboard', 'fatturapa\ui\IndexController@index');
    Route::get('sdi', 'fatturapa\ui\IndexController@sdi');
    Route::get('td{actor}', 'fatturapa\ui\IndexController@td');
    Route::get('mchannels', 'fatturapa\ui\IndexController@channels');  
});
