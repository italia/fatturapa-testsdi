<?php
declare(strict_types=1);

require_once("MyTest.php");

require_once(__DIR__ . "/../core/config.php");
// require_once(__DIR__ . "/../soap/SdIRiceviNotifica/autoload.php");
require_once(__DIR__ . "/../soap/RicezioneFatture/autoload.php");
// require_once(__DIR__ . "/../soap/TrasmissioneFatture/autoload.php");

final class RicezioneFatture extends MyTest
{
    protected $service;

    // initialize tests
    protected function setUp()
    {
        MyTest::setUp();
        $this->service = new \RicezioneFatture_service(array('trace' => 1));
    }

    public function testRiceviFatture()
    {
        foreach ($this->tds as $recipient) {
            $this->service->__setLocation(HOSTMAIN . "td$recipient/soap/RicezioneFatture/");
            $invoice = MyTest::getValidInvoice(MyTest::getTd($recipient), $recipient);
            $invoice = base64_encode($invoice);
            $metadata = '<xml></xml>';
            $metadata = base64_encode($metadata);
            $fileSdIConMetadati_Type = new fileSdIConMetadati_Type(
                222,
                'aaa.xml',
                $invoice,
                'metadati.xml',
                $metadata
            );
            $response = $this->service->RiceviFatture($fileSdIConMetadati_Type);
            $this->assertEquals("ER01", $response->getEsito());
        }
    }

    public function testNotificaDecorrenzaTermini()
    {
        foreach ($this->tds as $recipient) {
            $this->service->__setLocation(HOSTMAIN . "td$recipient/soap/RicezioneFatture/");
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
            $notification = base64_encode($notification);
            $fileSdI_Type = new \fileSdI_Type(
                111,
                'aaa.xml',
                $notification
            );
            $response = $this->service->NotificaDecorrenzaTermini($fileSdI_Type);
            $this->assertEquals(null, $response);
        }
    }

    public function tearDown()
    {
        MyTest::tearDown();

        $this->service = null;
    }
}
