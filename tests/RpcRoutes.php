<?php
declare(strict_types=1);

require_once(__DIR__ . "/../vendor/autoload.php");
require_once(__DIR__ . "/../core/config.php");

final class RpcRoutes extends PHPUnit\Framework\TestCase
{
    protected $client;

    // initialize tests
    protected function setUp()
    {
        $this->client = new GuzzleHttp\Client([
            'base_uri' => HOSTMAIN,
            'http_errors' => false
        ]);
    }

    // make sure that the supplied route responds with 404 to both GET and POST
    protected function noGetNoPost($route)
    {
        $response = $this->client->get($route);
        $this->assertEquals(404, $response->getStatusCode());
        $response = $this->client->post($route);
        $this->assertEquals(404, $response->getStatusCode());
    }

    // make sure that the supplied route responds to GET with 405 but do not respond to POST with 404 or 405
    protected function onlyPost($route)
    {
        $response = $this->client->get($route);
        $this->assertEquals(405, $response->getStatusCode());
        $response = $this->client->post($route);
        $this->assertNotEquals(404, $response->getStatusCode());
        $this->assertNotEquals(405, $response->getStatusCode());
    }

    // make sure that the supplied route responds to POST with 405 but do not respond to GET with 404 or 405
    protected function onlyGet($route)
    {
        $response = $this->client->post($route);
        $this->assertEquals(405, $response->getStatusCode());
        $response = $this->client->get($route);
        $this->assertNotEquals(404, $response->getStatusCode());
        $this->assertNotEquals(405, $response->getStatusCode());
    }

    // make sure that the supplied actor has all the non-actor-specific endpoint
    private function hasCommon($actor)
    {
        $this->onlyPost("/$actor/rpc/clear");
        $this->onlyGet("/$actor/rpc/datetime");
        $this->onlyPost("/$actor/rpc/timestamp");
        $this->onlyPost("/$actor/rpc/speed");
        $this->onlyGet("/$actor/rpc/notifications");
        $this->onlyPost("/$actor/rpc/dispatch");
        $this->onlyGet("/$actor/rpc/invoices");
    }

    // we do have the endpoints that we should have
    public function testSdiHasCommon()
    {
        $this->hasCommon('sdi');
    }
    public function testTdHasCommon()
    {
        $this->hasCommon('td0000001');
    }

    // make sure issuer-specific methods respond only to POST
    public function testIssuerHasUpload()
    {
        $this->onlyPost('/td0000001/rpc/upload');
    }
    public function testIssuerHasTransmit()
    {
        $this->onlyPost('/td0000001/rpc/transmit');
    }

    // test that exchanger-specific methods respond to GET with 405 but do not respond to POST with 404 or 405
    public function testExchangerHasCheckValidity()
    {
        $this->onlyPost('/sdi/rpc/checkValidity');
    }
    public function testExchangerHasDeliver()
    {
        $this->onlyPost('/sdi/rpc/deliver');
    }
    public function testExchangerHasCheckExpiration()
    {
        $this->onlyPost('/sdi/rpc/checkExpiration');
    }

    // make sure recipient-specific methods respond only to POST
    public function testRecipientHasAccept()
    {
        $this->onlyPost('/td0000001/rpc/accept');
    }
    public function testRecipientHasRefuse()
    {
        $this->onlyPost('/td0000001/rpc/refuse');
    }

    // we don't have endpoints we should not have

    // issuer-specific routes should fail against sdi
    public function testSdiHasNoUpload()
    {
        $this->noGetNoPost('/sdi/rpc/upload');
    }
    public function testSdiHasNoTransmit()
    {
        $this->noGetNoPost('/sdi/rpc/transmit');
    }

    // recipient-specific routes should fail against sdi
    public function testSdiHasNoAccept()
    {
        $this->noGetNoPost('/sdi/rpc/accept');
    }
    public function testSdiHasNoRefuse()
    {
        $this->noGetNoPost('/sdi/rpc/refuse');
    }

    // exchanger-specific routes should fail against td*
    public function testTdHasNoValidity()
    {
        $this->noGetNoPost('/td0000001/rpc/checkValidity');
    }
    public function testTdHasNoDeliver()
    {
        $this->noGetNoPost('/td0000001/rpc/deliver');
    }
    public function testTdHasNoCheckExpiration()
    {
        $this->noGetNoPost('/td0000001/rpc/checkExpiration');
    }

    public function tearDown()
    {
        $this->client = null;
    }
}
