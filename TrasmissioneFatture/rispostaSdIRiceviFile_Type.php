<?php

class rispostaSdIRiceviFile_Type
{

    /**
     * @var identificativoSdI_Type $IdentificativoSdI
     */
    protected $IdentificativoSdI = null;

    /**
     * @var \DateTime $DataOraRicezione
     */
    protected $DataOraRicezione = null;

    /**
     * @var erroreInvio_Type $Errore
     */
    protected $Errore = null;

    /**
     * @param identificativoSdI_Type $IdentificativoSdI
     * @param \DateTime $DataOraRicezione
     */
    public function __construct($IdentificativoSdI, \DateTime $DataOraRicezione)
    {
      $this->IdentificativoSdI = $IdentificativoSdI;
      $this->DataOraRicezione = $DataOraRicezione->format(\DateTime::ATOM);
    }

    /**
     * @return identificativoSdI_Type
     */
    public function getIdentificativoSdI()
    {
      return $this->IdentificativoSdI;
    }

    /**
     * @param identificativoSdI_Type $IdentificativoSdI
     * @return rispostaSdIRiceviFile_Type
     */
    public function setIdentificativoSdI($IdentificativoSdI)
    {
      $this->IdentificativoSdI = $IdentificativoSdI;
      return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDataOraRicezione()
    {
      if ($this->DataOraRicezione == null) {
        return null;
      } else {
        try {
          return new \DateTime($this->DataOraRicezione);
        } catch (\Exception $e) {
          return false;
        }
      }
    }

    /**
     * @param \DateTime $DataOraRicezione
     * @return rispostaSdIRiceviFile_Type
     */
    public function setDataOraRicezione(\DateTime $DataOraRicezione)
    {
      $this->DataOraRicezione = $DataOraRicezione->format(\DateTime::ATOM);
      return $this;
    }

    /**
     * @return erroreInvio_Type
     */
    public function getErrore()
    {
      return $this->Errore;
    }

    /**
     * @param erroreInvio_Type $Errore
     * @return rispostaSdIRiceviFile_Type
     */
    public function setErrore($Errore)
    {
      $this->Errore = $Errore;
      return $this;
    }

}
