<?php

namespace DanAbrey\MFLApi\Denormalizers;

use DanAbrey\MFLApi\Models\MFLDraftPick;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class MFLDraftPickDenormalizer implements DenormalizerInterface
{
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $draftPick = new MFLDraftPick();

        $draftPick->timestamp = $data['timestamp'];
        $draftPick->franchise = $data['franchise'];
        $draftPick->round = $data['round'];
        $draftPick->player = $data['player'];
        $draftPick->pick = $data['pick'];
        $draftPick->comments = $data['comments'];

        return $draftPick;
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return $type === MFLDraftPick::class;
    }
}