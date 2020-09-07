<?php

namespace DanAbrey\MFLApi\Denormalizers;

use DanAbrey\MFLApi\Models\MFLRosterPlayer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class MFLRosterPlayerDenormalizer implements DenormalizerInterface
{
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $rosterPlayer = new MFLRosterPlayer();
        $rosterPlayer->id = $data['id'];
        $rosterPlayer->status = $data['status'];
        if (isset($data['contractYear'])) {
            $rosterPlayer->contractYear = $data['contractYear'] ?: null;
        }
        if (isset($data['contractInfo'])) {
            $rosterPlayer->contractInfo = $data['contractInfo'] ?: null;
        }
        if (isset($data['salary'])) {
            $rosterPlayer->salary = $data['salary'] ?: null;
        }

        return $rosterPlayer;
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return $type === MFLRosterPlayer::class;
    }
}
