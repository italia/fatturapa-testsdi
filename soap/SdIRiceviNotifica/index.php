<?php

require_once("../config.php");
require_once("SdIRiceviNotificaHandler.php");
require_once("../SoapServerDebug.php");

$srv = new SoapServerDebug('SdIRiceviNotifica_v1.0.wsdl');
$srv->setClass("SdIRiceviNotificaHandler");
$srv->handle();
error_log('==== '. print_r($srv->getAllDebugValues(), true));
