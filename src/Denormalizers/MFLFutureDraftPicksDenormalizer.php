<?php

namespace DanAbrey\MFLApi\Denormalizers;

use DanAbrey\MFLApi\Models\MFLFutureDraftPick;
use DanAbrey\MFLApi\Models\MFLFutureDraftPickFranchise;
use DanAbrey\MFLApi\Models\MFLRoster;
use DanAbrey\MFLApi\Models\MFLRosterPlayer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class MFLFutureDraftPicksDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $franchise = new MFLFutureDraftPickFranchise();

        $franchise->id = $data['id'];

        $picksDenormalizer = new Serializer([new ObjectNormalizer(), new ArrayDenormalizer()]);

        $picks = [];

        if (isset($data['futureDraftPick'])) {
            // If only one pick, the MFL API returns that single pick as the value, rather than an array of 1
            if (isset($data['futureDraftPick']) && isset($data['futureDraftPick']['round'])) {
                $data['futureDraftPick'] = [$data['futureDraftPick']];
            }

            $picks = $picksDenormalizer->denormalize(
                $data['futureDraftPick'],
                MFLFutureDraftPick::class . '[]',
                $format
            );
        }

        $franchise->picks = $picks;

        return $franchise;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === MFLFutureDraftPickFranchise::class;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [MFLFutureDraftPickFranchise::class => true];
    }
}
