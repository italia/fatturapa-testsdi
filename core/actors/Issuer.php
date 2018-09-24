<?php


namespace FatturaPa\Core\Actors;

use FatturaPa\Core\Models\Database;
use FatturaPa\Core\Models\Invoice;
use FatturaPa\Core\Actors\Base;
use Log;

class Issuer
{
    public static function Issuer()
    {
    }
    public static function upload($NomeFile, $XML)
    {
        $dateTime = Base::getDateTime();
        $Invoice = Invoice::create(
            [
                'nomefile' => $NomeFile,
                'posizione' => '',
                'cedente' => '',
                'anno' => '',
                'status' => 'I_UPLOADED',
                'blob' => $XML,
                'ctime' => $dateTime->date,
                'actor' => Base::getActor()
            ]
        );
        return $Invoice;
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
    // sets all invoices with ids in $invoices array to $status
    private static function setStatus($invoices, $status)
    {
        foreach ($invoices as $invoice) {
            error_log("invoice $invoice delivered");
            // Invoice::find($invoice)->update(['status' => $status ]);
        }
    }
    public static function invalid($invoices)
    {
    }
    public static function failed($invoices)
    {
    }
    public static function delivered($invoices)
    {
        self::setStatus($invoices, 'I_DELIVERED');
    }
    public static function accepted($invoices)
    {
    }
    public static function refused($invoices)
    {
    }
    public static function expired($invoices)
    {
    }
}
