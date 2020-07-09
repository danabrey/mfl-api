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
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class MFLApiClient
{
    private HttpClientInterface $httpClient;
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

    public function setHttpClient(HttpClientInterface $httpClient): void
    {
        $this->httpClient = $httpClient;
    }

    protected function getClient(): HttpClientInterface
    {
        return $this->client ?? HttpClient::create();
    }

    protected function getUrl(array $arguments = []): string
    {
        return sprintf(
            "%s?%s",
            $this->getApiBase(),
            $this->getArgumentsForUrl($arguments),
        );
    }

    protected function makeRequest(string $method, string $url): array
    {
        try {
            $response = $this->httpClient->request($method, $url);
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
     * @param string $leagueId
     * @return MFLLeague
     * @throws InvalidParametersException
     * @throws UnauthorizedException
     * @throws UnknownApiError
     */
    public function league(string $leagueId): MFLLeague
    {
        $arguments = [
            'TYPE' => 'league',
            'L' => $leagueId,
        ];

        $response = $this->makeRequest( 'GET', $this->getUrl($arguments));

        $normalizers = [new ArrayDenormalizer(), new MFLLeagueDenormalizer()];
        $serializer = new Serializer($normalizers);
        return $serializer->denormalize($response['league'], MFLLeague::class);
    }

    /**
     * @return array|MFLPlayer[]
     * @throws InvalidParametersException
     * @throws UnauthorizedException
     * @throws UnknownApiError
     */
    public function players(): array
    {
        $arguments = [
            'TYPE' => 'players',
            'DETAILS' => '1',
        ];

        $response = $this->makeRequest('GET', $this->getUrl($arguments));

        $normalizers = [new ArrayDenormalizer(), new ObjectNormalizer()];
        $serializer = new Serializer($normalizers);
        return $serializer->denormalize($response['players']['player'], MFLPlayer::class . '[]');
    }

    /**
     * @param string $leagueId
     * @return array|MFLRoster[]
     * @throws InvalidParametersException
     * @throws UnauthorizedException
     * @throws UnknownApiError
     */
    public function rosters(string $leagueId): array
    {
        $arguments = [
            'TYPE' => 'rosters',
            'L' => $leagueId,
        ];

        $response = $this->makeRequest( 'GET', $this->getUrl($arguments));

        $normalizers = [new ArrayDenormalizer(), new MFLRosterDenormalizer()];
        $serializer = new Serializer($normalizers);
        return $serializer->denormalize($response['rosters']['franchise'], MFLRoster::class . '[]');
    }
}
