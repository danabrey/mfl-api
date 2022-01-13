<?php

namespace DanAbrey\MFLApi\Models;

final class MFLTrade
{
    public string $timestamp;
    public string $franchise;
    public string $franchise1_gave_up;
    public string $franchise2;
    public string $franchise2_gave_up;
    public string $type;
    public string $expires;
}
