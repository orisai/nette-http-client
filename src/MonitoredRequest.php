<?php declare(strict_types = 1);

namespace OriNette\HttpClient;

use Symfony\Contracts\HttpClient\ResponseInterface;

final class MonitoredRequest
{

	private string $method;

	private string $url;

	/** @var array<mixed> */
	private array $options;

	private ResponseInterface $response;

	/**
	 * @param array<mixed> $options
	 */
	public function __construct(
		string $method,
		string $url,
		array $options,
		ResponseInterface $response
	)
	{
		$this->method = $method;
		$this->url = $url;
		$this->options = $options;
		$this->response = $response;
	}

	public function getMethod(): string
	{
		return $this->method;
	}

	public function getUrl(): string
	{
		return $this->url;
	}

	/**
	 * @return array<mixed>
	 */
	public function getOptions(): array
	{
		return $this->options;
	}

	public function getResponse(): ResponseInterface
	{
		return $this->response;
	}

}
