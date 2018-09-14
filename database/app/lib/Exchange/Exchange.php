<?php


namespace Lib;

use Models\Database;
use Models\Invoice;
use Lib\Base;

class Exchange
{

    public static function Exchange()
    {
        new Database();
    }
    public static function receive($XML, $NomeFile, $posizione, $actor)
    {
        Exchange::Exchange();
        $xmlString = base64_decode($XML);
        $xml = Exchange::unpackInvoice($xmlString);
        $cedente = $xml->FatturaElettronicaHeader->CedentePrestatore->DatiAnagrafici->IdFiscaleIVA->IdPaese .
            '-' .
            $xml->FatturaElettronicaHeader->CedentePrestatore->DatiAnagrafici->IdFiscaleIVA->IdCodice;
        $data = $xml->FatturaElettronicaBody[0]->DatiGenerali->DatiGeneraliDocumento->Data;
        $dateTime = Base::getDateTime();
        $Invoice = Invoice::create(
            [
                'uuid' => '1c873278-dec8-'.rand(999, 9999).'-8c69-7b647adca8ce',
                'nomefile' => $NomeFile,
                'posizione' => $posizione,
                'cedente' => $cedente,
                'anno' => substr($data, 0, 4),
                'status' => 'E_RECEIVED',
                'blob' => $XML,
                'ctime' => $dateTime->date,
                'actor' => $actor
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
                Invoice::find($Invoice['uuid'])->update(['status' => 'E_VALID' ]);
            } else {
                Invoice::find($Invoice['uuid'])->update(['status' => 'E_INVALID']);
            }
        }
        return true;
    }
    public static function deliver()
    {
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
    private function unpackInvoice($xmlString)
    {
        // defend against XML External Entity Injection
        libxml_disable_entity_loader(true);
        $collapsed_xml_string = preg_replace("/\s+/", "", $xmlString);
        $collapsed_xml_string = $collapsed_xml_string ? $collapsed_xml_string : $xmlString;
        if (preg_match("/\<!DOCTYPE/i", $collapsed_xml_string)) {
            throw new \InvalidArgumentException('Invalid XML: Detected use of illegal DOCTYPE');
        }

        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlString, 'SimpleXMLElement', LIBXML_NOWARNING);
        if ($xml === false) {
            throw new \InvalidArgumentException("Cannot load XML\n");
        }
        return $xml;
    }
    private static function validateInvoice($xmlString)
    {
        $xml = new \DOMDocument();
        $xml->loadXML($xmlString, LIBXML_NOBLANKS);
        try {
            $valid = $xml->schemaValidate(BASEROOT.'database/app/lib/schemas/Schema_del_file_xml_FatturaPA_versione_1.2_cleanup.xsd');
        } catch (\Exception $e) {
            $valid = false;
        }
        return $valid;
    }
}
