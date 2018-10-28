<?php
declare(strict_types=1);

require_once("MyTest.php");

require_once(__DIR__ . "/../soap/config.php");
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
        $this->service->__setLocation(HOSTNAME . 'RicezioneFatture/');
    }

    public function testRiceviFatture()
    {
        $NomeFile = 'aaa.xml';
        $File = '<xml></xml>';
        $File = base64_encode($File);
        $metadati = "<xml></xml>";
        $base64_meta = base64_encode($metadati);
        $fileSdIConMetadati_Type = new fileSdIConMetadati_Type(
            222,
            $NomeFile,
            $File,
            $metadati,
            $base64_meta
        );
        $response2 = $this->service->RiceviFatture($fileSdIConMetadati_Type);
        print_r($response2);
    }

    public function testNotificaDecorrenzaTermini()
    {
        $fileSdI_Type = new \fileSdI_Type(111, $NomeFile, $File);
        $response3 = $this->service->NotificaDecorrenzaTermini($fileSdI_Type);
    }

    public function tearDown()
    {
        MyTest::tearDown();

        $this->service = null;
    }
}
