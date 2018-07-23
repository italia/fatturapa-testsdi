<?php

require_once("../config.php");
require_once("SdIRiceviFileHandler.php");

$srv = new SoapServer('SdIRiceviFile_v1.0.wsdl');
$srv->setClass("SdIRiceviFileHandler");
$srv->handle();