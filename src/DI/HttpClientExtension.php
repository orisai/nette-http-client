<?php declare(strict_types = 1);

namespace OriNette\HttpClient\DI;

use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nyholm\Psr7\Factory\Psr17Factory;
use OriNette\HttpClient\MonitoringHttpClient;
use OriNette\HttpClient\Tracy\HttpClientPanel;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use stdClass;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Tracy\Bar;

/**
 * @property-read stdClass $config
 */
final class HttpClientExtension extends CompilerExtension
{

	private ServiceDefinition $monitoringClientDefinition;

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'debug' => Expect::structure([
				'panel' => Expect::bool(false),
			]),
			'tls' => Expect::structure([
				// verify_host
				'verifyHost' => Expect::bool(true),
				// verify_peer
				'verifyPeer' => Expect::bool(true),
			]),
		]);
	}

	public function loadConfiguration(): void
	{
		parent::loadConfiguration();

		$builder = $this->getContainerBuilder();
		$config = $this->config;

		$psr17FactoryDefinition = $this->registerFactory($builder);
		$this->registerClient($psr17FactoryDefinition, $config, $builder);
	}

	private function registerFactory(ContainerBuilder $builder): ServiceDefinition
	{
		return $builder->addDefinition($this->prefix('factory'))
			->setFactory(Psr17Factory::class)
			->setAutowired([
				RequestFactoryInterface::class,
				ResponseFactoryInterface::class,
				ServerRequestFactoryInterface::class,
				StreamFactoryInterface::class,
				UploadedFileFactoryInterface::class,
				UriFactoryInterface::class,
			]);
	}

	private function registerClient(
		ServiceDefinition $psr17FactoryDefinition,
		stdClass $config,
		ContainerBuilder $builder
	): void
	{
		$symfonyClientDefinition = $builder->addDefinition($this->prefix('symfony.client'))
			->setFactory(
				HttpClient::class . '::create',
				[
					[
						'verify_host' => $config->tls->verifyHost,
						'verify_peer' => $config->tls->verifyPeer,
					],
				],
			)
			->setAutowired(false);

		if ($config->debug->panel) {
			$monitoringClientDefinition = $builder->addDefinition($this->prefix('monitoring.client'))
				->setFactory(MonitoringHttpClient::class, [
					$symfonyClientDefinition,
				])
				->setAutowired(false);
			$this->monitoringClientDefinition = $monitoringClientDefinition;
		} else {
			$monitoringClientDefinition = null;
		}

		$builder->addDefinition($this->prefix('client'))
			->setFactory(Psr18Client::class, [
				$monitoringClientDefinition ?? $symfonyClientDefinition,
				$psr17FactoryDefinition,
				$psr17FactoryDefinition,
			])
			->setAutowired([
				ClientInterface::class,
			]);
	}

	public function beforeCompile(): void
	{
		parent::beforeCompile();

		$builder = $this->getContainerBuilder();
		$config = $this->config;

		$this->registerTracyPanel($config, $builder);
	}

	private function registerTracyPanel(stdClass $config, ContainerBuilder $builder): void
	{
		if (!$config->debug->panel) {
			return;
		}

		$this->monitoringClientDefinition->addSetup(
			[self::class, 'setupPanel'],
			[
				"$this->name.panel",
				$builder->getDefinitionByType(Bar::class),
				$this->monitoringClientDefinition,
			],
		);
	}

	public static function setupPanel(
		string $name,
		Bar $bar,
		MonitoringHttpClient $httpClient
	): void
	{
		$bar->addPanel(
			new HttpClientPanel($httpClient),
			$name,
		);
	}

}
