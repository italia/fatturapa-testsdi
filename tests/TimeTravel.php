<?php
declare(strict_types=1);

require_once(__DIR__ . "/../vendor/autoload.php");
require_once(__DIR__ . "/../core/config.php");

final class TimeTravel extends PHPUnit\Framework\TestCase
{
    protected $client;

    protected function setUp()
    {
        $this->client = new GuzzleHttp\Client([
            'base_uri' => HOSTMAIN,
            'http_errors' => false
        ]);
    }
    
    public function testTimeTravel()
    {
        // can clear sdi
        $response = $this->client->post('/sdi/rpc/clear');
        $this->assertEquals(200, $response->getStatusCode());

        // can not set a negative speed
        $response = $this->client->post('/sdi/rpc/speed', ['query' => ['speed' => -1]]);
        $this->assertEquals(400, $response->getStatusCode());
        
        // can set speed to 0
        $response = $this->client->post('/sdi/rpc/speed', ['query' => ['speed' => 0]]);
        $this->assertEquals(200, $response->getStatusCode());

        // can set date time to a fixed value, and retrieve it back
        $datetime = '2019-01-01T12:00Z';
        $response = $this->client->post('/sdi/rpc/timestamp', ['query' => ['timestamp' => $datetime]]);
        $this->assertEquals(200, $response->getStatusCode());
        $response = $this->client->get('/sdi/rpc/datetime');
        $this->assertEquals(200, $response->getStatusCode());
        $body = $response->getBody();
        $this->assertEquals('timestamp: 2019-01-01 12:00:00.000000', $response->getBody()->getContents());
    }

    public function tearDown()
    {
        $this->client = null;
    }
}
