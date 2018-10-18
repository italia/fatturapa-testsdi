<?php

require_once("autoload.php");
require dirname(__FILE__) . '/../../core/config.php';
require dirname(__FILE__) .'/../../core/vendor/autoload.php';
require dirname(__FILE__) . '/rispostaSdIRiceviFile_Type.php';

use FatturaPa\Core\Actors\Exchange;

class SdIRiceviFileHandler
{

    public function RiceviFile($parametersIn)
    {
        error_log('HSTART------------------:');
        error_log('parametersIn: '.json_encode($parametersIn));
        error_log('------------------END');
                    
        // ADD TO DB
        $Invoice = Exchange::receive($parametersIn->File, $parametersIn->NomeFile, 1);
        // Get current timestamp
        $DataOraRicezione =  new \DateTime($Invoice->ctime);
        $IdentificativoSdI = $Invoice->id;

        $rispostaSdIRiceviFile = new \rispostaSdIRiceviFile_Type($IdentificativoSdI, $DataOraRicezione);
        // $errore = "EI01";
        // $rispostaSdIRiceviFile->setErrore($errore);

        return $rispostaSdIRiceviFile;
    }
}
