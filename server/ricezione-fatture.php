<?php

require_once ("../config.php");

$soap = new soap_server();
$soap->debug_flag = false;

$soap->configureWSDL('RicezioneFatture_v1');

// Imposta Target Namespace
$soap->wsdl->schemaTargetNamespace = SOAP_EP_RICEZIONE;

// Registra Funzione `RicezioneHandler.riceviFatture`
$soap->register("RicezioneHandler.riceviFatture",
                array('name'=>'xsd:string'),
                array('return'=>'xsd:string')
            );

// Registra Funzione `RicezioneHandler.notificaDecorrenzaTermini`
$soap->register("RicezioneHandler.notificaDecorrenzaTermini",
                array('name'=>'xsd:string'),
                array('return'=>'xsd:string')
            );

$soap->service(isset($HTTP_RAW_POST_DATA) ?
               $HTTP_RAW_POST_DATA : '');
exit();