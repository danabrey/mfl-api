<?php

namespace DanAbrey\MFLApi\Denormalizers;

use DanAbrey\MFLApi\Models\MFLTrade;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class MFLTradesDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $trades = [];

        // If only one trade, the MFL API returns that single trade as the value, rather than an array of 1
        if (isset($data['franchise1'])) {
            $data = [$data];
        }

        $trades = $this->denormalize(
            $data,
            MFLTrade::class . '[]',
            $format
        );

        return $trades;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === MFLTrade::class;
    }


    public function getSupportedTypes(?string $format): array
    {
        return [MFLTrade::class => true];
    }
}
