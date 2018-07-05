<?php

ini_set( "soap.wsdl_cache_enabled", 0 );
ini_set( 'soap.wsdl_cache_ttl', 0 );

// Imposta timezone
date_default_timezone_set('Europe/Rome');

// carica classi da composer
require_once(__DIR__."/vendor/autoload.php");
require_once(__DIR__."/server/ricezione_fatture_handler.php");

// da decommentare in branch develop
//define("ENDPOINT","https://teamdigitale1.simevo.com/testenv/");
define("ENDPOINT","http://localhost/sdi-testenv/");

//server trasmittente
define("SOAP_SERVER_LOCATION", ENDPOINT."/server/");

//end point trasmissione fatture trasmittente
define("SOAP_EP_TRASMISSIONE", SOAP_SERVER_LOCATION."/trasmissione-fatture.php?wsdl");

//end point ricezione fatture
define("SOAP_EP_RICEZIONE", SOAP_SERVER_LOCATION."/ricezione-fatture.php?wsdl");