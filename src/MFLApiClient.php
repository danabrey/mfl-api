<?php
namespace DanAbrey\MFLApi;

use DanAbrey\MFLApi\Denormalizers\MFLLeagueDenormalizer;
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
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class MFLApiClient
{
    private string $year;
    private ?string $apiKey;
    private Serializer $serializer;
    private ResponseParser $responseParser;

    public function __construct(int $year, string $apiKey = null)
    {
        $this->year = $year;
        $this->apiKey = $apiKey;

        $normalizers = [new ArrayDenormalizer(), new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers);
        $this->responseParser = new ResponseParser();
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

    protected function getClient(): HttpClientInterface
    {
        return HttpClient::create();
    }

    protected function getUrl(array $arguments = []): string
    {
        return sprintf(
            "%s?%s",
            $this->getApiBase(),
            $this->getArgumentsForUrl($arguments),
        );
    }

    public function league(string $leagueId): MFLLeague
    {
        $arguments = $this->get([
            'TYPE' => 'league',
            'L' => $leagueId,
        ]);

        return $this->responseParser->league($this->getClient(), $this->getUrl($arguments));
    }

    /**
     * @return array|MFLPlayer[]
     */
    public function players(): array
    {
        $arguments = $this->get([
            'TYPE' => 'players',
            'DETAILS' => '1',
        ]);

        return $this->responseParser->players($this->getClient(), $this->getUrl($arguments));
    }

    /**
     * @param string $leagueId
     * @return array|MFLRoster[]
     * @throws InvalidParametersException
     * @throws UnknownApiError
     */
    public function rosters(string $leagueId): array
    {
        $arguments = $this->get([
            'TYPE' => 'rosters',
            'L' => $leagueId,
        ]);

        $rostersResponseParser = new ResponseParser();
        return $rostersResponseParser->rosters($this->getClient(), $this->getUrl($arguments));
    }
}
