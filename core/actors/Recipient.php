<?php


namespace FatturaPa\Core\Actors;

use Models\Database;
use Models\Invoice;
use Models\Notification;

class Recipient
{
    public static function Recipient()
    {
    }
    public static function receive($XML, $metadata)
    {
    }
    public static function accept($invoices)
    {
    }
    public static function refuse($invoices)
    {
    }
    public static function expire($invoices)
    {
    }
	public static function dispatchi()
    {
        $notifications = Notification::all()
            ->where('status', 'N_PENDING')
            ->where('actor', Base::getActor());
		$notifications = $notifications->toArray();

		foreach($notifications as $notification)
		{			
			$service = new \SdIRiceviNotifica_service(array('trace' => 1));
			$service->__setLocation(HOSTMAIN.'/sdi/soap/SdIRiceviNotifica/');
			$fileSdI = new \fileSdI_Type($notification['id'], $notification['nomefile'], $notification['blob']);
													
			try {
                $response = $service->NotificaEsito($fileSdI);
                if ($response) {
                	Notification::find($notification['id'])->update(['status' => 'N_DELIVERED' ]);
                }
            } catch (SoapFault $e) {
                Invoice::find($Invoice['id'])->update(['status' => 'I_INVALID' ]);
            }
			
		}			
    	 return true;
    }
}
