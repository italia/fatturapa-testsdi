<?php

require_once("autoload.php");
require '../../database/config.php';
require '../../database/vendor/autoload.php';

use Lib\Exchange;

class SdIRiceviFileHandler
{

    public function RiceviFile($parametersIn)
    {
        // Get current timestamp
        date_default_timezone_set('Europe/Berlin');
        $DataOraRicezione = new \DateTime();
        $IdentificativoSdI = 'TODO';
        $new_elem = array("identificativo_sdi" => $IdentificativoSdI,
                          "nome_file" => $parametersIn->NomeFile,
                          "data_ricezione" => $DataOraRicezione,
                          "stato" => 1);
        // ADD TO DB
        Exchange::receive($parametersIn->File, $parametersIn->NomeFile);
        //
        $rispostaSdIRiceviFile = new \rispostaSdIRiceviFile_Type($IdentificativoSdI, $DataOraRicezione);
        $errore = "EI01";
        $rispostaSdIRiceviFile->setErrore($errore);
        return $rispostaSdIRiceviFile;
    }
}
