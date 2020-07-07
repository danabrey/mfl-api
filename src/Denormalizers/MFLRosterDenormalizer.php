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

        $rosterPlayers = [];

        if(isset($data['player'])) {
            // If only one player, the MFL API returns that single player as the value, rather than an array of 1
            if (isset($data['player']) && isset($data['player']['id'])) {
                $data['player'] = [$data['player']];
            }

            $rosterPlayers = $rosterPlayerDenormalizer->denormalize(
                $data['player'],
                MFLRosterPlayer::class . "[]",
                $format,
                $context
            );
        }

        $roster->players = $rosterPlayers;

        return $roster;
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return $type === MFLRoster::class;
    }
}
