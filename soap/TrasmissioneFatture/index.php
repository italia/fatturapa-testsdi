<?php

require_once("../config.php");
require_once("TrasmissioneFattureHandler.php");
require_once("../SoapServerDebug.php");

$srv = new SoapServerDebug('TrasmissioneFatture_v1.1.wsdl');
$srv->setClass("TrasmissioneFattureHandler");
$srv->handle();
error_log('==== '. print_r($srv->getAllDebugValues(), true));
