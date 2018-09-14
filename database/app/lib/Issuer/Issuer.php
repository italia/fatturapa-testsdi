<?php


namespace Lib;

use Models\Database;
use Models\Invoice;
use Lib\Base;

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
                'uuid' => '1c873278-dec8-'.rand(999, 9999).'-8c69-7b647adca8ce',
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
        $wsdl=ROOTMAIN.Base::getActor(). '/soap/SdIRiceviFile/SdIRiceviFile_v1.0.wsdl';
        $Invoice = Invoice::all()->where('status', 'I_UPLOADED')->where('actor', Base::getActor());
        $Invoices = $Invoice->toArray();
            
        foreach ($Invoices as $Invoice) {
            $service = new \SdIRiceviFile_service(array('trace' => 1), $wsdl);
            $service->__setLocation(HOSTMAIN.Base::getActor().'/soap/SdIRiceviFile/');
                            
            $NomeFile = $Invoice['nomefile'];
            $File = $Invoice['blob'];
                        
            $fileSdIBase = new \fileSdIBase_Type($NomeFile, $File);
            $metadati = "metadati";
            $base64_meta = base64_encode($metadati);
                                    
            try {
                $response = $service->RiceviFile($fileSdIBase);
                if ($response->getErrore()) {
                    Invoice::find($Invoice['uuid'])->update(['status' => 'I_INVALID' ]);
                } else {
                    Invoice::find($Invoice['uuid'])->update(['status' => 'I_TRANSMITTED' ]);
                }
            } catch (SoapFault $e) {
                Invoice::find($Invoice['uuid'])->update(['status' => 'I_INVALID' ]);
                //print($service->__getLastResponse());
            }
        }
        return true;
    }
    public static function invalid($invoices)
    {
    }
    public static function failed($invoices)
    {
    }
    public static function delivered($invoices)
    {
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
