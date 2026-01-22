<?php

namespace DanAbrey\MFLApi\Denormalizers;

use DanAbrey\MFLApi\Models\MFLFranchise;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class MFLFranchiseDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $franchise = new MFLFranchise();

        $franchise->name = $data['name'];
        $franchise->franchiseId = $data['id'];

        return $franchise;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === MFLFranchise::class;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [MFLFranchise::class => true];
    }
}
