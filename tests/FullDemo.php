<?php
declare(strict_types=1);

require_once(__DIR__ . "/../vendor/autoload.php");

final class FullDemo extends PHPUnit\Framework\TestCase
{
    protected $client;

    protected static $actors = ['sdi', 'td0000001', 'td0000002'];
    protected static $tds = ['td0000001', 'td0000002'];

    // initialize tests
    protected function setUp()
    {
        $this->client = new GuzzleHttp\Client([
            'base_uri' => 'http://teamdigitale3.simevo.com',
            'http_errors' => false
        ]);
    }

    private function getTable($actor, $table, $status = '')
    {
        echo "getting table $table for actor $actor";
        $url = "/$actor/rpc/$table";
        if ($status != '') {
            $url = $url . "/?status=$status";
        }
        $response = $this->client->get($url);
        $this->assertEquals(200, $response->getStatusCode());
        $body = $response->getBody();
        $contents = $body->getContents();
        $obj = json_decode($contents);
        var_dump($obj);
        $this->assertObjectHasAttribute($table, $obj);
        return $obj->$table;
    }

    private function clearActors()
    {
        foreach (self::$actors as $actor) {
            echo "clear actor $actor";
            $response = $this->client->post("/$actor/rpc/clear");
            $this->assertEquals(200, $response->getStatusCode());
        }
    }

    // test item 1 of full demo: that after clearing the DB, all invoice and notification queues are empty
    public function testClearDoesClearQueues()
    {
        $this->clearActors();
        foreach (self::$actors as $actor) {
            $this->assertEquals(0, sizeof($this->getTable($actor, 'invoices')));
            $this->assertEquals(0, sizeof($this->getTable($actor, 'notifications')));
        }
    }

    // test item 2 of full demo: that after clearing the DB, and uploading a sample invoice
    // for td000001, I will find it in the I_UPLOADED queue for td0000001
    // and all other queues are empy
    public function testUpload()
    {
        $this->clearActors();
        foreach (self::$tds as $td) {
            $xml = '<xml></<xml>';
            $response = $this->client->post("/$td/rpc/upload", [
                'multipart' => [
                    [
                        'name'     => 'File',
                        'contents' => $xml,
                        'filename' => 'aaa.xml'
                    ]
                ]
            ]);
            $this->assertEquals(200, $response->getStatusCode());
            foreach (self::$actors as $actor) {
                $expectedInvoices = ($actor == $td ? 1 : 0);
                $this->assertEquals($expectedInvoices, sizeof($this->getTable($actor, 'invoices')));
                $this->assertEquals(0, sizeof($this->getTable($actor, 'notifications')));
            }
            $uploaded_invoices = $this->getTable($td, 'invoices', 'I_UPLOADED');
            $this->assertEquals(1, sizeof($uploaded_invoices));
        }
    }

    public function tearDown()
    {
        $this->client = null;
    }
}
