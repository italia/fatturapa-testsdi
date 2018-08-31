<?php

require_once("../config.php");
require_once("SdIRiceviFileHandler.php");
require_once("../SoapServerDebug.php");

$srv = new SoapServerDebug('SdIRiceviFile_v1.0.wsdl');
$srv->setClass("SdIRiceviFileHandler");
$srv->handle();
error_log('==== '. print_r($srv->getAllDebugValues(), true));
