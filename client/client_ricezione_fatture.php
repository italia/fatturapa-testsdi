<?php

require_once ("../config.php");
ini_set('display_errors', 'On');
error_reporting(E_ALL);

$client = new nusoap_client(SOAP_EP_RICEZIONE,"wsdl");
$client->setEndpoint(SOAP_EP_RICEZIONE);
$client->soap_defencoding = 'UTF-8';
$client->decode_utf8 = false;

$params = array("name" => "Funzione riceviFatture");

// Effettua chiamata
$result = $client->call('RicezioneHandler.riceviFatture', $params, '', '', false, true);

// Mostra risultati
if ($client->fault) {
    echo '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>'; print_r($result); echo '</pre>';
} else {    
    var_dump($result);
}

$params = array("name" => "Funzione notificaDecorrenzaTermini");

// Effettua chiamata
$result = $client->call('RicezioneHandler.notificaDecorrenzaTermini', $params, '', '', false, true);

// Mostra risultati
if ($client->fault) {
    echo '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>'; print_r($result); echo '</pre>';
} else {    
    var_dump($result);
}

