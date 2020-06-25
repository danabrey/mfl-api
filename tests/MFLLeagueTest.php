<?php

use DanAbrey\MFLApi\MFLApiResponse;
use PHPUnit\Framework\TestCase;

final class MFLLeagueTest extends TestCase
{
    public function testCanBeCreated(): void
    {
        $client = new DanAbrey\MFLApi\MFLApiClient(2020, "aRNt2c%2BVvuWvx0GmPFHBYTAeEbox");
        $result = $client->league("48002");

        var_dump($result);

        $this->assertInstanceOf(MFLApiResponse::class, $result);
    }
}

