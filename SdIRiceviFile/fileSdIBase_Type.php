<?php

class fileSdIBase_Type
{

    /**
     * @var nomeFile_Type $NomeFile
     */
    public $NomeFile = null;

    /**
     * @var base64Binary $File
     */
    public $File = null;

    /**
     * @param nomeFile_Type $NomeFile
     * @param base64Binary $File
     */
    public function __construct($NomeFile, $File)
    {
      $this->NomeFile = $NomeFile;
      $this->File = $File;
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
     * @return fileSdIBase_Type
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
     * @return fileSdIBase_Type
     */
    public function setFile($File)
    {
      $this->File = $File;
      return $this;
    }

}
