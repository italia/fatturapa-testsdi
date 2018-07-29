<?php

require_once("../config.php");
require_once("RicezioneFattureHandler.php");
require_once("../SoapServerDebug.php");

$srv = new SoapServerDebug('RicezioneFatture_v1.0.wsdl');
$srv->setClass("RicezioneFattureHandler");
$srv->handle();
error_log('==== '. print_r($srv->getAllDebugValues(), true));
