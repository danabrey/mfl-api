<?php
namespace DanAbrey\MFLApi\Models;

final class MFLRosterPlayer
{
    public string $id;
    public string $status;
    public ?int $contractYear;
    public ?string $contractInfo;
    public ?string $salary;
}
