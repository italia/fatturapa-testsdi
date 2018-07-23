<?php

class rispostaRiceviFatture_Type
{

    /**
     * @var esitoRicezione_Type $Esito
     */
    protected $Esito = null;

    /**
     * @param esitoRicezione_Type $Esito
     */
    public function __construct($Esito)
    {
      $this->Esito = $Esito;
    }

    /**
     * @return esitoRicezione_Type
     */
    public function getEsito()
    {
      return $this->Esito;
    }

    /**
     * @param esitoRicezione_Type $Esito
     * @return rispostaRiceviFatture_Type
     */
    public function setEsito($Esito)
    {
      $this->Esito = $Esito;
      return $this;
    }

}
