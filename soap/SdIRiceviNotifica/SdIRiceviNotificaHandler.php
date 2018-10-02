<?php

require_once("autoload.php");
require '../../core/config.php';
require '../../core/vendor/autoload.php';

use FatturaPa\Core\Models\Invoice;
use FatturaPa\Core\Actors\Base;
use FatturaPa\Core\Actors\Exchange;

class SdIRiceviNotificaHandler
{

    public function NotificaEsito($parametersIn)
    {
    	
        $invoice_id = $parametersIn->IdentificativoSdI;        	
		Base::receive(
            $notification_blob = $parametersIn->File,
            $filename = $parametersIn->NomeFile,
            $type = 'NotificaEsito',
            $invoice_id
        );
        // TODO: change status of invoices as required: R_ACCEPTED or R_REFUSED
        Exchange::accept($invoice_id);
        //Invoice::where('id', '=', $invoice_id)->update(array('status' => 'E_ACCEPTED'));
    }
}
