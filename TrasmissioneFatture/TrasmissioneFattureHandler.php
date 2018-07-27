<?php

require_once("autoload.php");

class TrasmissioneFattureHandler{
    
    public function RicevutaConsegna(fileSdI_Type $parametersIn){
        $json = json_decode(file_get_contents(ROOT . DB_FILE),TRUE);
        $IdentificativoSdI = $parametersIn.IdentificativoSdI;
        $stato = FATTURA_ACCETTATA;
        $json[$IdentificativoSdI]["stato"] = $stato;
        file_put_contents(ROOT . DB_FILE, json_encode($json));
    }

    public function NotificaMancataConsegna(ileSdI_Type $parametersIn){
        $json = json_decode(file_get_contents(ROOT . DB_FILE),TRUE);
        $IdentificativoSdI = $parametersIn.IdentificativoSdI;
        $stato = FATTURA_MANCATA_CONSEGNA;
        $json[$IdentificativoSdI]["stato"] = $stato;
        file_put_contents(ROOT . DB_FILE, json_encode($json));
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