<?php

Route::get('invoices', 'fatturapa\libsdi\InvoicesController@index');
Route::get('checkValidity', 'fatturapa\libsdi\InvoicesController@checkValidity');
Route::get('clear', 'fatturapa\libsdi\BaseController@clear');
Route::post('timestamp', 'fatturapa\libsdi\BaseController@setdatetime');
Route::post('speed', 'fatturapa\libsdi\BaseController@speed');
Route::get('datetime', 'fatturapa\libsdi\BaseController@getdatetime');