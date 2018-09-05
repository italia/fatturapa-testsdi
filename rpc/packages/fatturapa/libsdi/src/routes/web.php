<?php

Route::get('invoices', 'fatturapa\libsdi\InvoicesController@index');
Route::get('checkValidity', 'fatturapa\libsdi\InvoicesController@checkValidity');
Route::get('clear', 'fatturapa\libsdi\BaseController@clear');
Route::get('timestamp', 'fatturapa\libsdi\BaseController@settimestamp');
Route::get('speed', 'fatturapa\libsdi\BaseController@speed');
Route::get('gettimestamp', 'fatturapa\libsdi\BaseController@gettimestamp');