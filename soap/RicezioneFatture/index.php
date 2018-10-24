<?php

require_once("RicezioneFattureHandler.php");

error_log('==== RicezioneFatture');
$srv = new \SoapServer(dirname(__FILE__) . '/RicezioneFatture_v1.0.wsdl');
$srv->setClass("RicezioneFattureHandler");
$srv->handle();
