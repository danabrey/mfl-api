<?php
namespace DanAbrey\MFLApi;

use DanAbrey\MFLApi\Denormalizers\MFLLeagueDenormalizer;
use DanAbrey\MFLApi\Exceptions\InvalidParametersException;
use DanAbrey\MFLApi\Exceptions\UnknownApiError;
use DanAbrey\MFLApi\Models\MFLLeague;
use DanAbrey\MFLApi\Models\MFLPlayer;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

final class MFLApiClient
{
    private string $year;
    private ?string $apiKey;
    private Serializer $serializer;

    public function __construct(int $year, string $apiKey = null)
    {
        $this->year = $year;
        $this->apiKey = $apiKey;

        $normalizers = [new ArrayDenormalizer(), new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers);
    }

    protected function getApiBase(): string
    {
        return sprintf(
            "https://api.myfantasyleague.com/%s/export",
            $this->year,
        );
    }

    protected function getArgumentsForUrl(array $arguments = []): string
    {

        $arguments = [
            'JSON' => '1',
            'APIKEY' => $this->apiKey,
        ] + $arguments;

        return http_build_query($arguments);
    }

    /**
     * @param array $arguments
     * @return mixed
     * @throws InvalidParametersException
     * @throws UnknownApiError
     */
    protected function get(array $arguments = [])
    {
        $url = sprintf(
            "%s?%s",
            $this->getApiBase(),
            $this->getArgumentsForUrl($arguments),
        );

        $client = HttpClient::create();

        try {
            $response = $client->request('GET', $url);
            $decodedResponse = json_decode($response->getContent(), true);

            if (isset($decodedResponse['error'])) {
                switch ($decodedResponse['error']) {
                    case "An error has occurred - probably caused by one or more invalid parameters.":
                        throw new InvalidParametersException();
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

    protected function post()
    {

    }

    protected function request(string $path, array $arguments)
    {

    }

    public function league(string $leagueId): MFLLeague
    {
        $response = $this->get([
            'TYPE' => 'league',
            'L' => $leagueId,
        ]);

        $normalizers = [new ArrayDenormalizer(), new MFLLeagueDenormalizer()];
        $serializer = new Serializer($normalizers);
        $league = $serializer->denormalize($response['league'], MFLLeague::class);

        return $league;
    }

    /**
     * @return array|MFLPlayer[]
     */
    public function players(): array
    {
        $response = $this->get([
            'TYPE' => 'players',
            'DETAILS' => '1',
        ]);

        $normalizers = [new ArrayDenormalizer(), new ObjectNormalizer()];
        $serializer = new Serializer($normalizers);
        $players = $serializer->denormalize($response['players']['player'], MFLPlayer::class . '[]');

        return $players;
    }
}
