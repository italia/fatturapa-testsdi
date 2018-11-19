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
    public static function receive($invoice_blob, $filename, $position)
    {
        Exchange::Exchange();
        $dateTime = Base::getDateTime();
        $invoice = Invoice::create(
            [
                'nomefile' => $filename,
                'posizione' => $position,
                'cedente' => '',
                'anno' => '',
                'status' => 'E_RECEIVED',
                'blob' => $invoice_blob,
                'ctime' => $dateTime->date,
                'actor' => 'sdi',
                'issuer' => ''
            ]
        );
        return $invoice;
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
                $NomeFile = 'IT01234567890_11111_NS_123456.xml';
                Base::enqueue(
                    $notification_blob = $File,
                    $filename = $NomeFile,
                    $type = 'NotificaScarto',
                    $invoice_id = $Invoice['id']
                );
            }
        }
        return true;
    }

    public static function dispatchi()
    {
        $service1 = new \TrasmissioneFatture_service(array('trace' => 1));
        $service2 = new \RicezioneFatture_service(array('trace' => 1));

        $notifications = Notification::all()
            ->where('status', 'N_PENDING')
            ->where('actor', Base::getActor());
        $notifications = $notifications->toArray();
        
        foreach ($notifications as $notification) {
            echo 'looking at notification  ' . json_encode($notification) . '<br/>' . PHP_EOL;

            $fileSdI = new \fileSdI_Type($notification['invoice_id'], $notification['nomefile'], $notification['blob']);
            $invoice = Invoice::find($notification['invoice_id']);
            $issuer = $invoice->issuer;
            $sent = Base::dispatchNotification(
                $service1,
                "td$issuer",
                "TrasmissioneFatture",
                $notification['type'],
                $fileSdI
            );
            if ($notification['type'] == 'NotificaDecorrenzaTermini') {
                $xmlString = base64_decode($invoice['blob']);
                $xml = Base::unpack($xmlString);
                $recipient = $xml->FatturaElettronicaHeader->DatiTrasmissione->CodiceDestinatario;
                echo "sending NotificaDecorrenzaTermini notification to $recipient" . '<br/>' . PHP_EOL;
                $sent &= Base::dispatchNotification(
                    $service2,
                    "td$recipient",
                    "RicezioneFatture",
                    $notification['type'],
                    $fileSdI
                );
            } 
            if ($sent) {
                echo "sent !" . '<br/>';
                Notification::find($notification['id'])->update(['status' => 'N_DELIVERED' ]);
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
            $invoice_id = $Invoice['id'];
            $invoice_filename = $Invoice['nomefile'];
            $invoice_ctime = $Invoice['ctime'];
            $timeAfter48Hour = \strtotime($invoice_ctime . " + 48 hours");
            $timeAfter12Days = \strtotime($invoice_ctime . " + 12 days");
            $currentTime = \strtotime($dateTime->date);
            if ($currentTime >= $timeAfter12Days) {
                $Invoice['status'] = 'E_IMPOSSIBLE_DELIVERY';
                Invoice::find($invoice_id)->update(['status' => 'E_IMPOSSIBLE_DELIVERY' ]);
                // Attestazione di avvenuta trasmissione della fattura con impossibilità di recapito
                $notification = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="AT_v1.1.xsl"?>
<types:AttestazioneTrasmissioneFattura xmlns:types="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.1.xsd ">
<IdentificativoSdI>$invoice_id</IdentificativoSdI>
<NomeFile>$invoice_filename</NomeFile>
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
                Base::enqueue(
                    $notification_blob = base64_encode($notification),
                    $filename = 'IT01234567890_11111_AT_123456.xml',
                    $type = 'AttestazioneTrasmissioneFattura',
                    $invoice_id = $invoice_id
                );
            } else {
                if (!$dummy) {
                    $xmlString = base64_decode($Invoice['blob']);
                    $xml = Base::unpack($xmlString);
                    $recipient = $xml->FatturaElettronicaHeader->DatiTrasmissione->CodiceDestinatario;
                    $addressee = "td$recipient";
                    $metadati = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="MT_v1.0.xsl"?>
<types:MetadatiInvioFile xmlns:types="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd ">
    <IdentificativoSdI>$invoice_id</IdentificativoSdI>
    <NomeFile>$invoice_filename</NomeFile>
    <CodiceDestinatario>$recipient</CodiceDestinatario>
    <Formato>SDI10</Formato>
    <TentativiInvio>1</TentativiInvio>
    <MessageId>123456</MessageId>
    <Note>Esempio</Note>
</types:MetadatiInvioFile>
XML;
                    $nomeFileMetadati = '';
                    libxml_disable_entity_loader(false);
                    $service = new \RicezioneFatture_service(array('trace' => 1));
                    $service->__setLocation(HOSTMAIN.$addressee.'/soap/RicezioneFatture/');
                    $fileSdIConMetadati = new \fileSdIConMetadati_Type(
                        $invoice_id,
                        $invoice_filename,
                        $Invoice['blob'],
                        $nomeFileMetadati,
                        base64_encode($metadati)
                    );
                    
                    try {
                        $response = $service->RiceviFatture($fileSdIConMetadati);
                        if ($response) {
                            $Invoice['status'] = 'E_DELIVERED';
                            Invoice::find($invoice_id)->update(['status' => 'E_DELIVERED' ]);
                            // TODO: fill data in notification
                            $notification = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="RC_v1.0.xsl"?>
<types:RicevutaConsegna xmlns:types="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" IntermediarioConDupliceRuolo="Si" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd ">
  <IdentificativoSdI>$invoice_id</IdentificativoSdI>
  <NomeFile>$invoice_filename</NomeFile>
  <DataOraRicezione>$invoice_ctime</DataOraRicezione>
  <DataOraConsegna>$currentTime</DataOraConsegna>
  <Destinatario>
    <Codice>$recipient</Codice>
    <Descrizione>NO PA</Descrizione>
  </Destinatario>
  <MessageId>123456</MessageId>
</types:RicevutaConsegna>
XML;
                            echo $notification . PHP_EOL;
                            // TODO: sign notification (on hold)
                            Base::enqueue(
                                $notification_blob = base64_encode($notification),
                                $filename = 'IT01234567890_11111_RC_123456.xml',
                                $type = 'RicevutaConsegna',
                                $invoice_id = $invoice_id
                            );
                        }
                    } catch (SoapFault $e) {
                        echo "SOAP Fault: (faultcode: {".$e->faultcode."}, faultstring: {".$e->faultstring."})";
                        exit;
                    }
                }

                if (($currentTime >= $timeAfter48Hour) && $Invoice['status']=='E_VALID') {
                    Invoice::find($invoice_id)->update(['status' => 'E_FAILED_DELIVERY' ]);
                    // TODO: fill data in notification
                    $notification = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="MC_v1.0.xsl"?>
<types:NotificaMancataConsegna xmlns:types="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd ">
    <IdentificativoSdI>$invoice_id</IdentificativoSdI>
    <NomeFile>$invoice_filename</NomeFile>
    <DataOraRicezione>2013-06-06T12:00:00</DataOraRicezione>
    <Descrizione>Notifica di esempio</Descrizione>
    <MessageId>123456</MessageId>
    <Note>Esempio</Note>
</types:NotificaMancataConsegna>
XML;
                    // TODO: sign notification (on hold)
                    Base::enqueue(
                        $notification_blob = base64_encode($notification),
                        $filename = 'IT01234567890_11111_MC_123456.xml',
                        $type = 'NotificaMancataConsegna',
                        $invoice_id = $invoice_id
                    );
                }
            }
        }
    
        return true;
    }
    public static function checkExpiration()
    {
        $dateTime=Base::getDateTime();
        $Invoice = Invoice::where('status', 'E_DELIVERED')->where('actor', Base::getActor());
        $Invoices = $Invoice->get()->toArray();
        
        foreach ($Invoices as $Invoice) {
            $invoice_id = $Invoice['id'];
            $invoice_filename = $Invoice['nomefile'];
            $invoice_ctime = $Invoice['ctime'];
            $timeAfter15Days = \strtotime($invoice_ctime . " + 15 days");
            $currentTime = \strtotime($dateTime->date);
            if ($currentTime >= $timeAfter15Days) {
                Invoice::find($invoice_id)->update(['status' => 'E_EXPIRED' ]);
                    
                    $notification = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<?xml-stylesheet type="text/xsl" href="MC_v1.0.xsl"?>
<types:NotificaMancataConsegna xmlns:types="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd ">
    <IdentificativoSdI>$invoice_id</IdentificativoSdI>
    <NomeFile>$invoice_filename</NomeFile>
    <DataOraRicezione>2013-06-06T12:00:00</DataOraRicezione>
    <Descrizione>Notifica di esempio</Descrizione>
    <MessageId>123456</MessageId>
    <Note>Esempio</Note>
</types:NotificaMancataConsegna>
XML;
                    // TODO: sign notification (on hold)
                                        
                    Base::enqueue(
                        $notification_blob = base64_encode($notification),
                        $filename = 'IT01234567890_11111_MC_123456.xml',
                        $type = 'NotificaDecorrenzaTermini',
                        $invoice_id = $invoice_id
                    );
            }
        }
    }
    public static function acceptRefuse($invoice_id, $status, $esito)
    {
        new Database();
        Invoice::where('id', '=', $invoice_id)->update(array('status' => $status));
        $notification = <<<XML
<?xml version="1.0" encoding="UTF-8"?><?xml-stylesheet type="text/xsl" href="EC_v1.0.xsl"?>
<types:NotificaEsitoCommittente xmlns:types="http://www.fatturapa.gov.it/sdi/messaggi/v1.0" xmlns:ds="http://www.w3.org/2000/09/xmldsig#" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" versione="1.0" xsi:schemaLocation="http://www.fatturapa.gov.it/sdi/messaggi/v1.0 MessaggiTypes_v1.0.xsd ">
  <IdentificativoSdI>$invoice_id</IdentificativoSdI>
  <RiferimentoFattura>
    <NumeroFattura>1111</NumeroFattura>
    <AnnoFattura>2013</AnnoFattura>
    <PosizioneFattura>2</PosizioneFattura>
  </RiferimentoFattura>
  <Esito>$esito</Esito>
  <Descrizione>Esempio</Descrizione>
  <MessageIdCommittente>123456</MessageIdCommittente>
</types:NotificaEsitoCommittente>
XML;
        // TODO: sign notification (on hold)
        $File = base64_encode($notification);
        $NomeFile = 'IT01234567890_11111_EC_123456.xml';
        Base::enqueue(
            $notification_blob = $File,
            $filename = $NomeFile,
            $type = 'NotificaEsito',
            $invoice_id = $invoice_id
        );
    }
    private static function validateInvoice($xmlString)
    {
        $xml = new \DOMDocument();
        try {
            $xml->loadXML($xmlString, LIBXML_NOBLANKS);
            $schema = SAFEROOT.'core/schemas/Schema_del_file_xml_FatturaPA_versione_1.2_cleanup.xsd';
            $valid = $xml->schemaValidate($schema);
        } catch (\Exception $e) {
            $valid = false;
        }
        return $valid;
    }
}
