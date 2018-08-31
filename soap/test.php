<?php

require 'config.php';
require ROOT.'SdIRiceviFile/autoload.php';
require ROOT.'SdIRiceviNotifica/autoload.php';
require ROOT.'RicezioneFatture/autoload.php';
require ROOT.'TrasmissioneFatture/autoload.php';

$service = new \SdIRiceviFile_service(array('trace' => 1));
$service->__setLocation(HOSTNAME.'SdIRiceviFile/');

$NomeFile = 'IT01234567890_FPA01.xml';
$contents =  file_get_contents("samples/$NomeFile");
$File = base64_encode($contents);

$fileSdIBase = new \fileSdIBase_Type($NomeFile, $File);
$metadati = "metadati";
$base64_meta = base64_encode($metadati);

//var_dump($fileSdIBase);
//$response = $service->RiceviFile($fileSdIBase);

try {
    $response = $service->RiceviFile($fileSdIBase);
} catch (SoapFault $e) {
    print($service->__getLastResponse());
}
echo '<pre>';
print_r($response);
exit;

$ric_fatture_service = new \RicezioneFatture_service(array('trace' => 1));
$ric_fatture_service->__setLocation(HOSTNAME.'RicezioneFatture/');

$fileSdIConMetadati_Type = new fileSdIConMetadati_Type(
    $response->getIdentificativoSdI(),
    $NomeFile,
    $File,
    $metadati,
    $base64_meta
);
$response2 = $ric_fatture_service->RiceviFatture($fileSdIConMetadati_Type);

$fileSdI_Type = new \fileSdI_Type($response->getIdentificativoSdI(), $NomeFile, $File);

$ric_fatture_service->NotificaDecorrenzaTermini($fileSdI_Type);

//echo '<pre>'; print_r($response); exit;
