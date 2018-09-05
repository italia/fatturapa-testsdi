<?php

class fileSdIConMetadati_Type
{

    /**
     * @var identificativoSdI_Type $IdentificativoSdI
     */
    protected $IdentificativoSdI = null;

    /**
     * @var nomeFile_Type $NomeFile
     */
    protected $NomeFile = null;

    /**
     * @var base64Binary $File
     */
    protected $File = null;

    /**
     * @var nomeFile_Type $NomeFileMetadati
     */
    protected $NomeFileMetadati = null;

    /**
     * @var base64Binary $Metadati
     */
    protected $Metadati = null;

    /**
     * @param identificativoSdI_Type $IdentificativoSdI
     * @param nomeFile_Type $NomeFile
     * @param base64Binary $File
     * @param nomeFile_Type $NomeFileMetadati
     * @param base64Binary $Metadati
     */
    public function __construct($IdentificativoSdI, $NomeFile, $File, $NomeFileMetadati, $Metadati)
    {
        $this->IdentificativoSdI = $IdentificativoSdI;
        $this->NomeFile = $NomeFile;
        $this->File = $File;
        $this->NomeFileMetadati = $NomeFileMetadati;
        $this->Metadati = $Metadati;
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
     * @return fileSdIConMetadati_Type
     */
    public function setIdentificativoSdI($IdentificativoSdI)
    {
        $this->IdentificativoSdI = $IdentificativoSdI;
        return $this;
    }

    /**
     * @return nomeFile_Type
     */
    public function getNomeFile()
    {
        return $this->NomeFile;
    }

    /**
     * @param nomeFile_Type $NomeFile
     * @return fileSdIConMetadati_Type
     */
    public function setNomeFile($NomeFile)
    {
        $this->NomeFile = $NomeFile;
        return $this;
    }

    /**
     * @return base64Binary
     */
    public function getFile()
    {
        return $this->File;
    }

    /**
     * @param base64Binary $File
     * @return fileSdIConMetadati_Type
     */
    public function setFile($File)
    {
        $this->File = $File;
        return $this;
    }

    /**
     * @return nomeFile_Type
     */
    public function getNomeFileMetadati()
    {
        return $this->NomeFileMetadati;
    }

    /**
     * @param nomeFile_Type $NomeFileMetadati
     * @return fileSdIConMetadati_Type
     */
    public function setNomeFileMetadati($NomeFileMetadati)
    {
        $this->NomeFileMetadati = $NomeFileMetadati;
        return $this;
    }

    /**
     * @return base64Binary
     */
    public function getMetadati()
    {
        return $this->Metadati;
    }

    /**
     * @param base64Binary $Metadati
     * @return fileSdIConMetadati_Type
     */
    public function setMetadati($Metadati)
    {
        $this->Metadati = $Metadati;
        return $this;
    }
}
