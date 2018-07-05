<?php

require_once ("../config.php");

$soap = new soap_server();
$soap->debug_flag = false;

$soap->configureWSDL('TrasmissioneFatture_v1');

// Imposta Target Namespace
$soap->wsdl->schemaTargetNamespace = SOAP_EP_TRASMISSIONE;

// Registra Funzione `TrasmissioneHandler.ricevutaConsegna`
$soap->register("TrasmissioneHandler.ricevutaConsegna",
                array('name'=>'xsd:string'),
                array('return'=>'xsd:string')
            );

// Registra Funzione `TrasmissioneHandler.notificaMancataConsegna`
$soap->register("TrasmissioneHandler.notificaMancataConsegna",
                array('name'=>'xsd:string'),
                array('return'=>'xsd:string')
            );

// Registra Funzione `TrasmissioneHandler.notificaScarto`
$soap->register("TrasmissioneHandler.notificaScarto",
                array('name'=>'xsd:string'),
                array('return'=>'xsd:string')
            );

// Registra Funzione `TrasmissioneHandler.notificaEsito`
$soap->register("TrasmissioneHandler.notificaEsito",
                array('name'=>'xsd:string'),
                array('return'=>'xsd:string')
            );

// Registra Funzione `TrasmissioneHandler.notificaDecorrenzaTermini`
$soap->register("TrasmissioneHandler.notificaDecorrenzaTermini",
                array('name'=>'xsd:string'),
                array('return'=>'xsd:string')
            );

// Registra Funzione `TrasmissioneHandler.attestazioneTrasmissioneFattura`
$soap->register("TrasmissioneHandler.attestazioneTrasmissioneFattura",
                array('name'=>'xsd:string'),
                array('return'=>'xsd:string')
            );

$soap->service(isset($HTTP_RAW_POST_DATA) ?
               $HTTP_RAW_POST_DATA : '');
exit();

