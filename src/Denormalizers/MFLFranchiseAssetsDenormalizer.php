<?php

namespace DanAbrey\MFLApi\Denormalizers;

use DanAbrey\MFLApi\Models\MFLFranchiseAssetDraftPick;
use DanAbrey\MFLApi\Models\MFLFranchiseAssetPlayer;
use DanAbrey\MFLApi\Models\MFLFranchiseAssets;
use DanAbrey\MFLApi\Models\MFLRoster;
use DanAbrey\MFLApi\Models\MFLRosterPlayer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class MFLFranchiseAssetsDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $franchiseAssets = new MFLFranchiseAssets();

        $franchiseAssets->id = $data['id'];

        $franchiseAssetPlayerDenormalizer = new Serializer([new MFLFranchiseAssetPlayerDenormalizer(), new ArrayDenormalizer()]);
        $franchiseAssetDraftPickDenormalizer = new Serializer([new MFLFranchiseAssetDraftPickDenormalizer(), new ArrayDenormalizer()]);

        // Future draft picks

        $futurePicks = [];

        if (isset($data['futureYearDraftPicks']['draftPick'])) {
            // If only one player, the MFL API returns that single player as the value, rather than an array of 1
            if (isset($data['futureYearDraftPicks']['draftPick']) && isset($data['futureYearDraftPicks']['draftPick']['pick'])) {
                $data['futureYearDraftPicks']['draftPick'] = [$data['futureYearDraftPicks']['draftPick']];
            }

            $futurePicks = $franchiseAssetDraftPickDenormalizer->denormalize(
                $data['futureYearDraftPicks']['draftPick'],
                MFLFranchiseAssetDraftPick::class.'[]',
                $format
            );
        }
        
        // Current draft picks

        $currentPicks = [];

        if (isset($data['currentYearDraftPicks']['draftPick'])) {
            // If only one player, the MFL API returns that single player as the value, rather than an array of 1
            if (isset($data['currentYearDraftPicks']['draftPick']) && isset($data['currentYearDraftPicks']['draftPick']['pick'])) {
                $data['currentYearDraftPicks']['draftPick'] = [$data['currentYearDraftPicks']['draftPick']];
            }

            $currentPicks = $franchiseAssetDraftPickDenormalizer->denormalize(
                $data['currentYearDraftPicks']['draftPick'],
                MFLFranchiseAssetDraftPick::class.'[]',
                $format
            );
        }

        // Players

        $franchiseAssetPlayers = [];

        if (isset($data['players']['player'])) {
            // If only one player, the MFL API returns that single player as the value, rather than an array of 1
            if (isset($data['players']['player']) && isset($data['players']['player']['id'])) {
                $data['players']['player'] = [$data['players']['player']];
            }

            $franchiseAssetPlayers = $franchiseAssetPlayerDenormalizer->denormalize(
                $data['players']['player'],
                MFLFranchiseAssetPlayer::class.'[]',
                $format
            );
        }

        $franchiseAssets->players = $franchiseAssetPlayers;
        $franchiseAssets->futureYearDraftPicks = $futurePicks;
        $franchiseAssets->currentYearDraftPicks = $currentPicks;

        return $franchiseAssets;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === MFLFranchiseAssets::class;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [MFLFranchiseAssets::class => true];
    }
}
