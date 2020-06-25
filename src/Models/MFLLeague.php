<?php
namespace DanAbrey\MFLApi\Models;

final class MFLLeague
{
    public string $id;
    public string $name;
    public int $rosterSize;
    /**
     * @var array|MFLFranchise[]
     */
    public array $franchises;
}
