<?php

require 'config.php';

require 'SdIRiceviFile/autoload.php';
require 'SdIRiceviNotifica/autoload.php';
require 'RicezioneFatture/autoload.php';
require 'TrasmissioneFatture/autoload.php';


$json = json_decode(file_get_contents(ROOT . DB_FILE),TRUE);
//$json ["test"] = array("cod" => "1234");
//file_put_contents(ROOT . DB_FILE, json_encode($json));


$service = new \SdIRiceviFile_service(array('trace' => 1));
$service->__setLocation(HOSTNAME.'SdIRiceviFile/');

$NomeFile = 'cuccia.xml';
$File = base64_encode('bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb');
$fileSdIBase = new \fileSdIBase_Type($NomeFile, $File);
$metadati = "metadati";
$base64_meta = base64_encode($metadati);
// var_dump($fileSdIBase);

$response = $service->RiceviFile($fileSdIBase);
echo 'identificativo SDI = ' . $response->getIdentificativoSdI();
echo 'data ora ricezione = ' . $response->getDataOraRicezione()->format("Y-m-d H:i:s");
echo 'errore = ' . $response->getErrore();


$ric_fatture_service = new \RicezioneFatture_service(array('trace' => 1));
$ric_fatture_service->__setLocation(HOSTNAME.'RicezioneFatture/');

$fileSdIConMetadati_Type = new fileSdIConMetadati_Type($response->getIdentificativoSdI(),$NomeFile, $File,$metadati, $base64_meta);
$response2 = $ric_fatture_service->RiceviFatture($fileSdIConMetadati_Type);


$fileSdI_Type = new \fileSdI_Type($response->getIdentificativoSdI(),$NomeFile, $File);

$ric_fatture_service->NotificaDecorrenzaTermini($fileSdI_Type);
