<?php

Route::get('dashboard', 'fatturapa\ui\IndexController@index');
Route::get('sdi', 'fatturapa\ui\IndexController@sdi');
Route::get('td{actor}', 'fatturapa\ui\IndexController@td');


