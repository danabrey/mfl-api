<?php
namespace DanAbrey\MFLApi;

use DanAbrey\MFLApi\Denormalizers\MFLLeagueDenormalizer;
use DanAbrey\MFLApi\Denormalizers\MFLRosterDenormalizer;
use DanAbrey\MFLApi\Exceptions\InvalidParametersException;
use DanAbrey\MFLApi\Exceptions\UnauthorizedException;
use DanAbrey\MFLApi\Exceptions\UnknownApiError;
use DanAbrey\MFLApi\Models\MFLLeague;
use DanAbrey\MFLApi\Models\MFLPlayer;
use DanAbrey\MFLApi\Models\MFLRoster;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class ResponseParser
{
    protected function makeRequest(HttpClientInterface $client, string $method, string $url): array
    {
        try {
            $response = $client->request($method, $url);
            $decodedResponse = json_decode($response->getContent(), true);

            if (isset($decodedResponse['error'])) {
                if ($decodedResponse['error'] === "An error has occurred - probably caused by one or more invalid parameters.") {
                    throw new InvalidParametersException();
                }

                if (is_array($decodedResponse['error']) && str_contains($decodedResponse['error']['$t'], 'API requires logged in user')) {
                    throw new UnauthorizedException();
                }
            }
        } catch (ClientExceptionInterface $e) {
            // Probably a 404, MFL sometimes responds with this when it can't find the league ID
            throw new InvalidParametersException();
        } catch (TransportExceptionInterface | ServerExceptionInterface $e) {
            throw new UnknownApiError();
        }

        return $decodedResponse;
    }

    /**
     * @param HttpClientInterface $client
     * @param string $url
     * @return array|mixed|object
     * @throws InvalidParametersException
     * @throws UnauthorizedException
     * @throws UnknownApiError
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function league(HttpClientInterface $client, string $url): MFLLeague
    {
        $response = $this->makeRequest($client, 'GET', $url);

        $normalizers = [new ArrayDenormalizer(), new MFLLeagueDenormalizer()];
        $serializer = new Serializer($normalizers);
        return $serializer->denormalize($response['league'], MFLLeague::class);
    }

    public function rosters(HttpClientInterface $client, string $url): array
    {
        $response = $this->makeRequest($client, 'GET', $url);

        $normalizers = [new ArrayDenormalizer(), new MFLRosterDenormalizer()];
        $serializer = new Serializer($normalizers);
        return $serializer->denormalize($response['rosters']['franchise'], MFLRoster::class . '[]');
    }

    public function players(HttpClientInterface $client, string $url): array
    {
        $response = $this->makeRequest($client, 'GET', $url);

        $normalizers = [new ArrayDenormalizer(), new ObjectNormalizer()];
        $serializer = new Serializer($normalizers);
        return $serializer->denormalize($response['players']['player'], MFLPlayer::class . '[]');
    }
}