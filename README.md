# MFL API

This is a PHP library for interacting with the MyFantasyLeague.com API. It's a work in progress, and I am actively developing it for use in other projects.

## Installation

`composer require danabrey/mfl-api`

## Usage

Create an instance of the client

`$client = new DanAbrey\MFLApi\MFLApiClient(2020);`

The league year is required. You can optionally pass an API key as the second argument for sending in all requests:

`$client = new DanAbrey\MFLApi\MFLApiClient(2020, 'my_api_key');`

Use the client methods to make requests to the API, e.g.:

`$client->players()`

`$client->league('XXXXX')` where XXXXX is the league ID.

`$client->rosters('XXXXX')` where XXXXX is the league ID.

All methods return either a single instance or an array of plain PHP objects that represent the data returned. e.g. `MFLPlayer`, `MFLRoster[]`, etc.

### Exception handling

The library throws a number of exceptions for you to decide how to handle yourself. They all extend the abstract `DanAbrey\MFLApi\Exceptions\MFLApiException` class.

**UnauthorizedException**: the API responded telling you that the endpoint needs an API key to access that data

**InvalidParametersException**: the API responded with an error hinting that you may have forgotten to pass a required parameter

**UnknownApiError**: the API responded with a 5xx HTTP error, usually this will mean there is an issue with the API at MFL's end

## Note

It is your responsibility to abide by the [terms of the MFL API](https://www68.myfantasyleague.com/2020/api_info).

## Development

I am actively seeking contributors to this project. The MFL API is a complex and quite large beast, and accessing its data in projects can be a cumbersome task. This project aims to add reliable typing and understandable error handling via custom exceptions.

Please feel free to open an issue if you'd like to get involved.

### Running tests

`./vendor/bin/phpunit`

## Treeware

This package is MIT licensed and you are free to use it in open or closed source projects. If it makes it to your production environment, I would very much appreciate it if you bought the world some trees.

It’s now common knowledge that one of the best tools to tackle the climate crisis and keep our temperatures from rising above 1.5C is to [plant trees](https://www.bbc.co.uk/news/science-environment-48870920). If you contribute to my forest you’ll be creating employment for local families and restoring wildlife habitats.

You can buy trees at [offset.earth/treeware](https://plant.treeware.earth/Astrotomic/php-open-graph)

Read more about Treeware at [treeware.earth](https://treeware.earth)
