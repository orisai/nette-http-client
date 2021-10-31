<?php declare(strict_types = 1);

namespace OriNette\HttpClient\DI;

use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use Nyholm\Psr7\Factory\Psr17Factory;
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

/**
 * @property-read stdClass $config
 */
final class HttpClientExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([]);
	}

	public function loadConfiguration(): void
	{
		parent::loadConfiguration();

		$builder = $this->getContainerBuilder();

		$psr17FactoryDefinition = $this->registerPsr17Factory($builder);
		$this->registerPsr18Client($psr17FactoryDefinition, $builder);
	}

	private function registerPsr17Factory(ContainerBuilder $builder): ServiceDefinition
	{
		return $builder->addDefinition($this->prefix('nyholm.psr17Factory'))
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

	private function registerPsr18Client(ServiceDefinition $psr17FactoryDefinition, ContainerBuilder $builder): void
	{
		$symfonyClientDefinition = $builder->addDefinition($this->prefix('symfony.client'))
			->setFactory(HttpClient::class . '::create')
			->setAutowired(false);

		$builder->addDefinition($this->prefix('client'))
			->setFactory(Psr18Client::class, [
				$symfonyClientDefinition,
				$psr17FactoryDefinition,
				$psr17FactoryDefinition,
			])
			->setAutowired([
				ClientInterface::class,
			]);
	}

}
