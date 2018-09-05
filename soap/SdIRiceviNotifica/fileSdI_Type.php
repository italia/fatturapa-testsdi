<?php

class fileSdI_Type
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
     * @param identificativoSdI_Type $IdentificativoSdI
     * @param nomeFile_Type $NomeFile
     * @param base64Binary $File
     */
    public function __construct($IdentificativoSdI, $NomeFile, $File)
    {
        $this->IdentificativoSdI = $IdentificativoSdI;
        $this->NomeFile = $NomeFile;
        $this->File = $File;
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
     * @return fileSdI_Type
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
     * @return fileSdI_Type
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
     * @return fileSdI_Type
     */
    public function setFile($File)
    {
        $this->File = $File;
        return $this;
    }
}
