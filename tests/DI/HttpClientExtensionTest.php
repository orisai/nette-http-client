<?php declare(strict_types = 1);

namespace Tests\OriNette\HttpClient\DI;

use Nyholm\Psr7\Factory\Psr17Factory;
use OriNette\DI\Boot\ManualConfigurator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use function dirname;

final class HttpClientExtensionTest extends TestCase
{

	public function testClientWiring(): void
	{
		$configurator = new ManualConfigurator(dirname(__DIR__, 2));
		$configurator->setDebugMode(true);

		$configurator->addConfig(__DIR__ . '/wiring.neon');

		$container = $configurator->createContainer();

		self::assertFalse($container->isCreated('httpClient.nyholm.psr17Factory'));
		self::assertFalse($container->isCreated('httpClient.symfony.client'));

		$client = $container->getByType(ClientInterface::class);
		self::assertInstanceOf(Psr18Client::class, $client);
		self::assertSame($client, $container->getService('httpClient.client'));

		self::assertTrue($container->isCreated('httpClient.nyholm.psr17Factory'));
		self::assertTrue($container->isCreated('httpClient.symfony.client'));
		self::assertInstanceOf(HttpClientInterface::class, $container->getService('httpClient.symfony.client'));
	}

	public function testFactoryWiring(): void
	{
		$configurator = new ManualConfigurator(dirname(__DIR__, 2));
		$configurator->setDebugMode(true);

		$configurator->addConfig(__DIR__ . '/wiring.neon');

		$container = $configurator->createContainer();

		$factory = $container->getService('httpClient.nyholm.psr17Factory');
		self::assertInstanceOf(Psr17Factory::class, $factory);

		self::assertInstanceOf(
			RequestFactoryInterface::class,
			$container->getByType(RequestFactoryInterface::class),
		);
		self::assertInstanceOf(
			ResponseFactoryInterface::class,
			$container->getByType(ResponseFactoryInterface::class),
		);
		self::assertInstanceOf(
			ServerRequestFactoryInterface::class,
			$container->getByType(ServerRequestFactoryInterface::class),
		);
		self::assertInstanceOf(
			StreamFactoryInterface::class,
			$container->getByType(StreamFactoryInterface::class),
		);
		self::assertInstanceOf(
			UploadedFileFactoryInterface::class,
			$container->getByType(UploadedFileFactoryInterface::class),
		);
		self::assertInstanceOf(
			UriFactoryInterface::class,
			$container->getByType(UriFactoryInterface::class),
		);
	}

}
