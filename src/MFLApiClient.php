<?php

namespace DanAbrey\MFLApi;

use DanAbrey\MFLApi\Denormalizers\MFLDraftPickDenormalizer;
use DanAbrey\MFLApi\Denormalizers\MFLFranchiseAssetsDenormalizer;
use DanAbrey\MFLApi\Denormalizers\MFLFutureDraftPicksDenormalizer;
use DanAbrey\MFLApi\Denormalizers\MFLLeagueDenormalizer;
use DanAbrey\MFLApi\Denormalizers\MFLRosterDenormalizer;
use DanAbrey\MFLApi\Denormalizers\MFLTradesDenormalizer;
use DanAbrey\MFLApi\Exceptions\InvalidParametersException;
use DanAbrey\MFLApi\Exceptions\UnauthorizedException;
use DanAbrey\MFLApi\Exceptions\UnknownApiError;
use DanAbrey\MFLApi\Models\MFLDraftPick;
use DanAbrey\MFLApi\Models\MFLFranchiseAssets;
use DanAbrey\MFLApi\Models\MFLFutureDraftPickFranchise;
use DanAbrey\MFLApi\Models\MFLLeague;
use DanAbrey\MFLApi\Models\MFLPlayer;
use DanAbrey\MFLApi\Models\MFLPlayerInjuryReport;
use DanAbrey\MFLApi\Models\MFLRoster;
use DanAbrey\MFLApi\Models\MFLTrade;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class MFLApiClient
{
    private HttpClientInterface $httpClient;
    private string $year;
    private ?string $apiKey;
    private string $baseURL;

    private Serializer $serializer;

    public function __construct(int $year, string $apiKey = null, string $userAgent = null, ?string $loginCookie = null, ?string $baseURL = null)
    {
        $this->year = $year;
        $this->apiKey = $apiKey;

        $normalizers = [new ArrayDenormalizer(), new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers);

        $options = [
            'headers' => [],
        ];

        if ($userAgent) {
            $options['headers']['User-Agent'] = $userAgent;
        }

        if ($loginCookie) {
            $options['headers']['Cookie'] = 'MFL_USER_ID=' . $loginCookie;
        }

        $this->httpClient = HttpClient::create($options);

        $this->baseURL = $baseURL ?? 'https://api.myfantasyleague.com';
    }

    protected function getApiBase(): string
    {
        return sprintf(
            '%s/%s/export',
            $this->baseURL,
            $this->year,
        );
    }

    protected function getArgumentsForUrl(array $arguments = []): string
    {
        $arguments = [
            'JSON'   => '1',
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
        return $this->httpClient;
    }

    protected function getUrl(array $arguments = []): string
    {
        return sprintf(
            '%s?%s',
            $this->getApiBase(),
            $this->getArgumentsForUrl($arguments),
        );
    }

    protected function makeRequest(string $method, string $url): array
    {
        try {
            $response = $this->getClient()->request($method, $url);
            $decodedResponse = json_decode($response->getContent(), true);

            if (isset($decodedResponse['error'])) {
                if ($decodedResponse['error'] === 'An error has occurred - probably caused by one or more invalid parameters.') {
                    throw new InvalidParametersException();
                }

                if (is_array($decodedResponse['error']) && str_contains($decodedResponse['error']['$t'], 'API requires logged in user')) {
                    throw new UnauthorizedException('API requires logged in user');
                }

                if (is_array($decodedResponse['error']) && str_contains($decodedResponse['error']['$t'], 'Invalid league ID')) {
                    throw new InvalidParametersException('Invalid league ID');
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
     *
     * @throws InvalidParametersException
     * @throws UnauthorizedException
     * @throws UnknownApiError
     *
     * @return MFLLeague
     */
    public function league(string $leagueId): MFLLeague
    {
        $arguments = [
            'TYPE' => 'league',
            'L'    => $leagueId,
        ];

        $response = $this->makeRequest('GET', $this->getUrl($arguments));

        $normalizers = [new ArrayDenormalizer(), new MFLLeagueDenormalizer()];
        $serializer = new Serializer($normalizers);

        return $serializer->denormalize($response['league'], MFLLeague::class);
    }

    /**
     * @throws InvalidParametersException
     * @throws UnauthorizedException
     * @throws UnknownApiError
     *
     * @return array|MFLPlayer[]
     */
    public function players(): array
    {
        $arguments = [
            'TYPE'    => 'players',
            'DETAILS' => '1',
        ];

        $response = $this->makeRequest('GET', $this->getUrl($arguments));

        $normalizers = [new ArrayDenormalizer(), new ObjectNormalizer()];
        $serializer = new Serializer($normalizers);

        return $serializer->denormalize($response['players']['player'], MFLPlayer::class.'[]');
    }

    /**
     * @param string $leagueId
     *
     * @throws InvalidParametersException
     * @throws UnauthorizedException
     * @throws UnknownApiError
     *
     * @return array|MFLRoster[]
     */
    public function rosters(string $leagueId): array
    {
        $arguments = [
            'TYPE' => 'rosters',
            'L'    => $leagueId,
        ];

        $response = $this->makeRequest('GET', $this->getUrl($arguments));

        $normalizers = [new ArrayDenormalizer(), new MFLRosterDenormalizer()];
        $serializer = new Serializer($normalizers);

        return $serializer->denormalize($response['rosters']['franchise'], MFLRoster::class.'[]');
    }

    public function draftResults(string $leagueId): array
    {
        $arguments = [
            'TYPE' => 'draftResults',
            'L'    => $leagueId,
        ];

        $response = $this->makeRequest('GET', $this->getUrl($arguments));
        $normalizers = [new ArrayDenormalizer(), new MFLDraftPickDenormalizer()];
        $serializer = new Serializer($normalizers);

        $picks = isset($response['draftResults']['draftUnit']['draftPick']['franchise']) ? [$response['draftResults']['draftUnit']['draftPick']] : $response['draftResults']['draftUnit']['draftPick'];

        return $serializer->denormalize($picks, MFLDraftPick::class.'[]');
    }

    public function injuries(): array
    {
        $arguments = [
            'TYPE' => 'injuries',
        ];

        $response = $this->makeRequest('GET', $this->getUrl($arguments));
        $normalizers = [new ArrayDenormalizer(), new ObjectNormalizer()];
        $serializer = new Serializer($normalizers);

        return $serializer->denormalize($response['injuries']['injury'], MFLPlayerInjuryReport::class.'[]');
    }

    public function assets(string $leagueId): array
    {
        $arguments = [
            'TYPE' => 'assets',
            'L'    => $leagueId,
        ];

        $response = $this->makeRequest('GET', $this->getUrl($arguments));

        $normalizers = [new ArrayDenormalizer(), new MFLFranchiseAssetsDenormalizer()];
        $serializer = new Serializer($normalizers);

        return $serializer->denormalize($response['assets']['franchise'], MFLFranchiseAssets::class.'[]');
    }

    public function trades(string $leagueId): array
    {
        $arguments = [
            'TYPE' => 'transactions',
            'TRANS_TYPE' => 'TRADE',
            'L'    => $leagueId,
        ];

        $response = $this->makeRequest('GET', $this->getUrl($arguments));

        $normalizers = [new ArrayDenormalizer(), new ObjectNormalizer()];
        $serializer = new Serializer($normalizers);

        $trades = isset($response['transactions']['transaction']['franchise']) ? [$response['transactions']['transaction']] : $response['transactions']['transaction'];

        return $serializer->denormalize($trades, MFLTrade::class.'[]');
    }

    public function futureDraftPicks(string $leagueId): array
    {
        $arguments = [
            'TYPE' => 'futureDraftPicks',
            'L'    => $leagueId,
        ];

        $response = $this->makeRequest('GET', $this->getUrl($arguments));

        $normalizers = [new ArrayDenormalizer(), new MFLFutureDraftPicksDenormalizer()];
        $serializer = new Serializer($normalizers);

        if (count($response['futureDraftPicks']) === 0) {
            return [];
        }

        return $serializer->denormalize($response['futureDraftPicks']['franchise'], MFLFutureDraftPickFranchise::class.'[]');
    }
}
