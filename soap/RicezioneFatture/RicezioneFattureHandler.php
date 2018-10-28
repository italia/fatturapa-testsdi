<?php

require_once("autoload.php");
require dirname(__FILE__) . '/../../core/config.php';
require dirname(__FILE__) . '/../../core/vendor/autoload.php';
require dirname(__FILE__) . '/rispostaRiceviFatture_Type.php';
require dirname(__FILE__) . '/esitoRicezione_Type.php';

use FatturaPa\Core\Actors\Recipient;
use FatturaPa\Core\Actors\Issuer;
use FatturaPa\Core\Actors\Base;

class RicezioneFattureHandler
{
    public function RiceviFatture($parametersIn)
    {
        error_log('RicezioneFattureHandler::RiceviFile start -------------------------------------');
        error_log('parametersIn: '.json_encode($parametersIn));
        error_log('-------------------------------------------------------------------------------');
        $xmlString = base64_decode($parametersIn->Metadati);
        $xml = Base::unpack($xmlString);
        error_log("metadati = $xml");
        $invoice_remote_id = $xml->IdentificativoSdI;
        $invoice_filename = $xml->NomeFile;
        // $recipient = $xml->CodiceDestinatario;
        // TODO check that we are the right recipient
        // TODO check that we are in charge of receiving invoices for the Destinatario in the invoice
        $Invoice = Recipient::receive(
            $parametersIn->File, // $invoice_blob
            $invoice_filename,   // $filename
            1,                   // $position is always 1 until we implement multi-invoices (#22)
            $invoice_remote_id   // $remote_id
        );
        $rispostaRiceviFatture = new rispostaRiceviFatture_Type(\esitoRicezione_Type::ER01);
        error_log('RicezioneFattureHandler::RiceviFile end ---------------------------------------');
        return $rispostaRiceviFatture;
    }

    public function NotificaDecorrenzaTermini($parametersIn)
    {
        error_log('RicezioneFattureHandler::NotificaDecorrenzaTermini start-----------------------');
        error_log('parametersIn: '.json_encode($parametersIn));
        error_log('-------------------------------------------------------------------------------');
        Issuer::receive(
            $notification_blob = $parametersIn->File,
            $filename = $parametersIn->NomeFile,
            $type = 'NotificaDecorrenzaTermini',
            $status = 'R_EXPIRED'
        );
        error_log("==== RicezioneFattureHandler::NotificaDecorrenzaTermini end -------------------");
    }
}
