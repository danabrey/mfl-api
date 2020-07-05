<?php
namespace DanAbrey\MFLApi\Models;

final class MFLLeague
{
    public string $id;
    public string $name;
    public int $rosterSize;
    public int $taxiSquad = 0;
    /**
     * @var array|MFLFranchise[]
     */
    public array $franchises;

    public bool $usesContractYear = false;
    public bool $usesSalaries = false;
    public ?string $salaryCapAmount;

    public array $starters;
}
