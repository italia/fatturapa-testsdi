<?php

require_once("autoload.php");

class SdIRiceviFileHandler
{

    public function RiceviFile(\fileSdIBase_Type $parametersIn)
    {
        // Get current timestamp
        $DataOraRicezione = new \DateTime();        

        // Add Info to Json
        $json = json_decode(file_get_contents(ROOT . DB_FILE),TRUE);
        $IdentificativoSdI = count($json) + 1;
        $new_elem = array("identificativo_sdi" => $IdentificativoSdI,
                          "nome_file" => $parametersIn.NomeFile,
                          "data_ricezione" => $DataOraRicezione,
                          "stato" => 1);
        array_push($json, $new_elem);
        file_put_contents(ROOT . DB_FILE, json_encode($json));        

        // Return        
        $rispostaSdIRiceviFile = new \rispostaSdIRiceviFile_Type($IdentificativoSdI, $DataOraRicezione);
        $errore = "EI01";
        $rispostaSdIRiceviFile->setErrore($errore);
        return $rispostaSdIRiceviFile;
    }
}