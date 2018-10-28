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
}
