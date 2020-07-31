<?php
namespace DanAbrey\MFLApi\Models;

final class MFLPlayer
{
    public string $id;
    public string $name;
    public ?string $position = null;
    public ?string $team = null;
}
