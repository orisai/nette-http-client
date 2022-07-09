# Nette HTTP Client

[PSR-17](https://www.php-fig.org/psr/psr-17/) and [PSR-18](https://www.php-fig.org/psr/psr-18/) HTTP client integration
for [Nette](https://nette.org)

## Content

- [Setup](#setup)
- [Usage](#usage)
- [Tracy](#tracy)
- [TLS](#tls)
- [Headers](#headers)

## Setup

Install with [Composer](https://getcomposer.org)

```sh
composer require orisai/nette-http-client
```

Register extension

```neon
extensions:
	orisai.httpClient: OriNette\HttpClient\DI\HttpClientExtension
```

## Usage

We expose only interfaces from [PSR-17](https://www.php-fig.org/psr/psr-17/)
and [PSR-18](https://www.php-fig.org/psr/psr-18/), everything you need is already described by their documentation.

Namely, we give you these interfaces:

- `Psr\Http\Client\ClientInterface`
- `Psr\Http\Message\RequestFactoryInterface`
- `Psr\Http\Message\ResponseFactoryInterface`
- `Psr\Http\Message\ServerRequestFactoryInterface`
- `Psr\Http\Message\StreamFactoryInterface`
- `Psr\Http\Message\UploadedFileFactoryInterface`
- `Psr\Http\Message\UriFactoryInterface`

Implementation of an API may be as simple as following example:

```php
use Nette\Utils\Json;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class ExampleClient
{

	private string $token;

	private string $uri = 'https://example.com/';

	private ClientInterface $client;

	private RequestFactoryInterface $requestFactory;

	private StreamFactoryInterface $streamFactory;

	public function __construct(
		string $token,
		ClientInterface $client,
		RequestFactoryInterface $requestFactory,
		StreamFactoryInterface $streamFactory
	)
	{
		$this->token = $token;
		$this->client = $client;
		$this->requestFactory = $requestFactory;
		$this->streamFactory = $streamFactory;
	}

	public function setUri(string $uri): void
	{
		$this->uri = $uri;
	}

	/**
	 * @param array<mixed> $data
	 * @throws ClientExceptionInterface
	 */
	public function sendData(array $data): void
	{
		$request = $this->requestFactory->createRequest('POST', "$this->uri/api/v1/data");
		$request = $request
			->withHeader('Authorization', "Bearer $this->token")
			->withHeader('Content-Type', 'application/json')
			->withBody($this->streamFactory->createStream(Json::encode($data)));

		$this->client->sendRequest($request);
	}

}
```

## Tracy

To show requests and responses in Tracy panel, enable `debug > panel` option.

```neon
orisai.httpClient:
	debug:
		panel: %debugMode%
```

## TLS

```neon
orisai.httpClient:

	tls:
		# bool
		# Default: true
		verifyPeer: true

		# bool
		# Default: true
		verifyHost: true
```

## Headers

```neon
orisai.httpClient:

	# Default: []
	# array<string, string|array<int, string>>
	headers:
		name1: value
		name2:
			- value1
			- value2
```
