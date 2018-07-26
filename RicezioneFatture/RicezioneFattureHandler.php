<?php

require_once("autoload.php");

class RicezioneFattureHandler{

    public function RiceviFatture(fileSdIConMetadati_Type $parametersIn){
        return new \esitoRicezione_Type\ER01;
    }

    public function NotificaDecorrenzaTermini(fileSdI_Type $parametersIn){
        
    }
    

}