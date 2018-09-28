<?php

require_once("autoload.php");
require '../../core/config.php';
require '../../core/vendor/autoload.php';

use FatturaPa\Core\Actors\Recipient;

class RicezioneFattureHandler
{
    public function RiceviFatture($parametersIn)
    {
        // TODO get the remote_id from metatada ?
        $Invoice = Recipient::receive($parametersIn->File, $parametersIn->NomeFile, 1, $parametersIn->IdentificativoSdI);
        $rispostaRiceviFatture = new rispostaRiceviFatture_Type(\esitoRicezione_Type::ER01);
        return $rispostaRiceviFatture;
    }

    public function NotificaDecorrenzaTermini($parametersIn)
    {
        // TODO
    }
}
