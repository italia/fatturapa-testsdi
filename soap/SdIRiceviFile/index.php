<?php

require_once("SdIRiceviFileHandler.php");

error_log('==== SdIRiceviFile');
$srv = new \SoapServer(dirname(__FILE__) . '/SdIRiceviFile_v1.0.wsdl');
$srv->setClass("SdIRiceviFileHandler");
$srv->handle();
