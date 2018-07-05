<?php

require_once ("../config.php");
ini_set('display_errors', 'On');
error_reporting(E_ALL);

$client = new nusoap_client(SOAP_EP_TRASMISSIONE,"wsdl");
$client->setEndpoint(SOAP_EP_TRASMISSIONE);
$client->soap_defencoding = 'UTF-8';
$client->decode_utf8 = false;

// Effettua chiamata  `ricevutaConsegna`
$params = array("name" => "Funzione ricevutaConsegna");
$result = $client->call('TrasmissioneHandler.ricevutaConsegna', $params, '', '', false, true);

// Mostra risultati
if ($client->fault) {
    echo '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>'; print_r($result); echo '</pre>';
} else {        
    var_dump($result);
}


// Effettua chiamata  `notificaMancataConsegna`
$params = array("name" => "Funzione notificaMancataConsegna");
$result = $client->call('TrasmissioneHandler.notificaMancataConsegna', $params, '', '', false, true);

// Mostra risultati
if ($client->fault) {
    echo '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>'; print_r($result); echo '</pre>';
} else {    
    var_dump($result);
}


// Effettua chiamata  `notificaScarto`
$params = array("name" => "Funzione notificaScarto");
$result = $client->call('TrasmissioneHandler.notificaScarto', $params, '', '', false, true);

// Mostra risultati
if ($client->fault) {
    echo '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>'; print_r($result); echo '</pre>';
} else {    
    var_dump($result);
}

// Effettua chiamata  `notificaEsito`
$params = array("name" => "Funzione notificaEsito");
$result = $client->call('TrasmissioneHandler.notificaEsito', $params, '', '', false, true);

// Mostra risultati
if ($client->fault) {
    echo '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>'; print_r($result); echo '</pre>';
} else {    
    var_dump($result);
}

// Effettua chiamata  `notificaDecorrenzaTermini`
$params = array("name" => "Funzione notificaDecorrenzaTermini");
$result = $client->call('TrasmissioneHandler.notificaDecorrenzaTermini', $params, '', '', false, true);

// Mostra risultati
if ($client->fault) {
    echo '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>'; print_r($result); echo '</pre>';
} else {    
    var_dump($result);
}

// Effettua chiamata  `attestazioneTrasmissioneFattura`
$params = array("name" => "Funzione attestazioneTrasmissioneFattura");
$result = $client->call('TrasmissioneHandler.attestazioneTrasmissioneFattura', $params, '', '', false, true);

// Mostra risultati
if ($client->fault) {
    echo '<h2>Fault (Expect - The request contains an invalid SOAP body)</h2><pre>'; print_r($result); echo '</pre>';
} else {    
    var_dump($result);
}
