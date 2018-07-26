<?php

//date_default_timezone_set('Europe/Rome');

require_once(__DIR__."/vendor/autoload.php");

$generator = new \Wsdl2PhpGenerator\Generator();
$generator->generate(
    new \Wsdl2PhpGenerator\Config(array(
        'inputFile' => './TrasmissioneFatture/TrasmissioneFatture_v1.1.wsdl',
        'outputDir' => './TrasmissioneFatture'
    ))
);