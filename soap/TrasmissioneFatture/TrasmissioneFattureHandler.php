<?php

require_once("autoload.php");
require '../../database/config.php';
require '../../database/vendor/autoload.php';


class TrasmissioneFattureHandler
{
    
    public function RicevutaConsegna($parametersIn)
    {
    	 	
        $IdentificativoSdI = $parametersIn->IdentificativoSdI;
        $stato = 'TODO';
        $json[$IdentificativoSdI]["stato"] = $stato;
		Base::receive($invoice_uuid='sss', $type='sss', $notification_blob='sss');
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
