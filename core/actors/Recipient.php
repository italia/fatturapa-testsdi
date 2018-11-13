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
        $invoice = Invoice::find($id);
        $remote_id = $invoice->remote_id;
        $notification = <<<XML
<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="EC_v1.0.xsl"?>
<types:NotificaEsitoCommittente xmlns:types="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd ">
  <IdentificativoSdI>$remote_id</IdentificativoSdI>
  <RiferimentoFattura>
    <NumeroFattura>1111</NumeroFattura>
    <AnnoFattura>2013</AnnoFattura>
    <PosizioneFattura>2</PosizioneFattura>
  </RiferimentoFattura>
  <Esito>EC01</Esito>
  <Descrizione>Esempio</Descrizione>
  <MessageIdCommittente>123456</MessageIdCommittente>
</types:NotificaEsitoCommittente>
XML;
    
        // TODO: sign notification (on hold)
        $File = base64_encode($notification);
        $NomeFile = 'IT01234567890_11111_EC_001.xml';
        Base::enqueue(
            $notification_blob = $File,
            $filename = $NomeFile,
            $type = 'NotificaEsito',
            $invoice_id = $id
        );
    }
    public static function refuse($id)
    {
        Invoice::where('id', '=', $id)->update(array('status' => 'R_REFUSED'));
        $invoice = Invoice::find($id);
        $remote_id = $invoice->remote_id;
        $notification = <<<XML
<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="EC_v1.0.xsl"?>
<types:NotificaEsitoCommittente xmlns:types="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd ">
    <IdentificativoSdI>$remote_id</IdentificativoSdI>
    <RiferimentoFattura>
    <NumeroFattura>1111</NumeroFattura>
    <AnnoFattura>2013</AnnoFattura>
    <PosizioneFattura>2</PosizioneFattura>
    </RiferimentoFattura>
    <Esito>EC02</Esito>
    <Descrizione>Esempio</Descrizione>
    <MessageIdCommittente>123456</MessageIdCommittente>
</types:NotificaEsitoCommittente>
XML;
        // TODO: sign notification (on hold)
        $File = base64_encode($notification);
        $NomeFile = 'IT01234567890_11111_EC_001.xml';
        Base::enqueue(
            $notification_blob = $File,
            $filename = $NomeFile,
            $type = 'NotificaEsito',
            $invoice_id = $id
        );
    }
    public static function dispatchi()
    {
        $notifications = Notification::all()
            ->where('status', 'N_PENDING')
            ->where('actor', Base::getActor());
        $notifications = $notifications->toArray();
		
		
        $service = new \SdIRiceviNotifica_service(array('trace' => 1));

        foreach ($notifications as $notification) {
            $invoice = Invoice::find($notification['invoice_id']);
            $remote_id = $invoice->remote_id;
            $fileSdI = new \fileSdI_Type($remote_id, $notification['nomefile'], $notification['blob']);
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
