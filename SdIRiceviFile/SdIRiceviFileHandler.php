<?php

require_once("autoload.php");

class SdIRiceviFileHandler
{

    public function RiceviFile(fileSdIBase_Type $parametersIn)
    {        
        $IdentificativoSdI = 1;
        $DataOraRicezione = new \DateTime();
        $rispostaSdIRiceviFile = new \rispostaSdIRiceviFile_Type($IdentificativoSdI, $DataOraRicezione);
        $errore = "EI01";
        $rispostaSdIRiceviFile->setErrore($errore);
        return $rispostaSdIRiceviFile;
    }
}