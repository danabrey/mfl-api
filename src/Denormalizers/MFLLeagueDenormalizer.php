<?php

namespace DanAbrey\MFLApi\Denormalizers;

use DanAbrey\MFLApi\Models\MFLFranchise;
use DanAbrey\MFLApi\Models\MFLLeague;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Serializer;

class MFLLeagueDenormalizer implements DenormalizerInterface
{
    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $league = new MFLLeague();

        $league->id = $data['id'];
        $league->name = $data['name'];
        $league->rosterSize = $data['rosterSize'];
        $league->taxiSquad = intval($data['taxiSquad']);
        $league->usesContractYear = $data['usesContractYear'];
        $league->usesSalaries = $data['usesSalaries'];
        if (isset($data['salaryCapAmount']) && $data['salaryCapAmount'] !== '') {
            $league->salaryCapAmount = $data['salaryCapAmount'];
        }
        $league->starters = $data['starters'];

        $franchiseDenormalizer = new Serializer([new MFLFranchiseDenormalizer(), new ArrayDenormalizer()]);

        $franchises = $franchiseDenormalizer->denormalize(
            $data['franchises']['franchise'],
            MFLFranchise::class . "[]",
            $format,
            $context
        );
        $league->franchises = $franchises;

        return $league;
    }

    public function supportsDenormalization($data, string $type, string $format = null)
    {
        return $type === MFLLeague::class;
    }
}
