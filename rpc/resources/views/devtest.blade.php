<?php
ini_set("soap.wsdl_cache_enabled","0");
ini_set('max_execution_time',300);

//First Step
@require_once('../vendor/autoload.php');
@require_once('../public/dev/SdIRiceviFile/SdIRiceviFileHandler.php');

$srv = new SoapServer('../public/dev/SdIRiceviFile/SdIRiceviFile_v1.0.wsdl');
$srv->setClass("SdIRiceviFileHandler");
$srv->handle();

$generator = new \Wsdl2PhpGenerator\Generator();
$generator->generate(
    new \Wsdl2PhpGenerator\Config(array(
        'inputFile' => '../public/dev/SdIRiceviFile/SdIRiceviFile_v1.0.wsdl',
        'outputDir' => '../public/dev/SdIRiceviFile/'
    ))
);

//Second Step
@require_once('../public/dev/SdIRiceviFile/autoload.php');
$service = new \SdIRiceviFile_service(array('trace' => 1,'cache_wsdl'=>WSDL_CACHE_NONE,'keep_alive' => false,'soap_version'=>SOAP_1_2));
$service->__setLocation('http://192.168.1.108:8000/dev/SdIRiceviFile/');

$NomeFile = 'cuccia444444.xml';
$File = 'RRRRRRRRRRR';
$fileSdIBase = new \fileSdIBase_Type($NomeFile, $File);

$response = $service->RiceviFile($fileSdIBase);
echo '<pre>'; print_r($response); exit;

/*echo 'identificativo SDI = ' . $response->getIdentificativoSdI(); 
echo 'data ora ricezione = ' . $response->getDataOraRicezione();
echo 'errore = ' . $response->getErrore();*/