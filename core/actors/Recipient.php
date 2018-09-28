<?php

namespace FatturaPa\Core\Actors;

use FatturaPa\Core\Models\Database;
use FatturaPa\Core\Models\Invoice;
use FatturaPa\Core\Models\Notification;

class Recipient
{
    public static function Recipient()
    {
    }
    public static function receive($invoice_blob, $filename, $position, $remote_id)
    {
        new Database();
        $dateTime = Base::getDateTime();
        $invoice = Invoice::create(
            [
                'nomefile' => $filename,
                'posizione' => $position,
                'cedente' => '',
                'anno' => '',
                'status' => 'R_RECEIVED',
                'blob' => $invoice_blob,
                'ctime' => $dateTime->date,
                'actor' => Base::getActor(),
                'issuer' => '',
                'remote_id' => $remote_id
            ]
        );
        return $invoice;
    }
    public static function accept($id)
    {    	
    	Invoice::where('id', '=', $id)->update(array('status' => 'R_ACCEPTED'));
		/*Base::enqueue(
            $notification_blob = base64_encode($notification),
            $filename = 'IT01234567890_11111_MC_001.xml',
            $type = 'NotificaMancataConsegna',
            $invoice_id = $Invoice['id']
        );*/
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

        $service = new \SdIRiceviNotifica_service(array('trace' => 1));

		foreach($notifications as $notification)
		{
			$fileSdI = new \fileSdI_Type($notification['id'], $notification['nomefile'], $notification['blob']);
            $sent = Base::dispatchNotification($service, "sdi", "SdIRiceviNotifica", "NotificaEsito", $fileSdI);
            if ($sent) {
                echo "sent !";
                Notification::find($notification['id'])->update(['status' => 'N_DELIVERED' ]);
            }			
		}	
		
		//ACCEPT DRAFT
		/*$notifications = Notification::all()
            ->where('status', 'R_ACCEPTED')
            ->where('actor', Base::getActor());
		$notifications = $notifications->toArray();

        $service = new \SdIRiceviNotifica_service(array('trace' => 1));

		foreach($notifications as $notification)
		{
			$fileSdI = new \fileSdI_Type($notification['id'], $notification['nomefile'], $notification['blob']);
            $sent = Base::dispatchNotification($service, "sdi", "SdIRiceviNotifica", "NotificaEsito", $fileSdI);
            if ($sent) {
                echo "sent !";
                Notification::find($notification['id'])->update(['status' => 'E_ACCEPTED' ]);
            }			
		}*/
		
				
    	 return true;
    }
}
