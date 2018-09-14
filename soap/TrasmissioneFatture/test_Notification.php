<?php

require '../config.php';
require ROOT.'SdIRiceviFile/autoload.php';
require ROOT.'SdIRiceviNotifica/autoload.php';
require ROOT.'RicezioneFatture/autoload.php';
require ROOT.'TrasmissioneFatture/autoload.php';


$service = new \SdIRiceviFile_service(array('trace' => 1));
$service->__setLocation(HOSTNAME.'SdIRiceviFile/');

$NomeFile = $_REQUEST['NomeFile'];
$File = '';
if ($_FILES['File']['tmp_name']) {
    $File = base64_encode(file_get_contents($_FILES['File']['tmp_name']));
}
//$File =  file_get_contents('SdIRiceviFile/TrasmissioneTypes_v1.0.xsd');
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
	
$service = new \TrasmissioneFatture_service(array('trace' => 1));
$service->__setLocation(HOSTNAME.'TrasmissioneFatture/');
$fileSdI_Type = new \fileSdI_Type($response->getIdentificativoSdI(), $NomeFile, $File);
$response2 = $service->RicevutaConsegna($fileSdI_Type);
echo '<pre>'; print_r($response2);exit;

