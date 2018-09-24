<?php

require '../config.php';
require ROOT.'SdIRiceviFile/autoload.php';

$service = new \SdIRiceviFile_service(array('trace' => 1));
// $service = new \SoapClient(ROOT . 'SdIRiceviFile/SdIRiceviFile_v1.0.wsdl', array('trace' => 1));

echo '<pre>';
$location = HOSTNAME . 'SdIRiceviFile/';
echo('location = '. $location . PHP_EOL);
$service->__setLocation($location);
$file = base64_encode('<xml></xml>');
$fileSdIBase = new \fileSdIBase_Type('NomeFile', $file);
$response = $service->RiceviFile($fileSdIBase);
var_dump($response);
exit;

