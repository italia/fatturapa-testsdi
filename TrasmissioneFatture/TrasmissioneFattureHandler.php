<?php

require_once("autoload.php");

class TrasmissioneFattureHandler{
    
    public function RicevutaConsegna(fileSdI_Type $parametersIn){
        $json = json_decode(file_get_contents(ROOT . DB_FILE),TRUE);
        $IdentificativoSdI = $parametersIn.IdentificativoSdI;
        $stato = 4;
        $json[$IdentificativoSdI]["stato"] = $stato;
        file_put_contents(ROOT . DB_FILE, json_encode($json));
    }

    public function NotificaMancataConsegna(){
        
    }

    public function NotificaScarto(){
        
    }

    public function NotificaEsito(){
        
    }

    public function NotificaDecorrenzaTermini(){
        
    }

    public function AttestazioneTrasmissioneFattura(){
        
    }

}