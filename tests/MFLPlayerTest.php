<?php

use DanAbrey\MFLApi\Models\MFLPlayer;
use PHPUnit\Framework\TestCase;

final class MFLPlayerTest extends TestCase
{
    public function testCanBeCreated(): void
    {
        $this->assertInstanceOf(
            MFLPlayer::class,
            new MFLPlayer()
        );
    }


}

