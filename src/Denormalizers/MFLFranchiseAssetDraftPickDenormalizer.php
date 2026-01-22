<?php

namespace DanAbrey\MFLApi\Denormalizers;

use DanAbrey\MFLApi\Models\MFLFranchiseAssetDraftPick;
use DanAbrey\MFLApi\Models\MFLRosterPlayer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class MFLFranchiseAssetDraftPickDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $pick = new MFLFranchiseAssetDraftPick();
        $pick->pick = $data['pick'];
        $pick->description = $data['description'];

        return $pick;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === MFLFranchiseAssetDraftPick::class;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [MFLFranchiseAssetDraftPick::class => true];
    }
}
