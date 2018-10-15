<?php

require_once("SdIRiceviNotificaHandler.php");

$srv = new SoapServerDebug(dirname(__FILE__) . '/SdIRiceviNotifica_v1.0.wsdl');
$srv->setClass("SdIRiceviNotificaHandler");
$srv->handle();
error_log('==== '. print_r($srv->getAllDebugValues(), true));
