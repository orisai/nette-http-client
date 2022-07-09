<?php declare(strict_types = 1);

namespace OriNette\HttpClient;

use Nyholm\Psr7\Request;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;

/**
 * @internal
 */
final class ConfigurableRequestFactory implements RequestFactoryInterface
{

	/** @var array<string, string|array<int, string>> */
	private array $headers;

	/**
	 * @param array<string, string|array<int, string>> $headers
	 */
	public function __construct(array $headers)
	{
		$this->headers = $headers;
	}

	/**
	 * {@inheritDoc}
	 */
	public function createRequest(string $method, $uri): RequestInterface
	{
		$request = new Request($method, $uri);

		foreach ($this->headers as $name => $value) {
			$request = $request->withHeader($name, $value);
		}

		return $request;
	}

}
