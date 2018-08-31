<?php

require_once("autoload.php");

class TrasmissioneFattureHandler
{
    
    public function RicevutaConsegna($parametersIn)
    {
        $IdentificativoSdI = $parametersIn->IdentificativoSdI;
        $stato = 'TODO';
        $json[$IdentificativoSdI]["stato"] = $stato;
    }

    public function NotificaMancataConsegna($parametersIn)
    {
        $IdentificativoSdI = $parametersIn->IdentificativoSdI;
        $stato = 'TODO';
        $json[$IdentificativoSdI]["stato"] = $stato;
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
