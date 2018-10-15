<?php

require_once("autoload.php");
require dirname(__FILE__) . '/../../core/config.php';
require dirname(__FILE__) . '/../../core/vendor/autoload.php';

use FatturaPa\Core\Models\Invoice;
use FatturaPa\Core\Actors\Base;
use FatturaPa\Core\Actors\Exchange;

class SdIRiceviNotificaHandler
{

    public function NotificaEsito($parametersIn)
    {
        $xmlString = base64_decode($parametersIn->File);
        $xml = Base::unpack($xmlString);
        $invoice_id = $xml->IdentificativoSdI;
        $esito = $xml->Esito;
        if ($esito == 'EC01') {
            Exchange::accept_refuse($invoice_id, 'E_ACCEPTED', $esito);
        } else if ($esito == 'EC02') {
            Exchange::accept_refuse($invoice_id, 'E_REFUSED', $esito);
        } else {
            throw new \RuntimeException("Invalid Esito $esito");
        }
        Base::receive(
            $notification_blob = $parametersIn->File,
            $filename = $parametersIn->NomeFile,
            $type = 'NotificaEsito',
            $invoice_id
        );
    }
}
