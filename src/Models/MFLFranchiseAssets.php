<?php

namespace DanAbrey\MFLApi\Models;

final class MFLFranchiseAssets
{
    public string $id;
    /**
     * @var array|MFLFranchiseAssetDraftPick[]
     */
    public array $futureYearDraftPicks = [];
    /**
     * @var array|MFLFranchiseAssetDraftPick[]
     */
    public array $currentYearDraftPicks = [];
    /**
     * @var array|MFLFranchiseAssetPlayer[]
     */
    public array $players = [];
}
