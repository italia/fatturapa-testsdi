<?php
declare(strict_types=1);

require_once(__DIR__ . "/../core/config.php");
require_once(__DIR__ . "/../vendor/autoload.php");

class MyTest extends PHPUnit\Framework\TestCase
{
    protected $actors;
    protected $tds;
    protected $issuers; // 1-N relationship between issuer and cedente
    
    protected function setUp()
    {
        $this->client = new GuzzleHttp\Client([
            'base_uri' => substr(HOSTMAIN, 0, -1),
            'http_errors' => false
        ]);

        $this->actors = $this->getActors();
        $this->tds = $this->getIssuers();
        $channels = $this->getChannels();
        $issuers = [];
        foreach ($channels as $cedente => $issuer) {
            if (array_key_exists($issuer, $issuers)) {
                array_push($issuers[$issuer], $cedente);
            } else {
                $issuers[$issuer] = [$cedente];
            }
        }
        $this->issuers = $issuers;
    }

    protected function destruct()
    {
        $this->client = null;
    }

    protected function getCedente($issuer)
    {
        $rand_key = array_rand($this->issuers[$issuer]);
        return $this->issuers[$issuer][$rand_key];
    }

    private function getChannels()
    {
        echo "getting channels" . PHP_EOL;
        $response = $this->client->get("/sdi/rpc/channels");
        $body = $response->getBody();
        $contents = $body->getContents();
        $arr = json_decode($contents, true);
        return $arr['channels'];
    }

    protected function getInvoice($filename)
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException('File not found');
        }
        return file_get_contents($filename);
    }

    protected function getValidInvoice($issuer, $recipient)
    {
        // echo "FROM: $issuer" . PHP_EOL;
        // echo "TO: $recipient" . PHP_EOL;
        $filename = 'tests/samples/invoices/IT01234567890_FPR01.xml';
        $baseInvoice = $this->getInvoice($filename);
        $invoice = str_replace(
            "<CodiceDestinatario>ABC1234</CodiceDestinatario>",
            "<CodiceDestinatario>$recipient</CodiceDestinatario>",
            $baseInvoice
        );
        $cedente = $this->getCedente($issuer);
        $paese = substr($cedente, 0, 2);
        $invoice = str_replace(
            "          <IdPaese>IT</IdPaese>",
            "          <IdPaese>$paese</IdPaese>",
            $invoice
        );
        $codice = substr($cedente, 3);
        $invoice = str_replace(
            "          <IdCodice>01234567890</IdCodice>",
            "          <IdCodice>$codice</IdCodice>",
            $invoice
        );
        return $invoice;
    }

    protected function getInvalidInvoice()
    {
        $filename = 'tests/samples/invoices/missing_CedentePrestatore.xml';
        return $this->getInvoice($filename);
    }

    private function getActors()
    {
        echo "getting actors list" . PHP_EOL;
        $response = $this->client->get("/sdi/rpc/actors");
        $body = $response->getBody();
        $contents = $body->getContents();
        $arr = json_decode($contents, true);
        return $arr['actors'];
    }

    private function getIssuers()
    {
        echo "getting issuers list" . PHP_EOL;
        $response = $this->client->get("/sdi/rpc/issuers");
        $body = $response->getBody();
        $contents = $body->getContents();
        $arr = json_decode($contents, true);
        return $arr['issuers'];
    }

    // returns a random td, different from the supplied one
    protected function getTd($notThisOne)
    {
        $tds = $this->tds;
        unset($tds[$notThisOne]);
        $rand_key = array_rand($tds);
        return $tds[$rand_key];
    }
}
