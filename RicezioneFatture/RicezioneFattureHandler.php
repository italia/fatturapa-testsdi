<?php

require_once("autoload.php");

class RicezioneFattureHandler{

    public function RiceviFatture(fileSdIConMetadati_Type $parametersIn){
        $rispostaRiceviFatture_Type = new rispostaRiceviFatture_Type(\esitoRicezione_Type::ER01);

        return $rispostaRiceviFatture_Type;
    }

    public function NotificaDecorrenzaTermini(fileSdI_Type $parametersIn){
        
    }
    

}