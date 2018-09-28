<?php


namespace FatturaPa\Core\Actors;

use FatturaPa\Core\Models\Database;
use FatturaPa\Core\Models\Invoice;
use FatturaPa\Core\Actors\Base;
use Log;

class Issuer
{
    public static function upload($filename, $invoice_blob)
    {
        $dateTime = Base::getDateTime();
        $invoice = Invoice::create(
            [
                'nomefile' => $filename,
                'posizione' => '',
                'cedente' => '',
                'anno' => '',
                'status' => 'I_UPLOADED',
                'blob' => $invoice_blob,
                'ctime' => $dateTime->date,
                'actor' => Base::getActor()
            ]
        );
        return $invoice;
    }
    public static function transmit()
    {
        $Invoice = Invoice::all()->where('status', 'I_UPLOADED')->where('actor', Base::getActor());
        $Invoices = $Invoice->toArray();
            
        foreach ($Invoices as $Invoice) {
            $service = new \SdIRiceviFile_service(array('trace' => 1));
            $service->__setLocation(HOSTMAIN.'sdi/soap/SdIRiceviFile/');
                            
            $NomeFile = $Invoice['nomefile'];
            $File = $Invoice['blob'];
                        
            $fileSdIBase = new \fileSdIBase_Type($NomeFile, $File);
            
            Log::error('START------------------:');
            Log::error('Params:'.json_encode($Invoice));
            Log::error('------------------END');
                                    
            try {
                $response = $service->RiceviFile($fileSdIBase);
                if ($response->getErrore()) {
                    Invoice::find($Invoice['id'])->update(['status' => 'I_INVALID' ]);
                } else {
                    $invoice_id = $response->getIdentificativoSdI();
                    Invoice::find($Invoice['id'])->update(['status' => 'I_TRANSMITTED' ]);
                    Invoice::find($Invoice['id'])->update(['remote_id' => (int) $invoice_id ]);
                }
            } catch (SoapFault $e) {
                Invoice::find($Invoice['id'])->update(['status' => 'I_INVALID' ]);
                //print($service->__getLastResponse());
            }
        }
        return true;
    }
	public static function receive($notification_blob, $filename, $type, $status)
    {
    	new Database();
        error_log("receiving notification $filename");
        $xmlString = base64_decode($notification_blob);
    	$xml = Base::unpack($xmlString);
        $invoice_remote_id = $xml->IdentificativoSdI;
        error_log("remote_id = $invoice_remote_id");
        $invoice = Invoice::where('remote_id', $invoice_remote_id)->first();
        $invoice_id = $invoice['id'];
        error_log("id = $invoice_id");
        Base::receive($notification_blob, $filename, $type, $invoice_id);
        Invoice::find($invoice_id)->update(['status' => $status ]);
    }
}
