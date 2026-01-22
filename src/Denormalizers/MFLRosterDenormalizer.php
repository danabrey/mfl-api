<?php

namespace DanAbrey\MFLApi\Denormalizers;

use DanAbrey\MFLApi\Models\MFLRoster;
use DanAbrey\MFLApi\Models\MFLRosterPlayer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class MFLRosterDenormalizer implements DenormalizerInterface
{
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $roster = new MFLRoster();

        $roster->id = $data['id'];

        $rosterPlayerDenormalizer = new Serializer([new MFLRosterPlayerDenormalizer(), new ArrayDenormalizer()]);

        $rosterPlayers = [];

        if (isset($data['player'])) {
            // If only one player, the MFL API returns that single player as the value, rather than an array of 1
            if (isset($data['player']) && isset($data['player']['id'])) {
                $data['player'] = [$data['player']];
            }

            $rosterPlayers = $rosterPlayerDenormalizer->denormalize(
                $data['player'],
                MFLRosterPlayer::class.'[]',
                $format
            );
        }

        $roster->players = $rosterPlayers;

        return $roster;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === MFLRoster::class;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [MFLRoster::class => true];
    }
}
