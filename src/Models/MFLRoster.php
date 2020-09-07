<?php

namespace DanAbrey\MFLApi\Models;

final class MFLRoster
{
    public string $id;
    /**
     * @var array|MFLRosterPlayer[]
     */
    public array $players;
}
