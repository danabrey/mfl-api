<?php

namespace DanAbrey\MFLApi\Denormalizers;

use DanAbrey\MFLApi\Models\MFLFranchise;
use DanAbrey\MFLApi\Models\MFLLeague;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

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