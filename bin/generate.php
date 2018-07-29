#!/usr/bin/php
<?php
// generate all code from the WSDL files
//
// Copyright (c) 2018, Paolo Greppi <paolo.greppi@simevo.com>
// License: BSD 3-Clause

// date_default_timezone_set('Europe/Rome');

require_once("./vendor/autoload.php");

$services = array(
    array(
        'inputFile' => './SdIRiceviFile/SdIRiceviFile_v1.0.wsdl',
        'outputDir' => './SdIRiceviFile'
    ),
    array(
        'inputFile' => './SdIRiceviNotifica/SdIRiceviNotifica_v1.0.wsdl',
        'outputDir' => './SdIRiceviNotifica'
    ),
    array(
        'inputFile' => './RicezioneFatture/RicezioneFatture_v1.0.wsdl',
        'outputDir' => './RicezioneFatture'
    ),
    array(
        'inputFile' => './TrasmissioneFatture/TrasmissioneFatture_v1.1.wsdl',
        'outputDir' => './TrasmissioneFatture'
    )
);

$generator = new \Wsdl2PhpGenerator\Generator();
foreach ($services as $service) {
    $generator->generate(new \Wsdl2PhpGenerator\Config($service));
}
