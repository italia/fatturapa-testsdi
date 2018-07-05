<?php

ini_set( "soap.wsdl_cache_enabled", 0 );
ini_set( 'soap.wsdl_cache_ttl', 0 );

// Imposta timezone
date_default_timezone_set('Europe/Rome');

// carica classi da composer
require_once(__DIR__."/vendor/autoload.php");
//require_once(__DIR__."/soap/soap_handler.php");

// da decommentare in branch develop
//define("ENDPOINT","https://teamdigitale1.simevo.com/testenv/");
define("ENDPOINT","http://localhost/sdi-testenv/");

//server trasmittente
$st     =  ENDPOINT. "/server/";

//wsdl trasmissione fatture
$tfwsdl = "TrasmissioneFatture_v1.1.wsdl";

//wsdl ricezione fatture
$rfwsdl = "RicezioneFatture_v1.0.wsdl";

//end point trasmissione fatture trasmittente
$eptft  = $st . "trasmissione-fatture.php?wsdl";

//end point ricezione fatture
$eptrf  = $st . "ricezione-fatture.php?wsdl";