<?php declare(strict_types = 1);

namespace OriNette\HttpClient;

use Symfony\Component\HttpClient\AsyncDecoratorTrait;
use Symfony\Component\HttpClient\Response\AsyncResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Symfony\Contracts\Service\ResetInterface;

final class MonitoringHttpClient implements HttpClientInterface, ResetInterface
{

	use AsyncDecoratorTrait;

	/** @var array<MonitoredRequest> */
	private array $monitoredRequests = [];

	/**
	 * {@inheritdoc}
	 *
	 * @param array<mixed> $options
	 */
	public function request(string $method, string $url, array $options = []): ResponseInterface
	{
		$response = new AsyncResponse($this->client, $method, $url, $options);

		$this->monitoredRequests[] = new MonitoredRequest($method, $url, $options, $response);

		return $response;
	}

	public function reset(): void
	{
		$this->monitoredRequests = [];
	}

	/**
	 * @return array<MonitoredRequest>
	 */
	public function getMonitoredRequests(): array
	{
		return $this->monitoredRequests;
	}

}
