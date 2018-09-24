<?php

require_once("autoload.php");

class RicezioneFattureHandler
{

    public function RiceviFatture($parametersIn)
    {
        $rispostaRiceviFatture = new rispostaRiceviFatture_Type(\esitoRicezione_Type::ER01);

        return $rispostaRiceviFatture;
    }

    public function NotificaDecorrenzaTermini($parametersIn)
    {
    }
}
