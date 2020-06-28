<?php

namespace DanAbrey\MFLApi\Denormalizers;

use DanAbrey\MFLApi\Models\MFLFranchise;
use DanAbrey\MFLApi\Models\MFLLeague;
use DanAbrey\MFLApi\Models\MFLRoster;
use DanAbrey\MFLApi\Models\MFLRosterPlayer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class MFLRosterDenormalizer implements DenormalizerInterface
{
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $roster = new MFLRoster();

        $roster->id = $data['id'];

        $rosterPlayerDenormalizer = new Serializer([new ObjectNormalizer(), new ArrayDenormalizer()]);

        $rosterPlayers = $rosterPlayerDenormalizer->denormalize(
            $data['player'],
            MFLRosterPlayer::class . "[]",
            $format,
            $context
        );
        $roster->players = $rosterPlayers;

        return $roster;
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return $type === MFLRoster::class;
    }
}