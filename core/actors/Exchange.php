<?php


namespace FatturaPa\Core\Actors;

use FatturaPa\Core\Models\Database;
use FatturaPa\Core\Models\Invoice;
use FatturaPa\Core\Models\Notification;
use FatturaPa\Core\Models\Channel;
use FatturaPa\Core\Actors\Base;

class Exchange
{

    public static function Exchange()
    {
        new Database();
    }
    public static function receive($XML, $NomeFile, $posizione)
    {
        Exchange::Exchange();
        $dateTime = Base::getDateTime();
        $Invoice = Invoice::create(
            [
                'nomefile' => $NomeFile,
                'posizione' => $posizione,
                'cedente' => '',
                'anno' => '',
                'status' => 'E_RECEIVED',
                'blob' => $XML,
                'ctime' => $dateTime->date,
                'actor' => 'sdi',
                'issuer' => ''
            ]
        );
        return $Invoice;
    }
    public static function checkValidity()
    {
        
        Exchange::Exchange();
        $Invoice = Invoice::all()->where('status', 'E_RECEIVED');
        $Invoices = $Invoice->toArray();
        foreach ($Invoices as $Invoice) {
            $xmlString = base64_decode($Invoice['blob']);
            $valid = Exchange::validateInvoice($xmlString);
            if ($valid === true) {
                Invoice::find($Invoice['id'])->update(['status' => 'E_VALID' ]);
                $xml = Base::unpack($xmlString);
                $cedente = $xml->FatturaElettronicaHeader->CedentePrestatore->DatiAnagrafici->IdFiscaleIVA->IdPaese .
                    '-' .
                    $xml->FatturaElettronicaHeader->CedentePrestatore->DatiAnagrafici->IdFiscaleIVA->IdCodice;
                $data = $xml->FatturaElettronicaBody[0]->DatiGenerali->DatiGeneraliDocumento->Data;
                $anno = substr($data, 0, 4);
                $issuer = Channel::find($cedente)->issuer;
                Invoice::find($Invoice['id'])->update(['cedente' => $cedente ]);
                Invoice::find($Invoice['id'])->update(['anno' => $anno ]);
                Invoice::find($Invoice['id'])->update(['issuer' => $issuer ]);
            } else {
                Invoice::find($Invoice['id'])->update(['status' => 'E_INVALID']);
                // TODO: fill data in notification
                $notification = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="NS_v1.0.xsl"?>
<types:NotificaScarto xmlns:types="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd ">
  <IdentificativoSdI>111</IdentificativoSdI>
  <NomeFile>IT01234567890_11111.xml.p7m</NomeFile>
  <DataOraRicezione>2013-06-06T12:00:00Z</DataOraRicezione>
  <RiferimentoArchivio>
    <IdentificativoSdI>100</IdentificativoSdI>
    <NomeFile>IT01234567890_11111.zip</NomeFile>
  </RiferimentoArchivio>
  <ListaErrori>
    <Errore>
      <Codice>00100</Codice>
      <Descrizione>Certificato di firma scaduto</Descrizione>
    </Errore>
  </ListaErrori>
  <MessageId>123456</MessageId>
  <Note>Note</Note>
</types:NotificaScarto>
XML;
                // TODO: sign notification (on hold)
                $File = base64_encode($notification);
                $NomeFile = 'IT01234567890_11111_NS_001.xml';
                Base::enqueue(
                    $invoice_id = $Invoice['id'],
                    $type = 'NS',
                    $notification_blob = $File,
                    $NomeFile = $NomeFile
                );
            }
        }
        return true;
    }
    public static function dispatchi()
    {
        
        $notifications = Notification::all()
//            ->where('status', 'N_PENDING')
            ->where('actor', Base::getActor());
        $notifications = $notifications->toArray();
        
        //echo "<pre>";print_r($notifications);exit;
        
        foreach ($notifications as $notification) {
            // TODO1: send notification to correct actor (notification addressee)
            // When the notication has to be sent to a Recipient,
            // we can fetch the addressee peeking in the invoice XML from
            // `FatturaElettronicaHeader.DatiTrasmissione.CodiceDestinatario` field
            // When the notification has to be sent to an Issuer, the notification must be sent to the invoice issuer,
            // as in:
            // $invoice = Invoice::all()->where('id', $notification['invoice_id']);
            $invoice = Invoice::find($notification['invoice_id']);
            $issuer=$invoice->issuer;
            if ($issuer=='') {
                return; // this should not happen
            } else {
                $addressee = "td$issuer";
            }
            echo('addressee = ' . $addressee . PHP_EOL);

            $service = new \TrasmissioneFatture_service(array('trace' => 1));
            $service->__setLocation(HOSTMAIN.$addressee.'/soap/TrasmissioneFatture/');
            $fileSdI = new \fileSdI_Type($notification['id'], $notification['nomefile'], $notification['blob']);

            $sent = false;
            try {
                // TODO2: send to correct operation based on the notification type:
                // for notifications from Exchange to Issuer:
                // 1. Rejection notice (Notifica di scarto = NS) NotificaScarto operation
                // 2. Delivery receipt (Ricevuta di consegna = RC) RicevutaConsegna operation
                // 3. Failed delivery notice (Notifica di mancata consegna = MC) NotificaMancataConsegna operation
                // 4. Outcome notice (Notifica di esito = NE) NotificaEsito operation
                // 5. Deadline passed notice (Notifica di decorrenza termini = DT) NotificaDecorrenzaTermini operation
                // 6. Statement of happened transmission with delivery impossibility (Attestazione di avvenuta
                //  trasmissione con impossibilità di recapito = AT) AttestazioneTrasmissioneFattura operation
                if ($notification['type']=='NS') {
                    $service->NotificaScarto($fileSdI);
                    $sent = true;
                } elseif ($notification['type']=='RC') {
                    $service->RicevutaConsegna($fileSdI);
                    $sent = true;
                } elseif ($notification['type']=='MC') {
                    $response = $service->NotificaMancataConsegna($fileSdI);
                    $sent = true;
                } elseif ($notification['type']=='NE') {
                    $response = $service->NotificaEsito($fileSdI);
                    $sent = true;
                } elseif ($notification['type']=='DT') {
                    $response = $service->NotificaDecorrenzaTermini($fileSdI);
                    $sent = true;
                } elseif ($notification['type']=='AT') {
                    $response = $service->AttestazioneTrasmissioneFattura($fileSdI);
                    $sent = true;
                }
                // TODO
                // for notifications from Exchange to Recipient:
                // 1. Deadline passed notice (Notifica di decorrenza termini = DT) NotificaDecorrenzaTermini operation
                /*if($notification['type']=='DT')
                {
                    $service->NotificaEsito($fileSdI);
                    $sent = true;
                }*/

                if ($sent) {
                    echo "sent !";
                    Notification::find($notification['id'])->update(['status' => 'N_DELIVERED' ]);
                }
            } catch (SoapFault $e) {
                echo "failure !";
            }
        }
         return true;
    }
    // if dummy is set and true, it will simulate failure to deliver
    public static function deliver($dummy)
    {
        $dateTime=Base::getDateTime();
        $Invoice = Invoice::where('status', 'E_VALID')
            ->orWhere('status', 'E_FAILED_DELIVERY')
            ->where('actor', Base::getActor());
        $Invoices = $Invoice->get()->toArray();
                    
        foreach ($Invoices as $Invoice) {
            $timeAfter48Hour=\strtotime($Invoice['ctime'] . " + 48 hours");
            $timeAfter12Days=\strtotime($Invoice['ctime'] . " + 12 days");
            $currentTime=\strtotime($dateTime->date);
            if ($currentTime >= $timeAfter12Days) {
                $Invoice['status'] = 'E_IMPOSSIBLE_DELIVERY';
                Invoice::find($Invoice['id'])->update(['status' => 'E_IMPOSSIBLE_DELIVERY' ]);
                // TODO: fill data in notification
                $notification = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="DT_v1.0.xsl"?>
<types:NotificaDecorrenzaTermini xmlns:types="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" IntermediarioConDupliceRuolo="Si" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd http://www.w3.org/2000/09/xmldsig# xmldsig-core-schema.xsd">
  <IdentificativoSdI>111</IdentificativoSdI>
  <NomeFile>IT01234567890_11111.xml.p7m</NomeFile>
  <Descrizione>Notifica di esempio</Descrizione>
  <MessageId>123456</MessageId>
  <Note>Esempio</Note>
</types:NotificaDecorrenzaTermini>
XML;
                // TODO: sign notification (on hold)
                $File = base64_encode($notification);
                $NomeFile = 'IT01234567890_11111_DT_001.xml';
                Base::enqueue(
                    $invoice_id = $Invoice['id'],
                    $type = 'DT',
                    $notification_blob = $File,
                    $NomeFile = $NomeFile
                );
            } else {
                if (!$dummy) {
                    $xmlString = base64_decode($Invoice['blob']);
                    $xml = Base::unpack($xmlString);
                    $data = $xml->FatturaElettronicaHeader->DatiTrasmissione->CodiceDestinatario;
                    $addressee = "td$data";
                    
                    $metadati='metadata';
                    $nomeFileMetadati = '';
                    libxml_disable_entity_loader(false);
                    $service = new \RicezioneFatture_service(array('trace' => 1));
                    $service->__setLocation(HOSTMAIN.$addressee.'/soap/RicezioneFatture/');
                    $fileSdIConMetadati = new \fileSdIConMetadati_Type(
                        $Invoice['id'],
                        $Invoice['nomefile'],
                        $Invoice['blob'],
                        $nomeFileMetadati,
                        base64_encode($metadati)
                    );
                    
                    try {
                        $response = $service->RiceviFatture($fileSdIConMetadati);
                        if ($response) {
                            $Invoice['status'] = 'E_DELIVERED';
                            Invoice::find($Invoice['id'])->update(['status' => 'E_DELIVERED' ]);
                            // TODO: fill data in notification
                            $invoice_id = $Invoice['id'];
                            $notification = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="AT_v1.1.xsl"?>
<types:AttestazioneTrasmissioneFattura xmlns:types="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.1.xsd ">
<IdentificativoSdI>$invoice_id</IdentificativoSdI>
<NomeFile>IT01234567890_11111.xml.p7m</NomeFile>
<DataOraRicezione>2014-04-01T12:00:00</DataOraRicezione>
<Destinatario>
    <Codice>AAAAAA</Codice>
    <Descrizione>Pubblica Amministrazione di prova</Descrizione>
</Destinatario>
<MessageId>123456</MessageId>
<Note>Attestazione Trasmissione Fattura di prova</Note>
<HashFileOriginale>2c1f3a240a056d9537a8608fed310812ef7b1b7a410d0152f5c9c9e93486ae44</HashFileOriginale>
</types:AttestazioneTrasmissioneFattura>
XML;
                            // TODO: sign notification (on hold)
                            $File = base64_encode($notification);
                            $NomeFile = 'IT01234567890_11111_AT_001.xml';
                            Base::enqueue(
                                $invoice_id = $Invoice['id'],
                                $type = 'AT',
                                $notification_blob = $File,
                                $NomeFile = $NomeFile
                            );
                        }
                    } catch (SoapFault $e) {
                    }                    
                }

                if (($currentTime >= $timeAfter48Hour) && $Invoice['status']=='E_VALID') {
                    Invoice::find($Invoice['id'])->update(['status' => 'E_FAILED_DELIVERY' ]);
                    // TODO: fill data in notification
                    $notification = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="MC_v1.0.xsl"?>
<types:NotificaMancataConsegna xmlns:types="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd ">
    <IdentificativoSdI>111</IdentificativoSdI>
    <NomeFile>IT01234567890_11111.xml.p7m</NomeFile>
    <DataOraRicezione>2013-06-06T12:00:00</DataOraRicezione>
    <Descrizione>Notifica di esempio</Descrizione>
    <MessageId>123456</MessageId>
    <Note>Esempio</Note>
</types:NotificaMancataConsegna>
XML;
                    // TODO: sign notification (on hold)
                    $File = base64_encode($notification);
                    $NomeFile = 'IT01234567890_11111_MC_001.xml';
                    Base::enqueue(
                        $invoice_id = $Invoice['id'],
                        $type = 'NS',
                        $notification_blob = $File,
                        $NomeFile = $NomeFile
                    );
                }    
            }
        }
    
        return true;
    }
    public static function checkExpiration()
    {
    }
    public static function accept($invoices)
    {
    }
    public static function refuse($invoices)
    {
    }
    private static function validateInvoice($xmlString)
    {
        $xml = new \DOMDocument();
        $xml->loadXML($xmlString, LIBXML_NOBLANKS);
        try {
            $schema = BASEROOT.'core/schemas/Schema_del_file_xml_FatturaPA_versione_1.2_cleanup.xsd';
            $valid = $xml->schemaValidate($schema);
        } catch (\Exception $e) {
            $valid = false;
        }
        return $valid;
    }
}
