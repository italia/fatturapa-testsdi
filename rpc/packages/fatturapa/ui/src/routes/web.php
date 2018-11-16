<?php
Route::group(array('prefix' => config('app.url_suffix')), function() { 
    Route::get('dashboard', 'fatturapa\ui\IndexController@index');
    Route::get('sdi', 'fatturapa\ui\IndexController@sdi');
    Route::get('td{actor}', 'fatturapa\ui\IndexController@td');
    Route::get('mchannels', 'fatturapa\ui\IndexController@channels');  
});