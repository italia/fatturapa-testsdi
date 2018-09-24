<?php
declare(strict_types=1);

require_once(__DIR__ . "/../vendor/autoload.php");
require_once(__DIR__ . "/../core/config.php");

final class FullDemo extends PHPUnit\Framework\TestCase
{
    protected $client;

    protected static $actors = ['sdi', 'td0000001', 'td0000002'];
    protected static $tds = ['0000001', '0000002'];

    // initialize tests
    protected function setUp()
    {
        $this->client = new GuzzleHttp\Client([
            'base_uri' => substr(HOSTMAIN, 0, -1),
            'http_errors' => false
        ]);
    }

    private function getTable($actor, $table, $status = '')
    {
        echo "getting table $table for actor $actor" . PHP_EOL;
        $url = "/$actor/rpc/$table";
        if ($status != '') {
            $url = $url . "/?status=$status";
        }
        $response = $this->client->get($url);
        $this->assertEquals(200, $response->getStatusCode());
        $body = $response->getBody();
        $contents = $body->getContents();
        $arr = json_decode($contents, true);
        $this->assertArrayHasKey($table, $arr);
        return $arr[$table];
    }

    private function expectedInvoices($actor, $count, $status = '')
    {
        $invoices = $this->getTable($actor, 'invoices', $status);
        $this->assertEquals($count, sizeof($invoices));
    }

    private function expectedNotifications($actor, $count)
    {
        $notifications = $this->getTable($actor, 'notifications');
        $this->assertEquals($count, sizeof($notifications));
    }

    private function getInvoice($filename)
    {
        if (!file_exists($filename)) {
            throw new \InvalidArgumentException('File not found');
        }
        return file_get_contents($filename);
    }

    private function getValidInvoice()
    {
        $filename = 'tests/samples/invoices/IT01234567890_FPR01.xml';
        return $this->getInvoice($filename);
    }

    private function getInvalidInvoice()
    {
        $filename = 'tests/samples/invoices/missing_CedentePrestatore.xml';
        return $this->getInvoice($filename);
    }

    private function clearActors()
    {
        foreach (self::$actors as $actor) {
            echo "clear actor $actor" . PHP_EOL;
            $response = $this->client->post("$actor/rpc/clear");
            $this->assertEquals(200, $response->getStatusCode());
        }
    }

    private function freezeActors($datetime)
    {
        foreach (self::$actors as $actor) {
            echo "freeze actor $actor to datetime = $datetime" . PHP_EOL;
            $response = $this->client->post('sdi/rpc/speed', ['query' => ['speed' => 0]]);
            $this->assertEquals(200, $response->getStatusCode());
            $response = $this->client->post('sdi/rpc/timestamp', ['query' => ['timestamp' => $datetime]]);
            $this->assertEquals(200, $response->getStatusCode());
        }
    }

    private function advanceActors($timeDelta)
    {
        $response = $this->client->get('/sdi/rpc/datetime');
        $this->assertEquals(200, $response->getStatusCode());
        $body = $response->getBody();
        $timeStampString = substr($response->getBody()->getContents(), 12);

        $timestamp = strtotime("+$timeDelta", strtotime($timeStampString));
        $datetime = new DateTime();
        $datetime->setTimestamp($timestamp);
        $timestamp = $datetime->format(\DateTime::ATOM);
        foreach (self::$actors as $actor) {
            echo "advance actor $actor by $timeDelta to $timestamp" . PHP_EOL;
            $response = $this->client->post(
                'sdi/rpc/timestamp',
                ['query' => ['timestamp' => $timestamp]]
            );
            $this->assertEquals(200, $response->getStatusCode());
        }
    }

    // test item 1 of full demo: that after clearing the DB, all invoice and notification queues are empty
    public function testClearDoesClearQueues()
    {
        $this->clearActors();
        foreach (self::$actors as $actor) {
            $this->expectedInvoices($actor, 0);
            $this->expectedNotifications($actor, 0);
        }
    }

    // test item 2 of full demo: that after clearing the DB, and uploading a sample invoice
    // for td000000x, I will find it in the I_UPLOADED queue for td000000x
    // while all other queues will be empty
    public function testUpload()
    {
        $xml = '<xml></<xml>'; // dummy invoice
        foreach (self::$tds as $issuer) {
            $this->clearActors();
            $response = $this->client->post("td$issuer/rpc/upload", [
                'multipart' => [
                    [
                        'name'     => 'File',
                        'contents' => $xml,
                        'filename' => 'aaa.xml'
                    ]
                ]
            ]);
            $this->assertEquals(200, $response->getStatusCode());

            $this->expectedInvoices("td$issuer", 1, 'I_UPLOADED');
            foreach (self::$actors as $actor) {
                $this->expectedInvoices($actor, $actor == "td$issuer" ? 1 : 0);
                $this->expectedNotifications($actor, 0);
            }
        }
    }

    // test items 3, 4 and 5 of full demo: that forcing transmisson from td000000x of an uploaded
    // invoice, I will find it in the I_TRANSMITTED queue of td000000x and in the E_RECEIVED queue of sdi,
    // while all other queues will be empty
    public function testTransmit()
    {
        $xml = $this->getValidInvoice();
        foreach (self::$tds as $issuer) {
            $this->clearActors();
            $response = $this->client->post("td$issuer/rpc/upload", [
                'multipart' => [
                    [
                        'name'     => 'File',
                        'contents' => $xml,
                        'filename' => 'aaa.xml'
                    ]
                ]
            ]);
            $this->assertEquals(200, $response->getStatusCode());

            $response = $this->client->post("td$issuer/rpc/transmit");
            echo $response->getBody();
            $this->assertEquals(200, $response->getStatusCode());

            $this->expectedInvoices("td$issuer", 1, 'I_TRANSMITTED');
            $this->expectedInvoices("sdi", 1, 'E_RECEIVED');
            foreach (self::$actors as $actor) {
                $this->expectedInvoices($actor, $actor == "td$issuer" || $actor == 'sdi' ? 1 : 0);
                $this->expectedNotifications($actor, 0);
            }
        }
    }

    // test item 6 of full demo: that after forcing ES to check validity, invalid invoices will end
    // up in the E_INVALID status, while all other invoice queues will be empty except I_TRANSMITTED
    // for td000000x
    public function testCheckValidity1()
    {
        $xml = $this->getInvalidInvoice();
        foreach (self::$tds as $issuer) {
            $this->clearActors();
            $response = $this->client->post("td$issuer/rpc/upload", [
                'multipart' => [
                    [
                        'name'     => 'File',
                        'contents' => $xml,
                        'filename' => 'aaa.xml'
                    ]
                ]
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $response = $this->client->post("td$issuer/rpc/transmit");
            $this->assertEquals(200, $response->getStatusCode());
            $response = $this->client->post("sdi/rpc/checkValidity");
            $this->assertEquals(200, $response->getStatusCode());

            $this->expectedInvoices("td$issuer", 1, 'I_TRANSMITTED');
            $this->expectedInvoices("sdi", 1, 'E_INVALID');
            foreach (self::$actors as $actor) {
                $this->expectedInvoices($actor, $actor == "td$issuer" || $actor == 'sdi' ? 1 : 0);
                $this->expectedNotifications($actor, $actor == 'sdi' ? 1 : 0);
            }
        }
    }

    // test item 6 of full demo: that after forcing ES to check validity, valid invoices will end
    // up in the E_VALID status, while and all other invoice queues will be empty except
    // I_TRANSMITTED for td000000x
    public function testCheckValidity2()
    {
        $xml = $this->getValidInvoice();
        foreach (self::$tds as $issuer) {
            $this->clearActors();
            $response = $this->client->post("td$issuer/rpc/upload", [
                'multipart' => [
                    [
                        'name'     => 'File',
                        'contents' => $xml,
                        'filename' => 'aaa.xml'
                    ]
                ]
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            $response = $this->client->post("td$issuer/rpc/transmit");
            $this->assertEquals(200, $response->getStatusCode());
            $response = $this->client->post("sdi/rpc/checkValidity");
            echo $response->getBody();
            $this->assertEquals(200, $response->getStatusCode());

            $this->expectedInvoices("td$issuer", 1, 'I_TRANSMITTED');
            $this->expectedInvoices("sdi", 1, 'E_VALID');
            foreach (self::$actors as $actor) {
                $this->expectedInvoices($actor, $actor == "td$issuer" || $actor == 'sdi' ? 1 : 0);
                $this->expectedNotifications($actor, 0);
            }
        }
    }

    private function deliver($baseInvoice, $issuer, $recipient, $timeDelta, $dummy=false)
    {
        $this->clearActors();
        $this->freezeActors('2019-07-01T12:00Z');
        $invoice = str_replace(
            "<CodiceDestinatario>ABC1234</CodiceDestinatario>",
            "<CodiceDestinatario>$recipient</CodiceDestinatario>",
            $baseInvoice
        );
        if ($issuer == '0000002') {
            $invoice = str_replace(
                "          <IdCodice>01234567890</IdCodice>",
                "          <IdCodice>12345678901</IdCodice>",
                $invoice
            );
        }
        $response = $this->client->post("td$issuer/rpc/upload", [
            'multipart' => [
                [
                    'name'     => 'File',
                    'contents' => $invoice,
                    'filename' => 'aaa.xml'
                ]
            ]
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $response = $this->client->post("td$issuer/rpc/transmit");
        $this->assertEquals(200, $response->getStatusCode());
        $response = $this->client->post("sdi/rpc/checkValidity");
        $this->assertEquals(200, $response->getStatusCode());

        $this->advanceActors($timeDelta);
        $this->advanceActors('1 hour');
        $response = $this->client->post("sdi/rpc/deliver" . ($dummy ? '?dummy=true' : ''));
        $this->assertEquals(200, $response->getStatusCode());
    }

    // test item 7 of full demo: that if an invoice sits for longer than 48 hours in E_VALID
    // on forcing delivery it will be moved to E_FAILED_DELIVERY and all other invoice queues
    // will be empty except I_TRANSMITTED for td000000x
    // and that if an invoice sits for longer than 10 days in E_FAILED_DELIVERY, on forcing
    // delivery it will be moved to E_IMPOSSIBLE_DELIVERY and all other invoice queues will be
    // empty except I_TRANSMITTED for td000000x
    public function testDeliver1()
    {
        $xml = $this->getValidInvoice();
        foreach (self::$tds as $issuer) {
            foreach (self::$tds as $recipient) {
                if ($recipient != $issuer) {
                    $timeDelta = '48 hours';
                    $this->deliver($xml, $issuer, $recipient, $timeDelta, true);

                    $this->expectedInvoices("td$issuer", 1, 'I_TRANSMITTED');
                    $this->expectedInvoices("sdi", 1, 'E_FAILED_DELIVERY');
                    foreach (self::$actors as $actor) {
                        $this->expectedInvoices($actor, $actor == "td$issuer" || $actor == 'sdi' ? 1 : 0);
                        $this->expectedNotifications($actor, $actor == 'sdi' ? 1 : 0);
                    }

                    $timeDelta = '10 days';
                    $this->advanceActors($timeDelta);
                    $this->advanceActors('1 hour');
                    $response = $this->client->post("sdi/rpc/deliver?dummy");
                    $this->assertEquals(200, $response->getStatusCode());

                    $this->expectedInvoices("td$issuer", 1, 'I_TRANSMITTED');
                    $this->expectedInvoices("sdi", 1, 'E_IMPOSSIBLE_DELIVERY');
                    foreach (self::$actors as $actor) {
                        $this->expectedInvoices($actor, $actor == "td$issuer" || $actor == 'sdi' ? 1 : 0);
                        $this->expectedNotifications($actor, $actor == 'sdi' ? 2 : 0);
                    }
                }
            }
        }
    }

    // test item 7 of full demo: that if an invoice sits for longer than 48 hours + 10 days
    // in E_VALID, on forcing delivery it will be moved to E_IMPOSSIBLE_DELIVERY and all other
    // invoice queues will be empty except I_TRANSMITTED for td000000x
    public function testDeliver2()
    {
        $xml = $this->getValidInvoice();
        foreach (self::$tds as $issuer) {
            foreach (self::$tds as $recipient) {
                if ($recipient != $issuer) {
                    $timeDelta = '12 days';
                    $this->deliver($xml, $issuer, $recipient, $timeDelta);

                    $this->expectedInvoices("td$issuer", 1, 'I_TRANSMITTED');
                    $this->expectedInvoices("sdi", 1, 'E_IMPOSSIBLE_DELIVERY');
                    foreach (self::$actors as $actor) {
                        $this->expectedInvoices($actor, $actor == "td$issuer" || $actor == 'sdi' ? 1 : 0);
                        $this->expectedNotifications($actor, $actor == 'sdi' ? 1 : 0);
                    }
                }
            }
        }
    }

    // test item 7 of full demo: that if an invoice has been in E_VALID for less than 48 hours,
    // on forcing delivery it will be delivered to the recipient specified in the
    // `FatturaElettronica.FatturaElettronicaHeader.DatiTrasmissione.CodiceDestinatario` field
    // of the invoice XML; on successful delivery, the invoice will be moved to E_DELIVERED status
    // and all other invoice queues will be empty except I_TRANSMITTED for td000000x
    public function testDeliver3()
    {
        $xml = $this->getValidInvoice();
        foreach (self::$tds as $issuer) {
            foreach (self::$tds as $recipient) {
                if ($recipient != $issuer) {
                    $timeDelta = '6 h';
                    $this->deliver($xml, $issuer, $recipient, $timeDelta);

                    $this->expectedInvoices("td$issuer", 1, 'I_TRANSMITTED');
                    $this->expectedInvoices("sdi", 1, 'E_DELIVERED');
                    foreach (self::$actors as $actor) {
                        $this->expectedInvoices($actor, $actor == "td$issuer" || $actor == 'sdi' ? 1 : 0);
                        $this->expectedNotifications($actor, $actor == 'sdi' ? 1 : 0);
                    }
                }
            }
        }
    }

    // test item 7 of full demo: that if an invoice has been in E_FAILED_DELIVERY for less than
    // 48 hours + 10 days, on forcing delivery it will be delivered to the recipient specified in
    // the `FatturaElettronica.FatturaElettronicaHeader.DatiTrasmissione.CodiceDestinatario` field
    // of the invoice XML; on successful delivery, the invoice will be moved to E_DELIVERED status
    // and all other invoice queues will be empty except I_TRANSMITTED for td000000x
    public function testDeliver4()
    {
        $xml = $this->getValidInvoice();
        foreach (self::$tds as $issuer) {
            foreach (self::$tds as $recipient) {
                if ($recipient != $issuer) {
                    $timeDelta = '48 hours';
                    $this->deliver($xml, $issuer, $recipient, $timeDelta, true);

                    $this->expectedInvoices("td$issuer", 1, 'I_TRANSMITTED');
                    $this->expectedInvoices("sdi", 1, 'E_FAILED_DELIVERY');
                    foreach (self::$actors as $actor) {
                        $this->expectedInvoices($actor, $actor == "td$issuer" || $actor == 'sdi' ? 1 : 0);
                        $this->expectedNotifications($actor, $actor == 'sdi' ? 1 : 0);
                    }

                    $timeDelta = '9 days';
                    $this->advanceActors($timeDelta);
                    $this->advanceActors('1 hour');
                    $response = $this->client->post("sdi/rpc/deliver");
                    $this->assertEquals(200, $response->getStatusCode());
            
                    $this->expectedInvoices("td$issuer", 1, 'I_TRANSMITTED');
                    $this->expectedInvoices("sdi", 1, 'E_DELIVERED');
                    foreach (self::$actors as $actor) {
                        $this->expectedInvoices($actor, $actor == "td$issuer" || $actor == 'sdi' ? 1 : 0);
                        $this->expectedNotifications($actor, $actor == 'sdi' ? 2 : 0);
                    }
                }
            }
        }
    }

    // test items 8 and 9 of full demo: that if an invoice has been delivered, on forcing dispatch 
    // from the sdi, the invoice will be moved to I_DELIVERED status for td000000x
    public function testDispatch()
    {
        $xml = $this->getValidInvoice();
        foreach (self::$tds as $issuer) {
            foreach (self::$tds as $recipient) {
                if ($recipient != $issuer) {
                    $timeDelta = '6 h';
                    $this->deliver($xml, $issuer, $recipient, $timeDelta);

                    $response = $this->client->post("sdi/rpc/dispatch");
                    $this->assertEquals(200, $response->getStatusCode());

                    $this->expectedInvoices("td$issuer", 1, 'I_DELIVERED');
                    $this->expectedInvoices("sdi", 1, 'E_DELIVERED');
                    foreach (self::$actors as $actor) {
                        $this->expectedInvoices($actor, $actor == "td$issuer" || $actor == 'sdi' ? 1 : 0);
                        $this->expectedNotifications($actor, $actor == "td$issuer" || $actor == 'sdi' ? 1 : 0);
                    }
                }
            }
        }
    }

    public function tearDown()
    {
        $this->client = null;
    }
}
