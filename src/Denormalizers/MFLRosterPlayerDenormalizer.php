<?php

namespace DanAbrey\MFLApi\Denormalizers;

use DanAbrey\MFLApi\Models\MFLRosterPlayer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class MFLRosterPlayerDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $rosterPlayer = new MFLRosterPlayer();
        $rosterPlayer->id = $data['id'];
        $rosterPlayer->status = $data['status'];
        if (isset($data['contractYear']) && $data['contractYear'] !== '') {
            $rosterPlayer->contractYear = (int) $data['contractYear'] ?: null;
        }
        if (isset($data['contractInfo'])) {
            $rosterPlayer->contractInfo = $data['contractInfo'] ?: null;
        }
        if (isset($data['salary'])) {
            $rosterPlayer->salary = $data['salary'] ?: null;
        }

        return $rosterPlayer;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === MFLRosterPlayer::class;
    }


    public function getSupportedTypes(?string $format): array
    {
        return [MFLRosterPlayer::class => true];
    }
}
