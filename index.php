<?php

//date_default_timezone_set('Europe/Rome');

require_once(__DIR__."/vendor/autoload.php");

$generator = new \Wsdl2PhpGenerator\Generator();
$generator->generate(
    new \Wsdl2PhpGenerator\Config(array(
        'inputFile' => './SdIRiceviFile/SdIRiceviFile_v1.0.wsdl',
        'outputDir' => './SdIRiceviFile'
    ))
);

/*
$generator->generate(
    new \Wsdl2PhpGenerator\Config(array(
        'inputFile' => './SdIRiceviNotifica/ SdIRiceviNotifica_v1.0.wsdl',
        'outputDir' => './SdIRiceviNotifica'
    ))
);
*/