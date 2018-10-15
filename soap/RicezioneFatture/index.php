<?php

require_once("RicezioneFattureHandler.php");

$srv = new SoapServerDebug(dirname(__FILE__) . '/RicezioneFatture_v1.0.wsdl');
$srv->setClass("RicezioneFattureHandler");
$srv->handle();
error_log('==== '. print_r($srv->getAllDebugValues(), true));
