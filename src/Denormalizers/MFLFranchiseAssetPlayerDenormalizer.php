<?php

namespace DanAbrey\MFLApi\Denormalizers;

use DanAbrey\MFLApi\Models\MFLFranchiseAssetDraftPick;
use DanAbrey\MFLApi\Models\MFLFranchiseAssetPlayer;
use DanAbrey\MFLApi\Models\MFLRosterPlayer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class MFLFranchiseAssetPlayerDenormalizer implements DenormalizerInterface
{
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $pick = new MFLFranchiseAssetPlayer();
        $pick->id = $data['id'];

        return $pick;
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return $type === MFLFranchiseAssetPlayer::class;
    }
}
