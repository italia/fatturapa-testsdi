<?php

require_once("autoload.php");

class RicezioneFattureHandler
{

    public function RiceviFatture($parametersIn)
    {
        $rispostaRiceviFatture_Type = new rispostaRiceviFatture_Type(\esitoRicezione_Type::ER01);

        return $rispostaRiceviFatture_Type;
    }

    public function NotificaDecorrenzaTermini($parametersIn)
    {
    }
}
