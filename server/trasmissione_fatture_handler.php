<?php

class TrasmissioneHandler{

    public function __construct(){}

    public function ricevutaConsegna($msg){
        return "return: $msg";
    }

    public function notificaMancataConsegna($msg){
        return "return: $msg";
    }

    public function notificaScarto($msg){
        return "return: $msg";
    }

    function notificaEsito($msg){
        return "return: $msg";
    }

    function notificaDecorrenzaTermini($msg){
        return "return: $msg";
    }

    function attestazioneTrasmissioneFattura($msg){
        return "return: $msg";
    }

}