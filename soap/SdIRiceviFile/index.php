<?php

require_once("SdIRiceviFileHandler.php");

$srv = new SoapServerDebug(dirname(__FILE__) . '/SdIRiceviFile_v1.0.wsdl');
$srv->setClass("SdIRiceviFileHandler");
$srv->handle();
error_log('==== '. print_r($srv->getAllDebugValues(), true));
