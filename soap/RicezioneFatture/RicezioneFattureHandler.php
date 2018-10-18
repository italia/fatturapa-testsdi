<?php

require_once("autoload.php");
require dirname(__FILE__) . '/../../core/config.php';
require dirname(__FILE__) . '/../../core/vendor/autoload.php';
require dirname(__FILE__) . '/rispostaRiceviFatture_Type.php';
require dirname(__FILE__) . '/esitoRicezione_Type.php';

use FatturaPa\Core\Actors\Recipient;

class RicezioneFattureHandler
{
    public function RiceviFatture($parametersIn)
    {
        // TODO get the remote_id from metadata ?
        $Invoice = Recipient::receive(
            $parametersIn->File,
            $parametersIn->NomeFile,
            1,
            $parametersIn->IdentificativoSdI
        );
        $rispostaRiceviFatture = new rispostaRiceviFatture_Type(\esitoRicezione_Type::ER01);
        return $rispostaRiceviFatture;
    }

    public function NotificaDecorrenzaTermini($parametersIn)
    {
        // TODO
        Recipient::expire($parametersIn->IdentificativoSdI);
    }
}
