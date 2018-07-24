<?php

require_once("../config.php");
require_once("RicezioneFattureHandler.php");

$srv = new SoapServer('RicezioneFatture_v1.0.wsdl');
$srv->setClass("RicezioneFattureHandler");
$srv->handle();