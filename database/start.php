<?php

require 'config.php';
require 'vendor/autoload.php';

use Lib\Exchange;
$invoice=Exchange::receive('sdf','asdf');

echo "<pre>";
print_r($invoice);
exit;

/*$Invoice = Invoice::create(['uuid'=>'1','nomefile'=>'1','posizione'=>'1']);
echo "<pre>";
print_r($Invoice);
exit;*/
/*

require 'config.php';
require '../../laravel_soap/vendor/autoload.php';
//require '../../laravel_soap/config/app.php';

use Fatturapa\Libsdi\lib\SdlExchange\Exchange;

Exchange::receive('asdsa','asdsa');
echo "---";
exit;
 * 
 */