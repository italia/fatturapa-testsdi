<?php

class RicezioneHandler{

    public function __construct(){}

    public function riceviFatture($msg){
        return "return: $msg";
    }

    public function notificaDecorrenzaTermini($msg){
        return "return: $msg";
    }

}