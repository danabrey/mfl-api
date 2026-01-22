<?php

namespace DanAbrey\MFLApi\Denormalizers;

use DanAbrey\MFLApi\Models\MFLDraftPick;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class MFLDraftPickDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
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

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === MFLDraftPick::class;
    }


    public function getSupportedTypes(?string $format): array
    {
        return [MFLDraftPick::class => true];
    }
}
