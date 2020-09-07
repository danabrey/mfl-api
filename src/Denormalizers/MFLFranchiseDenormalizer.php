<?php

namespace DanAbrey\MFLApi\Denormalizers;

use DanAbrey\MFLApi\Models\MFLFranchise;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class MFLFranchiseDenormalizer implements DenormalizerInterface
{
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $franchise = new MFLFranchise();

        $franchise->name = $data['name'];
        $franchise->franchiseId = $data['id'];

        return $franchise;
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return $type === MFLFranchise::class;
    }
}
