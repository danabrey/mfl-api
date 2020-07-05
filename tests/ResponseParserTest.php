<?php

use DanAbrey\MFLApi\Models\MFLLeague;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class ResponseParserTest extends TestCase
{
    private \DanAbrey\MFLApi\ResponseParser $responseParser;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->responseParser = new \DanAbrey\MFLApi\ResponseParser();
    }

    public function testLeague()
    {
        $data = file_get_contents(__DIR__ . '/_data/league/1qb_without_apikey.json');
        $responses = [
            new MockResponse($data),
        ];
        $client = new MockHttpClient($responses);
        $league = $this->responseParser->league($client, 'https://api.myfantasyleague.com/2020/export?TYPE=league&L=xxxxx&APIKEY=&JSON=1');
        $this->assertInstanceOf(MFLLeague::class, $league);
        $this->assertEquals('11747', $league->id);
        $this->assertEquals('TheFanPub Best Ball II: 1QB, PPR', $league->name);
        $this->assertEquals(20, $league->rosterSize);
        $this->assertEquals(0, $league->taxiSquad);
        $this->assertFalse($league->usesSalaries);
        $this->assertFalse($league->usesContractYear);
        $this->assertNull($league->salaryCapAmount);
    }

    public function testUnauthorizedLeague()
    {
        $data = file_get_contents(__DIR__ . '/_data/league/1qb_without_api_key_private.json');
        $responses = [
            new MockResponse($data),
        ];
        $client = new MockHttpClient($responses);
        $this->expectException(\DanAbrey\MFLApi\Exceptions\UnauthorizedException::class);
        $league = $this->responseParser->league($client, 'https://api.myfantasyleague.com/2020/export?TYPE=league&L=xxxxx&APIKEY=&JSON=1');
    }

    public function testRosters()
    {
        $data = file_get_contents(__DIR__ . '/_data/rosters/rosters.json');
        $responses = [
            new MockResponse($data),
        ];
        $client = new MockHttpClient($responses);
        $rosters = $this->responseParser->rosters($client, 'https://api.myfantasyleague.com/2020/export?TYPE=rosters&L=xxxxx&APIKEY=&JSON=1');
        $this->assertIsArray($rosters);
        $this->assertEquals('0001', $rosters[0]->id);
        $this->assertEquals('13132', $rosters[0]->players[0]->id);
    }

    public function testPlayers()
    {
        $data = file_get_contents(__DIR__ . '/_data/players/players.json');
        $responses = [
            new MockResponse($data),
        ];
        $client = new MockHttpClient($responses);
        $players = $this->responseParser->players($client, 'https://api.myfantasyleague.com/2020/export?TYPE=players&DETAILS=1&JSON=1');
        $this->assertIsArray($players);
        $this->assertEquals('12360', $players[0]->id);
        $this->assertEquals('DE', $players[0]->position);
    }
}
