<?php

require_once("autoload.php");

class TrasmissioneFattureHandler
{
    
    public function RicevutaConsegna($parametersIn)
    {
        $json = json_decode(file_get_contents(ROOT . DB_FILE), true);
        $IdentificativoSdI = $parametersIn->IdentificativoSdI;
        $stato = FATTURA_ACCETTATA;
        $json[$IdentificativoSdI]["stato"] = $stato;
        file_put_contents(ROOT . DB_FILE, json_encode($json));
    }

    public function NotificaMancataConsegna($parametersIn)
    {
        $json = json_decode(file_get_contents(ROOT . DB_FILE), true);
        $IdentificativoSdI = $parametersIn->IdentificativoSdI;
        $stato = FATTURA_MANCATA_CONSEGNA;
        $json[$IdentificativoSdI]["stato"] = $stato;
        file_put_contents(ROOT . DB_FILE, json_encode($json));
    }

    public function NotificaScarto()
    {
    }

    public function NotificaEsito()
    {
    }

    public function NotificaDecorrenzaTermini()
    {
    }

    public function AttestazioneTrasmissioneFattura()
    {
    }
}
