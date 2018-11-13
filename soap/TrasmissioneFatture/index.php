<?php

require_once("TrasmissioneFattureHandler.php");

error_log('==== TrasmissioneFatture');
$srv = new \SoapServer(dirname(__FILE__) . '/TrasmissioneFatture_v1.1.wsdl');
$srv->setClass("TrasmissioneFattureHandler");
$srv->handle();
