<?php

require_once("SdIRiceviNotificaHandler.php");

error_log('==== SdIRiceviNotifica');
$srv = new \SoapServer(dirname(__FILE__) . '/SdIRiceviNotifica_v1.0.wsdl');
$srv->setClass("SdIRiceviNotificaHandler");
$srv->handle();
