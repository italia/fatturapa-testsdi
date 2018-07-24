<?php

require_once("../config.php");
require_once("TrasmissioneFattureHandler.php");

$srv = new SoapServer('TrasmissioneFatture_v1.1.wsdl');
$srv->setClass("TrasmissioneFattureHandler");
$srv->handle();