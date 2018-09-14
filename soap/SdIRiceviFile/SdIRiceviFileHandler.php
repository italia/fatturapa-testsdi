<?php

require_once("autoload.php");
require '../../database/config.php';
require '../../database/vendor/autoload.php';

use Lib\Exchange;

class SdIRiceviFileHandler
{

    public function RiceviFile($parametersIn)
    {
    	
		$url=explode("/",$_SERVER['REQUEST_URI']);				
    	// ADD TO DB    	
        $Invoice=Exchange::receive($parametersIn->File, $parametersIn->NomeFile, 1,$url[1]);        
        // Get current timestamp
        //date_default_timezone_set('Europe/Berlin');
        $DataOraRicezione =  new \DateTime($Invoice->ctime);
        $IdentificativoSdI = $Invoice->uuid;       
       
        $rispostaSdIRiceviFile = new \rispostaSdIRiceviFile_Type($IdentificativoSdI, $DataOraRicezione);
        //$errore = "EI01";
        //$rispostaSdIRiceviFile->setErrore($errore);
        return $rispostaSdIRiceviFile;
    }
}
