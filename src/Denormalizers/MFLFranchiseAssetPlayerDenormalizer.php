<?php

namespace DanAbrey\MFLApi\Denormalizers;

use DanAbrey\MFLApi\Models\MFLFranchiseAssetDraftPick;
use DanAbrey\MFLApi\Models\MFLFranchiseAssetPlayer;
use DanAbrey\MFLApi\Models\MFLRosterPlayer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class MFLFranchiseAssetPlayerDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $pick = new MFLFranchiseAssetPlayer();
        $pick->id = $data['id'];

        return $pick;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === MFLFranchiseAssetPlayer::class;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [MFLFranchiseAssetPlayer::class => true];
    }
}
