<?php

class rispostaSdINotificaEsito_Type
{

    /**
     * @var esitoNotifica_Type $Esito
     */
    protected $Esito = null;

    /**
     * @var fileSdIBase_Type $ScartoEsito
     */
    protected $ScartoEsito = null;

    /**
     * @param esitoNotifica_Type $Esito
     */
    public function __construct($Esito)
    {
        $this->Esito = $Esito;
    }

    /**
     * @return esitoNotifica_Type
     */
    public function getEsito()
    {
        return $this->Esito;
    }

    /**
     * @param esitoNotifica_Type $Esito
     * @return rispostaSdINotificaEsito_Type
     */
    public function setEsito($Esito)
    {
        $this->Esito = $Esito;
        return $this;
    }

    /**
     * @return fileSdIBase_Type
     */
    public function getScartoEsito()
    {
        return $this->ScartoEsito;
    }

    /**
     * @param fileSdIBase_Type $ScartoEsito
     * @return rispostaSdINotificaEsito_Type
     */
    public function setScartoEsito($ScartoEsito)
    {
        $this->ScartoEsito = $ScartoEsito;
        return $this;
    }
}
