<?php
namespace DanAbrey\MFLApi\Models;

final class MFLRosterPlayer
{
    public string $id;
    public string $status;
    public ?int $contractYear = null;
    public ?string $contractInfo = null;
    public ?string $salary = null;
}
