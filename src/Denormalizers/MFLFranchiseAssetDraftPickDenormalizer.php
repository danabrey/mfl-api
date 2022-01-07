<?php

namespace DanAbrey\MFLApi\Denormalizers;

use DanAbrey\MFLApi\Models\MFLFranchiseAssetDraftPick;
use DanAbrey\MFLApi\Models\MFLRosterPlayer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class MFLFranchiseAssetDraftPickDenormalizer implements DenormalizerInterface
{
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $pick = new MFLFranchiseAssetDraftPick();
        $pick->pick = $data['pick'];
        $pick->description = $data['description'];

        return $pick;
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return $type === MFLFranchiseAssetDraftPick::class;
    }
}
