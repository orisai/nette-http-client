<?php declare(strict_types = 1);

namespace OriNette\HttpClient\Tracy;

use OriNette\HttpClient\MonitoringHttpClient;
use Tracy\Helpers;
use Tracy\IBarPanel;
use function count;

final class HttpClientPanel implements IBarPanel
{

	private MonitoringHttpClient $httpClient;

	public function __construct(MonitoringHttpClient $httpClient)
	{
		$this->httpClient = $httpClient;
	}

	public function getTab(): string
	{
		return Helpers::capture(function (): void {
			$requestsCount = count($this->httpClient->getMonitoredRequests());

			if ($requestsCount === 0) {
				return;
			}

			require __DIR__ . '/HttpClient.tab.phtml';
		});
	}

	public function getPanel(): string
	{
		return Helpers::capture(function (): void {
			$requests = $this->httpClient->getMonitoredRequests();

			if (count($requests) === 0) {
				return;
			}

			require __DIR__ . '/HttpClient.panel.phtml';
		});
	}

}
