<?php

require 'SdIRiceviFile/autoload.php';

$service = new \SdIRiceviFile_service(array('trace' => 1));
$service->__setLocation('http://localhost:8000/SdIRiceviFile/');
// var_dump($service);

$NomeFile = 'cuccia.xml';
$File = 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb';
$fileSdIBase = new \fileSdIBase_Type($NomeFile, $File);
// var_dump($fileSdIBase);

$response = $service->RiceviFile($fileSdIBase);

echo 'identificativo SDI = ' . $response->getIdentificativoSdI();
echo 'data ora ricezione = ' . $response->getDataOraRicezione();
echo 'errore = ' . $response->getErrore();
