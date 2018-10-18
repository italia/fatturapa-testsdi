<?php
declare(strict_types=1);

require_once(__DIR__ . "/../vendor/autoload.php");
require_once(__DIR__ . "/../core/config.php");

final class FullDemo extends PHPUnit\Framework\TestCase
{
    protected $client;
    protected $actors;
    protected $tds;
    protected $issuers; // 1-N relationship between issuer and cedente

    // initialize tests
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

    private function getCedente($issuer)
    {
        // echo "issuer = $issuer" . PHP_EOL;
        // echo "issuers = " . print_r($this->issuers[$issuer], true) . PHP_EOL;
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

    private function getValidInvoice($issuer, $recipient)
    {
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

    private function getInvalidInvoice()
    {
        $filename = 'tests/samples/invoices/missing_CedentePrestatore.xml';
        return $this->getInvoice($filename);
    }

    private function clearActors()
    {
        foreach ($this->actors as $actor) {
            echo "clear actor $actor" . PHP_EOL;
            $response = $this->client->post("$actor/rpc/clear");
            $this->assertEquals(200, $response->getStatusCode());
        }
    }

    private function freezeActors($datetime)
    {
        foreach ($this->actors as $actor) {
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
        $timeStampString = $response->getBody()->getContents();
        $timeStampString=json_decode($timeStampString);
                            
        $timestamp = strtotime("+$timeDelta", $timeStampString->timestamp);
        $datetime = new DateTime();
        $datetime->setTimestamp($timestamp);
        $timestamp = $datetime->format(\DateTime::ATOM);
        foreach ($this->actors as $actor) {
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
        foreach ($this->actors as $actor) {
            $this->expectedInvoices($actor, 0);
            $this->expectedNotifications($actor, 0);
        }
    }

    public function testUploadDummyContent()
    {
        $invoice = '<xml></<xml>'; // dummy invoice
        foreach ($this->tds as $issuer) {
            $this->clearActors();
            $response = $this->client->post("td$issuer/rpc/upload", [
                'multipart' => [
                    [
                        'name'     => 'File',
                        'contents' => $invoice,
                        'filename' => 'aaa.xml'
                    ]
                ]
            ]);
            $this->assertEquals(400, $response->getStatusCode());
        }
    }

    public function testUploadWrongFileExtension()
    {
        $invoice = $this->getValidInvoice($this->tds[0], $this->tds[1]);
        foreach ($this->tds as $issuer) {
            $this->clearActors();
            $response = $this->client->post("td$issuer/rpc/upload", [
                'multipart' => [
                    [
                        'name'     => 'File',
                        'contents' => $invoice,
                        'filename' => 'aaa.bbb'
                    ]
                ]
            ]);
            $this->assertEquals(400, $response->getStatusCode());
        }
    }

    // test item 2 of full demo: that after clearing the DB, and uploading a sample invoice
    // for td000000x, I will find it in the I_UPLOADED queue for td000000x
    // while all other queues will be empty
    public function testUpload()
    {
        foreach ($this->tds as $issuer) {
            $this->clearActors();
            $invoice = $this->getValidInvoice($issuer, $this->tds[0] == $issuer ? $this->tds[1] : $this->tds[0]);
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

            $this->expectedInvoices("td$issuer", 1, 'I_UPLOADED');
            foreach ($this->actors as $actor) {
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
        foreach ($this->tds as $issuer) {
            $this->clearActors();
            $invoice = $this->getValidInvoice($issuer, $this->tds[0] == $issuer ? $this->tds[1] : $this->tds[0]);
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
            //echo ">>"."td$issuer/rpc/transmit";
            $response = $this->client->post("td$issuer/rpc/transmit");
            
        
            //echo $response->getBody();
            $this->assertEquals(200, $response->getStatusCode());

            $this->expectedInvoices("td$issuer", 1, 'I_TRANSMITTED');
            $this->expectedInvoices("sdi", 1, 'E_RECEIVED');
            foreach ($this->actors as $actor) {
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
        $invoice = $this->getInvalidInvoice();
        foreach ($this->tds as $issuer) {
            $this->clearActors();
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

            $this->expectedInvoices("td$issuer", 1, 'I_TRANSMITTED');
            $this->expectedInvoices("sdi", 1, 'E_INVALID');
            foreach ($this->actors as $actor) {
                $this->expectedInvoices($actor, $actor == "td$issuer" || $actor == 'sdi' ? 1 : 0);
                // sdi has a N_PENDING NotificaScarto notification for Issuer
                $this->expectedNotifications($actor, $actor == 'sdi' ? 1 : 0);
            }
        }
    }

    // test item 6 of full demo: that after forcing ES to check validity, valid invoices will end
    // up in the E_VALID status, while and all other invoice queues will be empty except
    // I_TRANSMITTED for td000000x
    public function testCheckValidity2()
    {
        foreach ($this->tds as $issuer) {
            $this->clearActors();
            $invoice = $this->getValidInvoice($issuer, $this->tds[0] == $issuer ? $this->tds[1] : $this->tds[0]);
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
            //echo $response->getBody();
            $this->assertEquals(200, $response->getStatusCode());

            $this->expectedInvoices("td$issuer", 1, 'I_TRANSMITTED');
            $this->expectedInvoices("sdi", 1, 'E_VALID');
            foreach ($this->actors as $actor) {
                $this->expectedInvoices($actor, $actor == "td$issuer" || $actor == 'sdi' ? 1 : 0);
                $this->expectedNotifications($actor, 0);
            }
        }
    }

    private function deliver($issuer, $recipient, $timeDelta, $dummy = false)
    {
        $this->clearActors();
        $this->freezeActors('2019-07-01T12:00Z');
        $invoice = $this->getValidInvoice($issuer, $recipient);
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
        //$this->advanceActors('1 hour');
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
        foreach ($this->tds as $issuer) {
            foreach ($this->tds as $recipient) {
                if ($recipient != $issuer) {
                    $timeDelta = '48 hours';
                    $this->deliver($issuer, $recipient, $timeDelta, true);

                    $this->expectedInvoices("td$issuer", 1, 'I_TRANSMITTED');
                    $this->expectedInvoices("sdi", 1, 'E_FAILED_DELIVERY');
                                        
                    foreach ($this->actors as $actor) {
                        $this->expectedInvoices($actor, $actor == "td$issuer" || $actor == 'sdi' ? 1 : 0);
                        // sdi has a N_PENDING NotificaMancataConsegna notification for issuer
                        $this->expectedNotifications($actor, $actor == 'sdi' ? 1 : 0);
                    }

                    $timeDelta = '10 days';
                    $this->advanceActors($timeDelta);
                    //$this->advanceActors('1 hour');
                    $response = $this->client->post("sdi/rpc/deliver?dummy");
                    $this->assertEquals(200, $response->getStatusCode());

                    $this->expectedInvoices("td$issuer", 1, 'I_TRANSMITTED');
                    $this->expectedInvoices("sdi", 1, 'E_IMPOSSIBLE_DELIVERY');
                    foreach ($this->actors as $actor) {
                        $this->expectedInvoices($actor, $actor == "td$issuer" || $actor == 'sdi' ? 1 : 0);
                        // sdi has a N_OBSOLETE NotificaMancataConsegna notification for issuer
                        // and a N_PENDING NotificaDecorrenzaTermini notification for issuer
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
        foreach ($this->tds as $issuer) {
            foreach ($this->tds as $recipient) {
                if ($recipient != $issuer) {
                    $timeDelta = '12 days';
                    $this->deliver($issuer, $recipient, $timeDelta);

                    $this->expectedInvoices("td$issuer", 1, 'I_TRANSMITTED');
                    $this->expectedInvoices("sdi", 1, 'E_IMPOSSIBLE_DELIVERY');
                    foreach ($this->actors as $actor) {
                        $this->expectedInvoices($actor, $actor == "td$issuer" || $actor == 'sdi' ? 1 : 0);
                        // sdi has a N_PENDING NotificaDecorrenzaTermini notification for issuer
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
    // and all other invoice queues will be empty except I_TRANSMITTED for td000000x and
    // R_RECEIVED for td000000y
    public function testDeliver3()
    {
        foreach ($this->tds as $issuer) {
            foreach ($this->tds as $recipient) {
                if ($recipient != $issuer) {
                    $timeDelta = '6 hours';
                    $this->deliver($issuer, $recipient, $timeDelta);

                    $this->expectedInvoices("td$issuer", 1, 'I_TRANSMITTED');
                    $this->expectedInvoices("sdi", 1, 'E_DELIVERED');
                    $this->expectedInvoices("td$recipient", 1, 'R_RECEIVED');
                    foreach ($this->actors as $actor) {
                        $this->expectedInvoices(
                            $actor,
                            $actor == "td$issuer" || $actor == 'sdi' || $actor == "td$recipient" ? 1 : 0
                        );
                        // sdi has N_PENDING AttestazioneTrasmissioneFattura notification for issuer
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
    // and all other invoice queues will be empty except I_TRANSMITTED for td000000x and R_RECEIVED
    // for td000000y
    public function testDeliver4()
    {
        foreach ($this->tds as $issuer) {
            foreach ($this->tds as $recipient) {
                if ($recipient != $issuer) {
                    $timeDelta = '48 hours';
                    $this->deliver($issuer, $recipient, $timeDelta, true);

                    $this->expectedInvoices("td$issuer", 1, 'I_TRANSMITTED');
                    $this->expectedInvoices("sdi", 1, 'E_FAILED_DELIVERY');
                    foreach ($this->actors as $actor) {
                        $this->expectedInvoices($actor, $actor == "td$issuer" || $actor == 'sdi' ? 1 : 0);
                        // sdi has a N_PENDING NotificaMancataConsegna notification for issuer
                        $this->expectedNotifications($actor, $actor == 'sdi' ? 1 : 0);
                    }

                    $timeDelta = '9 days';
                    $this->advanceActors($timeDelta);
                    //$this->advanceActors('1 hour');
                    $response = $this->client->post("sdi/rpc/deliver");
                    $this->assertEquals(200, $response->getStatusCode());
            
                    $this->expectedInvoices("td$issuer", 1, 'I_TRANSMITTED');
                    $this->expectedInvoices("sdi", 1, 'E_DELIVERED');
                    $this->expectedInvoices("td$recipient", 1, 'R_RECEIVED');
                    foreach ($this->actors as $actor) {
                        $this->expectedInvoices(
                            $actor,
                            $actor == "td$issuer" || $actor == 'sdi' || $actor == "td$recipient" ? 1 : 0
                        );
                        // sdi has a N_OBSOLETE NotificaMancataConsegna notification for issuer
                        // and a N_PENDING AttestazioneTrasmissioneFattura notification for issuer
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
        foreach ($this->tds as $issuer) {
            foreach ($this->tds as $recipient) {
                if ($recipient != $issuer) {
                    $timeDelta = '6 hours';
                    $this->deliver($issuer, $recipient, $timeDelta);

                    $response = $this->client->post("sdi/rpc/dispatch");
                    $this->assertEquals(200, $response->getStatusCode());

                    usleep(100000);
                    $this->expectedInvoices("td$issuer", 1, 'I_DELIVERED');
                    $this->expectedInvoices("sdi", 1, 'E_DELIVERED');
                    $this->expectedInvoices("td$recipient", 1, 'R_RECEIVED');
                    foreach ($this->actors as $actor) {
                        $this->expectedInvoices(
                            $actor,
                            $actor == "td$issuer" || $actor == 'sdi' || $actor == "td$recipient" ? 1 : 0
                        );
                        // sdi has a AttestazioneTrasmissioneFattura notification N_DELIVERED to issuer
                        // issuer has a N_RECEIVED AttestazioneTrasmissioneFattura notification from sdi
                        $this->expectedNotifications($actor, $actor == "td$issuer" || $actor == 'sdi' ? 1 : 0);
                    }
                }
            }
        }
    }

    // test item 10 of full demo: status after an invoice has been accepted by the recipient
    public function testAccept1()
    {
        foreach ($this->tds as $issuer) {
            foreach ($this->tds as $recipient) {
                if ($recipient != $issuer) {
                    $timeDelta = '6 hours';
                    $this->deliver($issuer, $recipient, $timeDelta);

                    $response = $this->client->post("sdi/rpc/dispatch");
                    $this->assertEquals(200, $response->getStatusCode());
                    $invoices = $this->getTable("td$recipient", 'invoices');
                    $id = $invoices[0]['id'];
                    $response = $this->client->post("td$recipient/rpc/accept/$id");
                    $this->assertEquals(200, $response->getStatusCode());

                    usleep(100000);
                    $this->expectedInvoices("td$issuer", 1, 'I_DELIVERED');
                    $this->expectedInvoices("sdi", 1, 'E_DELIVERED');
                    $this->expectedInvoices("td$recipient", 1, 'R_ACCEPTED');
                    foreach ($this->actors as $actor) {
                        $this->expectedInvoices(
                            $actor,
                            $actor == "td$issuer" || $actor == 'sdi' || $actor == "td$recipient" ? 1 : 0
                        );
                        // sdi has a AttestazioneTrasmissioneFattura notification N_DELIVERED to issuer
                        // issuer has a N_RECEIVED AttestazioneTrasmissioneFattura notification from sdi
                        // recipient has a N_PENDING NotificaEsito notification for sdi
                        $this->expectedNotifications(
                            $actor,
                            $actor == "td$issuer" || $actor == 'sdi' || $actor == "td$recipient" ? 1 : 0
                        );
                    }
                }
            }
        }
    }

    // test item 11 and 12 of full demo: status after an invoice has been accepted by the recipient
    // and notified back to sdi
    public function testAccept2()
    {
        foreach ($this->tds as $issuer) {
            foreach ($this->tds as $recipient) {
                if ($recipient != $issuer) {
                    $timeDelta = '6 hours';
                    $this->deliver($issuer, $recipient, $timeDelta);

                    $response = $this->client->post("sdi/rpc/dispatch");
                    $this->assertEquals(200, $response->getStatusCode());
                    
                    $invoices = $this->getTable("td$recipient", 'invoices');
                    $id = $invoices[0]['id'];
                    $response = $this->client->post("td$recipient/rpc/accept/$id");
                    $this->assertEquals(200, $response->getStatusCode());

                    $response = $this->client->post("td$recipient/rpc/dispatch");
                    $this->assertEquals(200, $response->getStatusCode());

                    usleep(100000);
                    $this->expectedInvoices("td$issuer", 1, 'I_DELIVERED');
                    $this->expectedInvoices("sdi", 1, 'E_ACCEPTED');
                    $this->expectedInvoices("td$recipient", 1, 'R_ACCEPTED');
                    foreach ($this->actors as $actor) {
                        $this->expectedInvoices(
                            $actor,
                            $actor == "td$issuer" || $actor == 'sdi' || $actor == "td$recipient" ? 1 : 0
                        );
                        // sdi has a AttestazioneTrasmissioneFattura notification N_DELIVERED to issuer
                        // issuer has a N_RECEIVED AttestazioneTrasmissioneFattura notification from sdi
                        // recipient has a NotificaEsito notification N_DELIVERED to sdi
                        // sdi has a N_RECEIVED NotificaEsito notification from recipient
                        // sdi has a N_PENDING NotificaEsito notification for issuer
                        $this->expectedNotifications(
                            $actor,
                            $actor == 'sdi' ? 3 : ($actor == "td$issuer" || $actor == "td$recipient" ? 1 : 0)
                        );
                    }
                }
            }
        }
    }

    // test item 13 and 14 of full demo: status after an invoice has been accepted by the recipient
    // and notified back to sdi and to issuer
    public function testAccept3()
    {
        foreach ($this->tds as $issuer) {
            foreach ($this->tds as $recipient) {
                if ($recipient != $issuer) {
                    $timeDelta = '6 hours';
                    $this->deliver($issuer, $recipient, $timeDelta);

                    $response = $this->client->post("sdi/rpc/dispatch");
                    $this->assertEquals(200, $response->getStatusCode());
                    
                    $invoices = $this->getTable("td$recipient", 'invoices');
                    $id = $invoices[0]['id'];
                    $response = $this->client->post("td$recipient/rpc/accept/$id");
                    $this->assertEquals(200, $response->getStatusCode());

                    $response = $this->client->post("td$recipient/rpc/dispatch");
                    $this->assertEquals(200, $response->getStatusCode());
                    $response = $this->client->post("sdi/rpc/dispatch");
                    $this->assertEquals(200, $response->getStatusCode());

                    usleep(100000);
                    $this->expectedInvoices("td$issuer", 1, 'I_ACCEPTED');
                    $this->expectedInvoices("sdi", 1, 'E_ACCEPTED');
                    $this->expectedInvoices("td$recipient", 1, 'R_ACCEPTED');
                    foreach ($this->actors as $actor) {
                        $this->expectedInvoices(
                            $actor,
                            $actor == "td$issuer" || $actor == 'sdi' || $actor == "td$recipient" ? 1 : 0
                        );
                        // sdi has a AttestazioneTrasmissioneFattura notification N_DELIVERED to issuer
                        // issuer has a N_RECEIVED AttestazioneTrasmissioneFattura notification from sdi
                        // recipient has a NotificaEsito notification N_DELIVERED to sdi
                        // sdi has a N_RECEIVED NotificaEsito notification from recipient
                        // sdi has a NotificaEsito notification N_DELIVERED to issuer
                        // issuer has a N_RECEIVED NotificaEsito notification from sdi
                        $this->expectedNotifications(
                            $actor,
                            $actor == 'sdi' ? 3 : ($actor == "td$issuer" ? 2 : ($actor == "td$recipient" ? 1 : 0))
                        );
                    }
                }
            }
        }
    }

    // status after an invoice has been refused by the recipient
    public function testRefuse1()
    {
        foreach ($this->tds as $issuer) {
            foreach ($this->tds as $recipient) {
                if ($recipient != $issuer) {
                    $timeDelta = '6 hours';
                    $this->deliver($issuer, $recipient, $timeDelta);

                    $response = $this->client->post("sdi/rpc/dispatch");
                    $this->assertEquals(200, $response->getStatusCode());
                    $invoices = $this->getTable("td$recipient", 'invoices');
                    $id = $invoices[0]['id'];
                    $response = $this->client->post("td$recipient/rpc/refuse/$id");
                    $this->assertEquals(200, $response->getStatusCode());

                    usleep(100000);
                    $this->expectedInvoices("td$issuer", 1, 'I_DELIVERED');
                    $this->expectedInvoices("sdi", 1, 'E_DELIVERED');
                    $this->expectedInvoices("td$recipient", 1, 'R_REFUSED');
                    foreach ($this->actors as $actor) {
                        $this->expectedInvoices(
                            $actor,
                            $actor == "td$issuer" || $actor == 'sdi' || $actor == "td$recipient" ? 1 : 0
                        );
                        // sdi has a AttestazioneTrasmissioneFattura notification N_DELIVERED to issuer
                        // issuer has a N_RECEIVED AttestazioneTrasmissioneFattura notification from sdi
                        // recipient has a N_PENDING NotificaEsito notification for sdi
                        $this->expectedNotifications(
                            $actor,
                            $actor == "td$issuer" || $actor == 'sdi' || $actor == "td$recipient" ? 1 : 0
                        );
                    }
                }
            }
        }
    }

    // status after an invoice has been refused by the recipient and notified back to sdi
    public function testRefuse2()
    {
        foreach ($this->tds as $issuer) {
            foreach ($this->tds as $recipient) {
                if ($recipient != $issuer) {
                    $timeDelta = '6 hours';
                    $this->deliver($issuer, $recipient, $timeDelta);

                    $response = $this->client->post("sdi/rpc/dispatch");
                    $this->assertEquals(200, $response->getStatusCode());
                    
                    $invoices = $this->getTable("td$recipient", 'invoices');
                    $id = $invoices[0]['id'];
                    $response = $this->client->post("td$recipient/rpc/refuse/$id");
                    $this->assertEquals(200, $response->getStatusCode());

                    $response = $this->client->post("td$recipient/rpc/dispatch");
                    $this->assertEquals(200, $response->getStatusCode());

                    usleep(100000);
                    $this->expectedInvoices("td$issuer", 1, 'I_DELIVERED');
                    $this->expectedInvoices("sdi", 1, 'E_REFUSED');
                    $this->expectedInvoices("td$recipient", 1, 'R_REFUSED');
                    foreach ($this->actors as $actor) {
                        $this->expectedInvoices(
                            $actor,
                            $actor == "td$issuer" || $actor == 'sdi' || $actor == "td$recipient" ? 1 : 0
                        );
                        // sdi has a AttestazioneTrasmissioneFattura notification N_DELIVERED to issuer
                        // issuer has a N_RECEIVED AttestazioneTrasmissioneFattura notification from sdi
                        // recipient has a NotificaEsito notification N_DELIVERED to sdi
                        // sdi has a N_RECEIVED NotificaEsito notification from recipient
                        // sdi has a N_PENDING NotificaEsito notification for issuer
                        $this->expectedNotifications(
                            $actor,
                            $actor == 'sdi' ? 3 : ($actor == "td$issuer" || $actor == "td$recipient" ? 1 : 0)
                        );
                    }
                }
            }
        }
    }

    // status after an invoice has been refused by the recipient and notified back to sdi and to issuer
    public function testRefuse3()
    {
        foreach ($this->tds as $issuer) {
            foreach ($this->tds as $recipient) {
                if ($recipient != $issuer) {
                    $timeDelta = '6 hours';
                    $this->deliver($issuer, $recipient, $timeDelta);

                    $response = $this->client->post("sdi/rpc/dispatch");
                    $this->assertEquals(200, $response->getStatusCode());
                    
                    $invoices = $this->getTable("td$recipient", 'invoices');
                    $id = $invoices[0]['id'];
                    $response = $this->client->post("td$recipient/rpc/refuse/$id");
                    $this->assertEquals(200, $response->getStatusCode());

                    $response = $this->client->post("td$recipient/rpc/dispatch");
                    $this->assertEquals(200, $response->getStatusCode());

                    $response = $this->client->post("sdi/rpc/dispatch");
                    $this->assertEquals(200, $response->getStatusCode());

                    usleep(100000);
                    $this->expectedInvoices("td$issuer", 1, 'I_REFUSED');
                    $this->expectedInvoices("sdi", 1, 'E_REFUSED');
                    $this->expectedInvoices("td$recipient", 1, 'R_REFUSED');
                    foreach ($this->actors as $actor) {
                        $this->expectedInvoices(
                            $actor,
                            $actor == "td$issuer" || $actor == 'sdi' || $actor == "td$recipient" ? 1 : 0
                        );
                        // sdi has a AttestazioneTrasmissioneFattura notification N_DELIVERED to issuer
                        // issuer has a N_RECEIVED AttestazioneTrasmissioneFattura notification from sdi
                        // recipient has a NotificaEsito notification N_DELIVERED to sdi
                        // sdi has a N_RECEIVED NotificaEsito notification from recipient
                        // sdi has a NotificaEsito notification N_DELIVERED to issuer
                        // issuer has a N_RECEIVED NotificaEsito notification from sdi
                        $this->expectedNotifications(
                            $actor,
                            $actor == 'sdi' ? 3 : ($actor == "td$issuer" ? 2 : ($actor == "td$recipient" ? 1 : 0))
                        );
                    }
                }
            }
        }
    }

    // status after an invoice has been in the E_DELIVERED for more than 15 days and checkExpiration is called
    public function testCheckExpiration1()
    {
        foreach ($this->tds as $issuer) {
            foreach ($this->tds as $recipient) {
                if ($recipient != $issuer) {
                    $timeDelta = '6 hours';
                    $this->deliver($issuer, $recipient, $timeDelta);

                    $this->advanceActors('15 days');
                    $response = $this->client->post("sdi/rpc/checkExpiration");
                    $this->assertEquals(200, $response->getStatusCode());

                    usleep(100000);
                    $this->expectedInvoices("td$issuer", 1, 'I_TRANSMITTED');
                    $this->expectedInvoices("sdi", 1, 'E_EXPIRED');
                    $this->expectedInvoices("td$recipient", 1, 'R_RECEIVED');
                    foreach ($this->actors as $actor) {
                        $this->expectedInvoices(
                            $actor,
                            $actor == "td$issuer" || $actor == 'sdi' || $actor == "td$recipient" ? 1 : 0
                        );
                        // sdi has an N_OBSOLETE AttestazioneTrasmissioneFattura notification
                        // and an N_PENDING NotificaDecorrenzaTermini notification
                        $this->expectedNotifications($actor, $actor == 'sdi' ? 2 : 0);
                    }
                }
            }
        }
    }

    // status after an invoice has been in the E_DELIVERED for more than 15 days and both checkExpiration
    // and dispatch are called
    public function testCheckExpiration2()
    {
        foreach ($this->tds as $issuer) {
            foreach ($this->tds as $recipient) {
                if ($recipient != $issuer) {
                    $timeDelta = '6 hours';
                    $this->deliver($issuer, $recipient, $timeDelta);

                    $this->advanceActors('15 days');

                    $response = $this->client->post("sdi/rpc/checkExpiration");
                    $this->assertEquals(200, $response->getStatusCode());
                    $response = $this->client->post("sdi/rpc/dispatch");
                    $this->assertEquals(200, $response->getStatusCode());
                    echo $response->getBody();

                    usleep(100000);
                    $this->expectedInvoices("td$issuer", 1, 'I_EXPIRED');
                    $this->expectedInvoices("sdi", 1, 'E_EXPIRED');
                    $this->expectedInvoices("td$recipient", 1, 'R_EXPIRED');
                    foreach ($this->actors as $actor) {
                        $this->expectedInvoices(
                            $actor,
                            $actor == "td$issuer" || $actor == 'sdi' || $actor == "td$recipient" ? 2 : 0
                        );
                        // sdi has an N_OBSOLETE AttestazioneTrasmissioneFattura notification
                        // and an N_DELIVERED NotificaDecorrenzaTermini notification
                        // issuer and recipient both have a N_RECEIVED  NotificaDecorrenzaTermini notification
                        $this->expectedNotifications(
                            $actor,
                            $actor == 'sdi' ? 2 : ($actor == "td$issuer" || $actor == "td$recipient" ? 1 : 0)
                        );
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
