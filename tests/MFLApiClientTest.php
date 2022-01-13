<?php

use DanAbrey\MFLApi\Exceptions\UnauthorizedException;
use DanAbrey\MFLApi\MFLApiClient;
use DanAbrey\MFLApi\Models\MFLDraftPick;
use DanAbrey\MFLApi\Models\MFLLeague;
use DanAbrey\MFLApi\Models\MFLPlayerInjuryReport;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class MFLApiClientTest extends TestCase
{
    private MFLApiClient $client;

    public function __construct(?string $name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->client = new DanAbrey\MFLApi\MFLApiClient('2020');
    }

    public function testLeague()
    {
        $data = file_get_contents(__DIR__.'/_data/league/1qb_without_apikey.json');
        $responses = [
            new MockResponse($data),
        ];
        $client = new MockHttpClient($responses);
        $this->client->setHttpClient($client);
        $league = $this->client->league('xxxxx');
        $this->assertInstanceOf(MFLLeague::class, $league);
        $this->assertEquals('11747', $league->id);
        $this->assertEquals('TheFanPub Best Ball II: 1QB, PPR', $league->name);
        $this->assertEquals(20, $league->rosterSize);
        $this->assertEquals(0, $league->taxiSquad);
        $this->assertFalse($league->usesSalaries);
        $this->assertFalse($league->usesContractYear);
        $this->assertNull($league->salaryCapAmount);
        $this->assertCount(4, $league->starters['position']);
        $this->assertEquals(8, $league->starters['count']);
    }

    public function testUnauthorizedLeague()
    {
        $data = file_get_contents(__DIR__.'/_data/league/1qb_without_api_key_private.json');
        $responses = [
            new MockResponse($data),
        ];
        $client = new MockHttpClient($responses);
        $this->client->setHttpClient($client);
        $this->expectException(UnauthorizedException::class);
        $league = $this->client->league('xxxxx');
    }

    public function testRosters()
    {
        $data = file_get_contents(__DIR__.'/_data/rosters/rosters.json');
        $responses = [
            new MockResponse($data),
        ];
        $client = new MockHttpClient($responses);
        $this->client->setHttpClient($client);
        $rosters = $this->client->rosters('xxxxx');
        $this->assertIsArray($rosters);
        $this->assertEquals('0001', $rosters[0]->id);
        $this->assertEquals('13132', $rosters[0]->players[0]->id);
    }

    public function testRostersWithContracts()
    {
        $data = file_get_contents(__DIR__.'/_data/rosters/rosters-with-contracts.json');
        $responses = [
            new MockResponse($data),
        ];
        $client = new MockHttpClient($responses);
        $this->client->setHttpClient($client);
        $rosters = $this->client->rosters('xxxxx');
        $this->assertIsArray($rosters);
        $this->assertEquals('0001', $rosters[0]->id);
        $this->assertEquals('13128', $rosters[0]->players[0]->id);
        $this->assertEquals(8167951, $rosters[0]->players[0]->salary);
        $this->assertEquals(4, $rosters[0]->players[0]->contractYear);
        $this->assertNull($rosters[0]->players[0]->contractInfo);
    }

    public function testPlayers()
    {
        $data = file_get_contents(__DIR__.'/_data/players/players.json');
        $responses = [
            new MockResponse($data),
        ];
        $client = new MockHttpClient($responses);
        $this->client->setHttpClient($client);
        $players = $this->client->players();
        $this->assertIsArray($players);
        $this->assertEquals('12360', $players[0]->id);
        $this->assertEquals('DE', $players[0]->position);
        $this->assertEquals('FA', $players[2]->team);
        $this->assertEquals('698821200', $players[3]->birthdate);
    }

    public function testDraftResults()
    {
        $data = file_get_contents(__DIR__.'/_data/draftResults/draftResults.json');
        $responses = [
            new MockResponse($data),
        ];
        $client = new MockHttpClient($responses);
        $this->client->setHttpClient($client);
        $draftResults = $this->client->draftResults('12345');
        $this->assertIsArray($draftResults);
        $this->assertInstanceOf(MFLDraftPick::class, $draftResults[0]);
    }

    public function testDraftResultsSingle()
    {
        $data = file_get_contents(__DIR__.'/_data/draftResults/draftResults-single.json');
        $responses = [
            new MockResponse($data),
        ];
        $client = new MockHttpClient($responses);
        $this->client->setHttpClient($client);
        $draftResults = $this->client->draftResults('12345');
        $this->assertIsArray($draftResults);
        $this->assertInstanceOf(MFLDraftPick::class, $draftResults[0]);
    }

    public function testInjuries()
    {
        $data = file_get_contents(__DIR__.'/_data/injuries/injuries.json');
        $responses = [
            new MockResponse($data),
        ];
        $client = new MockHttpClient($responses);
        $this->client->setHttpClient($client);
        $injuries = $this->client->injuries();
        $this->assertIsArray($injuries);
        $this->assertInstanceOf(MFLPlayerInjuryReport::class, $injuries[0]);
    }

    public function testAssets()
    {
        $data = file_get_contents(__DIR__.'/_data/assets/assets.json');
        $responses = [
            new MockResponse($data),
        ];
        $client = new MockHttpClient($responses);
        $this->client->setHttpClient($client);
        $assets = $this->client->assets('xxxxx');
        $this->assertIsArray($assets);
        $this->assertEquals('0001', $assets[0]->id);
        $this->assertEquals('FP_0002_2022_1', $assets[0]->futureYearDraftPicks[0]->pick);
        $this->assertEquals('DP_0_1', $assets[0]->currentYearDraftPicks[0]->pick);
    }

    public function testTrades()
    {
        $data = file_get_contents(__DIR__.'/_data/trades/trades.json');
        $responses = [
            new MockResponse($data),
        ];
        $client = new MockHttpClient($responses);
        $this->client->setHttpClient($client);
        $trades = $this->client->trades('xxxxx');
        $this->assertIsArray($trades);
        $this->assertEquals('0003', $trades[2]->franchise2);
        $this->assertEquals('FP_0008_2022_3,FP_0003_2023_4,', $trades[2]->franchise2_gave_up);
        $this->assertEquals('1638196218', $trades[2]->timestamp);
        $this->assertEquals('0008', $trades[2]->franchise);
        $this->assertEquals('13418,', $trades[2]->franchise1_gave_up);
        $this->assertEquals('1638196218', $trades[2]->timestamp);
        $this->assertEquals('TRADE', $trades[2]->type);
        $this->assertEquals('1638799200', $trades[2]->expires);
    }

    public function testTradeSingle()
    {
        $data = file_get_contents(__DIR__.'/_data/trades/trades-single.json');
        $responses = [
            new MockResponse($data),
        ];
        $client = new MockHttpClient($responses);
        $this->client->setHttpClient($client);
        $trades = $this->client->trades('xxxxx');
        $this->assertIsArray($trades);
        $this->assertEquals('0003', $trades[0]->franchise2);
        $this->assertEquals('FP_0008_2022_3,FP_0003_2023_4,', $trades[0]->franchise2_gave_up);
        $this->assertEquals('1638196218', $trades[0]->timestamp);
        $this->assertEquals('0008', $trades[0]->franchise);
        $this->assertEquals('13418,', $trades[0]->franchise1_gave_up);
        $this->assertEquals('1638196218', $trades[0]->timestamp);
        $this->assertEquals('TRADE', $trades[0]->type);
        $this->assertEquals('1638799200', $trades[0]->expires);
    }
}
