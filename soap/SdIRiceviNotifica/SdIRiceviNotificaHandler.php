<?php

require_once("autoload.php");
require '../../core/config.php';
require '../../core/vendor/autoload.php';

class SdIRiceviNotificaHandler
{

    public function NotificaEsito($parametersIn)
    {
        $invoice_id = $parametersIn->IdentificativoSdI;
        Base::receive(
            $invoice_id = $invoice_id,
            $type = 'EC',
            $notification_blob = $parametersIn->File,
            $NomeFile = $parametersIn->NomeFile
        );
        // TODO: change status of invoices as required: R_ACCEPTED or R_REFUSED
    }
}
